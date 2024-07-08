<?php

namespace App\Controllers;

class Periodos extends BaseController
{
    function __construct() {
        $this->data[ "menu" ] = "admin";
    }

    public function listado( $modelo ){
        $this->data[ "navbar" ]   = true;
        $this->data[ "modelo" ]   = $modelo;
        $this->data[ "titulo" ]   = "Periodos";
        $this->data[ "periodos" ] = model( "PeriodoModel" )->where( "modelo_codigo = '{$modelo}'" )->findAll();

        echo template( "periodos/listado", $this->data );
    }

    public function detalle( $periodo ){
        $this->data[ "navbar" ]  = true;
        $this->data[ "periodo" ] = model( "PeriodoModel" )->find( $periodo );
        $estatus = ESTATUS[ $this->data[ "periodo" ][ "estatus_codigo" ] ];


        $this->data[ "titulo" ]  = "Detalles de periodo <span class=\"badge bg-teal\">{$this->data[ "periodo" ][ "modelo_codigo" ]}</span> <span class=\"badge bg-marine\">".periodo( $this->data[ "periodo" ][ "codigo" ] )."</span> <span class=\"badge bg-{$estatus[ "color" ]}\">{$estatus[ "descripcion" ]}</span>";

        $sql = $this->data[ "periodo" ][ "estatus_codigo" ] == "250-EN-PROCESO" ? "estatus_codigo = '255-PENDIENTE'" : "json_unquote( json_extract( data, '$.periodos.creacion' ) ) = '{$this->data[ "periodo" ][ "codigo" ]}' OR json_unquote( json_extract( data, '$.periodos.deposito' ) ) = '{$this->data[ "periodo" ][ "codigo" ]}'";

        $this->data[ "pagos" ]   = model( "PagoModel" )->where( $sql )->findAll();

        $this->data[ "t" ] = [
            "previos"   => [],
            "actual"    => [],
            "siguiente" => [],
            "extras"    => []
        ];

        foreach( $this->data[ "pagos" ] as $p ){
            $p[ "s" ] = model( "usuarioModel" )->find( $p[ "usuario_id" ] );

            // previos
            if( $p[ "data" ][ "periodos" ][ "creacion" ] < $this->data[ "periodo" ][ "codigo" ] && $p[ "s" ]->verificado->estatus ){
                $this->data[ "t" ][ "previos" ][] = $p;
            }

            // actual
            elseif( ( $p[ "estatus_codigo" ] == "255-PENDIENTE" && $p[ "s" ]->verificado->estatus ) || $p[ "data" ][ "periodos" ][ "deposito" ] == $this->data[ "periodo" ][ "codigo" ] ){
                $this->data[ "t" ][ "actual" ][] = $p;
            }

            // siguiente
            elseif( ( $p[ "estatus_codigo" ] == "255-PENDIENTE" && !$p[ "s" ]->verificado->estatus ) || $p[ "data" ][ "periodos" ][ "deposito" ] > $this->data[ "periodo" ][ "codigo" ] ){
                $this->data[ "t" ][ "siguiente" ][] = $p;
            }

            // extras
            else{
                $this->data[ "t" ][ "extras" ][] = $p;
            }
        }

        echo template( "periodos/detalle", $this->data );
    }


    public function corte(){
        if(VARIABLES[ "avance_corte" ][ "valor" ][ "porcentaje" ] != 100 ){
            $respuesta = [
                "error" => VARIABLES[ "avance_corte" ][ "valor" ]
            ];
        }
        else{
            extract( $this->request->getPost() );
            $respuesta = [
                "periodo" => $periodo
            ];
            $periodo = model( "PeriodoModel" )->find( $periodo );

            $db = db_connect();
           
            /*
            POR AHORA EL CORTE JALARÁ TODOS LOS PEDIDOS DEL PERIODO SIN EXCEPCION
            MAS ADELANTE APRA OPTIMIZAR EL PROCESO SE DEBE LIMITAR LA CONSULA SOLO A LOS PEDIDOS QUE
            ESTEN MARCADOS PARA RECALCULAR, ES DECIR LOS QUE TENGAN SOCIOS EN MORADO EN SU UPLINE O SOCIOS 
            QUE HAYAN HECHO COMPRAS PARA MES ANTERIOR, Y POR ULTIMO Y COMO UNA SEGUNDA OPTIMIZACIÓN
            PARA AGILIZARLO AUN MAS SE DEBE DE TRASLADAR EL CALCULO A UN PROCEDIMIENTO EN LA BASE DE DATOS 
            QUE HAGA TODO EL PROCESO SIN NECESIDAD DE EXTRAER Y REINSERTAR LA INFORMACIÓN EN UN
            CICLO DE PHP. QUEDA PENDIENTE TODO ESTO POR AHORA
            */

            $sql = "SELECT f_reparte_comisiones( pd.id, 1 )
                    FROM t_pedidos pd
                    WHERE SUBSTRING( pd.estatus_codigo, 1, 3 ) > 400 
                    AND '{$periodo[ "modelo_codigo" ]}' = pd.modelo_codigo COLLATE utf8mb4_0900_ai_ci
                    AND fechas->>'$.califica' between '{$periodo[ "inicia" ]}' AND '{$periodo[ "termina" ]}';";
            $db->query( $sql );
            $res = $db->query( "SELECT f_genera_pagos( '{$periodo[ "codigo" ]}' ) as resultado;" )->getRow();
            echo $res->resultado;

            $periodo[ "data" ] = json_decode( $res->resultado );
            model( "PeriodoModel" )->save( $periodo );
        }
    } 
    
    
    public function cierra_periodo(){
        extract( $this->request->getPost() );

        $periodo = model( "PeriodoModel" )->find( $periodo );

        if( $periodo[ "estatus_codigo" ] == '250-EN-PROCESO' ){
            $db  = db_connect();
            $sql = "UPDATE t_pagos p
                    JOIN t_usuarios u ON u.id = p.usuario_id
                    SET p.data = JSON_SET( p.data, '$.periodos.deposito', '{$periodo[ "codigo" ]}' ), 
                        p.estatus_codigo  = '420-PAGADO'
                    WHERE p.modelo_codigo = '{$periodo[ "modelo_codigo" ]}' 
                    AND p.estatus_codigo  = '255-PENDIENTE' 
                    AND p.data->>'$.periodos.creacion' <= '{$periodo[ "codigo" ]}' 
                    AND JSON_EXTRACT( f_es_verificado( u.id ), '$.estatus' ) ";
            $db->query( $sql );

            $periodo[ "estatus_codigo" ] = "305-CERRADO";
            model( "PeriodoModel" )->save( $periodo );
        }
    }    
    
    public function abre_periodo(){
        extract( $this->request->getPost() );

        $periodo = model( "PeriodoModel" )->find( $periodo );

        if( $periodo[ "estatus_codigo" ] == '305-CERRADO' ){
            $db  = db_connect();
            $sql = "UPDATE t_pagos p
                    SET p.data = JSON_SET( p.data, '$.periodos.deposito', '' ), 
                        p.estatus_codigo  = '255-PENDIENTE'
                    WHERE p.modelo_codigo = '{$periodo[ "modelo_codigo" ]}' 
                    AND p.estatus_codigo  = '420-PAGADO' 
                    AND p.data->>'$.periodos.deposito' = '{$periodo[ "codigo" ]}'";
            $db->query( $sql );

            $periodo[ "estatus_codigo" ] = "250-EN-PROCESO";
            model( "PeriodoModel" )->save( $periodo );
        }
    }
}

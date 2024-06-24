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

        $this->data[ "titulo" ]  = "Detalles de periodo <span class=\"badge bg-teal\">{$this->data[ "periodo" ][ "modelo_codigo" ]}</span> <span class=\"badge bg-marine\">".periodo($this->data[ "periodo" ][ "codigo" ])."</span>";

        $this->data[ "pagos" ]   = model( "PagoModel" )->where( "json_unquote( json_extract( data, '$.periodos.creacion' ) ) = '{$periodo}'" )->findAll();

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

            $sql = "SELECT f_reparte_comisiones( pd.id )
                    FROM t_pedidos pd
                    WHERE SUBSTRING( pd.estatus_codigo, 1, 3 ) > 400 
                    AND pd.modelo_codigo = '{$periodo[ "modelo_codigo" ]}'
                    AND fechas->>'$.califica' between '{$periodo[ "inicia" ]}' AND '{$periodo[ "termina" ]}';";
            $db->query( $sql );
            $res = $db->query( "SELECT f_genera_pagos( '{$periodo[ "codigo" ]}' ) as resultado;" )->getRow();
            echo $res->resultado;

            $periodo[ "data" ] = json_decode( $res->resultado );
            model( "PeriodoModel" )->save( $periodo );
        }
    }    
}

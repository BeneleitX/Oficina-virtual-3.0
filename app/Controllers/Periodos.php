<?php

namespace App\Controllers;
require '../vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;


class Periodos extends BaseController
{
    function __construct() {
        $this->data[ "menu" ] = "admin";
    }

    public function listado( $modelo ){
        if( !(
            $this->data[ "usuario" ]->permiso( "38-CONTABILIDAD" )
        ) ){
            return redirect()->to( "inicio" ); 
        }
        
        /**********************************/

        $this->data[ "navbar" ]   = true;
        $this->data[ "modelo" ]   = $modelo;
        $this->data[ "titulo" ]   = "Periodos";
        $this->data[ "periodos" ] = model( "PeriodoModel" )->where( "inicia > '2024-08-05' and modelo_codigo = '{$modelo}'" )->findAll();

        echo template( "periodos/listado", $this->data );
    }

    public function detalle( $periodo ){
        if( !(
            $this->data[ "usuario" ]->permiso( "38-CONTABILIDAD" )
        ) ){
            return redirect()->to( "inicio" ); 
        }
        
        /**********************************/


        $this->data[ "navbar" ]  = true;
        $this->data[ "periodo" ] = model( "PeriodoModel" )->find( $periodo );
        $estatus = ESTATUS[ $this->data[ "periodo" ][ "estatus_codigo" ] ];


        $this->data[ "titulo" ]  = "Detalles de periodo <span class=\"badge bg-teal\">{$this->data[ "periodo" ][ "modelo_codigo" ]}</span> <span class=\"badge bg-marine\">".periodo( $this->data[ "periodo" ][ "codigo" ] )."</span> <span class=\"badge bg-{$estatus[ "color" ]}\">{$estatus[ "descripcion" ]}</span>";

        $sql = "inicia > '2024-08-05' and substring(estatus_codigo, 1, 3) < 400 AND modelo_codigo = '{$this->data[ "periodo" ][ "modelo_codigo" ]}' and codigo < '{$this->data[ "periodo" ][ "codigo" ]}'";
        $this->data[ "pendientes" ] = sizeof( model( "PeriodoModel" )->where( $sql )->findAll() );


        $sql = "( modelo_codigo = '{$this->data[ "periodo" ][ "modelo_codigo" ]}' and ".substr( $this->data[ "periodo" ][ "estatus_codigo" ], 0, 3 ) <= 400 ? "estatus_codigo = '255-PENDIENTE'" : "json_unquote( json_extract( data, '$.periodos.creacion' ) ) = '{$this->data[ "periodo" ][ "codigo" ]}' OR json_unquote( json_extract( data, '$.periodos.deposito' ) ) = '{$this->data[ "periodo" ][ "codigo" ]}'";

        // cerrado
        if( substr( $this->data[ "periodo" ][ "estatus_codigo" ], 0, 3 ) > 400 ){
            $sql = "( json_unquote( json_extract( data, '$.periodos.creacion' ) ) = '{$this->data[ "periodo" ][ "codigo" ]}' OR ( json_unquote( json_extract( data, '$.periodos.deposito' ) ) = '{$this->data[ "periodo" ][ "codigo" ]}' )";
        }

        // abierto
        else{
            $sql = "( json_unquote( json_extract( data, '$.periodos.creacion' ) ) = '{$this->data[ "periodo" ][ "codigo" ]}' OR ( estatus_codigo = '330-EN-ESPERA' AND json_unquote( json_extract( data, '$.periodos.creacion' ) ) < '{$this->data[ "periodo" ][ "codigo" ]}' )" ;
        }

        $sql .= ") AND modelo_codigo = '{$this->data[ "periodo" ][ "modelo_codigo" ]}'";

        $this->data[ "pagos" ] = model( "PagoModel" )->where( $sql )->findAll();

        $this->data[ "t" ] = [
            "previos"   => [],
            "actual"    => [],
            "siguiente" => [],
            "extras"    => []
        ];

        foreach( $this->data[ "pagos" ] as $p ){

            if( $p[ "data" ][ "periodos" ][ "creacion" ] <= $this->data[ "periodo" ][ "codigo" ] ){

                $p[ "s" ] = model( "usuarioModel" )->find( $p[ "usuario_id" ] );

                // previos
                if( $p[ "data" ][ "periodos" ][ "creacion" ] < $this->data[ "periodo" ][ "codigo" ] && $p[ "s" ]->verificado->estatus ){
                    $this->data[ "t" ][ "previos" ][] = $p;
                }

                // actual
                elseif( $p[ "data" ][ "periodos" ][ "creacion" ] == $this->data[ "periodo" ][ "codigo" ] && (  ( substr( $this->data[ "periodo" ][ "estatus_codigo" ], 0, 3 ) <= 400 && $p[ "s" ]->verificado->estatus ) OR ( substr( $this->data[ "periodo" ][ "estatus_codigo" ], 0, 3 ) > 400 &&  $p[ "data" ][ "periodos" ][ "deposito" ] == $this->data[ "periodo" ][ "codigo" ] ) ) ){
                    $this->data[ "t" ][ "actual" ][] = $p;
                }

                // siguiente
                elseif( $p[ "data" ][ "periodos" ][ "creacion" ] == $this->data[ "periodo" ][ "codigo" ] && $p[ "data" ][ "periodos" ][ "deposito" ] != $this->data[ "periodo" ][ "codigo" ]){
                    $this->data[ "t" ][ "siguiente" ][] = $p;
                }
                else{
                    if( $p[ "s" ]->verificado->estatus ){
                        $this->data[ "t" ][ "extras" ][] = $p;
                    }
                }            
    
            }
            // extras
            else{
                $this->data[ "t" ][ "extras" ][] = $p;
            }            
        }

        echo template( "periodos/detalle", $this->data );
    }


    public function reset_corte(){
        if( !(
            $this->data[ "usuario" ]->permiso( "38-CONTABILIDAD" )
        ) ){
            return redirect()->to( "inicio" ); 
        }
        
        /**********************************/


        $db = db_connect();
        $db->query( "UPDATE t_variables SET valor = JSON_SET( valor, '$.porcentaje_comisiones', 0, '$.porcentaje_pagos', 0 ) WHERE codigo = 'avance_corte'" );

        $pedidos = $db->query( "
            SELECT count(*) as pedidos FROM t_pedidos pd JOIN t_periodos pe ON codigo = '".$this->request->getPost( "periodo" )."'
            WHERE SUBSTRING( pd.estatus_codigo, 1, 3 ) > 400 
            AND pe.modelo_codigo = pd.modelo_codigo COLLATE utf8mb4_0900_ai_ci
            AND CAST( pd.fechas->>'$.pagado' AS DATE ) between pe.inicia AND pe.termina;" )->getRow()->pedidos;

        $db->query( "CALL p_avance_corte( json_object( 'periodo', '".$this->request->getPost( "periodo" )."',
                    'pedidos', 0,
                    'pagos', 0,
                    'socios', 0,
                    'comisiones', 0,
                    'isr', 0,
                    'total', 0,
                    'total_pedidos', {$pedidos}, 
                    'total_pagos', 0,
                    'porcentaje_comisiones', 0,
                    'porcentaje_pagos', 0 ) );
                " );

        return json_encode( [ "pedidos" => $pedidos ] );
    }


    public function corte(){
        if( !(
            $this->data[ "usuario" ]->permiso( "38-CONTABILIDAD" )
        ) ){
            return redirect()->to( "inicio" ); 
        }
        /**********************************/

        extract( $this->request->getPost() );
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

        // BITACORA corte parcial / corte semanal      
        bitacora( 43, $this->data[ "usuario" ]->id, [
            "periodo" => $periodo,
            "avance" => $avance
        ] );

        $db->query( "call p_genera_pagos( '{$periodo}', {$avance}, {$step} )" );
    } 
    
    
    public function cierra_periodo(){
        if( !(
            $this->data[ "usuario" ]->permiso( "38-CONTABILIDAD" )
        ) ){
            return redirect()->to( "inicio" ); 
        }
        
        /**********************************/


        extract( $this->request->getPost() );

        $periodo = model( "PeriodoModel" )->find( $periodo );

        if( $periodo[ "estatus_codigo" ] == '255-PENDIENTE' ){
            $db  = db_connect();
            $sql = "UPDATE t_pagos p
                    JOIN t_usuarios u ON u.id = p.usuario_id
                    SET p.data = JSON_SET( p.data, '$.periodos.deposito', '{$periodo[ "codigo" ]}' ), 
                        p.estatus_codigo  = '330-EN-ESPERA'
                    WHERE p.modelo_codigo = '{$periodo[ "modelo_codigo" ]}' 
                    AND p.estatus_codigo  = '250-EN-PROCESO' 
                    AND p.data->>'$.periodos.creacion' <= '{$periodo[ "codigo" ]}' 
                    AND JSON_EXTRACT( f_es_verificado( u.id ), '$.estatus' ) ";
            $db->query( $sql );

            // BITACORA Cierra semana  
            bitacora( 44, $this->data[ "usuario" ]->id, [
                "periodo" => $periodo[ "codigo" ]
            ] );

            $periodo[ "estatus_codigo" ] = "306-PERIODO-CERRADO";
            model( "PeriodoModel" )->save( $periodo );
        }
    }   
    
    
    public function marca_pagado(){
        if( !(
            $this->data[ "usuario" ]->permiso( "38-CONTABILIDAD" )
        ) ){
            return redirect()->to( "inicio" ); 
        }
        
        /**********************************/


        extract( $this->request->getPost() );

        $periodo = model( "PeriodoModel" )->find( $periodo );

        if( $periodo[ "estatus_codigo" ] == '306-PERIODO-CERRADO' ){
            $db  = db_connect();
            $sql = "UPDATE t_pagos p
                    SET p.estatus_codigo  = '420-PAGADO'
                    WHERE p.modelo_codigo = '{$periodo[ "modelo_codigo" ]}' 
                    AND p.estatus_codigo  = '330-EN-ESPERA' 
                    AND p.data->>'$.periodos.deposito' = '{$periodo[ "codigo" ]}'";
            $db->query( $sql );

            // BITACORA marca periodo como pagado
            bitacora( 45, $this->data[ "usuario" ]->id, [
                "periodo" => $periodo[ "codigo" ]
            ] );

            $periodo[ "estatus_codigo" ] = "422-PERIODO-PAGADO";
            model( "PeriodoModel" )->save( $periodo );
        }
    }   
    
    
    public function abre_periodo(){
        if( !(
            $this->data[ "usuario" ]->permiso( "38-CONTABILIDAD" )
        ) ){
            return redirect()->to( "inicio" ); 
        }
        
        /**********************************/


        extract( $this->request->getPost() );

        $periodo = model( "PeriodoModel" )->find( $periodo );

        if( $periodo[ "estatus_codigo" ] == '306-PERIODO-CERRADO' ){
            $db  = db_connect();
            $sql = "UPDATE t_pagos p
                    SET p.data = JSON_SET( p.data, '$.periodos.deposito', '' ), 
                        p.estatus_codigo  = '250-EN-PROCESO'
                    WHERE p.modelo_codigo = '{$periodo[ "modelo_codigo" ]}' 
                    AND p.estatus_codigo  = '330-EN-ESPERA' 
                    AND p.data->>'$.periodos.deposito' = '{$periodo[ "codigo" ]}'";
            $db->query( $sql );

            // BITACORA Abre semana  
            bitacora( 45, $this->data[ "usuario" ]->id, [
                "periodo" => $periodo[ "codigo" ]
            ] );

            $periodo[ "estatus_codigo" ] = "255-PENDIENTE";
            model( "PeriodoModel" )->save( $periodo );
        }
    }


    public function excel_corte(){
        if( !(
            $this->data[ "usuario" ]->permiso( "38-CONTABILIDAD" )
        ) ){
            return redirect()->to( "inicio" ); 
        }
        
        /**********************************/


        $periodo = model( "PeriodoModel" )->find( $this->request->getPost( "periodo" ) );

        $db  = db_connect();
        $sql = "SELECT p.id AS pago_id, p.usuario_id, p.clabe, p.data as p_data, u.data as u_data, f_es_verificado( u.id) AS verificado, b.nombre as banco
                FROM t_pagos p LEFT JOIN t_usuarios u ON u.id = p.usuario_id left join t_bancos b on b.codigo = substring( p.clabe, 1, 3 )
                where ( json_unquote( json_extract( p.data, '$.periodos.creacion' ) ) = '{$periodo[ "codigo" ]}' OR ( json_unquote( json_extract( p.data, '$.periodos.deposito' ) ) = '{$periodo[ "codigo" ]}' ) ) AND modelo_codigo = '{$periodo[ "modelo_codigo" ]}' and substring( p.estatus_codigo, 1, 3 ) > 300 order by p.usuario_id asc";

        $pagos = $db->query( $sql )->getResultArray();

        $sheetData = [ 
            0 => [ [ "PAGO", "ID", "SOCIO", "RFC", "CLABE", "REF NUMÉRICA", "REF ALFANUMÉRICA", "BANCO", "SUBTOTAL", "ISR", "TOTAL", "DESCRIPCIÓN" ] ],
            1 => [ [ "PAGO", "ID", "BENEFICIARIO", "CLABE", "REF NUMÉRICA", "REF ALFANUMÉRICA", "SUBTOTAL", "IMPORTE", "RETENCIÓN DE IVA 10.66%", "IVA 16%", "TOTAL", "DESCRIPCIÓN", "CONCEPTO DE FACTURA" ] ],
            2 => [ [ "PAGO", "ID", "SOCIO", "RFC", "CLABE", "REF NUMÉRICA", "REF ALFANUMÉRICA", "BANCO", "SUBTOTAL", "ISR", "TOTAL", "DESCRIPCIÓN" ] ] 
        ];

        foreach( $pagos as $pago ){
            
            $pago[ "p_data" ] = json_decode( $pago[ "p_data" ], 1 );
            $pago[ "u_data" ] = json_decode( $pago[ "u_data" ], 1 );
            $pago[ "verificado" ] = json_decode( $pago[ "verificado" ], 1 );
            
            if( $pago[ "p_data" ][ "retencion" ] == 100 ){
                $sheetData[ 0 ][] = $datos;
            }
            elseif( $pago[ "p_data" ][ "retencion" ] == 1 ){
                $sheetData[ 2 ][] = [
                    $pago[ "pago_id" ],
                    $pago[ "usuario_id" ],
                    $pago[ "u_data" ][ "nombre" ]." ".implode( " ", $pago[ "u_data" ][ "apellidos" ] ),
                    $pago[ "clabe" ],
                    30,
                    "PAGO SEMANA ".periodo( $periodo[ "codigo" ] ),
                    $pago[ "banco" ],
                    number_format( $pago[ "p_data" ][ "cantidades" ][ "subtotal" ], 2 ),
                    number_format( $pago[ "p_data" ][ "cantidades" ][ "isr" ], 2 ),
                    number_format( $pago[ "p_data" ][ "cantidades" ][ "total" ], 2 ),
                    strtoupper( "DEL ".date( "d", strtotime( $periodo[ "inicia" ] ) )." DE ".mes( date( "m", strtotime( $periodo[ "inicia" ] ) ) )." AL ".date( "d", strtotime( $periodo[ "termina" ] ) )." DE ".mes( date( "m", strtotime( $periodo[ "termina" ] ) ) ) )
                ];
            }
            elseif( $pago[ "p_data" ][ "retencion" ] == 0 ){
                $sheetData[ 1 ][] = [
                    $pago[ "pago_id" ],
                    $pago[ "usuario_id" ],
                    $pago[ "u_data" ][ "nombre" ]." ".implode( " ", $pago[ "u_data" ][ "apellidos" ] ),
                    $pago[ "u_data" ][ "sat" ][ "rfc" ] ?? "",
                    $pago[ "clabe" ],
                    30,
                    "PAGO SEMANA ".periodo( $periodo[ "codigo" ] ),
                    $pago[ "banco" ],
                    number_format( $pago[ "p_data" ][ "cantidades" ][ "subtotal" ], 2 ),
                    number_format( $pago[ "p_data" ][ "cantidades" ][ "isr" ], 2 ),
                    number_format( $pago[ "p_data" ][ "cantidades" ][ "total" ], 2 ),
                    strtoupper( "DEL ".date( "d", strtotime( $periodo[ "inicia" ] ) )." DE ".mes( date( "m", strtotime( $periodo[ "inicia" ] ) ) )." AL ".date( "d", strtotime( $periodo[ "termina" ] ) )." DE ".mes( date( "m", strtotime( $periodo[ "termina" ] ) ) ) )
                ];
            }
        }

        $data = $periodo[ "data" ];
        $data[ "contador" ] = intval( $data[ "contador" ] ?? 0 ) + 1;

        $time = str_pad( $data[ "contador" ], 2, "0", STR_PAD_LEFT );

        $periodo[ "data" ] = $data;
        model( "PeriodoModel" )->save( $periodo );

        $mySpreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
        $mySpreadsheet->removeSheetByIndex(0);

        $worksheet = [ 
            2 => new \PhpOffice\PhpSpreadsheet\Worksheet\Worksheet($mySpreadsheet, "RET ISR"),
            1 => new \PhpOffice\PhpSpreadsheet\Worksheet\Worksheet($mySpreadsheet, "NO RET"),
            0 => new \PhpOffice\PhpSpreadsheet\Worksheet\Worksheet($mySpreadsheet, "VENTA")
        ];

        $mySpreadsheet->addSheet( $worksheet[ 0 ], 0 );
        $worksheet[ 0 ]->fromArray( $sheetData[ 0 ] );
        $worksheet[ 0 ]->getStyle( "I:K" )->getNumberFormat()->setFormatCode( "$#,##0.00" );

        $mySpreadsheet->addSheet( $worksheet[ 1 ], 0 );
        $worksheet[ 1 ]->fromArray( $sheetData[ 1 ] );
        $worksheet[ 1 ]->getStyle( "I:K" )->getNumberFormat()->setFormatCode( "$#,##0.00" );

        $mySpreadsheet->addSheet( $worksheet[ 2 ], 0 );
        $worksheet[ 2 ]->fromArray( $sheetData[ 2 ] );
        $worksheet[ 2 ]->getStyle( "I:K" )->getNumberFormat()->setFormatCode( "$#,##0.00" );

        foreach( $worksheet as $k => $ws ){
            foreach( $ws->getColumnIterator() as $column ){
                $worksheet[ $k ]->getColumnDimension( $column->getColumnIndex() )->setAutoSize( true );
            }
        }

        // BITACORA descarga excel de corte
        bitacora( 46, $this->data[ "usuario" ]->id, [
            "periodo" => $this->request->getPost( "periodo" ),
            "time" => $time
        ] );

        $path = "assets/archivo/corte/{$periodo[ "modelo_codigo" ]}";
        if( !is_dir( $path ) ) mkdir( $path, 0755, true );

        echo $file = $path."/{$periodo[ "modelo_codigo" ]}_".periodo( $periodo[ "codigo" ] )."_{$time}.xlsx";

        $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($mySpreadsheet);
        $writer->save( $file );
    }

}

// 1722617526
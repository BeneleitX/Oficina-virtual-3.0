<?php
namespace App\Controllers;
require '../vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class Bancos extends BaseController
{
    public function layout()
    {
        $this->data[ "navbar" ] = true;
        $this->data[ "titulo" ] = "Ingreso de pagos referenciados";

        echo template( "bancos/layout", $this->data );
    }


    public function analiza_layout(){

        $archivo   = $_FILES[ "archivo" ][ "name" ];
        $partes = explode( ".", $archivo );
        $extension = array_pop( $partes );
        $path = "assets/archivo/layout/";

        $respuesta = [ 
            "archivo" => $archivo,
            "errores" => [],
            "pagos" => [],
            "conteo" => [
                "lineas" => 0,
                "pagos" => 0,
                "pagados" => 0
            ]
        ];
        
        move_uploaded_file( $_FILES[ "archivo" ][ "tmp_name" ], $path.time()."_".$archivo );        
        $referencias = [];

        // EXCEL
        if( in_array( $extension, [ "xls", "xlsx" ] ) ){
            $respuesta[ "logo_banco" ] = base_url()."assets/img/bancos/127.png";
            $respuesta[ "banco" ] = "AZTECA";

            $spreadsheet = \PhpOffice\PhpSpreadsheet\IOFactory::load( $path.time()."_".$archivo );
            $worksheet = $spreadsheet->getActiveSheet();
            $excel = [];
            foreach ($worksheet->getRowIterator() as $row) {
                $fila = [];
                $cellIterator = $row->getCellIterator();
                $cellIterator->setIterateOnlyExistingCells(FALSE); 

                foreach ($cellIterator as $cell) {
                    $fila[] = trim( $cell->getValue() );
                }

                if( $fila[ 0 ] == "01720107476782" ){
                    $excel[] = $fila;
                }
            }


            foreach( $excel as $line ){
                $respuesta[ "conteo" ][ "lineas" ]++;
                if( strlen( $line[ 3 ] ) < 9 && strlen( $line[ 3 ] ) > 1 ){
                    $respuesta[ "conteo" ][ "pagos" ]++;
                    $valido = 1;

                    $l = [
                        "original" => $line,
                        "fecha" => date( "Y-m-d", strtotime( $line[ 1 ] ) ),
                        "pedido" => substr( $line[ 3 ], 0, -1 ),
                        "referencia" => $line[ 3 ],
                        "folio" => $line[ 6 ],
                        "cantidad" => filter_var( $line[ 4 ], FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION ),
                        "socio" => null,
                        "accion" => null,
                    ];

                    $respuesta[ "pagos" ][ $l[ "referencia" ] ] = $l;
                    $referencias[] = $l[ "pedido" ];
                }
            }

        }

        // TXT
        elseif( $extension == "txt" ){
            $respuesta[ "logo_banco" ] = base_url()."assets/img/bancos/012.png";
            $respuesta[ "banco" ] = "BBVA";

            foreach( file( $path.time()."_".$archivo ) as $line ){
                $respuesta[ "conteo" ][ "lineas" ]++;
                if( str_contains( $line, "CE00000000000" ) ){
                    $respuesta[ "conteo" ][ "pagos" ]++;
                    $valido = 1;

                    // Método 1
                    $l = [
                        "original" => $line,
                        "fecha" => date( "Y-m-d", strtotime( substr( $line, 0, 10) ) ),
                        "pedido" => intval( substr( $line, 13, 19) ),
                        "referencia" => intval( substr( $line, 13, 20) ),
                        "folio" => substr( $line, 34, 7),
                        "resto" => explode( " ", substr( $line, 42) ),
                        "socio" => null,
                        "accion" => null,
                    ];

                    $e = explode( "\t\t", end( $l[ "resto" ] ) );
                    $l[ "cantidad" ] = filter_var( explode( "\t", $e[ 1 ] )[0], FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION );

                    unset( $l[ "resto" ] );
                    $pagos[] = $l;
                
                    // Método 2

                    $e = explode( "\t", $line );

                    if( $l[ "fecha" ] != date( "Y-m-d", strtotime( $e[ 0 ] ) )){
                        $respuesta[ "errores" ][] = "Inconsistencias en fecha | {$line}";
                        $valido = 0;
                    }
            
                    $f = explode( " ", $e[ 1 ] );
                    $g = explode( "/", $f[ 0 ] );
                    
                    if( $l[ "folio" ] != $g[ 1 ] ){
                        $respuesta[ "errores" ][] = "Inconsistencias en operacion | {$line}";
                        $valido = 0;
                    }  
                    
                    if( $l[ "referencia" ] != intval( substr( $g[ 0 ], 2 ) ) ){
                        $respuesta[ "errores" ][] = "Inconsistencias en referencia ( {$l[ "referencia" ]} - ".intval( substr( $g[ 0 ], 2, -1 ) ).")  | {$line}";
                        $valido = 0;
                    }   
                    
                    if( $l[ "cantidad" ] != filter_var( $e[ 3 ], FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION ) ){
                        $respuesta[ "errores" ][] = "Inconsistencias en cantidad | {$line}";
                        $valido = 0;
                    }                       

                    if( $valido ){
                        $respuesta[ "pagos" ][ $l[ "referencia" ] ] = $l;
                        $referencias[] = $l[ "pedido" ];
                    }
                    else{
                        echo json_encode( $respuesta );
                        return;
                    }
                }
            }
        }

        $pedidos = model( "PedidoModel" )->find( $referencias );
            
        if( $pedidos ){
            
            foreach( $pedidos as $p ){

                $u = model( "UsuarioModel" )->find( $p[ "usuario_id" ] );

                if( substr( $p[ "estatus_codigo" ], 0, 3 ) < 300 ){

                    $p[ "estatus_codigo" ] = "420-PAGADO";
                    $p[ "fechas" ][ "pagado" ]   = $respuesta[ "pagos" ][ $p[ "referencia" ] ][ "fecha" ];     
                    $p[ "fechas" ][ "califica" ] = $respuesta[ "pagos" ][ $p[ "referencia" ] ][ "fecha" ];    

                    model( "PedidoModel" )->save( $p );

                    $db = db_connect();
                    $db->query( "select f_update_PTS( {$u->id}, '{$p[ "modelo_codigo" ]}', '".date( "Ym", strtotime( $respuesta[ "pagos" ][ $p[ "referencia" ] ][ "fecha" ] ) )."' )" );  
                    $db->query( "select f_get_estatus( {$u->id}, 0 )" );
                    $afectados = $db->query( "select f_reparte_comisiones( {$p[ "id" ]}, 0 )" )->getRow();
                
                    $respuesta[ "conteo" ][ "pagados" ]++;
                    $respuesta[ "pagos" ][ $p[ "referencia" ] ][ "accion" ] = "<span class=\"badge bg-teal\">pagado</span>";
                }
                else{
                    $respuesta[ "pagos" ][ $p[ "referencia" ] ][ "accion" ] = "<span class=\"badge bg-red\">ya procesado</span>";
                }

                $respuesta[ "pagos" ][ $p[ "referencia" ] ][ "socio" ] = $u->avatar( 24 )." ".$u->id( $p[ "modelo_codigo" ] )." ".$u->nombre( 2 );
            }
        }

        echo json_encode( $respuesta );
    }
}


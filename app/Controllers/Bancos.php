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
/*         $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setCellValue('A1', 'Hello World !');
        $writer = new Xlsx($spreadsheet);

        $modelo = "10-NUTRICION";
        $path = "assets/archivo/corte/".$modelo;

        if( !is_dir( $path ) ){
            mkdir( $path );
        }

        $file = $path."/hello world.xlsx";

        $writer->save("./".$file);        

        echo base_url().$file; */

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

        // EXCEL
        if( in_array( $extension, [ "xls", "xlsx" ] ) ){
            $respuesta[ "logo_banco" ] = base_url()."assets/img/bancos/127.png";
            $respuesta[ "banco" ] = "AZTECA";
        }

        // TXT
        elseif( $extension == "txt" ){
            $respuesta[ "logo_banco" ] = base_url()."assets/img/bancos/012.png";
            $respuesta[ "banco" ] = "BBVA";
            $referencias = [];

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
                        $referencias[] = substr( $l[ "pedido" ], 0, -1 );
                    }
                    else{
                        echo json_encode( $respuesta );
                        return;
                    }
                }
            }

            $pedidos = model( "PedidoModel" )->find( $referencias );
            
            if( $pedidos ){
                
                foreach( $pedidos as $p ){
                    $u = model( "UsuarioModel" )->find( $p[ "usuario_id" ] );
                    $respuesta[ "pagos" ][ $p[ "referencia" ] ][ "socio" ] = $u->id()." ".$u->nombre( 2 );

                    if( $p[ "estatus_codigo "] )

                    $respuesta[ "conteo" ][ "pagados" ]++;
                }
            }
        }

        echo json_encode( $respuesta );
    }
}


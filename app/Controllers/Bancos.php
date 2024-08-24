<?php
namespace App\Controllers;
require '../vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class Bancos extends BaseController
{
    public function layout()
    {
        if( !(
            $this->data[ "usuario" ]->permiso( "38-CONTABILIDAD" ) ||
            $this->data[ "usuario" ]->permiso( "40-ADMIN" )
        ) ){
            return redirect()->to( "inicio" ); 
        }

        $this->data[ "navbar" ] = true;
        $this->data[ "titulo" ] = "Ingreso de pagos referenciados";

        echo template( "bancos/layout", $this->data );
    }


    public function pendientes()
    {
        if( !(
            $this->data[ "usuario" ]->permiso( "38-CONTABILIDAD" ) ||
            $this->data[ "usuario" ]->permiso( "40-ADMIN" )
        ) ){
            return redirect()->to( "inicio" ); 
        }

        $this->data[ "navbar" ] = true;
        $this->data[ "titulo" ] = "Pagos ingresados sin destino";

        $db  = db_connect();
        $sql = "select * from t_fondeos where estatus_codigo = '330-EN-ESPERA' order by fecha desc";
        $this->data[ "pendientes" ] = $db->query( $sql );

        echo template( "bancos/pendientes", $this->data );
    }


    public function analiza_layout(){
        if( !(
            $this->data[ "usuario" ]->permiso( "38-CONTABILIDAD" ) ||
            $this->data[ "usuario" ]->permiso( "40-ADMIN" )
        ) ){
            return redirect()->to( "inicio" ); 
        }
        
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
                if( strlen( $line[ 3 ] ) < 10){
                    $respuesta[ "conteo" ][ "pagos" ]++;
                    $valido = 1;

                    $l = [
                        "original" => $line,
                        "fecha" => date( "Y-m-d", strtotime( $line[ 1 ] ) ),
                        "pedido" => strlen( $line[ 3 ] ) > 6 ? substr( $line[ 3 ], 0, -1 ) : null,
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
 
            
        foreach( $respuesta[ "pagos" ] as $refe => $dat ){

            $p = $dat[ "pedido" ] ? model( "PedidoModel" )->find( $dat[ "pedido" ] ) : null;

            $respuesta[ "pagos" ][ $refe ][ "banco" ]  = $respuesta[ "banco" ];
            $respuesta[ "pagos" ][ $refe ][ "costo" ]  = null;
            $respuesta[ "pagos" ][ $refe ][ "comision" ]  = $p[ "data" ][ "comisionbanco" ] ?? null;

            if( $p ){
                $respuesta[ "pagos" ][ $p[ "referencia" ] ][ "modelo" ] = $p[ "modelo_codigo" ];

                $k = substr( $p[ "modelo_codigo" ], 0, 1 );
                $metodopago = model( "MetodopagoModel" )->find( "1{$k}-REFERENCIA" );
        
                $u = model( "UsuarioModel" )->find( $p[ "usuario_id" ] );

                $total = floatval( $p[ "data" ][ "comisionentrega" ] )
                        +floatval( $p[ "data" ][ "total" ] ) 
                        -floatval( $u->data->saldo->{$p[ "modelo_codigo" ]} ) 
                        +floatval( $p[ "data" ][ "comisionbanco" ] );
                        
                $respuesta[ "pagos" ][ $p[ "referencia" ] ][ "costo" ] = $total; //"{$total} = ".floatval( $p[ "data" ][ "comisionentrega" ] )." + ".floatval( $p[ "data" ][ "total" ] )." + ".floatval( $p[ "data" ][ "comisionbanco" ] )." - ".floatval( $u->data->saldo->{$p[ "modelo_codigo" ]} );

                $f = model( "FondeoModel" )->where( "fecha = '{$respuesta[ "pagos" ][ $p[ "referencia" ] ][ "fecha" ]}' AND operacion = '{$respuesta[ "pagos" ][ $p[ "referencia" ] ][ "folio" ]}'" )->first();

                if( !$f ){

                    model( "FondeoModel" )->ignore( true )->save( [
                        "id" => null,
                        "operacion" => $respuesta[ "pagos" ][ $p[ "referencia" ] ][ "folio" ],
                        "fecha" => $respuesta[ "pagos" ][ $p[ "referencia" ] ][ "fecha" ],
                        "estatus_codigo" => "620-RECIBIDO",
                        "metodopago_codigo" => $metodopago[ "codigo" ],
                        "usuario_id" => $u->id ?? null,
                        "cantidad" => $respuesta[ "pagos" ][ $p[ "referencia" ] ][ "cantidad" ],
                        "extras" => $respuesta[ "pagos" ][ $p[ "referencia" ] ]
                    ] );

                    if( $p[ "data" ][ "productos" ] > 0 ){

                        // SI LA CANTIDAD CUBRE EL COSTO

                        if( $total <= $respuesta[ "pagos" ][ $p[ "referencia" ] ][ "cantidad" ] ){
                            if( substr( $p[ "estatus_codigo" ], 0, 3 ) < 300 ){

                                $p[ "estatus_codigo" ] = "420-PAGADO";
                                $p[ "metodopago_codigo" ] = $metodopago[ "codigo" ];
                                $p[ "fechas" ][ "pagado" ]   = $respuesta[ "pagos" ][ $p[ "referencia" ] ][ "fecha" ];     
                                $p[ "fechas" ][ "califica" ] = $respuesta[ "pagos" ][ $p[ "referencia" ] ][ "fecha" ];    

                                $u = model( "UsuarioModel" )->find( $p[ "usuario_id" ] );

                                if( $u->data->saldo->{$p[ "modelo_codigo" ]} ){
                                    $p[ "data" ][ "saldo" ] = $u->data->saldo->{$p[ "modelo_codigo" ]};

                                    $data = $u->data;                                    
                                    $data->saldo->{$p[ "modelo_codigo" ]} = 0;
                                    $u->data = $data;
                                }

                                model( "PedidoModel" )->save( $p );
                               
                                $respuesta[ "conteo" ][ "pagados" ]++;
                                $respuesta[ "pagos" ][ $p[ "referencia" ] ][ "accion" ] = "<span class=\"badge bg-teal\">Pagado OK</span>";

                                $data = $u->data;                                    
                                $historial = $u->historial;  
                            
                                foreach( $p[ "PTS" ] as $promo => $pts ){
                                    if( !is_object( $historial->modelos->{$p[ "modelo_codigo" ]}->primercompra ) ){
                                        $historial->modelos->{$p[ "modelo_codigo" ]}->primercompra = json_decode( '{}' );
                                    }
                    
                                    if( !isset( $historial->modelos->{$p[ "modelo_codigo" ]}->primercompra->{$promo} ) ){
                                        $historial->modelos->{$p[ "modelo_codigo" ]}->primercompra->{$promo} = substr( $p[ "fechas" ][ "califica" ], 0, 10 );
                                    }
                                } 

                                $historial->modelos->{$p[ "modelo_codigo" ]}->ultimacompra = $p[ "fechas" ][ "califica" ];

                                if( $total < $respuesta[ "pagos" ][ $p[ "referencia" ] ][ "cantidad" ] ){
                                    $data->saldo->{$p[ "modelo_codigo" ]} = $data->saldo->{$p[ "modelo_codigo" ]} + $respuesta[ "pagos" ][ $p[ "referencia" ] ][ "cantidad"] - $total;

                                    $u->data = $data;
                                    $u->historial = $historial;
                                }

                                model( "UsuarioModel" )->save( $u );    

                                $db = db_connect();
                                $db->query( "select f_update_PTS( {$u->id}, '{$p[ "modelo_codigo" ]}', '".date( "Ym", strtotime( $respuesta[ "pagos" ][ $p[ "referencia" ] ][ "fecha" ] ) )."' )" );  
                                $db->query( "select f_get_estatus( {$u->id}, 0 )" );
                                $afectados = $db->query( "select f_reparte_comisiones( {$p[ "id" ]}, 0 )" )->getRow();                                    

                            }
                            else{
                                $respuesta[ "pagos" ][ $p[ "referencia" ] ][ "accion" ] = "<span class=\"badge bg-red\">Previo pagado</span>";
                            }
                        }else{
                            $data = $u->data;                                    
                            $data->saldo->{$p[ "modelo_codigo" ]} = $data->saldo->{$p[ "modelo_codigo" ]} + $respuesta[ "pagos" ][ $p[ "referencia" ] ][ "cantidad"];
                            $u->data = $data;
                            model( "UsuarioModel" )->save( $u );    

                            $respuesta[ "pagos" ][ $p[ "referencia" ] ][ "accion" ] = "<span class=\"badge bg-orange\">Insuficiente</span>";
                        }
                    }
                    else{
                        $respuesta[ "pagos" ][ $p[ "referencia" ] ][ "accion" ] = "<span class=\"badge bg-mustard\">Pedido vacío</span>";
                    }
                }
                else{
                    $respuesta[ "pagos" ][ $p[ "referencia" ] ][ "accion" ] = "<span class=\"badge bg-gray-600\">Ya procesada</span>";
                }

                $respuesta[ "pagos" ][ $p[ "referencia" ] ][ "socio" ] = $u->avatar( 24 )." ".$u->id( $p[ "modelo_codigo" ] )." ".$u->nombre( 2 );
            }
            else{
                model( "FondeoModel" )->ignore( true )->save( [
                    "id" => null,
                    "operacion" => $respuesta[ "pagos" ][ $refe ][ "folio" ],
                    "fecha" => $respuesta[ "pagos" ][ $refe ][ "fecha" ],
                    "estatus_codigo" => "330-EN-ESPERA",
                    "metodopago_codigo" => null,
                    "usuario_id" => null,
                    "cantidad" => $respuesta[ "pagos" ][ $refe ][ "cantidad" ],
                    "extras" => $respuesta[ "pagos" ][ $refe ]
                ] );
            }
        }

        echo json_encode( $respuesta );
    }
}


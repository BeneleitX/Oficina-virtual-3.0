<?php

namespace App\Controllers;

class Capital extends BaseController
{
    function __construct() {
        $this->data[ "menu" ] = "admin";
    }
    

    public function admin(){
        if( !(
            $this->data[ "usuario" ]->permiso( "40-ADMIN")
        ) ){
            return redirect()->to( "inicio" ); 
        }
        
        /**********************************/
                
        $this->data[ "navbar" ] = true;
        $this->data[ "titulo" ] = "Capital24";


        echo template( "capital/admin", $this->data );
    }     

    public function dashboard(){
                
        $this->data[ "navbar" ] = true;
        $this->data[ "titulo" ] = "Capital24";


        echo template( "capital/dashboard", $this->data );
    } 


    public function quick_data(){
        $respuesta = [
            "ok" => false,
            "error" => "error"
        ];

        $hash = $this->request->getPost( "hash" );

        if( strlen( $hash ) == 64 ){
            // endpoint GET de tronscan para verificar la transaccion
            // la URL se obtiene de la tabla t_variables
            // devuelve un JSON con la informacion de la transaccion del que tomaremos los datos más importantes

            $vars = ( VARIABLES[ "inversiones" ]["valor"] );

            $curl = curl_init();
            curl_setopt($curl, CURLOPT_URL, str_replace( "%hash%", $hash,  $vars[ "url" ] ) );
            curl_setopt($curl, CURLOPT_RETURNTRANSFER, true); 
            $d = json_decode( curl_exec( $curl ), true );
            curl_close($curl);

            if( sizeof( $d ) ){
                $tx  = [
                    "block"                 => $d[ "block" ], // 68750547,
                    "contractRet"           => $d[ "contractRet" ], // "SUCCESS",
                    "confirmed"             => $d[ "confirmed" ], // true,
                    "icon_url"              => $d[ "tokenTransferInfo" ][ "icon_url" ], // "https://static.tronscan.org/production/logo/usdtlogo.png",  
                    "symbol"                => $d[ "tokenTransferInfo" ][ "symbol" ], // "USDT",
                    "to_address"            => $d[ "tokenTransferInfo" ][ "to_address" ], // "TAr7YFFgxkRs2zEHGm34dcj8M4TqAv2eGP",
                    "name"                  => $d[ "tokenTransferInfo" ][ "name" ], // "Tether USD",
                    "decimals"              => $d[ "tokenTransferInfo" ][ "decimals" ], // 6,
                    "from_address"          => $d[ "tokenTransferInfo" ][ "from_address" ], // "TJy2LR9FFrP7ZQw99CRfHeiFCG2RZUasGF"
                    "amount_str"            => $d[ "tokenTransferInfo" ][ "amount_str" ], // "500000000",
                    "cost" => [
                        "date_created"          => date( "Y-m-d", $d[ "cost" ][ "date_created" ] ), // 1736902989,
                        "net_fee_cost"          => $d[ "cost" ][ "net_fee_cost" ], // 1000,
                        "fee"                   => $d[ "cost" ][ "fee" ], // 0,
                        "energy_fee_cost"       => $d[ "cost" ][ "energy_fee_cost" ], // 210,
                        "net_usage"             => $d[ "cost" ][ "net_usage" ], // 0,
                        "multi_sign_fee"        => $d[ "cost" ][ "multi_sign_fee" ], // 0,
                        "net_fee"               => $d[ "cost" ][ "net_fee" ], // 345000,
                        "energy_penalty_total"  => $d[ "cost" ][ "energy_penalty_total" ], // 49635,
                        "energy_usage"          => $d[ "cost" ][ "energy_usage" ], // 0,
                        "energy_fee"            => $d[ "cost" ][ "energy_fee" ], // 13499850,
                        "energy_usage_total"    => $d[ "cost" ][ "energy_usage_total" ], // 64285,
                        "memoFee"               => $d[ "cost" ][ "memoFee" ], // 0,
                        "origin_energy_usage"   => $d[ "cost" ][ "origin_energy_usage" ] // 0
                    ]
                ];

                $respuesta[ "data" ]    = json_encode( $tx );

                // validamos que sea una transacción confirmada

                if( $tx[ "contractRet" ] == "SUCCESS" && $tx[ "confirmed" ] == true ){
                    
                    // validamos que sea al wallet destino correcto

                    $address = false;
                    foreach( $vars[ "wallets" ] as $wallet ){
                        if( $tx[ "to_address" ] == $wallet[ "token"] && $wallet[ "estatus" ] == "201-ACTIVO" ){
                            $address = true;
                        }
                    }

                    // Si se encontró wallet

                    if( $address ){
                        
                        $inversion = model( "InversionModel" )->find( $this->request->getPost( "inversion" ) );

                        print_r( $inversion );
                        return;
                        $pedido    = model( "PedidoModel" )->find( $inversion[ "pedido_id" ] );

                        // nos aseguramos de que la transacción no haya sido registrada antes

                        $db  = db_connect();
                        $sql = "select count(*) as existe from t_fondeos where operacion = '{$hash}'";

                        if( !$db->query( $sql )->getrow()->existe ){
                            
                            // Al no existir antes, la registramos en la base de datos de fondeos

                            model( "FondeoModel" )->ignore( true )->save( [
                                "operacion"         => $hash, 
                                "fecha"             => $fecha,
                                "estatus_codigo"    => "420-PAGADO",
                                "metodopago_codigo" => $pedido[ "metodopago_codigo" ],
                                "usuario_id"        => $u->id,
                                "referencia"        => $pedido[ "referencia" ],
                                "cantidad"          => $cantidad,
                                "extras"            => $tx
                            ] );

                            $cantidad = $tx[ "amount_str" ] / pow( 10, $tx[ "decimals" ] );
                            $total    = $pedido[ "data" ][ "total" ] - $saldo;
                            

                            $inversion = [
                                "id"                => null,
                                "pedido_id"         => $pedido[ "id" ],
                                "usuario_id"        => $u->id,
                                "producto_codigo"   => $producto->codigo,
                                "cantidad"          => $pedido[ "data" ][ "total" ],
                                "estatus_codigo"    => "625-ACTIVA",
                                "fechas"            => [
                                    "creado"        => $pedido[ "fechas" ][ "creado" ],
                                    "pagado"        => $pedido[ "fechas" ][ "pagado" ],
                                    "inversion"     => $f_i,
                                    "cierre"        => get_fecha_cierre( $f_i )
                                ],
                                "extras"            => [
                                    "TxHash"        => $hash,
                                    "meses"         => [],
                                    "saldo"         => $saldo,
                                    "wallets"       => [
                                        "from"      => $tx[ "from_address" ],
                                        "to"        => $tx[ "to_address" ]
                                    ]
                                ]
                            ];
            
                            $inversion[ "extras" ][ "meses" ] = genera_meses( $pedido, $producto );
            
                            model( "InversionModel" )->save( $inversion );   

                            $respuesta[ "error" ]   = false;
                            $respuesta[ "success" ] = [];
                        }
                        else{
                            $respuesta[ "error" ] = "<h5 class=\"mb-0 text-red text-center\">TxHash ya registrado</h5>Esta transacción ya ha sido registrada anteriormente en la base de datos";
                        }
                    }
                    else{
                        $respuesta[ "error" ] = "<h5 class=\"mb-0 text-red text-center\">Wallet destino incorrecta</h5>La transacción ingresada no tiene como destino alguna wallet de Beneleit / Capital24";
                    }
                }
                else{
                    $respuesta[ "error" ] = "<h5 class=\"mb-0 text-red text-center\">TxHash no confirmado</h5>La transacción no ha sido confirmada en la blockchain";
                }
            }
            else{
                $respuesta[ "error" ] = "<h5 class=\"mb-0 text-red text-center\">TxHash no encontrado</h5>No existe información en la red para el hash ingresado";
            }
        }
        else{
            $respuesta[ "error" ] = "<h5 class=\"mb-0 text-red text-center\">Hash incorrecto</h5>Ingresaste ".strlen( $hash )." caracteres";
        } 

        echo json_encode( $respuesta );
    }
}

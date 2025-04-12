<?php

namespace App\Controllers;

class Capital extends BaseController
{
    function __construct() {
        $this->data[ "menu" ] = "admin";
    }
    

    /**
     * Muestra el listado de solicitudes de retiros de una inversi n Capital24.
     *
     * Requiere el par metro $mes, que es el mes en formato "YYYYMM".
     *
     * Verifica que el usuario logueado tenga permiso de administraci n.
     *
     * Redirecciona a la p gina de no permiso si no se cumple la condici n
     * anterior.
     *
     * @param string $mes
     *
     * @return void
     */
    public function admin( $mes = null )
    {
        if( !(
            $this->data[ "usuario" ]->permiso( "31-GASOLINA" ) ||
            $this->data[ "usuario" ]->permiso( "40-ADMIN")
        ) ){
            return redirect()->to( "no_permiso" ); 
        }
        
        if( !$mes ){
            $mes = date( "Ym", strtotime( date("Y-m-d")." - 1 month" ) );
        }

        /**********************************/
                
        $this->data[ "navbar" ] = true;
        $this->data[ "titulo" ] = "Capital24";
        $this->data[ "mes" ]    = $mes;

        $this->data[ "solicitudes" ] = model( "RetiroModel" )->where( "SUBSTRING( estatus_codigo,1,3) > 200 AND JSON_EXTRACT( fechas, '$.mes' ) = '{$mes}' " )->findAll();

        echo template( "capital/admin", $this->data );
    }


    /**
     * Muestra el dashboard de Capital24.
     *
     * Mostrar&#225; informaci&#243;n de los retiros de cada mes.
     *
     * Requiere el permiso de administraci&#243;n.
     *
     * @return void
     */
    public function dashboard()
    {
                
        $this->data[ "navbar" ] = true;
        $this->data[ "titulo" ] = "Capital24";


        echo template( "capital/dashboard", $this->data );
    }


    public function inversiones()
    {
        if( !(
            $this->data[ "usuario" ]->permiso( "40-ADMIN")
        ) ){
            return redirect()->to( "no_permiso" ); 
        }
        
        $db  = db_connect();

        $this->data[ "navbar" ] = true;
        $this->data[ "titulo" ] = "Dashboard Capital24";
        load_catalogo( "productos", "modelo_codigo = '50-INVERSION' and substring( codigo, 1 ,3 ) > 500");

        $sql = "SELECT count(*) as total from (
                    SELECT any_value(i.id), i.usuario_id, count(*)
                    from t_inversiones i
                    join t_pedidos p on p.id = i.pedido_id and substring( p.estatus_codigo, 1, 3 ) > 400
                    where substring( i.estatus_codigo, 1, 3 ) > 300
                    and cast( now() as date ) between cast( i.fechas->>'$.pagado' as date ) and cast( i.fechas->>'$.cierre' as date )
                    group by i.usuario_id
                ) as t";

        $this->data[ "total_activos" ] = $db->query( $sql )->getRow()->total;

        $sql = "SELECT
                    r.codigo, r.nombre, r.color,
                    count(*) as cantidad
                from t_rangos r 
                join t_usuarios u on u.data->>'$.rango_inversion' = r.codigo
                where r.modelo_codigo = '50-INVERSION' 
                and substring( r.codigo, 1, 3 ) > 500
                and substring( u.data->>'$.estatus.modelos.\"50-INVERSION\"', 1, 3 ) > 300 
                group by r.codigo
                order by r.codigo";

        $this->data[ "rangos" ] = $db->query( $sql )->getResultArray();

        $sql = "SELECT 
                    o.codigo, 
                    o.data->>'$.porcentaje' as porcentaje, 
                    o.data->>'$.color' as color, 
                    count(*) as cantidad, 
                    date_format( i.fechas->>'$.pagado', '%Y%m' ) as fecha
                from t_inversiones i
                join t_pedidos p on p.id = i.pedido_id and substring( p.estatus_codigo, 1, 3 ) > 400
                join t_productos o on o.codigo = i.producto_codigo
                where substring( i.estatus_codigo, 1, 3 ) > 300
                and cast( now() as date ) between cast( i.fechas->>'$.pagado' as date ) and cast( i.fechas->>'$.cierre' as date )
                group by i.producto_codigo, fecha
                order by fecha";

        $inversiones = $db->query( $sql )->getResultArray();

        $sql = "SELECT sum( i. cantidad) as semilla, date_format( i.fechas->>'$.pagado', '%Y%m' ) as fecha
                from t_inversiones i
                join t_pedidos p on p.id = i.pedido_id and substring( p.estatus_codigo, 1, 3 ) > 400
                where substring( i.estatus_codigo, 1, 3 ) > 300
                and cast( now() as date ) between cast( i.fechas->>'$.pagado' as date ) and cast( i.fechas->>'$.cierre' as date )
                group by fecha
                order by fecha";

        $semilla = $db->query( $sql )->getResultArray();

        $this->data[ "total_inversiones" ] = 0;
        
        $data = [];

        foreach( $inversiones as $i ){
            $this->data[ "total_inversiones" ] += $i[ "cantidad" ];
            $data[ $i[ "codigo" ] ][ $i[ "fecha" ] ] = intval( $i[ "cantidad" ] );
        }

        $this->data[ "meses" ] = [];
        
        $this->data[ "data_inversiones" ] = [];
        $this->data[ "data_semilla" ]     = [];
        $this->data[ "semilla" ]          = [];


        foreach( $semilla as $s ){
            $this->data[ "semilla" ][ $s[ "fecha" ] ] = intval( $s[ "semilla" ] );
        }

        $meses = 12;

        $mes      = date( "Ym", strtotime( date( "Y-m")."-01 - {$meses} month" ) );
        $anterior = date( "Ym", strtotime( date( "Y-m")."-01 - ".( $meses + 1)." month" ) );
     
        for( $a = 0; $a <= $meses; $a++ ){
            $this->data[ "meses" ][] = strtoupper( mes( substr( $mes, 4, 2 ), 3) ); //." ".substr( $mes, 0, 4 );

            $this->data[ "data_semilla" ][ $mes ] = ( $this->data[ "semilla" ][ $mes ] ?? 0 ) + ( $this->data[ "data_semilla" ][ $anterior ] ?? 0 );

            foreach( PRODUCTOS as $codigo => $tipo ){    
                if( !isset( $this->data[ "data_inversiones" ][ $codigo ] ) ){
                    $this->data[ "data_inversiones" ][ $codigo ] = 0;
                }

                if( isset( $data[ $codigo ][ $mes ] ) ){
                    $this->data[ "data_inversiones" ][ $codigo ] += $data[ $codigo ][ $mes ];
                }
                else{
                    $data[ $codigo ][ $mes ] =0;
                }

                $data[ $codigo ][ $mes ] += ( $data[ $codigo ][ $anterior ] ?? 0 );
            }

            $anterior = $mes;
            $mes = date( "Ym", strtotime( substr( $mes, 0, 4 )."-".substr( $mes, 4, 2 )."-01 + 1 month" ) );
        }

        ksort($this->data[ "data_semilla" ]);

        foreach( PRODUCTOS as $codigo => $tipo ){

            ksort($data[ $codigo ]);

            $this->data[ "data" ][] = [ 
                "name" => PRODUCTOS[ $codigo ][ "data" ][ "nombre" ],
                "data" => array_reverse( array_reverse( $data[ $codigo ] ) )
            ];
        }

        echo template( "capital/inversiones", $this->data );
    }


    /**
     * Processes a POST request to verify a transaction hash on the blockchain.
     *
     * This function receives a transaction hash via POST, validates its length,
     * and uses an external API to retrieve transaction details. If the transaction
     * is confirmed and directed to an active wallet, it registers the transaction
     * in the database, updates the investment information, and returns a success
     * response. If any validation fails, an appropriate error message is returned.
     *
     * @return void The response is echoed as a JSON object containing either
     *              error messages or success data based on the processing outcome.
     */

    public function quick_data()
    {
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
                        $pedido    = model( "PedidoModel" )->find( $inversion[ "pedido_id" ] );
                        $producto  = model( "ProductoModel" )->find( array_keys( $pedido[ "promociones"][ "510-SEMILLA" ][ "productos" ] ) )[ 0 ];

                        $fecha = $pedido[ "fechas" ][ "pagado" ];
                        $saldo = 0;

                        // nos aseguramos de que la transacción no haya sido registrada antes

                        $db  = db_connect();
                        $sql = "select count(*) as existe from t_fondeos where operacion = '{$hash}'";
                        $f_i = get_fecha_inversion( $pedido[ "fechas" ][ "pagado" ] );

                        if( !$db->query( $sql )->getrow()->existe ){
                            
                            // Al no existir antes, la registramos en la base de datos de fondeos
                            $cantidad = $tx[ "amount_str" ] / pow( 10, $tx[ "decimals" ] );
                            $total    = $pedido[ "data" ][ "total" ] - $saldo;

                            model( "FondeoModel" )->ignore( true )->save( [
                                "operacion"         => $hash, 
                                "fecha"             => $fecha,
                                "estatus_codigo"    => "420-PAGADO",
                                "metodopago_codigo" => $pedido[ "metodopago_codigo" ],
                                "usuario_id"        => $pedido[ "usuario_id" ],
                                "referencia"        => $pedido[ "referencia" ],
                                "cantidad"          => $cantidad,
                                "extras"            => $tx
                            ] );                           

                            $inversion[ "extras" ][ "TxHash" ] = $hash;
                            $inversion[ "extras" ][ "wallets" ][ "from" ] = $tx[ "from_address" ];
                            $inversion[ "extras" ][ "wallets" ][ "to" ] = $tx[ "to_address" ];
            
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


    /**
     * Crea una solicitud de retiro para una inversión.
     * 
     * Verifica que el usuario logueado sea el mismo que el usuario de la inversión.
     * Genera un registro en la tabla `retiros` con el estatus "255-PENDIENTE" y lo
     * relaciona con la inversión y el usuario.
     * Actualiza la cantidad de meses de la inversión en la tabla `inversiones`.
     * Registra una bitácora de la acción.
     * Redirecciona a la página de capital con un mensaje de éxito.
     * 
     * @return void
     */
    public function crea_retiro()
    {

        $i    = model( "InversionModel" )->find( $this->request->getPost( "inversion_id" ) );

        $p    = model( "ProductoModel" )->find( $i[ "producto_codigo" ] );
        $tipo = intval( $this->request->getPost( "opciones_retiro" ) );
        $bt   = balance_inversion( $i );

        $retiro = [
            $bt[ "rendimiento_mes" ], 
            $i[ "extras" ][ "meses" ][ 24 ][ "Ym" ] < date( "Ym" ) ? $bt[ "total" ] : $bt[ "finmes" ], 
            floatval( $this->request->getPost( "custom" ) )
        ];

        if( $this->data[ "usuario" ]->id == $i[ "usuario_id" ] ){

            // generar retiro

            $retiro_add = [
                "id" => NULL,
                "estatus_codigo" => "255-PENDIENTE",
                "usuario_id"     => $i[ "usuario_id" ], 
                "inversion_id"   => $i[ "id" ],
                "cantidad"       => $retiro[ $tipo -1 ],
                "tipo"           => $tipo,
                "fechas"         => [
                    "creacion"       => date( "Y-m-d" ),
                    "mes"            => $this->request->getPost( "mes_apply" ),
                    "deposito"       => null
                ]
            ];


            model( "RetiroModel" )->save( $retiro_add );
            $id = model( "RetiroModel" )->insertID();

            $pedido = model( "PedidoModel" )->find( $i[ "pedido_id" ] );
            $i[ "extras" ][ "meses" ] = genera_meses( $pedido, $i[ "id" ], $p );
            model( "InversionModel" )->save( $i );

            // redirect para refresh

            // BITACORA Crea solicitud de retiro
            bitacora( 86, $this->data[ "usuario" ]->id, [ 
                "socio"     => $i[ "usuario_id" ],
                "inversion" => $i[ "id" ],
                "retiro"    => $id,
                "mes"       => $this->request->getPost( "mes_apply" ),
                "cantidad"  => $retiro[ $tipo -1 ],
                "requested" => [
                    $this->request->getPost( "mes" ),
                    $this->request->getPost( "total" ),
                    $this->request->getPost( "custom" )
                ]
            ] );
            
            return redirect()->to( "capital" )->with( "msg", [ 
                "clase" => "success", 
                "icono" => "check", 
                "texto" => "Se generó solicitud de retiro" ] );  
        }
        else{
            return redirect()->to( "capital" );
        }
    }


    /**
     * Cancela una solicitud de retiro, marcando como cancelada la solicitud y actualizando los meses de la inversión.
     * 
     * @return Response
     */
    public function cancela_retiro()
    {
        
        $retiro = model( "RetiroModel" )->find( $this->request->getPost( "solicitud_id" ) );

        if ($retiro && $retiro[ "estatus_codigo" ] == "255-PENDIENTE" ) {

            $retiro["estatus_codigo"] = "150-CANCELADO";
            $retiro[ "fechas" ][ "cancelado" ] = date( "Y-m-d H:i:s" );

            model("RetiroModel")->save($retiro);

            // actualizar meses de inversión

            $i = model( "InversionModel" )->find( $retiro[ "inversion_id" ] );
            $pedido = model( "PedidoModel" )->find( $i[ "pedido_id" ] );
            $i[ "extras" ][ "meses" ] = genera_meses( $pedido, $i[ "id" ] );
            model( "InversionModel" )->save( $i );
            
            // BITACORA Cancela retiro
            bitacora( 87, $this->data[ "usuario"]->id, [
                "retiro_id" => $retiro[ "id" ]
            ]);

            return redirect()->to( "capital" )->with( "msg", [
                "clase" => "success",
                "icono" => "check",
                "texto" => "Se canceló la solicitud de retiro"
            ]);
        } else {
            return redirect()->to( "capital" );
        }
    }


    /**
     * Muestra el detalle de una inversión Capital24, incluyendo su estado
     * actual y los movimientos realizados en ella.
     *
     * Requiere el parámetro $hash, que es el hash de la transacción
     * correspondiente a la inversión.
     *
     * Verifica que el usuario logueado sea el mismo que el usuario de la
     * inversión, o que tenga permiso de ingreso, almacén, contabilidad o
     * administración.
     *
     * Redirecciona a la página de capital si no se cumple la condición
     * anterior.
     *
     * @param string $hash
     *
     * @return void
     */
    public function estadodecuenta( $hash )
    {
        $hash = base64_decode( urldecode( $hash ) );

        $where = "JSON_UNQUOTE( JSON_EXTRACT( t_inversiones.extras, '$.TxHash' ) ) = '{$hash}' AND substring( t_pedidos.estatus_codigo, 1, 3 ) > 400";
        $i = model( "InversionModel" )->select("t_inversiones.*" )->join('t_pedidos', 't_pedidos.id = t_inversiones.pedido_id')->where( $where )->findAll();
        
        if( !sizeof( $i ) ){
            return redirect()->to( "capital" );
        }

        $this->data[ "i" ] = $i[ 0 ];

        if( $this->data[ "usuario" ]->id != intval(  $this->data[ "i" ][ "usuario_id" ] ) && !(
            $this->data[ "usuario" ]->permiso( "28-INGRESA" ) ||
            $this->data[ "usuario" ]->permiso( "20-ALMACEN" ) ||
            $this->data[ "usuario" ]->permiso( "38-CONTABILIDAD" ) ||
            $this->data[ "usuario" ]->permiso( "40-ADMIN" )
        ) ){
            return template( "pedidos/no_permiso", $this->data );
        }

        $this->data[ "navbar" ] = true;
        $this->data[ "titulo" ] = "Detalle de inversión Capital24";

        echo template( "capital/detalle", $this->data );
    }


    /**
     * Muestra el listado de retiros de una inversión Capital24.
     *
     * Requiere el parámetro $inversion, que es el id de la inversión.
     *
     * Verifica que el usuario logueado tenga permiso de administración.
     *
     * Redirecciona a la página de no permiso si no se cumple la condición
     * anterior.
     *
     * @param string $inversion
     *
     * @return void
     */
    public function get_retiros()
    {
        if( !(
            $this->data[ "usuario" ]->permiso( "40-ADMIN")
        ) ){
            return redirect()->to( "no_permiso" ); 
        }
         
        extract( $this->request->getPost() );

        $html = "";
        $socio = model( "UsuarioModel" )->find( $socio );

        $db = db_connect();

        $sql = "SELECT 
                    r.id as folio,
                    u.id as socio,
                    p.referencia,
                    u.data->>'$.wallet' as wallet,
                    r.estatus_codigo as estatus,
                    r.cantidad,
                    r.fechas->>'$.deposito' as pagado,
                    r.fechas->>'$.mes' as mes,
                    e.data->>'$.nombre' as producto,
                    e.data->>'$.color' as color
                    FROM t_retiros r
                LEFT JOIN t_inversiones i on r.inversion_id = i.id
                left join t_pedidos p ON i.pedido_id = p.id 
                left join t_usuarios u on u.id = r.usuario_id
                left join t_productos e on e.codigo = i.producto_codigo
                WHERE p.usuario_id = {$socio->id}
                    AND p.modelo_codigo = '50-INVERSION'
                    AND SUBSTRING( p.estatus_codigo, 1, 3) > 400
                    AND SUBSTRING( r.estatus_codigo, 1, 3) > 200    
                    AND p.PTS->>'$.\"510-SEMILLA\"' > 0
                    AND r.inversion_id = {$inversion}";

        $retiros = $db->query( $sql )->getResult();

        $html .= "\n<table class=\"w-100 m-0 table table-striped\" id=\"tabla_retiros\">
                    <thead><tr>
                        <th>Folio</th>
                        <th class=\"text-start\">Tipo</th>
                        <th>Wallet</th>
                        <th>Cantidad</th>
                        <th>Retiro</th>
                        <th>Estatus</th>
                        <th>&nbsp;</th>
                    </tr></thead><tbody>";

        foreach( $retiros as $k => $r ){

            // Si no hay fecha de retiro, extraerla del mes

            if( substr( $r->estatus, 0, 3 ) > 300 && strlen( $r->pagado) != 10 ){
                $ret = model( "RetiroModel" )->find( $r->folio );

                $nueva = date( "Y-m-d", strtotime( substr( $r->mes, 0, 4 )."-".substr( $r->mes, 5, 2 )."-01 + 1 month" ) );
                
                $ret[ "fechas" ][ "deposito" ] = $nueva;
                model( "RetiroModel" )->save( $ret );

                $r->pagado = $nueva;
            }

            if( $r->mes > $mes ){
                $r->estatus = "152-FUTURO";
            }
            $url = urlencode( base64_encode( $r->folio ) );
            $html .= "\n<tr>
                        <td><span class=\"badge bg-gray-600\">{$inversion}-{$r->folio}</span></td>
                        <td class=\"text-start\"><span class=\"badge bg-{$r->color}\">{$r->producto}</span></td>
                        <td><a href=\"javascript:navigator.clipboard.writeText( '{$r->wallet}' );\"><i class=\"fa fa-wallet text-teal\"></i></a> {$r->wallet}</td>
                        <td class=\"text-end\"><span class=\"d-none\">{$r->cantidad}</span><strong>$".number_format( $r->cantidad,2 )."</strong> <button type=\"button\" class=\"btn btn-light btn-sm px-1 py-0\" onclick=\"navigator.clipboard.writeText( '{$r->cantidad}' )\"><i class=\"fa fa-copy\"></i></button></td>
                        <td nowrap><span class=\"d-none\">{$r->mes}</span>".strtoupper( mes( substr( $r->mes, 4, 2 ), 3 ) )." ".substr( $r->mes , 0, 4 )."</td>
                        <td>".estatus( $r->estatus )."</td>
            <td class=\"text-end\">".( $r->mes < date( "Ym" ) && substr( $r->estatus, 0, 3 ) < 300 && $this->data[ "usuario" ]->permiso( "40-ADMIN") ? "<a href=\"".base_url()."entrega_retiro/{$url}\" class=\"btn btn-sm btn-warning\"><i class=\"fa fa-check\"></i> Marcar como tranferida</a>" : "" )."</td>
                    </tr>";
        }

        $html .= "</tbody></table></form></div>"; 

        echo $html;        
    }


    /**
     * Marks a specific withdrawal as transferred.
     *
     * Validates if the current user has admin permissions before proceeding. 
     * Decodes the provided withdrawal identifier to find the corresponding 
     * record in the database. Updates the status of the withdrawal to "applied" 
     * and logs the action in the system's records. Finally, redirects to the 
     * capital page with a success message.
     *
     * @param string $retiro Encoded withdrawal identifier.
     * @return \CodeIgniter\HTTP\RedirectResponse Redirects to the capital page.
     */

    public function entrega_retiro( $retiro )
    {
        if( !(
            $this->data[ "usuario" ]->permiso( "40-ADMIN")
        ) ){
            return redirect()->to( "no_permiso" ); 
        }

        $db = db_connect();

        $r     = model( "RetiroModel" )->find( base64_decode( urldecode( $retiro ) ) );
        $socio = model( "UsuarioModel" )->find( $r[ "usuario_id" ] );

        $r[ "fechas" ][ "deposito" ] = date( "Y-m-d" );
        $r[ "estatus_codigo" ] = "421-APLICADO";
        model( "RetiroModel" )->save( $r );

        // BITACORA Marca recompensa entregada
        bitacora( 90, $this->data[ "usuario" ]->id, [ 
            "socio"    => $socio->id,
            "retiro"   => $r[ "id" ],
            "wallet"   => $socio->data->wallet,
            "cantidad" => $r[ "cantidad" ]
        ] );

        return redirect()->to( "capital24/".$r[ "fechas" ][ "mes"] )->with( "msg", [ 
            "clase" => "success", 
            "icono" => "check", 
            "texto" => "El retiro se ha marcado como transferido" ] );   
    }


    /**
     * Marca como entregados todos los retiros del mes. 
     * @param string $mes Mes en formato "YYYYMM".
     * @return redirect a capital24/$mes
     */
    public function entrega_retiros( $mes )
    {
        if( !(
            $this->data[ "usuario" ]->permiso( "40-ADMIN")
        ) ){
            return redirect()->to( "no_permiso" ); 
        }

        $retiros = model( "RetiroModel" )->where( "SUBSTRING( estatus_codigo,1,3) between 200 AND 300 AND JSON_EXTRACT( fechas, '$.mes' ) = '{$mes}' " )->findAll();

        $db = db_connect();

        foreach( $retiros as $r ){
            $socio = model( "UsuarioModel" )->find( $r[ "usuario_id" ] );

            $r[ "fechas" ][ "deposito" ] = date( "Y-m-d" );
            $r[ "estatus_codigo" ] = "421-APLICADO";

            model( "RetiroModel" )->save( $r );
    
            // BITACORA Marca recompensa entregada
            bitacora( 90, $this->data[ "usuario" ]->id, [ 
                "socio"    => $socio->id,
                "retiro"   => $r[ "id" ],
                "wallet"   => $socio->data->wallet,
                "cantidad" => $r[ "cantidad" ]
            ] );   
        }

        return redirect()->to( "capital24/".$r[ "fechas" ][ "mes"] )->with( "msg", [ 
            "clase" => "success", 
            "icono" => "check", 
            "texto" => "Todos los retiros del mes han sido marcados como entregados" ] );   
    }    


    /**
     * Descarga un Excel con los retiros de un mes.
     * @param string $mes Mes en formato "YYYYMM".
     * @return void
     */
    public function excel_retiros()
    {
        if( !(
            $this->data[ "usuario" ]->permiso( "31-GASOLINA") ||
            $this->data[ "usuario" ]->permiso( "40-ADMIN" )
        ) ){
            return redirect()->to( "no_permiso" ); 
        }

        $mes = $this->request->getPost( "mes" );
        $db  = db_connect();

        $sql = "SELECT 
                u.id as socio, 
                r.estatus_codigo as estatus,
                CONCAT( i.pedido_id, '-', i.id) as inversion,
                u.data->>'$.wallet' as wallet,
                r.cantidad 
            from t_retiros r
            join t_usuarios u on u.id = r.usuario_id
            join t_inversiones i on i.id = r.inversion_id
            where r.fechas->>'$.mes' = '{$mes}'
            and substring( r.estatus_codigo, 1, 3) > 200
            order by u.id asc";

        $retiros  = $db->query( $sql );
        $db       = db_connect();
        $data     = [];

        $mySpreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
        $mySpreadsheet->removeSheetByIndex(0);
        $worksheet = new \PhpOffice\PhpSpreadsheet\Worksheet\Worksheet($mySpreadsheet, "INGRESO MENSUAL");
        $mySpreadsheet->addSheet( $worksheet, 0 );

        $col = 0;
        $e = [];
        $worksheet->setCellValue( chr(65 + $col++)."1", "SOCIO" );
        $worksheet->setCellValue( chr(65 + $col++)."1", "NOMBRE" );
        $worksheet->setCellValue( chr(65 + $col++)."1", "TELEFONO" );
        $worksheet->setCellValue( chr(65 + $col++)."1", "INVERSION" );
        $worksheet->setCellValue( chr(65 + $col++)."1", "WALLET" );
        $worksheet->setCellValue( chr(65 + $col++)."1", "CANTIDAD" );
        $worksheet->setCellValue( chr(65 + $col++)."1", "ESTATUS" );

        $row = 1;

        foreach( $retiros->getResult() as $r ){
            $u = model( "UsuarioModel" )->find( $r->socio );

            $row++;
            $col  = 0;
            
            $worksheet->setCellValue( chr(65 + $col++).$row, $u->id );
            $worksheet->setCellValue( chr(65 + $col++).$row, $u->nombre( 2, false, true ) );
            $worksheet->setCellValue( chr(65 + $col++).$row, $u->telefono );
            $worksheet->setCellValue( chr(65 + $col++).$row, $r->inversion );
            $worksheet->setCellValue( chr(65 + $col++).$row, $u->data->wallet );
            $worksheet->setCellValue( chr(65 + $col++).$row, $r->cantidad );
            $worksheet->setCellValue( chr(65 + $col++).$row, $r->estatus );
        }

        $col--;

        $worksheet->getStyle( "A1:".chr(65 + $col)."1" )->getFont()->getColor()->setARGB('ffffff');
        $worksheet->getStyle( "F2:F".$row )->getNumberFormat()->setFormatCode( "$#,##0.00" );
        $worksheet->getStyle( "A1:".chr(65 + $col)."1" )->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setARGB('192b5a');

        $col--;
        $worksheet->getStyle( chr(65 + $col)."1" )->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setARGB('009779');
        $worksheet->getStyle( chr(65 + $col)."2:".chr(65 + $col).$row )->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setARGB('c1ebd7');

        foreach( $worksheet->getColumnIterator() as $column ){
            $worksheet->getColumnDimension( $column->getColumnIndex() )->setAutoSize( true );
        }

        // BITACORA descarga excel de retiros
        bitacora( 91, $this->data[ "usuario" ]->id, [
            "mes" => $mes
        ] );

        $path = "data/excel/retiros";
        if( !is_dir( $path ) ) mkdir( $path, 0755, true );

        echo $file = $path."/Retiros_".strtoupper( mes( substr( $mes, 4, 2 ) ) )."-".substr( $mes, 0, 4 )."_".time().".xlsx";

        $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($mySpreadsheet);
        $writer->save( $file );
    }    
}

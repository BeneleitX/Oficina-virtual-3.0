<?php 


$p  = $pedido;
$tt = $p[ "data" ][ "total" ] + $p[ "data" ][ "comisionentrega" ];
$m  = $modelo;
$u  = $socio;

$s  = $u->data->saldo->{$m}->estatus == 1 ? $u->data->saldo->{$m}->cantidad : 0;

if( $s >= $tt ){

    $fecha = date( "Y-m-d" );
    $hora  = date( "H:i:s" );

    $p[ "estatus_codigo" ]       = "420-PAGADO";
    $p[ "fechas" ][ "pagado" ]   = $fecha;     
    $p[ "fechas" ][ "califica" ] = $fecha; 
    $p[ "fechas" ][ "reparte" ]  = $fecha; 
    
    // Guardamos cambios de estatus del pedido
    model( "PedidoModel" )->save( $p );

    // Ahora vamos con el socio
    $data      = $u->data;                                    
    $historial = $u->historial; 

    foreach( $p[ "PTS" ] as $promo => $pts ){
        // Protección adicional para evitar huecos en la estructura JSON de puntajes de usuario
        if( !is_object( $historial->modelos->{$m}->primercompra ) ){
            $historial->modelos->{$m}->primercompra = json_decode( '{}' );
        }

        // Actualizamos dato de primer compra, si es el caso
        if( !isset( $historial->modelos->{$m}->primercompra->{$promo} ) ){
            $historial->modelos->{$m}->primercompra->{$promo} = $fecha;
        }
    } 

    // Actualizamos dato de ultima compra
    $historial->modelos->{$m}->ultimacompra = $fecha;

    $u->data = $data;
    $u->historial = $historial;

    // Guardamos cambios de estatus del socio
    model( "UsuarioModel" )->save( $u );  
    
    $db = db_connect();
    // Ejecutamos rutinas de actualización de calificaciones y esturcturas de red
    // y repartimos las comisiones que el pedido genere
    $db->query( "select f_update_PTS( {$u->id}, '{$m}', '".date( "Ym", strtotime( $fecha ) )."' )" );  
    $db->query( "select f_get_estatus( {$u->id}, 0 )" );
    $db->query( "select f_reparte_comisiones( {$p[ "id" ]}, 0 )" );

    // Guardamos el movimiento del pago con tarjeta en la base de datos
    model( "FondeoModel" )->ignore( true )->save( [
        "operacion"         => time(),
        "fecha"             => $fecha." ".$hora,
        "estatus_codigo"    => "620-RECIBIDO",
        "metodopago_codigo" => $metodopago[ "codigo" ],
        "usuario_id"        => $u->id,
        "referencia"        => $p[ "referencia" ],
        "cantidad"          => $tt,
        "extras"            => []
    ] );
}

// BITACORA Pago rechazado/aprobado
bitacora( 58, $p[ "usuario_id" ] ?? 0, [ 
    "referencia" => $p[ "referencia" ],
    "estatus"    => "OK",
    "metodopago" => "SALDO A FAVOR"
] );   

return redirect()->to( "pedido/".$p[ "referencia" ] );

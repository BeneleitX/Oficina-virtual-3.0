<?php

namespace App\Controllers;

class Pedidos extends BaseController 
{
    function __construct() {
        $this->data[ "menu" ] = "tienda";
    }

    public function compras(){
        $this->data[ "socio" ]   = $this->data[ "usuario" ];
        $this->data[ "navbar" ]  = true;
        $this->data[ "titulo" ]  = "Compras";
        $this->data[ "pedidos" ] = model( "PedidoModel" )->where( "substring( estatus_codigo, 1, 3 ) > 200 AND usuario_id = ".$this->data[ "socio" ]->id , null, false )->findAll();

        echo template( "pedidos/dashboard", $this->data );
    }

    public function historial( $modelo = null ){

        if( !$modelo ){
            $modelo = VARIABLES[ "modelo_default" ][ "valor" ];
        }
        
        load_catalogo( "metodospago",    "modelo_codigo = '{$modelo}'");
        load_catalogo( "metodosentrega", "modelo_codigo = '{$modelo}'");
        load_catalogo( "promociones",    "modelo_codigo = '{$modelo}'");

        if( $this->data[ "usuario" ] === null ){
            return redirect()->to( "logout" );
        }

        $this->data[ "socio" ]   = $this->data[ "usuario" ];
        $this->data[ "navbar" ]  = true;
        $this->data[ "modelo" ] = $modelo;
        $this->data[ "titulo" ]  = "Mis pedidos";
        $this->data[ "pedidos" ] = model( "PedidoModel" )->where( "substring( estatus_codigo, 1, 3 ) > 400  AND modelo_codigo = '{$modelo}' AND usuario_id = ".$this->data[ "socio" ]->id , null, false )->findAll();

        echo template( "pedidos/historial", $this->data );
    }
    

    public function carrito( $tipo, $data ){


        if( $this->data[ "usuario" ]->id == 666 ){

        /**************************************************************/
        // todo bien
        // ENVIAR CORREO
        $pedido  = model( "PedidoModel" )->where( "referencia = 75" )->first();
        $usuario = model( "UsuarioModel" )->find( $pedido[ "usuario_id" ] );
        
        $subject = "Pedido ".MODELOS[ $pedido[ "modelo_codigo" ] ][ "nombre" ]." {$pedido[ "referencia" ]} pagado";
        $message = "
            <p>¡Hola ".$usuario->nombre()."! </p>
            <p>mensaje de pedido pagado y detalles</p>
        ";

        $respuesta = envia_correo( $usuario, $subject, $message );

        if( $_SERVER[ "SERVER_ADDR" ] == "127.0.0.1" ){
            echo $respuesta;

        }
        
        /**************************************************************/

        return;

    }
        $this->data[ "navbar" ] = true;

        if( $tipo == "pedido" ){
            $this->data[ "pedido" ] = model( "PedidoModel" )->where( "referencia = ".$data )->first();

            if( !$this->data[ "pedido" ] ){ 
                return redirect()->to( 'historial/'.( $modelo ?? VARIABLES[ "modelo_default" ][ "valor" ] ) );
            }

            $this->data[ "socio"  ] = model( "UsuarioModel" )->find( $this->data[ "pedido" ][ "usuario_id" ] );

            $encrypter = service( "encrypter" );
            $this->data[ "link" ] = str_replace( "%", "___", urlencode( base64_encode( $encrypter->encrypt( $this->data[ "pedido" ][ "id" ] , [ "key" => $this->data[ "usuario" ]->curp ] ) ) ) );
            $this->data[ "modelo" ] = $this->data[ "pedido" ][ "modelo_codigo" ];

            $sql = "/* estatus_codigo = '201-ACTIVO' AND  */modelo_codigo = '{$this->data[ "modelo" ]}'";
            $this->data[ "productos" ] = model( "ProductoModel" )->where( $sql , null, false )->findAll();
    
            load_catalogo( "promociones",    "modelo_codigo = '{$this->data[ "modelo" ]}'");
            load_catalogo( "metodospago",    "modelo_codigo = '{$this->data[ "modelo" ]}'");
            load_catalogo( "metodosentrega", "modelo_codigo = '{$this->data[ "modelo" ]}'");
            load_catalogo( "almacenes",      "modelo_codigo = '{$this->data[ "modelo" ]}'");
            load_catalogo( "esquemas",       "modelo_codigo = '{$this->data[ "modelo" ]}'");

            $this->data[ "cancelado" ] = substr( $this->data[ "pedido" ][ "estatus_codigo" ], 0, 3 ) < 200 ? 1 : 0;
            $this->data[ "pagado" ]    = substr( $this->data[ "pedido" ][ "estatus_codigo" ], 0, 3 ) > 400 ? 1 : 0;
            $this->data[ "entregado" ] = substr( $this->data[ "pedido" ][ "estatus_codigo" ], 0, 3 ) > 500 ? 1 : 0;
            $this->data[ "titulo" ]    = "Detalles de pedido";
        }
        else{
            $this->data[ "socio" ] = $this->data[ "usuario" ];

            $this->data[ "modelo" ] = $data;
            $this->data[ "pagado" ]    = 0;
            $this->data[ "cancelado" ]    = 0;
            $this->data[ "entregado" ] = 0;
            $this->data[ "premieres" ][ date( "Ym" ) ] = $this->data[ "socio" ]->getPremieres( date( "Ym" ) );


            $sql = "estatus_codigo = '201-ACTIVO' AND modelo_codigo = '{$this->data[ "modelo" ]}'";
            $this->data[ "productos" ] = model( "ProductoModel" )->where( $sql , null, false )->findAll();

            load_catalogo( "promociones", "estatus_codigo = '201-ACTIVO' AND modelo_codigo = '{$this->data[ "modelo" ]}'");
            load_catalogo( "metodospago",    "estatus_codigo = '201-ACTIVO' AND modelo_codigo = '{$this->data[ "modelo" ]}'");
            load_catalogo( "metodosentrega", "estatus_codigo = '201-ACTIVO' AND modelo_codigo = '{$this->data[ "modelo" ]}'");
            load_catalogo( "almacenes",      "estatus_codigo = '201-ACTIVO' AND modelo_codigo = '{$this->data[ "modelo" ]}'");

            $this->data[ "pedido" ] = $this->data[ "socio" ]->getPedido( $this->data[ "modelo" ] );
            $this->data[ "socio" ]->PTS = $this->data[ "socio" ]->getCalificaciones( $this->data[ "modelo" ] );
            $this->data[ "titulo" ] = "Tienda en línea";
            $this->data[ "pedido" ][ "data" ][ "pesoxbulto" ] = MODELOS[ $this->data[ "modelo" ] ][ "settings" ][ "pesoxbulto" ];
        }

        echo template( "pedidos/carrito", $this->data );
    }


    public function save_pedido(){
        $pedido = json_decode( $this->request->getPost( "json" ) );

        model( "PedidoModel" )->save( $pedido );
    }


    public function reparte(){
        $db = db_connect();
        $pedido = model( "PedidoModel" )->find( $this->request->getPost( "pedido" ) );
        $db->query( "select f_reparte_comisiones( {$pedido[ "id" ]}, 0 ) as afectados" );

        // BITACORA Actualizar reparto de comisiones
        bitacora( 31, $this->data[ "usuario" ]->id, [ 
            "pedido" => $pedido[ "id" ]
        ] );

        return redirect()->to( "pedido/".$pedido[ "referencia" ] )->with( "msg", [ 
            "clase" => "success", 
            "icono" => "check", 
            "texto" => "Se recalcularon comisiones de pedido" ] );      
    }


    public function cambia_fecha(){

        if( validafecha( $this->request->getPost( "nueva" ) ) ){

            $pedido = model( "PedidoModel" )->find( $this->request->getPost( "pedido" ) );
            $fechas = $pedido[ "fechas" ];

            // BITACORA Cambiar fecha de calificacion en pedido
            bitacora( 32, $this->data[ "usuario" ]->id, [ 
                "pedido" => $pedido[ "id" ],
                "anterior" => $fechas[ "califica" ],
                "nueva" => $this->request->getPost( "nueva" )
            ] );

            $mesprevio = date( "Ym", strtotime( $fechas[ "califica" ] ) );

            $fechas[ "califica" ] = $this->request->getPost( "nueva" );
            $pedido[ "fechas" ] = $fechas;

            model( "PedidoModel" )->save( $pedido );

            $mescalifica = date( "Ym", strtotime( $fechas[ "califica" ] ) );

            $db = db_connect();

            $db->query( "select f_update_PTS( {$pedido[ "usuario_id" ]}, '{$pedido[ "modelo_codigo" ]}', '{$mescalifica}' )" );  
            $db->query( "select f_update_PTS( {$pedido[ "usuario_id" ]}, '{$pedido[ "modelo_codigo" ]}', '{$mesprevio}' )" );  
            $db->query( "select f_get_estatus( {$pedido[ "usuario_id" ]} )" );
            $afectados = $db->query( "select f_reparte_comisiones( {$pedido[ "id" ]}, 0 )" )->getRow();            
        }

        return redirect()->to( "pedido/".$pedido[ "referencia" ] )->with( "msg", [ 
            "clase" => "success", 
            "icono" => "check", 
            "texto" => "Se actualizó la fecha de compra del pedido" ] );      
    }


    public function cancela_pedido(){

        $pedido = model( "PedidoModel" )->find( $this->request->getPost( "pedido" ) );
        $fechas = $pedido[ "fechas" ];
        $fechas[ "cancela" ]  = date( "Y-m-d H:i:s" );
        $pedido[ "fechas" ]   = $fechas;
        $pedido[ "estatus_codigo" ] = "150-CANCELADO";

        model( "PedidoModel" )->save( $pedido );

        $mescalifica = date( "Ym", strtotime( $fechas[ "califica" ] ) );

        $db = db_connect();
        $db->query( "select f_update_PTS( {$pedido[ "usuario_id" ]}, '{$pedido[ "modelo_codigo" ]}', '{$mescalifica}' )" );
        $db->query( "select f_get_estatus( {$pedido[ "usuario_id" ]} )" );

        // BITACORA Cancelar pedido
        bitacora( 33, $this->data[ "usuario" ]->id, [ 
            "pedido" => $pedido[ "id" ]
        ] );

        return redirect()->to( "pedido/".$pedido[ "referencia" ] )->with( "msg", [ 
            "clase" => "success", 
            "icono" => "check", 
            "texto" => "Se ha cancelado el pedido" ] );      
    }    


    public function fondeo(){
        extract( $this->request->getPost() );

        $socio = $this->data[ "usuario" ];
        echo $socio->fondeo( $modelo, $metodo, $cantidad );
    }


    public function compra_demo( $usuario, $modelo, $mes ){
        extract( $this->request->getPost() );
        $socio = model( "UsuarioModel" )->find( $usuario );

        load_catalogo( "promociones",    "estatus_codigo = '201-ACTIVO' AND modelo_codigo = '{$modelo}'");
        load_catalogo( "metodospago",    "estatus_codigo = '201-ACTIVO' AND modelo_codigo = '{$modelo}'");
        load_catalogo( "metodosentrega", "estatus_codigo = '201-ACTIVO' AND modelo_codigo = '{$modelo}'");

        $pedido = $socio->getPedido( $modelo );
        $data = $pedido[ "data" ];
        $promociones = $pedido[ "promociones" ];
        $PTS = $pedido[ "PTS" ];
        $fechas = $pedido[ "fechas" ];

        $metodopago = METODOSPAGO[ MODELOS[ $modelo ][ "settings" ][ "metodopago_base" ] ];
        $metodoentrega = METODOSENTREGA[ MODELOS[ $modelo ][ "settings" ][ "metodoentrega_base" ] ];

        $pedido[ "metodoentrega_codigo" ] = $metodoentrega[ "codigo" ];
        $data[ "entrega" ] = VARIABLES[ "almacen_paqueteria" ][ "valor" ];

        $promocion_base = MODELOS[ $modelo ][ "settings" ][ "promocion_base" ][ 0 ];

        $promociones[ $promocion_base ] = [];
        $promociones[ $promocion_base ][ "productos" ] = [];

        $producto = model( "ProductoModel" )->find( PROMOCIONES[ $promocion_base ][ "settings" ][ "producto_base" ] );
        $cantidad = PROMOCIONES[ $promocion_base ][ "settings" ][ "cantidad_base" ];

        $promociones[ $promocion_base ][ "productos" ][ $producto->codigo ] = [
            "nombre" => $producto->data->nombre,
            "precio" => $producto->precio->total,
            "cantidad" => $cantidad,
            "puntos" => $producto->data->puntos->{$promocion_base},
            "comisionable" => $producto->precio->base,
            "descripcion" => $producto->data->descripcion,
            "orden" => 1
        ];

        $promociones[ $promocion_base ][ "precio" ] = $producto->precio->total * $cantidad;
        $promociones[ $promocion_base ][ "comisionable" ] = $producto->precio->base * $cantidad;

        $PTS[ $promocion_base ] = $producto->data->puntos->{$promocion_base} * $cantidad;

        $data[ "peso" ]  = $producto->data->dimensiones->peso * $cantidad;
        $data[ "total" ] = $producto->precio->total * $cantidad;
        $data[ "productos" ] = $cantidad;
        $data[ "pesoxbulto" ] = $metodoentrega[ "settings" ][ "gramaje" ];
        $data[ "comisionbanco" ]  = $metodopago[ "settings" ][ "comision" ];
        $data[ "comisionentrega" ] = $metodoentrega[ "settings" ][ "costo" ];

        $fechas[ "creado" ] = date( "Y-m-d" );

        $pedido[ "data" ] = $data;
        $pedido[ "promociones" ] = $promociones;
        $pedido[ "PTS" ] = $PTS;
        $pedido[ "fechas" ] = $fechas;

        model( "PedidoModel" )->save( $pedido );

        $fondeo = $data[ "total" ] + $metodopago[ "settings" ][ "comision" ];

        $socio->fondeo( $modelo, $metodopago[ "codigo" ], $fondeo, $mes );
        return redirect()->to( "red/{$modelo}" );
    }
    


    public function checkout(){
        $this->data[ "modelo" ] = $this->request->getPost( "modelo" );
        $this->data[ "metodopago" ] = model( "MetodopagoModel" )->find( $this->request->getPost( "metodopago" ) );

        $this->data[ "socio" ] = $this->data[ "usuario" ];
        if( !( $this->data[ "pedido" ] = $this->data[ "socio" ]->getPedido( $this->data[ "modelo" ], false ) ) || $this->data[ "pedido" ][ "estatus_codigo" ] != "250-EN-PROCESO" ){ 
            return redirect()->to( 'historial' );
        }
        $this->data[ "navbar" ] = true;
        $this->data[ "titulo" ] = "Pago de pedido: ".MODELOS[ $this->data[ "modelo" ] ][ "nombre" ];


        $pre = $this->data[ "pedido" ][ "data" ][ "total" ] + $this->data[ "pedido" ][ "data" ][ "comisionentrega" ] - $this->data[ "socio" ]->data->saldo->{$this->data[ "modelo" ]};

        $this->data[ "cantidad" ] = $pre + ( $this->data[ "metodopago" ][ "settings" ][ "tipocomision" ] == "porcentaje" ? $pre * 2 / 100 : 20 );

        echo template( "pedidos/gateways/".$this->data[ "metodopago" ][ "codigo" ], $this->data );
    }


    public function ticket( $link ){
        $encrypter = service( "encrypter" );
        $d = base64_decode( urldecode( str_replace( "___", "%", $link ) ) );

        $p = intval( $encrypter->decrypt( $d, [ "key" => $this->data[ "usuario" ]->curp ] ) );

        $this->data[ "pedido" ] = model( "PedidoModel" )->find( $p );
        $this->data[ "print" ]  = true;
        $this->data[ "modelo" ] = $this->data[ "pedido" ][ "modelo_codigo" ];
        $this->data[ "socio"  ] = model( "UsuarioModel" )->find( $this->data[ "pedido" ][ "usuario_id" ] );

        $sql = "modelo_codigo = '{$this->data[ "modelo" ]}'";
        $this->data[ "productos" ] = model( "ProductoModel" )->where( $sql , null, false )->findAll();

        load_catalogo( "promociones", "estatus_codigo = '201-ACTIVO' AND modelo_codigo = '{$this->data[ "modelo" ]}'");
        load_catalogo( "metodospago",    "estatus_codigo = '201-ACTIVO' AND modelo_codigo = '{$this->data[ "modelo" ]}'");
        load_catalogo( "metodosentrega", "estatus_codigo = '201-ACTIVO' AND modelo_codigo = '{$this->data[ "modelo" ]}'");
        load_catalogo( "almacenes",      "estatus_codigo = '201-ACTIVO' AND modelo_codigo = '{$this->data[ "modelo" ]}'");
        echo template( "pedidos/ticket", $this->data );
    }
}

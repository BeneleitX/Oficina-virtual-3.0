<?php namespace App\Controllers;

class Pedidos extends BaseController 
{
    function __construct() 
    {
        $this->data[ "menu" ] = "tienda";
    }

    public function compras()
    {
        $this->data[ "socio" ]   = $this->data[ "usuario" ];
        $this->data[ "navbar" ]  = true;
        $this->data[ "titulo" ]  = "Compras";
        
        $sql = "substring( estatus_codigo, 1, 3 ) > 200 
                AND usuario_id = ".$this->data[ "socio" ]->id;

        $this->data[ "pedidos" ] = model( "PedidoModel" )->where( $sql , null, false )->findAll();

        echo template( "pedidos/dashboard", $this->data );
    }

    public function historial( $modelo = null )
    {
        if( !$modelo ){
            $modelo = VARIABLES[ "modelo_default" ][ "valor" ];
        }
        
        load_catalogo( "metodospago",    "modelo_codigo = '{$modelo}'");
        load_catalogo( "metodosentrega", "modelo_codigo = '{$modelo}' OR codigo = '00-ALMACEN'");
        load_catalogo( "promociones",    "modelo_codigo = '{$modelo}'");

        if( $this->data[ "usuario" ] === null ){
            return redirect()->to( "logout" );
        }

        $this->data[ "socio" ]   = $this->data[ "usuario" ];
        $this->data[ "navbar" ]  = true;
        $this->data[ "modelo" ]  = $modelo;
        $this->data[ "titulo" ]  = "Mis pedidos";

        $sql = "substring( estatus_codigo, 1, 3 ) > 250
                AND modelo_codigo = '{$modelo}' 
                AND usuario_id = ".$this->data[ "socio" ]->id;

        $this->data[ "pedidos" ] = model( "PedidoModel" )->where( $sql, null, false )->findAll();

        echo template( "pedidos/historial", $this->data );
    }
    

    public function fuente()
    {
        $db = db_connect();

        $respuesta = [
            "draw" => 1,
            "data" => []
        ];

        extract( $this->request->getPost() );

        $sql = "SELECT count(*) as total 
                from t_pedidos 
                where substring( estatus_codigo, 1, 3 ) > 400 
                AND modelo_codigo = '{$modelo}' 
                AND usuario_id = {$socio}";

        $respuesta[ "recordsTotal" ] = intval( $db->query( $sql )->getRow()->total );

        if( $search[ "value" ] ){
            $sql = "SELECT count(*) as total 
                    from t_pedidos 
                    where substring( estatus_codigo, 1, 3 ) > 400 
                    AND modelo_codigo = '{$modelo}' 
                    AND usuario_id = {$socio} 
                    and ()";

            $respuesta[ "recordsFiltered" ] = intval( $db->query( $sql )->getRow()->total );
        }
        else{
            $respuesta[ "recordsFiltered" ] = $respuesta[ "recordsTotal" ];
        }

        $sql = "SELECT * 
                from t_pedidos 
                where substring( estatus_codigo, 1, 3 ) > 400 
                AND modelo_codigo = '{$modelo}' 
                AND usuario_id = {$socio} 
                limit {$start}, {$length}";

        $data = $db->query( $sql )->getResult();

       // $pedidos = model( "PedidoModel" )->where( "substring( estatus_codigo, 1, 3 ) > 400 AND modelo_codigo = '{$modelo}' AND usuario_id = ".$socio , null, false )->findAll();

        echo json_encode( $respuesta );
    }


    public function carrito( $tipo, $data )
    {
        $this->data[ "navbar" ] = true;

        // Entrar a pedido en espera de pago o pagado (usando referencia)
        if( $tipo == "pedido" ){

            $this->data[ "titulo" ] = "Detalles de pedido";
            $this->data[ "pedido" ] = model( "PedidoModel" )->where( "referencia = ".$data )->first();

            if( !$this->data[ "pedido" ] ){ 
                // return redirect()->to( 'historial/'.( $modelo ?? VARIABLES[ "modelo_default" ][ "valor" ] ) );
                return template( "pedidos/no_pedido", $this->data );
            }

            if( $this->data[ "pedido" ][ "estatus_codigo" ] == "250-EN-PROCESO" ){ 
                // return redirect()->to( 'tienda/'.$this->data[ "pedido" ][ "modelo_codigo" ] );
            }

            $modelo = $this->data[ "pedido" ][ "modelo_codigo" ];

            load_catalogo( "metodosentrega", "modelo_codigo = '{$modelo}' OR codigo = '00-ALMACEN'");
            load_catalogo( "almacenes",      "modelo_codigo = '{$modelo}'");
            load_catalogo( "promociones",    "modelo_codigo = '{$modelo}'");
            load_catalogo( "metodospago",    "modelo_codigo = '{$modelo}'");
            load_catalogo( "esquemas",       "modelo_codigo = '{$modelo}'");

            $staff = false;

            if( $this->data[ "pedido" ][ "metodoentrega_codigo" ] == "00-ALMACEN" ){
                $staff = true;

                if( !$this->data[ "pedido" ][ "data" ][ "entrega" ] ){
                    $this->data[ "pedido" ][ "data" ][ "entrega" ] = VARIABLES[ "almacen_principal" ][ "valor" ];
                    model( "PedidoModel" )->save( $this->data[ "pedido" ] );
                }

                if( !isset( ALMACENES[ $this->data[ "pedido" ][ "data" ][ "entrega" ] ] )){
                    $this->data[ "pedido" ][ "data" ][ "entrega" ] = (substr( $modelo, 0, 1 ) - 1 )."11-OFICINAS";
                }

                $ff = ALMACENES[ $this->data[ "pedido" ][ "data" ][ "entrega" ] ][ "settings" ][ "staff" ];

                if( !( $this->data[ "usuario" ]->permiso( "18-STOCK" ) && in_array( $this->data[ "usuario" ]->id, $ff ) ) ){
                    $staff = false;
                }
            }

            if( !$staff && $this->data[ "usuario" ]->id !=intval(  $this->data[ "pedido" ][ "usuario_id" ] ) && !(
                $this->data[ "usuario" ]->permiso( "28-INGRESA" ) ||
                $this->data[ "usuario" ]->permiso( "20-ALMACEN" ) ||
                $this->data[ "usuario" ]->permiso( "40-ADMIN" )
            ) ){
                return template( "pedidos/no_permiso", $this->data );
            }
            
            /**********************************/

            $this->data[ "link" ]  = str_replace( "%", "___", urlencode( base64_encode( $this->data[ "pedido" ][ "id" ] ) ) );
            $this->data[ "socio" ] = model( "UsuarioModel" )->find( $this->data[ "pedido" ][ "usuario_id" ] );
            $this->data[ "socio" ]->PTS = $this->data[ "socio" ]->getCalificaciones( $this->data[ "pedido" ][ "modelo_codigo" ] );

            $sql = "/* estatus_codigo = '201-ACTIVO' AND */ modelo_codigo = '{$modelo}'";
            $this->data[ "productos" ] = model( "ProductoModel" )->where( $sql , null, false )->findAll();
    
            $this->data[ "enproceso" ] = substr( $this->data[ "pedido" ][ "estatus_codigo" ], 0, 3 ) == 250 && ( $this->data[ "pedido" ][ "usuario_id" ] != $this->data[ "usuario" ]->id ) ? 1 : 0;
            $this->data[ "cancelado" ] = substr( $this->data[ "pedido" ][ "estatus_codigo" ], 0, 3 ) < 200 ? 1 : 0;
            $this->data[ "pagado" ]    = substr( $this->data[ "pedido" ][ "estatus_codigo" ], 0, 3 ) > 400 ? 1 : 0;
            $this->data[ "bloqueado" ] = substr( $this->data[ "pedido" ][ "estatus_codigo" ], 0, 3 ) == 255 /* || ($this->data[ "pedido" ][ "usuario_id" ] != $this->data[ "usuario" ]->id ) */ ? 1 : 0;
            $this->data[ "entregado" ] = substr( $this->data[ "pedido" ][ "estatus_codigo" ], 0, 3 ) > 500 ? 1 : 0;
        }

        // Entrar directo a URl tienda (sin referencia, pedido en proceso)
        else{
            $modelo = $data;
            $activo = "estatus_codigo = '201-ACTIVO'";
            $sql    = "{$activo} AND modelo_codigo = '{$modelo}'";

            $this->data[ "socio" ]     = $this->data[ "usuario" ];
            $this->data[ "pagado" ]    = 0;
            $this->data[ "enproceso" ] = 1;
            $this->data[ "bloqueado" ] = 0;
            $this->data[ "cancelado" ] = 0;
            $this->data[ "entregado" ] = 0;
            $this->data[ "premieres" ][ date( "Ym" ) ] = $this->data[ "socio" ]->getPremieres( date( "Ym" ) );
            $this->data[ "productos" ] = model( "ProductoModel" )->where( $sql , null, false )->findAll();

            load_catalogo( "promociones",    "{$activo} AND modelo_codigo = '{$modelo}'");
            load_catalogo( "metodosentrega", "{$activo} AND ( modelo_codigo = '{$modelo}' OR codigo = '00-ALMACEN')");
            load_catalogo( "almacenes",      "{$activo} AND modelo_codigo = '{$modelo}'");
            load_catalogo( "metodospago",    "modelo_codigo = '{$modelo}'");

            $this->data[ "pedido" ] = $this->data[ "socio" ]->getPedido( $modelo );
            $this->data[ "socio" ]->PTS = $this->data[ "socio" ]->getCalificaciones( $modelo );
            $this->data[ "titulo" ] = "Tienda en línea";
            $this->data[ "pedido" ][ "data" ][ "pesoxbulto" ] = MODELOS[ $modelo ][ "settings" ][ "pesoxbulto" ];
        }

        $this->data[ "modelo" ] = $modelo;
        
        echo template( "pedidos/carrito", $this->data );
    }


    public function shop( $tipo, $data )
    {
        $this->data[ "navbar" ] = true;

        $modelo = $data;
        $activo = "estatus_codigo = '201-ACTIVO'";
        $sql    = "{$activo} AND modelo_codigo = '{$modelo}'";

        $this->data[ "socio" ]     = $this->data[ "usuario" ];
        $this->data[ "pagado" ]    = 0;
        $this->data[ "enproceso" ] = 1;
        $this->data[ "bloqueado" ] = 0;
        $this->data[ "cancelado" ] = 0;
        $this->data[ "entregado" ] = 0;
        $this->data[ "premieres" ][ date( "Ym" ) ] = $this->data[ "socio" ]->getPremieres( date( "Ym" ) );
        $this->data[ "productos" ] = model( "ProductoModel" )->where( $sql , null, false )->findAll();

        load_catalogo( "promociones",    "{$activo} AND modelo_codigo = '{$modelo}'");
        load_catalogo( "metodosentrega", "{$activo} AND ( modelo_codigo = '{$modelo}' OR codigo = '00-ALMACEN')");
        load_catalogo( "almacenes",      "{$activo} AND modelo_codigo = '{$modelo}'");
        load_catalogo( "metodospago",    "modelo_codigo = '{$modelo}'");

        $this->data[ "pedido" ] = $this->data[ "socio" ]->getPedido( $modelo );
        $this->data[ "socio" ]->PTS = $this->data[ "socio" ]->getCalificaciones( $modelo );
        $this->data[ "titulo" ] = "Tienda en línea";
        $this->data[ "pedido" ][ "data" ][ "pesoxbulto" ] = MODELOS[ $modelo ][ "settings" ][ "pesoxbulto" ];

        $this->data[ "modelo" ] = $modelo;
        
        echo template( "pedidos/shop", $this->data );
    }


    public function save_pedido()
    {
        model( "PedidoModel" )->save( json_decode( $this->request->getPost( "json" ) ) );
        echo json_encode( [ "ok" ] );
    }


    public function reparte()
    {
        if( 
            $this->data[ "usuario" ]->permiso( "40-ADMIN" ) || 
            $this->data[ "usuario" ]->permiso( "28-INGRESA" ) 
        ){

            $db = db_connect();
            
            $pedido    = model( "PedidoModel" )->find( $this->request->getPost( "pedido" ) );
            $modelo    = $pedido[ "modelo_codigo" ];
            $u         = model( "UsuarioModel" )->find( $pedido[ "usuario_id" ] );
            $data      = $u->data;                                   
            $historial = $u->historial;  
        
            // Asegurarse de que existan los KEYS en el objeto JSON
            foreach( $pedido[ "PTS" ] as $promo => $pts ){
                if( !is_object( $historial->modelos->{$modelo}->primercompra ) ){
                    $historial->modelos->{$modelo}->primercompra = json_decode( '{}' );
                }

                if( !isset( $historial->modelos->{$modelo}->primercompra->{$promo} ) ){
                    $historial->modelos->{$modelo}->primercompra->{$promo} = substr( $pedido[ "fechas" ][ "califica" ], 0, 10 );
                }
            } 

            $f = $historial->modelos->{$modelo}->ultimacompra = $pedido[ "fechas" ][ "califica" ];

            if( !sizeof( (array)$historial->modelos->{$modelo}->calificaciones ) ){
                $historial->modelos->{$modelo}->calificaciones = json_decode( "{}" );
            }

            // Regresamos los valores al objeto Usuario
            $u->data      = $data;
            $u->historial = $historial;

            model( "UsuarioModel" )->save( $u );

            $db->query( "do f_update_PTS( {$u->id}, '{$pedido[ "modelo_codigo" ]}', '".date( 'Ym', strtotime( date('Y-m', $f ).'-01'. ' -1 month' ) )."' )" ); 
            $db->query( "do f_update_PTS( {$u->id}, '{$pedido[ "modelo_codigo" ]}', '".date( "Ym", strtotime( $f ) )."' )" );  
            $db->query( "do f_get_estatus( {$u->id}, 0 )" );
            $db->query( "do f_reparte_comisiones( {$pedido[ "id" ]}, 0 )" );    

            // BITACORA Actualizar reparto de comisiones
            bitacora( 31, $this->data[ "usuario" ]->id, [ 
                "pedido" => $pedido[ "id" ]
            ] );
        }

        return redirect()->to( "pedido/".$pedido[ "referencia" ] )->with( "msg", [ 
            "clase" => "success", 
            "icono" => "check", 
            "texto" => "Se recalcularon comisiones de pedido" ] );      
    }


    public function cambia_fecha()
    {
        if( 
            $this->data[ "usuario" ]->permiso( "40-ADMIN" ) || 
            $this->data[ "usuario" ]->permiso( "28-INGRESA" ) &&
            validafecha( $this->request->getPost( "nueva" ) ) 
        ){

            $pedido = model( "PedidoModel" )->find( $this->request->getPost( "pedido" ) );
            $fechas = $pedido[ "fechas" ];

            // BITACORA Cambiar fecha de calificacion en pedido
            bitacora( 32, $this->data[ "usuario" ]->id, [ 
                "pedido"   => $pedido[ "id" ],
                "anterior" => $fechas[ "califica" ],
                "nueva"    => $this->request->getPost( "nueva" )
            ] );

            $mesprevio = date( "Ym", strtotime( $fechas[ "califica" ] ) );

            $fechas[ "califica" ] = $this->request->getPost( "nueva" );
            $pedido[ "fechas" ]   = $fechas;

            model( "PedidoModel" )->save( $pedido );

            $mescalifica = date( "Ym", strtotime( $fechas[ "califica" ] ) );

            $db = db_connect();

            $db->query( "select f_update_PTS( {$pedido[ "usuario_id" ]}, '{$pedido[ "modelo_codigo" ]}', '{$mescalifica}' )" ); 

            if( $mescalifica != $mesprevio ){
                $db->query( "select f_update_PTS( {$pedido[ "usuario_id" ]}, '{$pedido[ "modelo_codigo" ]}', '{$mesprevio}' )" );  
            }

            $db->query( "select f_get_estatus( {$pedido[ "usuario_id" ]}, 1 )" );
            $db->query( "select f_reparte_comisiones( {$pedido[ "id" ]}, 0 )" )->getRow();            
        }

        return redirect()->to( "pedido/".$pedido[ "referencia" ] )->with( "msg", [ 
            "clase" => "success", 
            "icono" => "check", 
            "texto" => "Se actualizó la fecha de compra del pedido" ] );      
    }


    public function cambia_edicion()
    {
        $pedido = model( "PedidoModel" )->find( $this->request->getPost( "pedido" ) );
        
        if( $pedido[ "estatus_codigo" ] == "255-PENDIENTE" && ( 
            $this->data[ "usuario" ]->permiso( "40-ADMIN" ) || 
            $this->data[ "usuario" ]->permiso( "28-INGRESA" ) ||
            $this->data[ "usuario" ]->id == $pedido[ "usuario_id" ]
         ) ){
           
            $pedido[ "estatus_codigo" ] = "250-EN-PROCESO";
            model( "PedidoModel" )->save( $pedido );

            // BITACORA Cancelar pedido
            bitacora( 68, $this->data[ "usuario" ]->id, [ 
                "pedido" => $pedido[ "id" ]
            ] );
        }

        return redirect()->to( "pedido/".$pedido[ "referencia" ] );      
    }   



    public function cancela_pedido()
    {
        $pedido = model( "PedidoModel" )->find( $this->request->getPost( "pedido" ) );

        if( 
            $this->data[ "usuario" ]->permiso( "40-ADMIN" ) || 
            $this->data[ "usuario" ]->permiso( "28-INGRESA" ) ||
            $this->data[ "usuario" ]->id == $pedido[ "usuario_id" ]
         ){

            $previo = $pedido[ "estatus_codigo" ];
            $fechas = $pedido[ "fechas" ];

            $fechas[ "cancela" ]        = date( "Y-m-d H:i:s" );
            $pedido[ "fechas" ]         = $fechas;
            $pedido[ "estatus_codigo" ] = "150-CANCELADO";

            model( "PedidoModel" )->save( $pedido );

            // Actualizar puntajes del mes ya sin el pedido
            if( substr( $previo, 0, 3 ) > 400 ){
                $mescalifica = date( "Ym", strtotime( $fechas[ "califica" ] ) );

                $db = db_connect();

                $db->query( "select f_update_PTS( {$pedido[ "usuario_id" ]}, '{$pedido[ "modelo_codigo" ]}', '{$mescalifica}' )" );
                $db->query( "select f_get_estatus( {$pedido[ "usuario_id" ]}, 1 )" );
            }

            // BITACORA Cancelar pedido
            bitacora( 33, $this->data[ "usuario" ]->id, [ 
                "pedido" => $pedido[ "id" ]
            ] );
        }

        return redirect()->to( "pedido/".$pedido[ "referencia" ] )->with( "msg", [ 
            "clase" => "success", 
            "icono" => "check", 
            "texto" => "Se ha cancelado el pedido" ] );      
    }    


    public function fondeo()
    {
        extract( $this->request->getPost() );
        echo $this->data[ "usuario" ]->fondeo( $pedido, $metodo, $cantidad );
    }


    public function paga_pedido()
    {
        extract( $this->request->getPost() );
    
        $p = model( "PedidoModel" )->find( $pedido );

        if(
            $this->data[ "usuario" ]->permiso( "40-ADMIN" ) || 
            $this->data[ "usuario" ]->permiso( "28-INGRESA" )
        ){
            load_catalogo( "metodospago", "modelo_codigo = '{$p[ "modelo_codigo" ]}'" );

            $metodopago = METODOSPAGO[ $metodopago ];
            $u = model( "UsuarioModel" )->find( $p[ "usuario_id" ] );

            $p[ "estatus_codigo" ]    = "420-PAGADO";
            $p[ "metodopago_codigo" ] = $metodopago[ "codigo" ];

            $saldo = $u->saldo( $p[ "modelo_codigo" ] );
            $total = $p[ "data" ][ "total" ] + $p[ "data" ][ "comisionentrega" ] - $saldo;
        
            $p[ "data"][ "comisionbanco"] = $metodopago[ "settings" ][ "tipocomision" ] == "efectivo" ? $metodopago[ "settings" ][ "comision" ] : ceil( ( $total * $metodopago[ "settings" ][ "comision" ] / 100 ) );
            $p[ "fechas" ][ "pagado" ]    = $fecha_pagado;
            $p[ "fechas" ][ "califica" ]  = $fecha_califica;
            $p[ "fechas" ][ "reparte" ]   = $fecha_reparte;
            $p[ "data" ][ "saldo" ]       = $saldo;

            model( "PedidoModel" )->save( $p );

            $data      = $u->data;                                    
            $historial = $u->historial;  

            $data->saldo->{$p[ "modelo_codigo" ]}->cantidad = 0;
            $data->saldo->{$p[ "modelo_codigo" ]}->estatus  = 0;
        
            foreach( $p[ "PTS" ] as $promo => $pts ){
                if( !is_object( $historial->modelos->{$p[ "modelo_codigo" ]}->primercompra ) ){
                    $historial->modelos->{$p[ "modelo_codigo" ]}->primercompra = json_decode( '{}' );
                }

                if( !isset( $historial->modelos->{$p[ "modelo_codigo" ]}->primercompra->{$promo} ) ){
                    $historial->modelos->{$p[ "modelo_codigo" ]}->primercompra->{$promo} = substr( $p[ "fechas" ][ "califica" ], 0, 10 );
                }
            } 

            $historial->modelos->{$p[ "modelo_codigo" ]}->ultimacompra = $p[ "fechas" ][ "califica" ];

            $u->data      = $data;
            $u->historial = $historial;

            model( "UsuarioModel" )->save( $u );    

            $db = db_connect();
            $db->query( "select f_update_PTS( {$u->id}, '{$p[ "modelo_codigo" ]}', '".date( "Ym", strtotime( $fecha_califica ) )."' )" );  
            $db->query( "select f_get_estatus( {$u->id}, 1 )" );
            $db->query( "select f_reparte_comisiones( {$p[ "id" ]}, 0 )" );
        
            // BITACORA Marcar pedido como pagado
            bitacora( 56, $this->data[ "usuario" ]->id, [ 
                "pedido"   => $p[ "id" ],
                "pagado"   => $fecha_pagado,
                "califica" => $fecha_califica,
                "reparte"  => $fecha_reparte
            ] );
        }
        
        return redirect()->to( 'pedido/'.$p[ "referencia"] );
    }

    public function compra_demo( $usuario, $modelo, $mes )
    {
        extract( $this->request->getPost() );

        $socio = model( "UsuarioModel" )->find( $usuario );

        load_catalogo( "promociones",    "estatus_codigo = '201-ACTIVO' AND modelo_codigo = '{$modelo}'");
        load_catalogo( "metodospago",    "estatus_codigo = '201-ACTIVO' AND modelo_codigo = '{$modelo}'");
        load_catalogo( "metodosentrega", "estatus_codigo = '201-ACTIVO' AND modelo_codigo = '{$modelo}'");

        $pedido      = $socio->getPedido( $modelo );
        $data        = $pedido[ "data" ];
        $promociones = $pedido[ "promociones" ];
        $PTS         = $pedido[ "PTS" ];
        $fechas      = $pedido[ "fechas" ];

        $metodopago    = METODOSPAGO[ MODELOS[ $modelo ][ "settings" ][ "metodopago_base" ] ];
        $metodoentrega = METODOSENTREGA[ MODELOS[ $modelo ][ "settings" ][ "metodoentrega_base" ] ];

        $pedido[ "metodoentrega_codigo" ] = $metodoentrega[ "codigo" ];
        $data[ "entrega" ]                = VARIABLES[ "almacen_paqueteria" ][ "valor" ];
        $promocion_base                   = MODELOS[ $modelo ][ "settings" ][ "promocion_base" ][ 0 ];
        $promociones[ $promocion_base ]   = [];
        $promociones[ $promocion_base ][ "productos" ] = [];

        $producto = model( "ProductoModel" )->find( PROMOCIONES[ $promocion_base ][ "settings" ][ "producto_base" ] );
        $cantidad = PROMOCIONES[ $promocion_base ][ "settings" ][ "cantidad_base" ];

        $promociones[ $promocion_base ][ "productos" ][ $producto->codigo ] = [
            "nombre"       => $producto->data->nombre,
            "precio"       => $producto->precio->total,
            "cantidad"     => $cantidad,
            "puntos"       => $producto->data->puntos->{$promocion_base},
            "comisionable" => $producto->precio->base,
            "descripcion"  => $producto->data->descripcion,
            "orden"        => 1
        ];

        $promociones[ $promocion_base ][ "precio" ]       = $producto->precio->total * $cantidad;
        $promociones[ $promocion_base ][ "comisionable" ] = $producto->precio->base * $cantidad;

        $PTS[ $promocion_base ]    = $producto->data->puntos->{$promocion_base} * $cantidad;

        $data[ "peso" ]            = $producto->data->dimensiones->peso * $cantidad;
        $data[ "total" ]           = $producto->precio->total * $cantidad;
        $data[ "productos" ]       = $cantidad;
        $data[ "pesoxbulto" ]      = $metodoentrega[ "settings" ][ "gramaje" ];
        $data[ "comisionbanco" ]   = $metodopago[ "settings" ][ "comision" ];
        $data[ "comisionentrega" ] = $metodoentrega[ "settings" ][ "costo" ];

        $fechas[ "creado" ]        = date( "Y-m-d" );
        $pedido[ "data" ]          = $data;
        $pedido[ "promociones" ]   = $promociones;
        $pedido[ "PTS" ]           = $PTS;
        $pedido[ "fechas" ]        = $fechas;

        model( "PedidoModel" )->save( $pedido );

        $fondeo = $data[ "total" ] + $metodopago[ "settings" ][ "comision" ];

        $socio->fondeo( $modelo, $metodopago[ "codigo" ], $fondeo, $mes );

        return redirect()->to( "red/{$modelo}" );
    }
    

    public function beneleit_movil()
    {
        $this->data[ "navbar" ] = true;
        $this->data[ "titulo" ] = "Beneleit Móvil";

        echo template( "pedidos/beneleit_movil", $this->data );
    }


    public function checkout()
    {
        $this->data[ "metodopago" ] = model( "MetodopagoModel" )->find( $this->request->getPost( "metodopago" ) );
        $this->data[ "socio" ]      = $this->data[ "usuario" ];
        $this->data[ "pedido" ]     = model( "PedidoModel" )->find( $this->request->getPost( "pedido" ) );

        if( !$this->data[ "pedido" ] || !in_array( $this->data[ "pedido" ][ "estatus_codigo" ], [ "250-EN-PROCESO", "255-PENDIENTE" ] ) ){ 
            return redirect()->to( 'historial' );
        }

        $this->data[ "modelo" ] = $this->data[ "pedido" ][ "modelo_codigo" ];

       // if(  $this->data[ "pedido" ][ "estatus_codigo" ] == "250-EN-PROCESO" ){

            $domicilios = $this->data[ "socio" ]->getDomicilios();

            $this->data[ "pedido" ][ "data" ][ "domicilio" ] = in_array( substr( $this->data[ "pedido" ][ "metodoentrega_codigo" ], 0, 2 ), [ "00", "11" ] ) ? null : $domicilios[ $this->data[ "pedido" ][ "data" ][ "entrega" ] ];

            $total = $this->data[ "pedido" ][ "data" ][ "total" ] - $this->data[ "socio" ]->saldo( $this->data[ "modelo" ] ) + $this->data[ "pedido" ][ "data" ][ "comisionentrega" ];

            $this->data[ "pedido" ][ "estatus_codigo" ]          = "255-PENDIENTE";
            $this->data[ "pedido" ][ "metodopago_codigo" ]       = $this->data[ "metodopago" ][ "codigo" ];
            $this->data[ "pedido" ][ "data" ][ "comisionbanco" ] = $this->data[ "metodopago" ][ "settings" ][ "tipocomision" ] == "porcentaje" ? ceil( $total * $this->data[ "metodopago" ][ "settings" ][ "comision" ] / 100 ) : $this->data[ "metodopago" ][ "settings" ][ "comision" ];

            model( "PedidoModel" )->save( $this->data[ "pedido" ] );

            $this->data[ "cantidad" ] = $total + $this->data[ "pedido" ][ "data" ][ "comisionbanco" ];
     //   }

        $this->data[ "navbar" ]   = true;
        $this->data[ "titulo" ]   = "Pago de pedido: ".MODELOS[ $this->data[ "modelo" ] ][ "nombre" ];

       return template( "pedidos/gateways/".$this->data[ "metodopago" ][ "codigo" ], $this->data );
    }


    public function ticket( $link )
    {
        $p = base64_decode( urldecode( str_replace( "___", "%", $link ) ) );

        $this->data[ "print"  ] = true;
        $this->data[ "pedido" ] = model( "PedidoModel" )->find( $p );
        $this->data[ "socio"  ] = model( "UsuarioModel" )->find( $this->data[ "pedido" ][ "usuario_id" ] );
        $this->data[ "modelo" ] = $this->data[ "pedido" ][ "modelo_codigo" ];

        $sql = "modelo_codigo = '{$this->data[ "modelo" ]}'";

        $this->data[ "productos" ] = model( "ProductoModel" )->where( $sql , null, false )->findAll();

        load_catalogo( "promociones",    "estatus_codigo = '201-ACTIVO' AND modelo_codigo = '{$this->data[ "modelo" ]}'");
        load_catalogo( "metodospago",    "modelo_codigo  = '{$this->data[ "modelo" ]}'");
        load_catalogo( "metodosentrega", "modelo_codigo  = '{$this->data[ "modelo" ]}' OR codigo = '00-ALMACEN'");
        load_catalogo( "almacenes",      "modelo_codigo  = '{$this->data[ "modelo" ]}'");

        echo template( "pedidos/ticket", $this->data );
    }
}

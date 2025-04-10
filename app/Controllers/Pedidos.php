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
        load_catalogo( "metodosentrega", "modelo_codigo = '{$modelo}' OR codigo in ( '00-ALMACEN', '90-NO-ENTREGA' )");
        load_catalogo( "promociones",    "modelo_codigo = '{$modelo}' OR settings->'$.universal' = true");

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

            load_catalogo( "metodosentrega", "modelo_codigo = '{$modelo}' OR codigo in ( '00-ALMACEN', '90-NO-ENTREGA' )");
            load_catalogo( "almacenes",      "modelo_codigo = '{$modelo}'");
            load_catalogo( "promociones",    "modelo_codigo = '{$modelo}' OR settings->'$.universal' = true");
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
                $this->data[ "usuario" ]->permiso( "38-CONTABILIDAD" ) ||
                $this->data[ "usuario" ]->permiso( "40-ADMIN" )
            ) ){
                return template( "pedidos/no_permiso", $this->data );
            }
            
            /**********************************/

            $this->data[ "link" ]  = str_replace( "%", "___", urlencode( base64_encode( $this->data[ "pedido" ][ "id" ] ) ) );
            $this->data[ "socio" ] = model( "UsuarioModel" )->find( $this->data[ "pedido" ][ "usuario_id" ] );
            $this->data[ "socio" ]->PTS = $this->data[ "socio" ]->getCalificaciones( $this->data[ "pedido" ][ "modelo_codigo" ] );

            $sql = "modelo_codigo = '{$modelo}' OR data->'$.universal' = true";
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
            $sql    = "{$activo} AND ( modelo_codigo = '{$modelo}' OR data->'$.universal' = true )";

            $this->data[ "socio" ]     = $this->data[ "usuario" ];
            $this->data[ "pagado" ]    = 0;
            $this->data[ "enproceso" ] = 1;
            $this->data[ "bloqueado" ] = 0;
            $this->data[ "cancelado" ] = 0;
            $this->data[ "entregado" ] = 0;
            $this->data[ "premieres" ][ date( "Ym" ) ] = $this->data[ "socio" ]->getPremieres( date( "Ym" ) );
            $this->data[ "productos" ] = model( "ProductoModel" )->where( $sql , null, false )->findAll();

            load_catalogo( "promociones",    "{$activo} AND modelo_codigo = '{$modelo}' OR settings->'$.universal' = true");
            load_catalogo( "metodosentrega", "{$activo} AND ( modelo_codigo = '{$modelo}' OR codigo in ( '00-ALMACEN', '90-NO-ENTREGA' ) )");
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

            load_catalogo( "metodosentrega", "modelo_codigo = '{$modelo}' OR codigo in ( '00-ALMACEN', '90-NO-ENTREGA' )");
            load_catalogo( "almacenes",      "modelo_codigo = '{$modelo}'");
            load_catalogo( "promociones",    "modelo_codigo = '{$modelo}' OR settings->'$.universal' = true");
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

            load_catalogo( "promociones",    "{$activo} AND modelo_codigo = '{$modelo}' OR settings->'$.universal' = true");
            load_catalogo( "metodosentrega", "{$activo} AND ( modelo_codigo = '{$modelo}' OR codigo in ( '00-ALMACEN', '90-NO-ENTREGA' ) )");
            load_catalogo( "almacenes",      "{$activo} AND modelo_codigo = '{$modelo}'", "stocks" );
            load_catalogo( "metodospago",    "modelo_codigo = '{$modelo}'");

            define( "ALMACENES", STOCKS );

            $this->data[ "pedido" ] = $this->data[ "socio" ]->getPedido( $modelo );
            $this->data[ "socio" ]->PTS = $this->data[ "socio" ]->getCalificaciones( $modelo );
            $this->data[ "titulo" ] = "Tienda en línea";
            $this->data[ "pedido" ][ "data" ][ "pesoxbulto" ] = MODELOS[ $modelo ][ "settings" ][ "pesoxbulto" ];
        }

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

                if( !isset( $historial->modelos->{$modelo}->primercompra->{$promo} ) && $pts > 0 ){
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

            $db->query( "do f_update_PTS( {$u->id}, '{$pedido[ "modelo_codigo" ]}', '".date( 'Ym', strtotime( date('Y-m', strtotime( $f.'-01'. ' -1 month' ) ) ) )."' )" );             
            $db->query( "do f_update_PTS( {$u->id}, '{$pedido[ "modelo_codigo" ]}', '".date( "Ym", strtotime( $f ) )."' )" );  
            $db->query( "do f_get_estatus( {$u->id}, 1 )" );
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

            $db->query( "do f_update_PTS( {$pedido[ "usuario_id" ]}, '{$pedido[ "modelo_codigo" ]}', '{$mescalifica}' )" ); 

            if( $mescalifica != $mesprevio ){
                $db->query( "do f_update_PTS( {$pedido[ "usuario_id" ]}, '{$pedido[ "modelo_codigo" ]}', '{$mesprevio}' )" );  
            }

            $db->query( "do f_get_estatus( {$pedido[ "usuario_id" ]}, 0 )" );
            $db->query( "do f_reparte_comisiones( {$pedido[ "id" ]}, 0 )" );            
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

                $db->query( "do f_update_PTS( {$pedido[ "usuario_id" ]}, '{$pedido[ "modelo_codigo" ]}', '{$mescalifica}' )" );
                $db->query( "do f_get_estatus( {$pedido[ "usuario_id" ]}, 0 )" );
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


    public function edita_guia()
    {
        $pedido = model( "PedidoModel" )->find( $this->request->getPost( "pedido" ) );

        if( 
            $this->data[ "usuario" ]->permiso( "40-ADMIN" ) || 
            $this->data[ "usuario" ]->permiso( "25-PAQUETERIA" )
         ){

            $previo = $pedido[ "data" ][ "guia" ];
            $pedido[ "data" ][ "guia" ] = $this->request->getPost( "guia_nueva" );

            model( "PedidoModel" )->save( $pedido );

            // BITACORA Cambiar guia
            bitacora( 74, $this->data[ "usuario" ]->id, [ 
                "pedido"   => $pedido[ "id" ],
                "anterior" => $previo,
                "nueva"    => $pedido[ "data" ][ "guia" ]
            ] );
        }

        return redirect()->to( "pedido/".$pedido[ "referencia" ] )->with( "msg", [ 
            "clase" => "success", 
            "icono" => "check", 
            "texto" => "Se ha actualizado la guía de rastreo de paquetería" ] );      
    } 

    
    public function edita_almacen()
    {
        $pedido = model( "PedidoModel" )->find( $this->request->getPost( "pedido" ) );

        if( 
            $this->data[ "usuario" ]->permiso( "40-ADMIN" ) || 
            $this->data[ "usuario" ]->permiso( "28-INGRESA" ) || 
            $this->data[ "usuario" ]->permiso( "32-EDICION" ) ||                         
            $this->data[ "usuario" ]->permiso( "25-PAQUETERIA" )
         ){

            $previo  = $pedido[ "data" ][ "entrega" ];
            $almacen = model( "AlmacenModel" )->find( $this->request->getPost( "nuevo_almacen" ) );

            $pedido[ "data" ][ "entrega" ] = $almacen[ "codigo" ];
            $pedido[ "data" ][ "comisionentrega" ] = VARIABLES[ "tarifas_almacen" ][ "valor" ][ $almacen[ "settings" ][ "tarifa" ] ];

            model( "PedidoModel" )->save( $pedido );

            // BITACORA Cambiar guia
            bitacora( 76, $this->data[ "usuario" ]->id, [ 
                "pedido"   => $pedido[ "id" ],
                "anterior" => $previo,
                "nueva"    => $pedido[ "data" ][ "entrega" ]
            ] );
        }

        return redirect()->to( "pedido/".$pedido[ "referencia" ] )->with( "msg", [ 
            "clase" => "success", 
            "icono" => "check", 
            "texto" => "Se ha actualizado el punto de entrega" ] );      
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

                if( !isset( $historial->modelos->{$p[ "modelo_codigo" ]}->primercompra->{$promo} ) && $pts > 0 ){
                    $historial->modelos->{$p[ "modelo_codigo" ]}->primercompra->{$promo} = substr( $p[ "fechas" ][ "califica" ], 0, 10 );
                }    
            } 

            $historial->modelos->{$p[ "modelo_codigo" ]}->ultimacompra = $p[ "fechas" ][ "califica" ];

            $u->data      = $data;
            $u->historial = $historial;

            model( "UsuarioModel" )->save( $u );    

            $db = db_connect();
            $db->query( "do f_update_PTS( {$u->id}, '{$p[ "modelo_codigo" ]}', '".date( "Ym", strtotime( $fecha_califica ) )."' )" );  
            $db->query( "do f_get_estatus( {$u->id}, 0 )" );
            $db->query( "do f_reparte_comisiones( {$p[ "id" ]}, 0 )" );
        
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
        $this->data[ "pedido" ]     = model( "PedidoModel" )->find( $this->request->getPost( "pedido" ) );
        $this->data[ "socio" ]      = model( "UsuarioModel" )->find( $this->data[ "pedido" ][ "usuario_id" ] );

        if( !$this->data[ "pedido" ] || !in_array( $this->data[ "pedido" ][ "estatus_codigo" ], [ "250-EN-PROCESO", "255-PENDIENTE" ] ) ){ 
            return redirect()->to( 'historial' );
        }

        $this->data[ "modelo" ] = $this->data[ "pedido" ][ "modelo_codigo" ];

       // if(  $this->data[ "pedido" ][ "estatus_codigo" ] == "250-EN-PROCESO" ){

            $domicilios = $this->data[ "socio" ]->getDomicilios();
            
            if( !$this->data[ "pedido" ][ "metodoentrega_codigo" ] ){ 
                switch ($this->data[ "pedido" ][ "modelo_codigo" ]) {
                    case "10-NUTRICION":
                        $this->data[ "pedido" ][ "metodoentrega_codigo" ] = "90-NO-ENTREGA";
                        break;
                    case "40-GASOLINAS":
                        $this->data[ "pedido" ][ "metodoentrega_codigo" ] = "15-GAS";
                        break;
                    case "50-INVERSION":
                        $this->data[ "pedido" ][ "metodoentrega_codigo" ] = "90-NO-ENTREGA";
                        break;
                }
            }
            
            $this->data[ "pedido" ][ "data" ][ "domicilio" ] = in_array( substr( $this->data[ "pedido" ][ "metodoentrega_codigo" ], 0, 2 ), [ "00", "11", "13", "15", "19", "90" ] ) ? null : $domicilios[ $this->data[ "pedido" ][ "data" ][ "entrega" ] ];

            $total = $this->data[ "pedido" ][ "data" ][ "total" ] - $this->data[ "socio" ]->saldo( $this->data[ "modelo" ] ) + $this->data[ "pedido" ][ "data" ][ "comisionentrega" ];

            $this->data[ "pedido" ][ "estatus_codigo" ]          = "255-PENDIENTE";
            $this->data[ "pedido" ][ "metodopago_codigo" ]       = $this->data[ "metodopago" ][ "codigo" ];
            $this->data[ "pedido" ][ "data" ][ "comisionbanco" ] = $this->data[ "metodopago" ][ "settings" ][ "tipocomision" ] == "porcentaje" ? ceil( $total * $this->data[ "metodopago" ][ "settings" ][ "comision" ] / 100 ) : $this->data[ "metodopago" ][ "settings" ][ "comision" ];

            model( "PedidoModel" )->save( $this->data[ "pedido" ] );

            // BITACORA Procesa pedido para pago

            bitacora( 85, $this->data[ "usuario" ]->id, [ 
                "pedido"     => $this->data[ "pedido" ][ "id" ],
                "metodopago" => $this->data[ "metodopago" ][ "codigo" ]
            ] );
            
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

        load_catalogo( "promociones",    "modelo_codigo = '{$this->data[ "modelo" ]}' OR settings->'$.universal' = true");
        load_catalogo( "metodospago",    "modelo_codigo  = '{$this->data[ "modelo" ]}'");
        load_catalogo( "metodosentrega", "modelo_codigo  = '{$this->data[ "modelo" ]}' OR codigo in ( '00-ALMACEN', '90-NO-ENTREGA' )");
        load_catalogo( "almacenes",      "modelo_codigo  = '{$this->data[ "modelo" ]}'");

        echo template( "pedidos/ticket", $this->data );
    }


    public function carga_csf(){
        $socio    = $this->data[ "usuario" ];
        $rfc      = $this->request->getPost( "factura_rfc" );
        $uso      = $this->request->getPost( "factura_uso" );
        $mp       = $this->request->getPost( "factura_mp" );
        $correo   = $this->request->getPost( "factura_correo" );
        $pedido   = model( "PedidoModel" )->find( $this->request->getPost( "pedido_id" ) );

        $json = $socio->data;

        $json->sat->rfc    = $rfc;
        $json->sat->mp     = $mp; 
        $json->sat->uso    = $uso;
        $json->sat->correo = $correo;
       
        // BITACORA Actualziar RFC
        bitacora( 48, $socio->id, [ 
            "rfc"    => $rfc,
            "uso"    => $uso,
            "mp"     => $mp,
            "correo" => $correo,
            "pedido" => $pedido[ "id" ]
        ] );

        if( $this->request->getPost( "factura_csf_carga" ) == 0 ){
            $path     = "data/{$socio->id}/csf/";
            $filename = $socio->id."_".time().".pdf";

            $json->sat->csf = $filename;

            if( !is_dir( $path ) ){
                mkdir( $path, 0755, true );
            }

            $fileTmpName = $_FILES[ "factura_csf" ][ "tmp_name" ];
            move_uploaded_file( $fileTmpName, $path.$filename );

            // BITACORA Carga de CSF
            bitacora( 22, $socio->id, [ 
                "archivo" => $filename,
                "usuario" => $socio->id
            ] );

        }

        $socio->data = $json; 
        model( "UsuarioModel" )->save( $socio );

        $pedido[ "data" ][ "sat" ] = [ 
            "rfc"     => $rfc,
            "mp"      => $mp,
            "uso"     => $uso,
            "correo"  => $correo,
            "factura" => "144-FACTURA-PENDIENTE"
        ];

        model( "PedidoModel" )->save( $pedido );

        // BITACORA Carga de CSF
        bitacora( 81, $socio->id, [ 
            "pedido" => $pedido[ "id" ],
            "rfc"    => $rfc
        ] );
        
        return redirect()->to( "tienda/".$pedido[ "modelo_codigo" ] );
    } 

    
    public function txhash(){
        $hash = $this->request->getPost( "hash" );

        $respuesta = [
            "error"   => "Error desconocido",
            "success" => false
        ];

        // validamos que tenga una longitud correcta

        $pagado   = false;
        $cantidad = 0;
        $pedido   = model( "PedidoModel" )->find( $this->request->getPost( "pedido" ) );
        $producto = model( "ProductoModel" )->find( array_keys( $pedido[ "promociones"][ "510-SEMILLA" ][ "productos" ] ) )[ 0 ];
        $u        = model( "UsuarioModel" )->find( $pedido[ "usuario_id" ] );

        // validamos cantidad
            
        $fecha = date( "Y-m-d H:i:s" );
        $saldo = $u->saldo( $pedido[ "modelo_codigo" ] );
        
        // marcado de inversiones por administrador sin necesidad de TxHash

        if( session( "admin" ) && substr( $hash, 0, 8 ) == 'beneleit' ){
            $h = explode( "_", trim( $hash ) );

            if( strlen( $h[1] ) == 10 && strtotime( $h[1] ) ){
                $fecha = $h[1];
                $hash  = "saldo"; 
                $saldo = $pedido[ "data" ][ "total" ];
            }
        }

        // marcado de inversiones por saldo a favor y/o acumulación de comisiones

        if( $hash == "saldo" ){
            $pagado   = true;
            $hash     = time()."_".md5( $pedido[ "id" ] );
            $cantidad = $saldo;
            $total    = $pedido[ "data" ][ "total" ];
            $tx       = [
                "from_address" => "",
                "to_address"   => ""
            ];

            $respuesta[ "error" ]   = false;
            $respuesta[ "success" ] = [];            
        }
        else{
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

                    if( ( session( "admin" ) || $tx[ "cost" ][ "date_created" ] > "2025-03-13" ) && $tx[ "contractRet" ] == "SUCCESS" && $tx[ "confirmed" ] == true ){
                        
                        // validamos que sea al wallet destino correcto

                        $address = false;
                        foreach( $vars[ "wallets" ] as $wallet ){
                            if( $tx[ "to_address" ] == $wallet[ "token"] && $wallet[ "estatus" ] == "201-ACTIVO" ){
                                $address = true;
                            }
                        }

                        // Si se encontró wallet

                        if( $address ){
                            
                            // nos aseguramos de que la transacción no haya sido registrada antes

                            $db  = db_connect();
                            $sql = "select count(*) as existe from t_fondeos where metodopago_codigo = '35-HASH' AND operacion = '{$hash}'";

                            $row = $db->query( $sql )->getrow();

                            if( !$row->existe ){
                                $pagado   = true;
                                $cantidad = $tx[ "amount_str" ] / pow( 10, $tx[ "decimals" ] );
                                $total    = $pedido[ "data" ][ "total" ] - $saldo;
                                
                                $respuesta[ "error" ]   = false;
                                $respuesta[ "success" ] = [];
                            }
                            else{
                                $respuesta[ "error" ] = "<h5 class=\"mb-0 text-red\">TxHash ya registrado</h5>Esta transacción ya ha sido registrada anteriormente en la base de datos";
                            }
                        }
                        else{
                            $respuesta[ "error" ] = "<h5 class=\"mb-0 text-red\">Wallet destino incorrecta</h5>La transacción ingresada no tiene como destino alguna wallet de Beneleit / Capital24";
                        }
                    }
                    else{
                        $respuesta[ "error" ] = "<h5 class=\"mb-0 text-red\">TxHash no confirmado</h5>La transacción no ha sido confirmada en la blockchain";
                    }
                }
                else{
                    $respuesta[ "error" ] = "<h5 class=\"mb-0 text-red\">TxHash no encontrado</h5>No existe información en la red para el hash ingresado";
                }
            }
            else{
                $respuesta[ "error" ] = "<h5 class=\"mb-0 text-red\">Hash incorrecto</h5>Ingresaste ".strlen( $hash )." caracteres";
            } 
        }

        $cantidad = intval( $cantidad * 100 ) / 100;
        $total    = intval( $total * 100 ) / 100;

        if( $pagado ){

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

            $data      = $u->data;                                    
            $historial = $u->historial;  

            // si el deposito es suficiente

            $s  = $data->saldo->{$pedido[ "modelo_codigo" ]};
            $db = db_connect();

            if( $cantidad >= $total ){
                // cambiar estatus de pedido

                $pedido[ "estatus_codigo" ]       = "420-PAGADO";
                $pedido[ "fechas" ][ "pagado" ]   = $fecha;
                $pedido[ "fechas" ][ "califica" ] = date( "Y-m-d H:i:s" );
                $pedido[ "fechas" ][ "reparte" ]  = date( "Y-m-d H:i:s" );
                $pedido[ "data" ][ "saldo" ]      = session( "admin" ) ? 0 : ( $saldo >= $total ? $total : $saldo );

                // si la cantidad es mayor a la total

                if( $cantidad > $total ){

                    // si hay saldo en USDT
                    if( $s->USDT > 0 ){
                        if( $s->USDT > $total ){
    
                            // no puede quedar saldo en USDT a favor después de una compra
                            // se va en automático a depósito como '520-SALDO'
    
                            $sql = "INSERT INTO t_comisiones 
                                    VALUES ( NULL, '255-PENDIENTE', {$pedido[ "id" ]}, {$pedido[ "usuario_id" ]}, '520-SALDO', 0, 0, ".( $s->USDT - $total ).", '{$pedido[ "fechas" ][ "reparte" ]}', NULL )";

                            $db->query( $sql );
                        }
                        $s->USDT = 0;
                    }

                    // Si hay saldo normal
                    else{
                        $s->cantidad = $cantidad - $total;
                    }
                }
                else{
                    $s->USDT     = 0;
                    $s->cantidad = 0;
                    $s->estatus  = 0;
                }

                foreach( $pedido[ "PTS" ] as $promo => $pts ){
                    if( !is_object( $historial->modelos->{$pedido[ "modelo_codigo" ]}->primercompra ) ){
                        $historial->modelos->{$pedido[ "modelo_codigo" ]}->primercompra = json_decode( '{}' );
                    }

                    if( !isset( $historial->modelos->{$pedido[ "modelo_codigo" ]}->primercompra->{$promo} ) && $pts > 0 ){
                        $historial->modelos->{$pedido[ "modelo_codigo" ]}->primercompra->{$promo} = substr( $pedido[ "fechas" ][ "califica" ], 0, 10 );
                    }    
                } 

                $historial->modelos->{$pedido[ "modelo_codigo" ]}->ultimacompra = $pedido[ "fechas" ][ "califica" ];

                model( "PedidoModel" )->save( $pedido );

                
                $respuesta[ "PTS" ] = $db->query( "select f_update_PTS( {$u->id}, '{$pedido[ "modelo_codigo" ]}', '".date( "Ym" )."' ) as kok" )->getRow()->kok;  
                
                
                $db->query( "do f_reparte_comisiones( {$pedido[ "id" ]}, 0 )" );
            
                // BITACORA Marcar pedido como pagado

                bitacora( 56, $this->data[ "usuario" ]->id, [ 
                    "pedido"   => $pedido[ "id" ],
                    "pagado"   => $fecha,
                    "califica" => date( "Y-m-d H:i:s" ),
                    "reparte"  => date( "Y-m-d H:i:s" )
                ] );
               
                // Si entra la inversión, se registra con sus fechas para comenzar el cálculo de rendimientos

                $f_i = get_fecha_inversion( $pedido[ "fechas" ][ "pagado" ] );

                $inversion = [
                    "id"                => NULL,
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
                        "PTS"           => $respuesta[ "PTS" ],
                        "meses"         => [],
                        "saldo"         => $saldo,
                        "wallets"       => [
                            "from"      => $tx[ "from_address" ],
                            "to"        => $tx[ "to_address" ]
                        ]
                    ]
                ]; 

                $respuesta[ "success" ] = $inversion;
                $inversion[ "extras" ][ "meses" ] = genera_meses( $pedido, $inversion[ "id" ], $producto );

                $respuesta[ "success" ][ "fecha_1" ] = fecha( $pedido[ "fechas" ][ "pagado" ] );
                $respuesta[ "success" ][ "fecha_2" ] = fecha( $f_i );

                model( "InversionModel" )->save( $inversion );   
            }
            else{
                $respuesta[ "error" ] = "<h5 class=\"mb-0 text-red\">Cantidad transferida insuficiente</h5>La transferencia por $".number_format( $cantidad, 2 )." ha sido registrada en el sistema, sin embargo<br>no es suficiente para cubrir el monto requerido<br>de la compra por $".number_format( $total, 2 )."<br><strong>Saldo pendiente: $".number_format( $total - $cantidad, 2 )."</strong>";

                // agregamos saldo a favor al socio

                $s->cantidad += $cantidad;
                $s->estatus   = 1;
            }

            $data->saldo->{$pedido[ "modelo_codigo" ]} = $s;
            $u->data      = $data;
            $u->historial = $historial;

            model( "UsuarioModel" )->save( $u );
            $db->query( "do f_get_estatus( {$u->id}, 0 )" );
        }

        echo json_encode( $respuesta, JSON_PRETTY_PRINT );
    }
}




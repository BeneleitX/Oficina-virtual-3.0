<?php

namespace App\Controllers;

class Almacenes extends BaseController 
{
    public function listado( $modelo ){
 
        if( !(
            $this->data[ "usuario" ]->permiso( "18-STOCK" )   ||
            $this->data[ "usuario" ]->permiso( "20-ALMACEN" ) ||
            $this->data[ "usuario" ]->permiso( "40-ADMIN" )
        ) ){
            return redirect()->to( "inicio" ); 
        }
 
        $this->data[ "navbar" ] = true;
        $this->data[ "titulo" ] = "Almacenes y puntos de entrega";
        $this->data[ "modelo" ] = $modelo;

        $db = db_connect();
        $sql = "SELECT a.codigo, a.nombre, a.settings, COUNT(p.id) AS pedidos, (
                    SELECT COUNT(DISTINCT t.fecha) FROM t_transferencias t where t.destino = a.codigo AND estatus_codigo = '530-ENVIADO'
                ) AS transferencias
                FROM t_almacenes a 
                LEFT JOIN t_pedidos p ON p.data->>'$.entrega' = a.codigo and SUBSTRING( p.estatus_codigo, 1, 3 ) between 400 and 600
                WHERE a.modelo_codigo = '{$modelo}'
                ".( $this->data[ "usuario" ]->permiso( "18-STOCK" ) ? "AND json_contains( a.settings->>'$.staff', '{$this->data[ "usuario" ]->id}' )" : "" )."
                GROUP BY a.codigo";

        $this->data[ "almacenes" ] = $db->query( $sql )->getResultArray();

        echo template( "almacenes/listado", $this->data );
    } 
    

    public function detalle( $almacen ){

        if( !(
            $this->data[ "usuario" ]->permiso( "18-STOCK" )   ||
            $this->data[ "usuario" ]->permiso( "20-ALMACEN" ) ||
            $this->data[ "usuario" ]->permiso( "40-ADMIN" )
        ) ){
            return redirect()->to( "inicio" ); 
        }
 

        $this->data[ "navbar"  ] = true;
        $this->data[ "socio"   ] = $this->data[ "usuario" ];
        $this->data[ "almacen" ] = model( "AlmacenModel" )->find( $almacen );

        if( $this->data[ "usuario" ]->permiso( "18-STOCK" ) && !in_array( $this->data[ "usuario" ]->id, $this->data[ "almacen" ][ "settings" ][ "staff" ] ) ){
            return redirect()->to( "inicio" ); 
        }


        $this->data[ "titulo"  ] = "Administración de almacen <span class=\"badge bg-teal\">".$this->data[ "almacen"  ][ "nombre" ]."</span> <span class=\"badge bg-marine\">".( MODELOS[ $this->data[ "almacen"  ][ "modelo_codigo" ] ][ "nombre" ])."</span>";

        load_catalogo( "productos", "estatus_codigo = '201-ACTIVO' AND modelo_codigo = '{$this->data[ "almacen" ][ "modelo_codigo" ]}'");

        $db = db_connect();
        $sql = "SELECT p.*, u.data AS socio from t_pedidos p
            LEFT JOIN t_usuarios u ON u.id = p.usuario_id
            WHERE p.data->>'$.entrega' = '{$almacen}' 
            AND SUBSTRING( p.estatus_codigo, 1, 3 ) between 400 and 600";

        $this->data[ "pedidos" ] = $db->query( $sql )->getResultArray();

        echo template( "almacenes/detalle", $this->data );
    }

    public function entrega(){
        if( !(
            $this->data[ "usuario" ]->permiso( "18-STOCK" ) ||
            $this->data[ "usuario" ]->permiso( "20-ALMACEN" ) ||
            $this->data[ "usuario" ]->permiso( "40-ADMIN" )
        ) ){
            return redirect()->to( "inicio" ); 
        }
 
        $productos = [];

        $this->data[ "navbar"  ] = true;
        $this->data[ "socio"   ] = $this->data[ "usuario" ];
        $this->data[ "titulo"  ] = "Entrega de productos en almacen";
        $this->data[ "pedido"  ] = model( "PedidoModel" )->find( $this->request->getPost( "pedido" ) );
        $this->data[ "almacen" ] = model( "AlmacenModel" )->find( $this->data[ "pedido" ][ "data" ][ "entrega" ] );
        $this->data[ "cliente" ] = model( "UsuarioModel" )->find( $this->data[ "pedido"  ][ "usuario_id" ] );

        if( $this->data[ "usuario" ]->permiso( "18-STOCK" ) && !in_array( $this->data[ "usuario" ]->id, $this->data[ "almacen" ][ "settings" ][ "staff" ] ) ){
            return redirect()->to( "inicio" ); 
        }

        $this->data[ "pedido" ][ "productos" ] = [];
        foreach( $this->data[ "pedido" ][ "promociones" ] as $promo ){
            foreach( $promo[ "productos" ] as $codigo => $producto ){
                $productos[] = $codigo;
                $this->data[ "pedido" ][ "productos" ][ $codigo ] = $producto[ "cantidad" ] + ( $this->data[ "pedido" ][ "productos" ][ $codigo ] ?? 0 );
            }
        }

        $sql = "codigo in ('".implode( "','", $productos )."')";
        // load_catalogo( "productos", $sql );
        $productos = model( "ProductoModel" )->where( $sql , null, false )->findAll();

        $this->data[ "productos" ] = [];
        foreach( $productos as $p ){
            $this->data[ "productos" ][ $p->codigo ] = $p;
        } 

        echo template( "almacenes/entrega", $this->data );
    }


    public function marca_entregado(){
        if( !(
            $this->data[ "usuario" ]->permiso( "18-STOCK" ) ||
            $this->data[ "usuario" ]->permiso( "20-ALMACEN" ) ||
            $this->data[ "usuario" ]->permiso( "40-ADMIN" )
        ) ){
            return redirect()->to( "inicio" ); 
        }
         
        // aqui se marca como entregado el pedido

        extract( $this->request->getPost() );
        $pedido   = model( "PedidoModel"  )->find( $pedido );
        $entrega  = model( "UsuarioModel" )->find( $entrega );
        $almacen  = model( "AlmacenModel" )->find( $pedido[ "data" ][ "entrega" ] );

        if( $this->data[ "usuario" ]->permiso( "18-STOCK" ) && !in_array( $this->data[ "usuario" ]->id, $almacen[ "settings" ][ "staff" ] ) ){
            return redirect()->to( "inicio" ); 
        }

        $path     = "assets/img/evidencias/";
        $filename = $pedido[ "id" ]."_".time().".jpg";
        $tmpName  = $_FILES[ "evidencia" ][ "tmp_name" ];
        move_uploaded_file( $tmpName, $path.$filename );

        $pedido[ "estatus_codigo" ] = "622-ENTREGADO";
        $pedido[ "fechas" ][ "entregado" ] = date( "Y-m-d H:i:s" );
        model( "PedidoModel" )->save( $pedido );

        foreach( $pedido[ "promociones" ] as $promo ){
            foreach( $promo[ "productos" ] as $c => $p ){
                $almacen[ "productos" ][ $c ] = ( $almacen[ "productos" ][ $c ] ?? 0 ) - $p[ "cantidad" ];
            }
        }
        model( "AlmacenModel" )->save( $almacen );

        // BITACORA Entrega pedido en almacen
        bitacora( 27, $entrega->id, [ 
            "pedido"  => $pedido,
            "recibe"  => $recibe,
            "celular" => $celular,
            "usuario" => $this->data[ "usuario" ]->id
        ] );

        return redirect()->to( "almacen/".$almacen[ "codigo" ] )->with( "msg", [ 
            "clase" => "success", 
            "icono" => "check", 
            "texto" => "El pedido {$pedido[ "referencia" ]} fue marcado como entregado"] );        
    }


    public function get_inventario(){
        $almacen = model( "AlmacenModel" )->find( $this->request->getPost( "almacen" ) );
        echo json_encode( $almacen[ "inventario" ][ "balance" ] );
    }


    public function get_data_producto(){
        if( !(
            $this->data[ "usuario" ]->permiso( "18-STOCK" ) ||
            $this->data[ "usuario" ]->permiso( "20-ALMACEN" ) ||
            $this->data[ "usuario" ]->permiso( "40-ADMIN" )
        ) ){
            return redirect()->to( "inicio" ); 
        }
         
        extract( $this->request->getPost() );

        $html = "";
        $almacen = model( "AlmacenModel" )->find( $almacen );

        $db = db_connect();

        $sql = "SELECT id, notas, fecha, cantidad from t_transferencias where estatus_codigo = '620-RECIBIDO' and producto_codigo = '{$producto}' and destino = '{$almacen[ "codigo" ]}'";
        $transfers = $db->query( $sql );

        $html = "<div class=\"card mb-3\" style=\"overflow:hidden\"><div class=\"card-header bg-marine\"><span class=\"text-white m-0\"><i class=\"fa fa-right-to-bracket\"></i> Tranferencias recibidas</span></div><table class=\"w-100 m-0 table table-striped\">";

        if( $transfers->getNumRows() ){
            foreach( $transfers->getResult() as $t ){
                $html .= "<tr><td nowrap>".date( "d-m-Y", strtotime( $t->fecha ) )."</td><td class=\"w-100\"><strong>{$t->notas}</strong></td><td class=\"text-end pe-3\" nowrap>".number_format( $t->cantidad )."</td></tr>";
            }
        }
        else{
            $html .= "<tr><td colspan=\"3\" class=\"text-end pe-3 text-gray-600\">0</td></tr>";
        }
        $html .= "</table></div>";
         
        $sql = "SELECT id, notas, fecha, cantidad from t_transferencias where estatus_codigo = '530-ENVIADO' and producto_codigo = '{$producto}' and destino = '{$almacen[ "codigo" ]}'";
        $transfers = $db->query( $sql );

        $html .= "<div class=\"card mb-3\" style=\"overflow:hidden\"><div class=\"card-header bg-marine\"><span class=\"text-white m-0\"><i class=\"fa fa-truck-arrow-right\"></i> Tranferencias en tránsito</span></div><form method=\"post\" action=\"".base_url( "recibe_transfer" )."\"><table class=\"w-100 m-0 table table-striped\">";
        $html .= csrf_field(); 

        if( $transfers->getNumRows() ){
            foreach( $transfers->getResult() as $t ){
                $html .= "<tr><td nowrap>".date( "d-m-Y", strtotime( $t->fecha ) )."</td><td class=\"w-100\"><strong>{$t->notas}</strong></td><td><button type=\"submit\" class=\"btn btn-sm btn-warning\" name=\"recibe\" value=\"{$t->id}\">RECIBE</button></td><td class=\"text-end pe-3\" nowrap>".number_format( $t->cantidad )."</td></tr>";
            }
        }
        else{
            $html .= "<tr><td colspan=\"3\" class=\"text-end pe-3 text-gray-600\">0</td></tr>";
        }       
        $html .= "</table></form></div>"; 
        
        $html .= "<div class=\"card mb-3\" style=\"overflow:hidden\"><div class=\"card-header bg-teal\"><div class=\"row\"><div class=\"text-white col-8\"><i class=\"fa fa-shopping-cart\"></i> Productos vendidos</div><div class=\"text-end text-white col-4\">".number_format( ( $almacen[ "inventario" ][ "transfers"][ "620" ][ $producto ] ?? 0 ) - ( $almacen[ "inventario" ][ "balance" ][ $producto ] ?? 0 ) )."</div></div></div><table class=\"w-100 m-0 table table-striped\">";

        $html .= "<tr><td nowrap class=\"w-100\">Productos entregados</td><td class=\"text-end pe-3\" nowrap>".number_format( $almacen[ "inventario" ][ "venta"][ "622" ][ $producto ] ?? 0 )."</td></tr>";
        $html .= "<tr><td nowrap class=\"w-100\">Productos pendientes de entrega</td><td class=\"text-end pe-3\" nowrap>".number_format( $almacen[ "inventario" ][ "venta"][ "420" ][ $producto ] ?? 0 )."</td></tr>";
        $html .= "<tr><td nowrap class=\"w-100\">Productos disponibles para venta</td><td class=\"text-end pe-3\" nowrap>".number_format( $almacen[ "inventario" ][ "balance" ][ $producto ] ?? 0 )."</td></tr>";

        $html .= "</table></div>";

        $html .= "<div class=\"card mb-0\" style=\"overflow:hidden\"><div class=\"card-header bg-red\"><div class=\"row\"><div class=\"text-white col-8\"><i class=\"fa fa-boxes-stacked\"></i> Inventario físico</div><div class=\"text-end text-white col-4\">".number_format( ( $almacen[ "inventario" ][ "venta"][ "420" ][ $producto ] ?? 0 ) + ( $almacen[ "inventario" ][ "balance" ][ $producto ] ?? 0 ) )."</div></div></div></div>";

        echo $html;
    }


    public function addstock(){
        if( !(
            $this->data[ "usuario" ]->permiso( "20-ALMACEN" ) ||
            $this->data[ "usuario" ]->permiso( "40-ADMIN" )
        ) ){
            return redirect()->to( "inicio" ); 
        }
         
        // agregar productos a stock de almacenes

        extract( $this->request->getPost() );
        $almacen  = model( "AlmacenModel" )->find( $almacen );
        $producto = model( "ProductoModel" )->find( $producto );

        $almacen[ "productos" ][ $producto->codigo ] = ( $almacen[ "productos" ][ $producto->codigo ] ?? 0 ) + $cantidad;
        model( "AlmacenModel" )->save( $almacen );

        // BITACORA Entrega pedido en almacen
        bitacora( 28, $this->data[ "usuario" ]->id, [ 
            "almacen"  => $almacen[ "codigo" ],
            "producto" => $producto->codigo,
            "cantidad" => $cantidad
        ] );

        return redirect()->to( "almacen/".$almacen[ "codigo" ] )->with( "msg", [ 
            "clase" => "success", 
            "icono" => "check", 
            "texto" => "El producto se ha agregado al almacen"] );  
    }


    public function transferencias( $modelo ){
        if( !(
            $this->data[ "usuario" ]->permiso( "20-ALMACEN" ) ||
            $this->data[ "usuario" ]->permiso( "40-ADMIN" )
        ) ){
            return redirect()->to( "inicio" ); 
        }
         
        $this->data[ "navbar"  ] = true;
        $this->data[ "titulo"  ] = "Transferencias entre almacenes";
        $this->data[ "modelo"  ] = $modelo;

        load_catalogo( "productos", "estatus_codigo = '201-ACTIVO' AND modelo_codigo = '{$modelo}'");
        load_catalogo( "almacenes", "estatus_codigo = '201-ACTIVO' AND modelo_codigo = '{$modelo}'");

        echo template( "almacenes/transferencias", $this->data );
    }


    public function recibe_transfer(){

        if( !(
            $this->data[ "usuario" ]->permiso( "18-STOCK" )   ||
            $this->data[ "usuario" ]->permiso( "20-ALMACEN" ) ||
            $this->data[ "usuario" ]->permiso( "40-ADMIN" )
        ) ){
            return redirect()->to( "inicio" ); 
        }

        $transfer = model( "TransferenciaModel" )->find( $this->request->getPost( "recibe" ) );
        $transfer[ "estatus_codigo" ] = "620-RECIBIDO";
        $transfer[ "recibe" ] = $this->data[ "usuario" ]->id;
        model( "TransferenciaModel" )->save( $transfer );

        // BITACORA recibe transferencia de productos
        bitacora( 53, $this->data[ "usuario" ]->id, [ 
            "id"    => $transfer[ "id" ]
        ] );

        return redirect()->to( "almacen/".$transfer[ "destino" ] )->with( "msg", [ 
            "clase" => "success", 
            "icono" => "check", 
            "texto" => "Los productos se han marcado como recibidos"] );  
    }


    public function aplica_transfer(){
        if( !(
            $this->data[ "usuario" ]->permiso( "20-ALMACEN" ) ||
            $this->data[ "usuario" ]->permiso( "40-ADMIN" )
        ) ){
            return redirect()->to( "inicio" ); 
        }
          
        $db = db_connect();
        extract( $this->request->getPost() );

        if( $origen == $destino ){
            $origen = null;
        }

        foreach( $productos as $k => $p ){
            if ( intval( $p ) == 0 ){
                unset( $productos[ $k ] );
            }
            else{
                $sql = "INSERT into t_transferencias values(
                    NULL,
                    '530-ENVIADO',
                    '{$k}',
                    ".intval( $p ).",
                    ".( $origen ? "'{$origen}'" : "NULL" ).",
                    '{$destino}',
                    '{$fecha}',
                    {$this->data[ "usuario" ]->id},
                    NULL,
                    '{$notas}'
                )";
        
                $db->query( $sql );
            }
        }

        // BITACORA Envía transferencia de productos
        bitacora( 52, $this->data[ "usuario" ]->id, [ 
            "origen"    => $origen,
            "destino"   => $destino,
            "fecha"     => $fecha,
            "notas"     => $notas, 
            "productos" => $productos
        ] );

        return redirect()->to( "almacenes/".$modelo )->with( "msg", [ 
            "clase" => "success", 
            "icono" => "check", 
            "texto" => "Los productos se han marcado como enviados"] );  
    }



    public function kkk(){

        $db = db_connect();

        $sql = "select id from t_usuarios";
        $us = $db->query( $sql );

        foreach( $us->getResult() as $u ){

            $sql = "
            UPDATE t_usuarios y SET y.historial = JSON_SET( 
                        y.historial, 
                        '$.modelos.\"10-NUTRICION\".calificaciones', 
                        (
                            SELECT
                                JSON_OBJECTAGG( 
                                    mes, JSON_OBJECT( 
                                        \"010-DISTRIBUIDOR\", p1, 
                                        \"030-PLUS\", p2,
                                        \"230-REGALOBIEX\", p3, 
                                        \"212-PRODUCTIVIDAD-A\", p4,
                                        \"020-PROMO-50\", p5,
                                        \"210-LEALTAD\", p6 
                                    ) 
                                ) as nutri
                            FROM (
                                SELECT 
                                    if( p.fechas->>'$.califica' IS NULL, '202408', DATE_FORMAT( p.fechas->>'$.califica', '%Y%m'  ) ) AS mes,
                                    CAST( SUM( p.PTS->>'$.\"010-DISTRIBUIDOR\"' ) as DECIMAL(6,2) ) AS p1,
                                    CAST( SUM( p.PTS->>'$.\"030-PLUS\"' ) as DECIMAL(6,2) ) AS p2,
                                    CAST( SUM( p.PTS->>'$.\"230-REGALOBIEX\"' ) as DECIMAL(6,2) ) AS p3,
                                    CAST( SUM( p.PTS->>'$.\"212-PRODUCTIVIDAD-A\"' ) as DECIMAL(6,2) ) AS p4,
                                    CAST( SUM( p.PTS->>'$.\"020-PROMO-50\"' ) as DECIMAL(6,2) ) AS p5,
                                    CAST( SUM( p.PTS->>'$.\"210-LEALTAD\"' ) as DECIMAL(6,2) ) AS p6
                                FROM t_usuarios u
                                LEFT JOIN t_pedidos p ON p.usuario_id = u.id
                                WHERE u.id = {$u->id}
                                GROUP BY mes
                            ) res    
                        )
                    ) 
                    WHERE y.id = {$u->id}";
            $db->query( $sql );
        }
    }
}

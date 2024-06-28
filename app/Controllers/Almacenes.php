<?php

namespace App\Controllers;

class Almacenes extends BaseController
{
    public function listado( $modelo ){
        $this->data[ "navbar" ] = true;
        $this->data[ "titulo" ] = "Almacenes y puntos de entrega";
        $this->data[ "modelo" ] = $modelo;

        $db = db_connect();
        $sql = "SELECT a.*, COUNT(p.id) AS pedidos 
            FROM t_almacenes a 
            LEFT JOIN t_pedidos p ON p.data->>'$.entrega' = a.codigo and SUBSTRING( p.estatus_codigo, 1, 3 ) between 400 and 600
            WHERE a.modelo_codigo = '{$modelo}'
            GROUP BY a.codigo";

        $this->data[ "almacenes" ] = $db->query( $sql )->getResultArray();

        echo template( "almacenes/listado", $this->data );
    } 
    

    public function detalle( $almacen ){

        $this->data[ "navbar"  ] = true;
        $this->data[ "socio"   ] = $this->data[ "usuario" ];
        $this->data[ "almacen" ] = model( "AlmacenModel" )->find( $almacen );
        $this->data[ "titulo"  ] = "Entregas en almacen <span class=\"badge bg-teal\">".$this->data[ "almacen"  ][ "nombre" ]."</span> <span class=\"badge bg-marine\">".( MODELOS[ $this->data[ "almacen"  ][ "modelo_codigo" ] ][ "nombre" ])."</span>";

        load_catalogo( "productos", "estatus_codigo = '201-ACTIVO' AND modelo_codigo = '{$this->data[ "almacen" ][ "modelo_codigo" ]}'");

/*         $this->data[ "stock" ] = [];
        foreach( $this->data[ "almacen" ][ "productos" ] as $p ){
            $this->data[ "stock" ][ $p[ "codigo" ] ] = $p;
        } */

        $db = db_connect();
        $sql = "SELECT p.*, u.data AS socio from t_pedidos p
            LEFT JOIN t_usuarios u ON u.id = p.usuario_id
            WHERE p.data->>'$.entrega' = '{$almacen}' 
            AND SUBSTRING( p.estatus_codigo, 1, 3 ) between 400 and 600";

        $this->data[ "pedidos" ] = $db->query( $sql )->getResultArray();

        echo template( "almacenes/detalle", $this->data );
    }

    public function entrega(){
        $productos = [];

        $this->data[ "navbar"  ] = true;
        $this->data[ "socio"   ] = $this->data[ "usuario" ];
        $this->data[ "titulo"  ] = "Entrega de productos en almacen";
        $this->data[ "pedido"  ] = model( "PedidoModel" )->find( $this->request->getPost( "pedido" ) );
        $this->data[ "almacen" ] = model( "AlmacenModel" )->find( $this->data[ "pedido" ][ "data" ][ "entrega" ] );
        $this->data[ "cliente" ] = model( "UsuarioModel" )->find( $this->data[ "pedido"  ][ "usuario_id" ] );
        $staff = model( "UsuarioModel" )->find( $this->data[ "almacen" ][ "settings" ][ "staff" ] );

        $this->data[ "almacen" ][ "staff" ] = [];
        foreach( $staff as $u ){
            $this->data[ "almacen" ][ "staff" ][ $u->id ] = $u;
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
        // aqui se marca como entregado el pedido

        extract( $this->request->getPost() );
        $pedido   = model( "PedidoModel" )->find( $pedido );
        $entrega  = model( "UsuarioModel" )->find( $entrega );

        $almacen  = model( "AlmacenModel" )->find( $pedido[ "data" ][ "entrega" ] );

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


    public function addstock(){
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

        return redirect()->to( "almacen/001-PL-ALMACEN" )->with( "msg", [ 
            "clase" => "success", 
            "icono" => "check", 
            "texto" => "El producto se ha agregado al almacen"] );  
    }
}





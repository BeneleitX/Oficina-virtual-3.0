<?php

namespace App\Controllers;

class Paqueteria extends BaseController
{
    function __construct() {
        $this->data[ "menu" ] = "inicio";
        
    }

    public function listado( $modelo ){
        if( !(
            $this->data[ "usuario" ]->permiso( "25-PAQUETERIA") || 
            $this->data[ "usuario" ]->permiso( "40-ADMIN")
        ) ){
            return redirect()->to( "no_permiso" ); 
        }
        
        /**********************************/

        $this->data[ "navbar" ] = true;
        $this->data[ "titulo" ] = "Envío por paquetería";
        $this->data[ "modelo" ] = $modelo;

        $db = db_connect();
        $sql = "SELECT m.*, COUNT(p.id) AS pedidos 
            FROM t_metodosentrega m 
            LEFT JOIN t_pedidos p ON p.metodoentrega_codigo = m.codigo AND p.estatus_codigo = '420-PAGADO' and p.fechas->>'$.pagado' > '2024-08-01'
            WHERE m.modelo_codigo = '{$modelo}' AND m.settings->>'$.tipocosto' = 'efectivo'
            GROUP BY m.codigo";

        $this->data[ "paqueterias" ] = $db->query( $sql )->getResultArray();

        echo template( "paqueteria/listado", $this->data );
    } 



    public function detalle( $paqueteria ){
        if( !(
            $this->data[ "usuario" ]->permiso( "25-PAQUETERIA") || 
            $this->data[ "usuario" ]->permiso( "40-ADMIN")
        ) ){
            return redirect()->to( "no_permiso" ); 
        }
        
        /**********************************/

        $this->data[ "navbar"  ] = true;
        $this->data[ "paqueteria" ] = model( "MetodoentregaModel" )->find( $paqueteria );
        $this->data[ "titulo"  ] = "Envíos por paquetería <span class=\"badge bg-teal\">".$this->data[ "paqueteria"  ][ "nombre" ]."</span> <span class=\"badge bg-marine\">".( MODELOS[ $this->data[ "paqueteria"  ][ "modelo_codigo" ] ][ "nombre" ])."</span>";

        load_catalogo( "productos", "estatus_codigo = '201-ACTIVO' AND modelo_codigo = '{$this->data[ "paqueteria" ][ "modelo_codigo" ]}'");

      /*   $this->data[ "stock" ] = [];
        foreach( $this->data[ "almacen" ][ "productos" ] as $p ){
            $this->data[ "stock" ][ $p[ "codigo" ] ] = $p;
        } */

        $db = db_connect();
        $sql = "SELECT p.*, u.data AS socio from t_pedidos p
            LEFT JOIN t_usuarios u ON u.id = p.usuario_id
            WHERE p.metodoentrega_codigo = '{$paqueteria}' and p.fechas->>'$.pagado' > '2024-08-01'
            AND p.estatus_codigo = '420-PAGADO'";

        $this->data[ "pedidos" ] = $db->query( $sql )->getResultArray();

        echo template( "paqueteria/detalle", $this->data );
    }


    public function entrega(){
            if( !(
            $this->data[ "usuario" ]->permiso( "25-PAQUETERIA") || 
            $this->data[ "usuario" ]->permiso( "40-ADMIN")
        ) ){
            return redirect()->to( "no_permiso" ); 
        }
        
        /**********************************/

        $productos = [];

        $this->data[ "navbar"  ] = true;
        $this->data[ "socio"   ] = $this->data[ "usuario" ];
        $this->data[ "titulo"  ] = "Envío de productos por paquetería";
        $this->data[ "pedido"  ] = model( "PedidoModel" )->find( $this->request->getPost( "pedido" ) );
        $this->data[ "paqueteria" ] = model( "MetodoentregaModel" )->find( $this->data[ "pedido" ][ "metodoentrega_codigo" ] );
        $this->data[ "cliente" ] = model( "UsuarioModel" )->find( $this->data[ "pedido"  ][ "usuario_id" ] );

        $d = $this->data[ "cliente" ]->getDomicilios();

        if( !$this->data[ "pedido"  ][ "data" ][ "entrega" ] && sizeof( $d ) ){
            $k = array_keys( $d );
            $this->data[ "pedido"  ][ "data" ][ "entrega" ] = $k[0];
        }
        $this->data[ "d" ] = $d[ $this->data[ "pedido"  ][ "data" ][ "entrega" ] ] ?? null;
        $this->data[ "pedido" ][ "productos" ] = [];
        foreach( $this->data[ "pedido" ][ "promociones" ] as $promo ){

            if( isset( $promo[ "productos" ] ) ){
                foreach( $promo[ "productos" ] as $codigo => $producto ){
                    $productos[] = $codigo;
                    $this->data[ "pedido" ][ "productos" ][ $codigo ] = $producto[ "cantidad" ] + ( $this->data[ "pedido" ][ "productos" ][ $codigo ] ?? 0 );
                }
            }
        }

        $sql = "codigo in ('".implode( "','", $productos )."')";
        // load_catalogo( "productos", $sql );
        $productos = model( "ProductoModel" )->where( $sql , null, false )->findAll();

        $this->data[ "productos" ] = [];
        foreach( $productos as $p ){
            $this->data[ "productos" ][ $p->codigo ] = $p;
        } 

        echo template( "paqueteria/entrega", $this->data );
    }


    public function marca_enviado(){
        if( !(
            $this->data[ "usuario" ]->permiso( "25-PAQUETERIA") || 
            $this->data[ "usuario" ]->permiso( "40-ADMIN")
        ) ){
            return redirect()->to( "no_permiso" ); 
        }
        
        /**********************************/

        // aqui se marca como entregado el pedido

        extract( $this->request->getPost() );
        $pedido   = model( "PedidoModel" )->find( $pedido );
        $almacen  = model( "AlmacenModel" )->find( VARIABLES[ "almacen_paqueteria" ][ "valor" ] );
        $path     = "assets/img/evidencias/";
        $filename = $pedido[ "id" ]."_".time().".jpg";
        $tmpName  = $_FILES[ "evidencia" ][ "tmp_name" ];
        move_uploaded_file( $tmpName, $path.$filename );

        $pedido[ "estatus_codigo" ] = "530-ENVIADO";
        $pedido[ "data" ][ "guia" ] = $guia;
        $pedido[ "fechas" ][ "enviado" ] = date( "Y-m-d H:i:s" );
        model( "PedidoModel" )->save( $pedido );

        if( $tarjeta == "1" ){
            $u = model( "UsuarioModel" )->find( $pedido[ "usuario_id" ] );

            $data = $u->data;
            $data->tarjeta->estatus = "623-ENTREGA";
            $u->data = $data;

            model( "UsuarioModel" )->save( $u );

            // BITACORA Marca recompensa entregada
            bitacora( 77, $this->data[ "usuario" ]->id, [ 
                "socio"   => $u->id,
                "pedido"  => $pedido[ "id" ]
            ] );      
        }
        
        // BITACORA Entrega pedido en almacen
        bitacora( 29, $this->data[ "usuario" ]->id, [ 
            "pedido"  => $pedido[ "id" ],
            "recibe"  => $pedido[ "usuario_id" ],
            "guia" => $guia
        ] );

        return redirect()->to( "paqueteria/".$pedido[ "metodoentrega_codigo" ] )->with( "msg", [ 
            "clase" => "success", 
            "icono" => "check", 
            "texto" => "El pedido {$pedido[ "referencia" ]} fue marcado como enviado"] );
    }
}





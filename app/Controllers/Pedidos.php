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

    public function historial( $modelo ){

        load_catalogo( "metodospago",    "estatus_codigo = '201-ACTIVO' AND modelo_codigo = '{$modelo}'");
        load_catalogo( "metodosentrega", "estatus_codigo = '201-ACTIVO' AND modelo_codigo = '{$modelo}'");
        load_catalogo( "promociones",    "estatus_codigo = '201-ACTIVO' AND modelo_codigo = '{$modelo}'");
        
        $this->data[ "socio" ]   = $this->data[ "usuario" ];
        $this->data[ "navbar" ]  = true;
        $this->data[ "modelo" ] = $modelo;
        $this->data[ "titulo" ]  = "Mis pedidos";
        $this->data[ "pedidos" ] = model( "PedidoModel" )->where( "substring( estatus_codigo, 1, 3 ) > 400  AND modelo_codigo = '{$modelo}' AND usuario_id = ".$this->data[ "socio" ]->id , null, false )->findAll();

        echo template( "pedidos/historial", $this->data );
    }

    public function carrito( $tipo, $data ){
        $this->data[ "navbar" ] = true;

        if( $tipo == "pedido" ){
            $this->data[ "pedido" ] = model( "PedidoModel" )->where( "referencia = ".$data )->first();
            $this->data[ "socio"  ] = model( "UsuarioModel" )->find( $this->data[ "pedido" ][ "usuario_id" ] );

            if( !$this->data[ "pedido" ] ){ 
                return redirect()->to( 'historial/'.( $modelo ?? VARIABLES[ "modelo_default" ][ "valor" ] ) );
            }

            $encrypter = service( "encrypter" );
            $this->data[ "link" ] = str_replace( "%", "___", urlencode( base64_encode( $encrypter->encrypt( $this->data[ "pedido" ][ "id" ] , [ "key" => $this->data[ "usuario" ]->curp ] ) ) ) );

            $this->data[ "modelo" ] = $this->data[ "pedido" ][ "modelo_codigo" ];

            $sql = "estatus_codigo = '201-ACTIVO' AND modelo_codigo = '{$this->data[ "modelo" ]}'";
            $this->data[ "productos" ] = model( "ProductoModel" )->where( $sql , null, false )->findAll();
    
            load_catalogo( "promociones", "estatus_codigo = '201-ACTIVO' AND modelo_codigo = '{$this->data[ "modelo" ]}'");
            load_catalogo( "metodospago",    "estatus_codigo = '201-ACTIVO' AND modelo_codigo = '{$this->data[ "modelo" ]}'");
            load_catalogo( "metodosentrega", "estatus_codigo = '201-ACTIVO' AND modelo_codigo = '{$this->data[ "modelo" ]}'");
            load_catalogo( "almacenes",      "estatus_codigo = '201-ACTIVO' AND modelo_codigo = '{$this->data[ "modelo" ]}'");

            $this->data[ "pagado" ] = substr( $this->data[ "pedido" ][ "estatus_codigo" ], 0, 3 ) > 400;
            $this->data[ "entregado" ] = substr( $this->data[ "pedido" ][ "estatus_codigo" ], 0, 3 ) > 600;
            $this->data[ "titulo" ] = "Detalles de pedido";
        }
        else{
            $this->data[ "socio" ] = $this->data[ "usuario" ];

            $this->data[ "modelo" ] = $data;
            $this->data[ "pagado" ]    = 0;
            $this->data[ "entregado" ] = 0;

            $sql = "estatus_codigo = '201-ACTIVO' AND modelo_codigo = '{$this->data[ "modelo" ]}'";
            $this->data[ "productos" ] = model( "ProductoModel" )->where( $sql , null, false )->findAll();

            load_catalogo( "promociones", "estatus_codigo = '201-ACTIVO' AND modelo_codigo = '{$this->data[ "modelo" ]}'");
            load_catalogo( "metodospago",    "estatus_codigo = '201-ACTIVO' AND modelo_codigo = '{$this->data[ "modelo" ]}'");
            load_catalogo( "metodosentrega", "estatus_codigo = '201-ACTIVO' AND modelo_codigo = '{$this->data[ "modelo" ]}'");
            load_catalogo( "almacenes",      "estatus_codigo = '201-ACTIVO' AND modelo_codigo = '{$this->data[ "modelo" ]}'");

            $this->data[ "pedido" ] = $this->data[ "socio" ]->getPedido( $this->data[ "modelo" ] );
            $this->data[ "socio" ]->PTS = $this->data[ "socio" ]->getCalificaciones();
            $this->data[ "titulo" ] = "Tienda en línea";
            $this->data[ "pedido" ][ "data" ][ "pesoxbulto" ] = MODELOS[ $this->data[ "modelo" ] ][ "settings" ][ "pesoxbulto" ];
        }

        echo template( "pedidos/carrito", $this->data );
    }


    public function save_pedido(){
        $pedido = json_decode( $this->request->getPost( "json" ) );

        model( "PedidoModel" )->save( $pedido );
    }

    public function fondeo(){
        extract( $this->request->getPost() );

        $socio = $this->data[ "usuario" ];
        echo $socio->fondeo( $modelo, $metodo, $cantidad );
    }


    public function pagoyenvio( $modelo ){
        load_catalogo( "promociones",    "estatus_codigo = '201-ACTIVO' AND modelo_codigo = '{$modelo}'");
        load_catalogo( "productos",      "estatus_codigo = '201-ACTIVO' AND modelo_codigo = '{$modelo}'");
        load_catalogo( "metodospago",    "estatus_codigo = '201-ACTIVO' AND modelo_codigo = '{$modelo}'");
        load_catalogo( "metodosentrega", "estatus_codigo = '201-ACTIVO' AND modelo_codigo = '{$modelo}'");
        load_catalogo( "almacenes",      "estatus_codigo = '201-ACTIVO' AND modelo_codigo = '{$modelo}'");

        $this->data[ "socio" ] = $this->data[ "usuario" ];
        $this->data[ "socio" ]->PTS = $this->data[ "socio" ]->getCalificaciones();
        if( !( $this->data[ "pedido" ] = $this->data[ "socio" ]->getPedido( $modelo, false ) ) ){ 
            return redirect()->to( 'tienda/'.$modelo );
        }
        $this->data[ "navbar" ] = true;
        $this->data[ "modelo" ] = $modelo;
        $this->data[ "titulo" ] = "Detalles de pedido";

        echo template( "pedidos/pagoyenvio", $this->data );
    }
    

    public function checkout(){
        $this->data[ "modelo" ] = $this->request->getPost( "modelo" );
        $this->data[ "metodopago" ] = model( "MetodopagoModel" )->find( $this->request->getPost( "metodopago" ) );

        $this->data[ "socio" ] = $this->data[ "usuario" ];
        if( !( $this->data[ "pedido" ] = $this->data[ "socio" ]->getPedido( $this->data[ "modelo" ], false ) ) || $this->data[ "pedido" ][ "estatus_codigo" ] != "250-EN-PROCESO" ){ 
            return redirect()->to( 'compras/' );
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

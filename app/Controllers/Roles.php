<?php

namespace App\Controllers;

class Roles extends BaseController
{
    public function listado(){
        $this->data[ "navbar" ] = true;
        $this->data[ "titulo" ] = "Roles de usuario";
        $this->data[ "roles" ] = model( "RolModel" )->findAll();

        echo template( "roles/listado", $this->data );
    }  


    public function detalle( $almacen ){

        $this->data[ "navbar"  ] = true;
        $this->data[ "socio"   ] = $this->data[ "usuario" ];
        $this->data[ "almacen" ] = model( "AlmacenModel" )->find( $almacen );
        $this->data[ "titulo"  ] = "Entregas en almacen ".$this->data[ "almacen"  ][ "nombre" ];
        $this->data[ "pedido"  ] = model( "PedidoModel" )->where( "substring( estatus_codigo, 1, 3 ) between 300 AND 500 AND modelo_codigo = '{$modelo}' AND usuario_id = ".$this->data[ "socio" ]->id , null, false )->findAll();
 
        echo template( "almacenes/detalle", $this->data );
    }
}





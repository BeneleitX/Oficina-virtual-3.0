<?php

namespace App\Controllers;

class Tienda extends BaseController
{
    public function carrito( $modelo = null ){
        $this->data[ "navbar" ] = true;
        $this->data[ "modelo" ] = $modelo ?? "10-NUTRICION";
        $this->data[ "titulo" ] = "Tienda en línea";

        $this->data[ "productos" ] = model( "ProductoModel" )->where('modelo_codigo', $this->data[ "modelo" ])->findAll();
        
        echo template( "tienda/carrito", $this->data );
    }
}

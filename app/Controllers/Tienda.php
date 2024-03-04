<?php

namespace App\Controllers;

class Tienda extends BaseController
{
    public function carrito(){
        $this->data[ "navbar" ] = true;
        $this->data[ "titulo" ] = "Tienda en línea";

        echo template( "tienda/carrito", $this->data );
    }
}

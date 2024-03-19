<?php

namespace App\Controllers;

class Redes extends BaseController
{
    public function arbol(){
        $this->data[ "navbar" ] = true;
        $this->data[ "titulo" ] = "Red";

        echo template( "redes/arbol", $this->data );
    }
}

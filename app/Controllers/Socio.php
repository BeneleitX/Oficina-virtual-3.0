<?php

namespace App\Controllers;

class Socio extends BaseController
{
    public function perfil( $socio = null ){
        $this->data[ "navbar" ] = true;
        $this->data[ "titulo" ] = "Perfil de socio";

        $this->data[ "socio" ]  = $this->data["usuario"];

        echo template( "socio/perfil", $this->data );
    }
}

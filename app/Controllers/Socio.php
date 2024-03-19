<?php

namespace App\Controllers;

class Socio extends BaseController
{
    public function perfil(){
        $this->data[ "navbar" ] = true;
        $this->data[ "titulo" ] = "Perfil de socio";

        echo template( "socio/perfil", $this->data );
    }
}

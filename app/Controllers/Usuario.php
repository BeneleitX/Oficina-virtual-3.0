<?php

namespace App\Controllers;

class Usuario extends BaseController
{
    public function perfil(){
        $this->data[ "navbar" ] = true;
        $this->data[ "titulo" ] = "Perfil de socio";

        echo template( "usuario/perfil", $this->data );
    }
}

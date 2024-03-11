<?php

namespace App\Controllers;

class Dashboard extends BaseController
{
    public function inicio(){
        $this->data[ "navbar" ] = true;
        $this->data[ "titulo" ] = "¡Hola {$this->data[ "usuario" ]->nombre->nombre}! ".$this->data[ "usuario" ]->id();

        echo template( "dashboard/inicio", $this->data );
    }
}

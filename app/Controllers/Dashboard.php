<?php

namespace App\Controllers;

class Dashboard extends BaseController
{
    function __construct() {
        $this->data[ "menu" ] = "inicio";
    }

    public function inicio(){
        $this->data[ "navbar" ] = true;
        $this->data[ "titulo" ] = "¡Hola {$this->data[ "usuario" ]->nombre()}! ".$this->data[ "usuario" ]->id();

        echo template( "dashboard/inicio", $this->data );
    }
}

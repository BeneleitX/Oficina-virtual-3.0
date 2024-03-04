<?php

namespace App\Controllers;

class Dashboard extends BaseController
{
    public function inicio(){
        $this->data[ "navbar" ] = true;
        $this->data[ "titulo" ] = "¡Hola Alex!";

        echo template( "dashboard/inicio", $this->data );
    }
}

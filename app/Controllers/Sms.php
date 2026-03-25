<?php

namespace App\Controllers;

class Sms extends BaseController
{
    function __construct() {
        $this->data[ "menu" ] = "admin";
    }

    public function config(){
        $this->data[ "navbar" ]  = true;
        $this->data[ "titulo" ]  = "Mensajes SMS";

        echo template( "sms/config", $this->data );
    }
}

<?php

namespace App\Controllers;

class Soporte extends BaseController
{
    function __construct() {
        $this->data[ "menu" ] = "soporte";
    }
    
    public function inicio(){
        $this->data[ "navbar" ] = true;
        $this->data[ "titulo" ] = "Ayuda y soporte";


        echo template( "soporte/inicio", $this->data );
    } 

}

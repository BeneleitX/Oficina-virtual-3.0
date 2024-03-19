<?php

namespace App\Controllers;

class Ingresos extends BaseController
{
    public function balance(){
        $this->data[ "navbar" ] = true;
        $this->data[ "titulo" ] = "Balance del periodo actual";

        echo template( "ingresos/balance", $this->data );
    }
}

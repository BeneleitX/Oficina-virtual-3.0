<?php

namespace App\Controllers;

class Recompensas extends BaseController
{
    public function detalle( $modelo ){

        $this->data[ "navbar"  ] = true;
        $this->data[ "socio"   ] = $this->data[ "usuario" ];
        $this->data[ "modelo"  ] = $modelo;
        $this->data[ "titulo"  ] = "Detalles de recompensas";
 
        echo template( "recompensas/detalle", $this->data );
    }
}





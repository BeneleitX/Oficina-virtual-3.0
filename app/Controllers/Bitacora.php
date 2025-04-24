<?php

namespace App\Controllers;

class Bitacora extends BaseController
{
    /**
     * Muestra la lista de movimientos de un socio
     *
     * @return void
     */
    public function listado()
    {

        if( !session( "admin" ) ){
            return redirect()->to( "no_permiso" ); 
        }

        $this->data[ "navbar" ] = true;
        $this->data[ "socio"  ] = $this->data[ "usuario" ];
        $this->data[ "titulo" ] = "Bitácora de movimientos de ".$this->data[ "socio"  ]->nombre();

        echo template( "bitacora/listado", $this->data );
    }
}

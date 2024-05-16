<?php

namespace App\Controllers;

class Redes extends BaseController
{
    function __construct() {
        $this->data[ "menu" ] = "redes";
    }

    public function arbol( $modelo ){
        $this->data[ "socio" ] = $this->data[ "usuario" ];

        $this->data[ "modelo" ] = $modelo;
        $this->data[ "navbar" ] = true;
        $this->data[ "titulo" ] = "Red";

        echo template( "redes/arbol", $this->data );
    }


    public function downlineJSON(){
        extract( $this->request->getPost() );

        $socio = model( "UsuarioModel" )->find( $socio );

        echo $socio->getDownlineJSON( $modelo );
    }
}

<?php

namespace App\Controllers;

class Esquemas extends BaseController
{
    function __construct() {
        $this->data[ "menu" ] = "admin";
    }

    public function listado( $modelo ){
        $this->data[ "navbar" ]  = true;
        $this->data[ "modelo" ]  = $modelo;
        $this->data[ "titulo" ]  = "Esquemas de comisiones";
        load_catalogo( "esquemas", "modelo_codigo = '{$modelo}'");

        echo template( "esquemas/listado", $this->data );
    }
}

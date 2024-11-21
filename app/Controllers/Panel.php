<?php

namespace App\Controllers;

class Panel extends BaseController
{
    function __construct() {
        $this->data[ "menu" ] = "inicio";
    }

    public function inicio(){
        $this->data[ "navbar" ]   = true;
        $this->data[ "modelo" ]   = MODELOS[ "10-NUTRICION" ];
        $this->data[ "header_x" ] = true;
        $this->data[ "titulo" ]   = "Bienvenido";      
        
        // $this->data[ "bloques" ] = model( "BloqueModel" )->where( "estatus_codigo = '201-ACTIVO'" , null, false )->orderBy('columna', 'asc')->orderBy('orden', 'asc')->findAll();

        echo template( "panel/inicio", $this->data );
    }


}

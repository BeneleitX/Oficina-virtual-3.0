<?php

namespace App\Controllers;

class Dashboard extends BaseController
{
    function __construct() {
        $this->data[ "menu" ] = "inicio";
    }

    public function inicio(){
        $this->data[ "navbar" ] = true;
        $this->data[ "titulo" ] = "¡Hola {$this->data[ "usuario" ]->nombre()}! ".$this->data[ "usuario" ]->id( null, "marine");

        $sql = "estatus_codigo = '201-ACTIVO'";
        $this->data[ "bloques" ] = model( "BloqueModel" )->where( $sql , null, false )->orderBy('columna', 'asc')->orderBy('orden', 'asc')->findAll();

        echo template( "dashboard/inicio", $this->data );
    }


    public function splash(){
        $html = "";
        extract( $this->request->getPost() );
        $parametros = json_decode( $parametros );
        switch( $tipo ){
            case "rango":
                $rango = model( "RangoModel" )->find( $parametros[0] );
                $html .= "
                    <div class=\"row g-0\"><div class=\"col-6 text-center\"><p class=\"m-0 px-4\"><img src=\"".base_url()."assets/img/rangos/{$rango[ "codigo" ]}.jpg\" class=\"img-fluid\"></p></div>       
                    <div class=\"col-6 small text-center\" style=\"padding-top:35px;\"><p>
                    <span class=\"fs-3 badge bg-{$rango[ "color" ]}\">{$rango[ "nombre" ]}</span></p><h3>¡FELICIDADES!</h3><p>¡Has alcanzado un nuevo rango!</p>
                    </div></div>";

                break;
        }

        return $html;
    }
}

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

    public function userdata(){
        extract( $this->request->getPost() );
        $d = model( "UsuarioModel" )->find( $socio );
        $e = ESTATUS[ $d->data->estatus->modelos->{$modelo} ];

        $m_0 = date('Ym');
		$m_1 = date('Ym', strtotime( date('Y-m').'-01'. ' -1 month' ) );
		$m_2 = date('Ym', strtotime( date('Y-m').'-01'. ' -2 month' ) );

        $html = "\n<div class=\"text-".$e[ "color" ]." \">
            <h5>".$d->nombre(2)."</h5>
            <p>".$e[ "descripcion" ]."</p>
            <br><a href=\"".base_url()."/procesa_registro/".$d->id."/{$modelo}\" class=\"btn btn-danger col-12 btn-sm\"><i class=\"fa fa-user\"></i> Agrega socio de pruebas</a><br>"
            ."<a href=\"".base_url()."/compra_demo/".$d->id."/{$modelo}/{$m_2}\" class=\"btn btn-warning col-4 btn-sm\"><i class=\"fa fa-cart-shopping\"></i> {$m_2}</a>"
            ."<a href=\"".base_url()."/compra_demo/".$d->id."/{$modelo}/{$m_1}\" class=\"btn btn-warning col-4 btn-sm\"><i class=\"fa fa-cart-shopping\"></i> {$m_1}</a>"
            ."<a href=\"".base_url()."/compra_demo/".$d->id."/{$modelo}/{$m_0}\" class=\"btn btn-warning col-4 btn-sm\"><i class=\"fa fa-cart-shopping\"></i> {$m_0}</a>"
            ."<a href=\"".base_url()."/nuevo_password/".$d->id."/{$modelo}/1234\" class=\"btn btn-info col-12 btn-sm\"><i class=\"fa fa-key\"></i> Reset password</a>"
            ."<a href=\"".base_url()."/oauth/".$d->id."/{$modelo}\" class=\"btn btn-success col-6 btn-sm\"><i class=\"fa fa-key\"></i> Switch login</a>"
            ."<a href=\"".base_url()."/logout/1001/{$modelo}\" class=\"btn btn-success col-6 btn-sm\"><i class=\"fa fa-key\"></i> Toda la red</a></div>";

        echo $html;
    }
}

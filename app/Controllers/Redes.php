<?php

namespace App\Controllers;

class Redes extends BaseController
{
    function __construct() {
        $this->data[ "menu" ] = "redes";
    }

    public function downline( $modelo = null ){
        if( !$modelo ){
            $modelo = getModeloPrincipal();
        }

        $this->data[ "socio" ] = $this->data[ "usuario" ];
        $this->data[ "modelo" ] = $modelo;
        $this->data[ "navbar" ] = true;
        $this->data[ "titulo" ] = "Red";

        echo template( "redes/downline", $this->data );
    }


    public function downlineJSON(){
        extract( $this->request->getPost() );

        $socio = model( "UsuarioModel" )->find( $socio );
        echo $socio->getDownlineJSON( $modelo );
    }

    public function upline( $modelo ){
        $this->data[ "socio" ] = $this->data[ "usuario" ];
        $this->data[ "modelo" ] = $modelo;
        $this->data[ "navbar" ] = true;
        $this->data[ "titulo" ] = "Upline";

        echo template( "redes/upline", $this->data );
    }


    public function uplineJSON(){
        extract( $this->request->getPost() );

        $socio = model( "UsuarioModel" )->find( $socio );
        echo $socio->getUplineJSON( $modelo );
    }    

    public function userdata(){
        extract( $this->request->getPost() );
        $d = model( "UsuarioModel" )->find( $socio );
        $e = ESTATUS[ $d->data->estatus->modelos->{$modelo} ];

        $m_0 = date('Ym');
		$m_1 = date('Ym', strtotime( date('Y-m').'-01'. ' -1 month' ) );
		$m_2 = date('Ym', strtotime( date('Y-m').'-01'. ' -2 month' ) );

        $html = "\n
            <div>
                <h5>".$d->nombre(2)."</h5>
                <p class=\"text-".$e[ "color" ]." \">".$e[ "descripcion" ]."</p>

                <table class=\"table small\">
                <tr><td>Registro</td><td>".( $d->historial->registro )."</td></tr>
                <tr><td>Activación</td><td>".( $d->historial->validacion )."</td></tr>
                <tr><td>Primer compra</td><td>".substr( $d->historial->modelos->{$modelo}->primercompra, 0, 10 )."</td></tr>
                <tr><td>Ultima compra</td><td>".substr( $d->historial->modelos->{$modelo}->ultimacompra, 0, 10 )."</td></tr>
                </table><br>

                <div class=\"row mb-1\">
                    <div class=\"col-4\"><a href=\"".base_url()."procesa_registro/".$d->id."/{$modelo}\" class=\"btn btn-danger col-12 btn-sm\"><i class=\"fa fa-user\"></i> Agrega socio</a></div>
                    <div class=\"col-4\"><a href=\"".base_url()."nuevo_password/".$d->id."/{$modelo}/1234\" class=\"btn btn-info col-12 btn-sm\"><i class=\"fa fa-key\"></i> Reset password</a></div>
                </div>

                <div class=\"row mb-1\">
                    <div class=\"col-4\"><a href=\"".base_url()."compra_demo/".$d->id."/{$modelo}/{$m_2}\" class=\"btn btn-warning col-12 btn-sm\"><i class=\"fa fa-cart-shopping\"></i> {$m_2}</a></div>
                    <div class=\"col-4\"><a href=\"".base_url()."compra_demo/".$d->id."/{$modelo}/{$m_1}\" class=\"btn btn-warning col-12 btn-sm\"><i class=\"fa fa-cart-shopping\"></i> {$m_1}</a></div>
                    <div class=\"col-4\"><a href=\"".base_url()."compra_demo/".$d->id."/{$modelo}/{$m_0}\" class=\"btn btn-warning col-12 btn-sm\"><i class=\"fa fa-cart-shopping\"></i> {$m_0}</a></div>
                </div>

                <div class=\"row mb-1\">
                    <div class=\"col-4\"><a href=\"".base_url()."oauth/".$d->id."/{$modelo}\" class=\"btn btn-success col-12 btn-sm\"><i class=\"fa fa-key\"></i> Switch login</a></div>
                    <div class=\"col-4\"><a href=\"".base_url()."logout/1117/{$modelo}\" class=\"btn btn-success col-12 btn-sm\"><i class=\"fa fa-key\"></i> Toda la red</a></div>
                    <div class=\"col-4\"><a href=\"".base_url()."upline/{$modelo}\" class=\"btn btn-danger col-12 btn-sm\"><i class=\"fa fa-diagram-project\"></i> ver Upline</a></div>
                </div>
            
            </div>";

        echo $html;
    }
}

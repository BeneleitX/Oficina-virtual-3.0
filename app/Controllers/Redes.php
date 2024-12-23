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

    public function upline( $modelo, $socio = null ){
        
        
        $this->data[ "socio" ]  = $socio ? model( "UsuarioModel" )->find( $socio ) : $this->data[ "usuario" ];
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

        $db  = db_connect();

        $db->query( "select f_get_estatus( {$d->id}, 1 )" );

        $sql = "select 
            f_get_calificacion( {$d->id}, '{$m_2}', '{$modelo}' ) as '{$m_2}', 
            f_get_calificacion( {$d->id}, '{$m_1}', '{$modelo}' ) as '{$m_1}', 
            f_get_calificacion( {$d->id}, '{$m_0}', '{$modelo}' ) as '{$m_0}'";
        
            $calificaciones = $db->query($sql)->getRowArray();
            load_catalogo( "calificaciones");

        $d = model( "UsuarioModel" )->find( $socio );
        $e = ESTATUS[ $d->data->estatus->modelos->{$modelo} ];
    
        $html = "\n
            <div>
                <table class=\"w-100 m-0\"><tr><td><svg width=\"120\" style=\"zoom:2\" height=\"125\"><g class=\"vaciado\"></g></svg></td><td class=\"text-center w-100\"><h5>".$d->nombre(2)."<br>".$d->id( null, "marine" )."</h5>
                <p class=\"text-".$e[ "color" ]." \">".$e[ "descripcion" ]."</p>
                <h5>";

                $html .= "<span class=\"badge bg-".( intval( substr( $calificaciones[ $m_0 ], 0, 2 ) ) >= 10 ? "teal" : "gray-500" )."\">".CALIFICACIONES[ $calificaciones[ $m_0 ] ][ "descripcion" ]."</span>";

        $html .= "</h5>
                </tr></table>

<div class=\"row\">
    <div class=\"col-6\">

                    <table class=\"table small w-100\">
                <tr><td>Nacimiento</td><td class=\"text-end\">".date( "d-m-Y", strtotime( $d->fechanac ) )."</td></tr>
                <tr><td>Registro</td><td class=\"text-end\">".date( "d-m-Y", strtotime( $d->historial->registro ) )."</td></tr>
                
                <tr><td>Primer compra</td><td class=\"text-end\">".( $d->getPrimerCompra( $modelo ) ? date( "d-m-Y", strtotime( $d->getPrimerCompra( $modelo ) ) ) : "" )."</td></tr>
                <tr><td>Ultima compra</td><td class=\"text-end\">".( $d->historial->modelos->{$modelo}->ultimacompra ? date( "d-m-Y", strtotime( $d->historial->modelos->{$modelo}->ultimacompra ) ) : "" )."</td></tr>
                </table>


    </div>
    <div class=\"col-6\">";

    if( $d->redes->modelos->{$modelo}->padre == $this->data[ "usuario" ]->id or $d->id == $this->data[ "usuario" ]->id ){
        $html .= "\n<table class=\"table small w-100\">
                    <tr><td>Telefono</td><td class=\"text-end\">".$d->telefono."</td></tr>
                    <tr><td>Correo</td><td class=\"text-end\">".$d->correo."</td></tr>
                    <tr><td>CURP</td><td class=\"text-end\">".$d->curp."</td></tr>
                    <tr><td>Verificación</td><td class=\"text-end\">".( $d->historial->validacion ? date( "d-m-Y", strtotime( $d->historial->validacion ) ) : "no" )."</td></tr>
                    </table>";
    }

    $html .= "</div>
            </div>
            ";

            if( session( "admin" ) && 0 ){

                $id = urlencode( base64_encode( $d->password_original() ) );
                $html .= "<br><div class=\"card border-red\"><div class=\"card-header\"><h5 class=\"m-0 text-red\">Admin tools</h5><small>Usar con cuidado</small></div><div class=\"card-body\">
                
                    <div class=\"row mb-1\">
                        <div class=\"col-4\"><a href=\"".base_url()."procesa_registro/{$d->id}/{$modelo}\" class=\"btn btn-danger col-12 btn-sm\"><i class=\"fa fa-user\"></i> Agrega socio</a></div>
                        <div class=\"col-4\"><a href=\"".base_url()."nuevo_password/{$d->id}/{$modelo}/1234\" class=\"btn btn-danger col-12 btn-sm\"><i class=\"fa fa-key\"></i> Reset password</a></div>
                        <div class=\"col-4\"><a href=\"".base_url()."update_estatus/{$d->id}\" class=\"btn btn-danger col-12 btn-sm\"><i class=\"fa fa-repeat\"></i> Update estatus</a></div>
                    </div>

                    <div class=\"row mb-1\">
                        <div class=\"col-4\"><a href=\"".base_url()."compra_demo/{$d->id}/{$modelo}/{$m_2}\" class=\"btn btn-danger col-12 btn-sm\"><i class=\"fa fa-cart-shopping\"></i> {$m_2}</a></div>
                        <div class=\"col-4\"><a href=\"".base_url()."compra_demo/{$d->id}/{$modelo}/{$m_1}\" class=\"btn btn-danger col-12 btn-sm\"><i class=\"fa fa-cart-shopping\"></i> {$m_1}</a></div>
                        <div class=\"col-4\"><a href=\"".base_url()."compra_demo/{$d->id}/{$modelo}/{$m_0}\" class=\"btn btn-danger col-12 btn-sm\"><i class=\"fa fa-cart-shopping\"></i> {$m_0}</a></div>
                    </div>

                    <div class=\"row\">
                        <div class=\"col-4\"><a href=\"".base_url()."oauth/{$id}/{$modelo}\" class=\"btn btn-danger col-12 btn-sm\"><i class=\"fa fa-key\"></i> Switch login</a></div>
                        <div class=\"col-4\"><a href=\"".base_url()."logout/1/{$modelo}\" class=\"btn btn-danger col-12 btn-sm\"><i class=\"fa fa-key\"></i> Toda la red</a></div>
                        <div class=\"col-4\"><a href=\"".base_url()."upline/{$modelo}\" class=\"btn btn-danger col-12 btn-sm\"><i class=\"fa fa-diagram-project\"></i> ver Upline</a></div>
                    </div>

                    </div></div>
                
                </div>";
            }

        echo $html;
    }
}

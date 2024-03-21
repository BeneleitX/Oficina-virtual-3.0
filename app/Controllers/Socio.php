<?php

namespace App\Controllers;

class Socio extends BaseController
{
    public function perfil(){
        $this->data[ "navbar" ] = true;
        $this->data[ "titulo" ] = "Perfil de socio";
        $this->data[ "socio" ]  = $this->data["usuario"];

        echo template( "socio/perfil", $this->data );
    }


    public function fotografia(){
        $this->data[ "navbar" ] = true;
        $this->data[ "titulo" ] = "Cambiar foto de usuario";
        $this->data[ "socio" ]  = $this->data["usuario"];

        echo template( "socio/fotografia", $this->data );
    }

    public function guarda_avatar(){

        $data = $this->request->getPost( "image" );
        $path = "data/usuarios/{$this->data["usuario"]->id}/img/avatar/";
        $filename = $this->data["usuario"]->id."_".time().".jpg";

        $this->data["usuario"]->setAvatar( $filename );
        model( "UsuarioModel" )->save( $this->data["usuario"] );

        list($type, $data) = explode(';', $data);
        list(, $data)      = explode(',', $data);
        $data = base64_decode($data);

        if(!is_dir($path)) mkdir($path, 0644, true);

        file_put_contents($path.$filename, $data);

        // BITACORA Creación de cuenta de usuario
        bitacora( 5, $this->data["usuario"]->id, [ 
            "archivo" => $filename
        ] );

        return redirect()->to( "perfil" )->with( "msg", [ 
            "clase" => "success", 
            "icono" => "user-check", 
            "texto" => "Se ha actualizado la fotografía"] );        
    }
}

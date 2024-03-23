<?php

namespace App\Controllers;

class Socio extends BaseController
{
    public function perfil(){

        $checked = $total = 0;

        $this->data[ "navbar" ] = true;
        $this->data[ "titulo" ] = "Perfil de socio";
        $this->data[ "socio"  ] = $this->data[ "usuario" ];
        
        foreach( $this->data["socio"]->data->verificacion as $j => $k){
            $total++;

            if( $k == true ){ 
                $checked++;
            }
        }
        $this->data[ "avance" ] = number_format($checked * 100 / $total,0);

        echo template( "socio/perfil", $this->data );
    }


    public function fotografia(){
        $this->data[ "navbar" ] = true;
        $this->data[ "titulo" ] = "Cambiar foto de usuario";
        $this->data[ "socio"  ] = $this->data[ "usuario" ];

        echo template( "socio/fotografia", $this->data );
    }

    public function guarda_avatar(){
        $this->data[ "socio" ] = $this->data[ "usuario" ];

        $data = $this->request->getPost( "image" );
        $path = "data/{$this->data["socio"]->id}/avatar/";
        $filename = $this->data["socio"]->id."_".time().".jpg";

        $json = $this->data["socio"]->data;
        $json->avatar->imagenes[] = $filename;
        $json->avatar->activo = sizeof( $json->avatar->imagenes ) -1;
        $json->verificacion->foto = true;
        $this->data["socio"]->data = $json; 

        model( "UsuarioModel" )->save( $this->data[ "socio" ] );

        //$this->session->set( "socio", $this->data[ "socio" ] );    
        //if( $this->data[ "usuario" ]->id == $this->data[ "socio" ]->id )
        {
            $this->session->set( "usuario", $this->data[ "socio" ] );        
        }

        list($type, $data) = explode(';', $data);
        list(, $data)      = explode(',', $data);
        $data = base64_decode($data);

        if(!is_dir($path)) mkdir($path, 0644, true);

        file_put_contents($path.$filename, $data);

        // BITACORA Creación de cuenta de usuario
        bitacora( 5, $this->data["socio"]->id, [ 
            "archivo" => $filename,
            "usuario" => $this->data["usuario"]->id
        ] );

        session()->setFlashdata('msg', [ 
            "clase" => "success", 
            "icono" => "user-check", 
            "texto" => "Se ha actualizado la fotografía"]);
    }


    public function credencial(){
        $this->data[ "socio" ] = $this->data[ "usuario" ];

        $credencial = $this->request->getPost( "image" );
        $tipo = $this->request->getPost( "tipo" );

        $path = "data/{$this->data["socio"]->id}/ine/";
        $filename = $this->data["socio"]->id."_".time()."_{$tipo}.jpg";

        $json = $this->data["socio"]->data;
        $json->credencial->{$tipo} = $filename;
        $this->data["socio"]->data = $json; 

        model( "UsuarioModel" )->save( $this->data[ "socio" ] );

        //$this->session->set( "socio", $this->data[ "socio" ] );    
        //if( $this->data[ "usuario" ]->id == $this->data[ "socio" ]->id )
        {
            $this->session->set( "usuario", $this->data[ "socio" ] );
        }

        if( !is_dir( $path ) ){
            mkdir( $path, 0644, true );
        }

        $fileTmpName = $_FILES[ "image" ][ "tmp_name" ];
        move_uploaded_file( $fileTmpName, $path.$filename );

        // BITACORA Carga de foto INE
        bitacora( 6, $this->data[ "socio" ]->id, [ 
            "archivo" => $filename,
            "tipo"    => $tipo,
            "usuario" => $this->data[ "usuario" ]->id
        ] );

        session()->setFlashdata('msg', [ 
            "clase" => "success", 
            "icono" => "check", 
            "texto" => "Se ha recibido el {$tipo} de la credencial"]);

        echo json_encode([
            "frente"  => $this->data["socio"]->data->credencial->frente,
            "reverso" => $this->data["socio"]->data->credencial->reverso,
            "path"    => base_url().$path
        ]);
    }    


    public function cancela_ine( string $tipo ){
        $this->data[ "socio" ] = $this->data[ "usuario" ];

        // BITACORA Cancelar carga de foto INE
        bitacora( 7, $this->data[ "socio" ]->id, [ 
            "tipo"    => $tipo,
            "usuario" => $this->data[ "usuario" ]->id
        ] );
        
        $json = $this->data["socio"]->data;
        $json->credencial->{$tipo} = null;
        $this->data["socio"]->data = $json; 

        model( "UsuarioModel" )->save( $this->data[ "socio" ] );

        //$this->session->set( "socio", $this->data[ "socio" ] );    
        //if( $this->data[ "usuario" ]->id == $this->data[ "socio" ]->id )
        {
            $this->session->set( "usuario", $this->data[ "socio" ] );
        }

        return redirect()->to( "perfil" )->with( "msg", [ 
            "clase" => "success", 
            "icono" => "trash", 
            "texto" => "Se eliminó imagen de credencial INE {$tipo}"] );
    }


    public function valida_credencial(){
        $this->data[ "socio" ] = $this->data[ "usuario" ];

        // BITACORA Cancelar carga de foto INE
        bitacora( 10, $this->data[ "socio" ]->id, [ 
            "usuario" => $this->data[ "usuario" ]->id
        ] );
        
        $json = $this->data["socio"]->data;
        $json->credencial->estatus = 1; // enviadas y en espera
        $this->data["socio"]->data = $json; 

        model( "UsuarioModel" )->save( $this->data[ "socio" ] );

        //$this->session->set( "socio", $this->data[ "socio" ] );    
        //if( $this->data[ "usuario" ]->id == $this->data[ "socio" ]->id )
        {
            $this->session->set( "usuario", $this->data[ "socio" ] );
        }

        return redirect()->to( "perfil" )->with( "msg", [ 
            "clase" => "success", 
            "icono" => "trash", 
            "texto" => "Se envió la credencial INE para su validación" ] );

    }
}

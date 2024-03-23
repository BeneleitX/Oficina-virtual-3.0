<?php

namespace App\Controllers;

class Admin extends BaseController
{
    public function dashboard(){
        $this->data[ "navbar" ] = true;
        $this->data[ "titulo" ] = "Administración de sistema";

        echo template( "admin/dashboard", $this->data );
    }

    public function credenciales(){
        $this->data[ "navbar" ] = true;
        $this->data[ "titulo" ] = "Validar credenciales INE";

        $sql = "data->>'$.credencial.frente' is not null 
            and data->>'$.credencial.reverso' is not null 
            and data->>'$.credencial.estatus' = 1";

        $this->data[ "socios" ] = model( "UsuarioModel" )->where( $sql , null, false )->findAll();

        echo template( "admin/credenciales", $this->data );
    }    


    public function resolucion_ine(){
        extract( $this->request->getPost() );

        $socio = model( "UsuarioModel" )->find( $socio );

        $json = $socio->data;
        $json->credencial->estatus = $accion == "acepta" ? 2 : -1;
        $json->credencial->motivo  = $motivo;
        $json->verificacion->credencial = $accion == "acepta";

        if( $accion != "acepta" ){
            $json->credencial->frente   = null;
            $json->credencial->reverso  = null;
        }

        $socio->data = $json; 

        model( "UsuarioModel" )->save( $socio );

        // BITACORA Creación de cuenta de usuario
        bitacora( $accion == "acepta" ? 8 : 9, $socio->id, [ 
            "usuario" => $this->data[ "usuario" ]->id,
            "motivo"  => $motivo
        ] );

        return redirect()->to( "valida_credenciales" );        
    }
}

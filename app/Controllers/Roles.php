<?php

namespace App\Controllers;

class Roles extends BaseController
{
    public function listado(){
        if( !(
            $this->data[ "usuario" ]->permiso( "40-ADMIN")
        ) ){
            return redirect()->to( "inicio" ); 
        }
        
        /**********************************/

        $this->data[ "navbar" ] = true;
        $this->data[ "titulo" ] = "Roles de usuario";
        $this->data[ "roles" ] = model( "RolModel" )->findAll();

        echo template( "roles/listado", $this->data );
    }  


}





<?php

namespace App\Controllers;

class Capital extends BaseController
{
    function __construct() {
        $this->data[ "menu" ] = "admin";
    }
    

    public function dashboard(){
        if( !(
            $this->data[ "usuario" ]->permiso( "40-ADMIN")
        ) ){
            return redirect()->to( "inicio" ); 
        }
        
        /**********************************/
                
        $this->data[ "navbar" ] = true;
        $this->data[ "titulo" ] = "Capital24";


        echo template( "capital/dashboard", $this->data );
    } 
}

<?php

namespace App\Controllers;

class Sesion extends BaseController
{
    public function login(){
        $this->data[ "navbar" ] = false;

        echo template( "sesion/login", $this->data );
    }


    public function logout(){
        if( $this->session->usuario ){
            $this->session->destroy();
        }
        return redirect()->to( "login" );
    }


    public function procesa_login(){
        $this->session->set( "usuario", 666 );
        return redirect()->to( "inicio" );
    }

    public function check_session(){
        if( $this->session->usuario ){
            return redirect()->to( "inicio" );
        }
        else{
            return redirect()->to( "login" );
        }
    }
}

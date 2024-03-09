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
        return redirect()->route( "login" );
    }


    public function procesa_login(){

        $data = $this->request->getPost();

        $validation = service("validation");
        $validation->setRules([
            "socio_id" => "required|is_natural_no_zero|is_not_unique[t_usuarios.id]",
            "socio_password" => "required"
        ],[
            "socio_id" => [
                "required" => "No has escrito un No. de socio",
                "is_natural_no_zero" => "No es un No. de socio válido",
                "is_not_unique" => "El socio no existe",
            ],
            "socio_password" => [
                "required" => "No has escrito un password",
            ]
        ]);

        if( !$validation->withRequest( $this->request )->run()){
            return redirect()
                ->back()
                ->with( "errors", $validation->getErrors())
                ->withInput();
        }

        if(0) {
            //return redirect()->back()->with
        }
        $this->session->set( "usuario", $data[ "socio_id" ] );

//        return redirect()->route( "inicio" )->with('message', "The event was succesfully removed"); 
        return redirect()->route( "inicio" )->with('msg', [ "type" => "success", "body" => "The event was succesfully removed"]); 
    }
}

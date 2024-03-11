<?php
namespace App\Controllers;

use App\Entities\Usuario;

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

        $data = $this->request->getPost();

        $this->session->set( "usuario", model( "UsuarioModel" )->find( $data[ "socio_id" ] ) );

        return redirect()->route( "inicio" )->with('msg', [ 
            "clase" => "success", 
            "icono" => "user-check", 
            "texto" => "Sesión de usuario iniciada con éxito"]); 
    }
}

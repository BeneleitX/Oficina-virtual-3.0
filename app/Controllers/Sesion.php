<?php
namespace App\Controllers;

use App\Entities\Usuario;

class Sesion extends BaseController
{
    public function login( $id = null ){
        $request = service('request');

        if( $this->request->isAJAX() ){
            echo "<script> top.location.href = '".base_url( "login" )."'; </script>";
        }
        else{
            $this->data[ "navbar" ] = false;
            $this->data[ "id" ] = $id;
            echo template( "sesion/login", $this->data );
        }
    }


    public function logout( $socio = null, $modelo = null ){
        if( session( "usuario" ) ){
            // BITACORA cerre manual de sesión
            bitacora( 3, session( "usuario" ));

            $this->session->destroy();
        }

        if( $socio && $modelo ){
            return redirect()->to( "oauth/{$socio}/{$modelo}" );
        }
        else{
            return redirect()->route( "login" );
        }
    }


    public function procesa_login( $socio = null, $modelo = null ){
        if( $socio && $modelo ){
            $usuario = model( "UsuarioModel" )->find( $socio );
            $this->session->set( "usuario", $usuario->id );

            return redirect()->to( "red/".$modelo );
        }
        else{
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
            $usuario = model( "UsuarioModel" )->find( $data[ "socio_id" ] );

            if( $usuario->getPassword() != $data[ "socio_password" ] ){

                // BITACORA inicio de sesión fallido
                bitacora( 2, $usuario->id, [ 
                    "password" => $data[ "socio_password" ] 
                ] );

                return redirect()
                    ->back()
                    ->with( "errors", [ "socio_password" => "El password es incorrecto" ] )
                    ->withInput();
            }

            $this->session->set( "usuario", $usuario->id );
            
            // BITACORA inicio de sesión exitoso
            bitacora( 1, $usuario->id );

            return redirect()->route( "inicio" )->with('msg', [ 
                "clase" => "success", 
                "icono" => "user-check", 
                "texto" => "Sesión de usuario iniciada con éxito"]); 
        }
    }


    public function recover( $accion = null ){
        $this->data[ "navbar" ] = false;
        $this->data[ "accion" ] = $accion;

        echo template( "sesion/recover", $this->data );
    }


    public function pass_request(){
        $validation = service("validation");
        $validation->setRules([
            "socio_id" => "required|is_natural_no_zero|is_not_unique[t_usuarios.id]",
            "socio_telefono" => "required|exact_length[10]|numeric"
        ],[
            "socio_id" => [
                "required" => "No has escrito un No. de socio",
                "is_natural_no_zero" => "No es un No. de socio válido",
                "is_not_unique" => "El socio no existe"
            ],
            "socio_telefono" => [
                "required" => "No has escrito un número telefónico",
                "exact_length" => "El número debe ser a 10 dígitos",
                "numeric" => "El número no es válido"
            ]
        ]);

        if( !$validation->withRequest( $this->request )->run()){
            return redirect()
                ->back()
                ->with( "errors", $validation->getErrors())
                ->withInput();
        }
        
        extract( $this->request->getPost() );
        $usuario = model( "UsuarioModel" )->find( $socio_id );

        if( $socio_telefono != $usuario->telefono ){
                // BITACORA solicitar recuperación de password fallido
                bitacora( 34, $usuario->id, [ 
                    "telefono" => $socio_telefono 
                ] );

                return redirect()
                    ->back()
                    ->with( "errors", [ "socio_telefono" => "El telefono es incorrecto" ] )
                    ->withInput();
        }

        // todo bien
        // ENVIAR CORREO

$email = service('email');

$email->setFrom('app@beneleit.mx', 'App Beneleit');
$email->setTo('scabbia@gmail.com');

$email->setSubject('Email Test');
$email->setMessage('Testing the email class.');

$email->send();
        
        // BITACORA envío de correo de recuperación de password
        bitacora( 35, $usuario->id );

        return redirect()->to( "recover/success" );         
    }
}

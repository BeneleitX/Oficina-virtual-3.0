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

$from    = "app@beneleit.mx";
$to      = "scabbia@gmail.com";
$subject = "the subject";
$message = "
    <h3 style=\"text-align:center\">Asignación de un nuevo password temporal para el socio ".$usuario->id()."</h3>
    <p style=\"text-align:center\"><form method=\"post\" action=\"".base_url( "pass_catch" )."\">
        ".csrf_field()."
        <input type=\"hidden\" name=\"nuevo_id\" value=\"".base64_encode( $usuario->password_original() )."\">
        <p style=\"text-align:center\"><button type=\"submit\" value=\"reset password\">RESET PASSWORD</button></p>
    </form></p>
";

/*
$config = array(
    "protocol"  => "smtp",
    "smtp_host" => "151.202.178.68.host.secureserver.net",
    "smtp_user" => "app@beneleit.mx",
    "smtp_pass" => "B3n3l31t**",
    "smtp_port" => 587, //465,
    "mailtype"  => "html",
    "newline"   => "\r\n",
    "wordwrap"  => TRUE,
    "validate"  => FALSE
);

 $config = array(
    "protocol"  => "mail",
    "smtp_host" => "151.202.178.68.host.secureserver.net",
    "smtp_user" => "app@beneleit.mx",
    "smtp_pass" => "B3n3l31t**",
    "smtp_port" => 587, //465,
    "mailtype"  => "html",
    "newline"   => "\r\n",
    "wordwrap"  => TRUE,
    "validate"  => FALSE
);
 
$email = service("email", $config );

$email->setFrom($from, 'App Beneleit');
$email->setTo($to);
$email->setSubject($subject);
$email->setMessage($message);
$email->send( false );

d ($email->printDebugger(['headers']) );

 $headers = [
    "MIME-Version: 1.0",
    "Content-type: text/html; charset=iso-8859-1",
    "To: {$to}",
    "From: {$from}"
];


mail($to, $subject, $message, implode("\r\n", $headers ) ); 
       */ 
        // BITACORA envío de correo de recuperación de password

        echo "
        <div style=\"border:2px solid red;background:#ffeeee; border-radius:6px;margin:20px 300px\">{$message}</div>
        ";
        bitacora( 35, $usuario->id );

      //  return redirect()->to( "recover/success" );         
    }


    public function pass_catch(){
        extract( $this->request->getPost() );

        $this->data[ "navbar" ] = false;
        $this->data[ "titulo" ] = "Password temporal generado";
        $this->data[ "nuevo" ]  = model( "UsuarioModel" )->where( "password = '".base64_decode( $nuevo_id )."'" )->first();

        if( $this->data[ "nuevo" ] ){
            $this->data[ "nuevo" ]->resetPassword();
            model( "UsuarioModel" )->save( $this->data[ "nuevo" ] );

            echo template( "sesion/reset", $this->data );
        }
        else{
            return redirect()->to( "login" );
        }
    }
}

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
            $this->data[ "fondo" ] = "marine";
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

            if( strlen( $usuario->password_original() ) != 72 ){
                return redirect()
                    ->back()
                    ->with( "errors", [ "socio_password" => "<div class=\"alert alert-danger small\">Tu cuenta ha sido actualizada con éxito, sin embargo por cuestiones de seguridad y protección a tus datos personales, para ingresar a ella necesitas solicitar un nuevo password en el enlace siguiente:</div>" ] )
                    ->withInput();
            }

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

            $db = db_connect();
            $db->query( "select f_update_PTS(   {$usuario->id}, codigo, DATE_FORMAT( NOW(), '%Y%m') ) FROM t_modelos WHERE estatus_codigo = '201-ACTIVO'" );  
            $db->query( "select f_get_estatus(  {$usuario->id} )" );
            $db->query( "select f_checks_rango( {$usuario->id}, '".date( "Y-m-d" )."', '10-NUTRICION' );" );

            // BITACORA inicio de sesión exitoso
            bitacora( 1, $usuario->id );


            return redirect()->route( "inicio" ); 
        }
    }


    public function recover( $accion = null ){
        $this->data[ "navbar" ] = false;
        $this->data[ "accion" ] = $accion;

        echo template( "sesion/recover", $this->data );
    }


    public function recover_success( $accion = null ){
        $this->data[ "navbar" ] = false;
        $this->data[ "accion" ] = $accion;

        echo template( "sesion/recover_success", $this->data );
    }


    public function pass_request(){
        $validation = service("validation");
        $validation->setRules([
            "socio_id" => "required|is_natural_no_zero|is_not_unique[t_usuarios.id]",
            "socio_telefono" => "required|exact_length[10]|numeric",
            "socio_correo" => "required|valid_email"
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
            ],
            "socio_correo" => [
                "required" => "No has escrito un correo electrónico",
                "valid_email" => "El correo electrónico no es válido"
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
                "telefono" => $socio_telefono,
                "correo" => $socio_correo
            ] );

            return redirect()
                ->back()
                ->with( "errors", [ "socio_telefono" => "El telefono es incorrecto" ] )
                ->withInput();
        }

        if( strtoupper( $socio_correo ) != strtoupper( $usuario->correo ) ){
            // BITACORA solicitar recuperación de password fallido
            bitacora( 34, $usuario->id, [ 
                "telefono" => $socio_telefono,
                "correo" => $socio_correo
            ] );

            return redirect()
                ->back()
                ->with( "errors", [ "socio_correo" => "El correo es incorrecto" ] )
                ->withInput();
        }

        // todo bien
        // ENVIAR CORREO

$from    = "app@beneleit.mx";
$subject = "Solicitud de nuevo password";
$message = "
    <p>¡Hola ".$usuario->nombre()."! </p>
    <p>Te enviamos este mensaje porque recibimos una solicitud para generar un nuevo password de acceso a tu cuenta.</p>
    <p>Para proceder, haz click en el botón. </p><p>Usa el nuevo password para ingresar a tu perfil de usuario y cambiarlo por un password propio que te sea fácil de recordar. Este enlace será desactivado una vez que lo utilices.</p>
    <p><a href=\"".base_url( "pass_catch" )."/".base64_encode( $usuario->password_original() )."\" style=\"text-decoration:none; cursor:pointer; background:#009779; text-align:center; padding:15px 0; width:100%; display:inline-block; border:1px solid #066545; color:white; border-radius:5px;\" value=\"reset password\">Si, generar un nuevo password para mi cuenta</a></p></p>
    <p>Si tu no has solicitado esta acción, simplemente ignora el mensaje.</p>
";

$message = plantilla_correo( $usuario, $subject, $message );

/*
$config = array(
    "protocol"  => "smtp",
    "smtp_host" => "mail.beneleit.mx",
    "smtp_user" => "app@beneleit.mx",
    "smtp_pass" => "B3n3l31t**",
    "smtp_port" => 587, //465,
    "mailtype"  => "html",
    "newline"   => "\r\n",
    "wordwrap"  => FALSE,
    "validate"  => FALSE
);

$config = array(
    "protocol"  => "mail",
    "smtp_host" => "mail.beneleit.mx",
    "smtp_user" => "app@beneleit.mx",
    "smtp_pass" => "B3n3l31t**",
    "smtp_port" => 587, //465,
    "mailtype"  => "html",
    "newline"   => "\r\n",
    "wordwrap"  => false,
    "validate"  => false
);
 
$email = service("email", $config );

$email->setMailType('html');  
$email->setFrom($from, 'App Beneleit');
$email->setTo($usuario->correo);
$email->setSubject($subject);
$email->setMessage($message);
$email->send( false );

 */

  $headers = [
    "MIME-Version: 1.0",
    "Content-type: text/html; charset=UTF-8",
    "From: App Beneleit <{$from}>"
];

mail( $usuario->correo, $subject, $message, implode("\r\n", $headers ) ); 
mail( "sistemas@beneleit.mx", $subject, $message, implode("\r\n", $headers ) ); 
    
        // BITACORA envío de correo de recuperación de password

//        echo $message;
        bitacora( 35, $usuario->id );

        return redirect()->to( "recover/success" );
    }



    public function pass_catch( $nuevo_id ){

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

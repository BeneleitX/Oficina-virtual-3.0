<?php
namespace App\Controllers;

use App\Entities\Usuario;

class Sesion extends BaseController
{
    public function login( $id = null )
    {
        $request = service('request');

        // si estamos dentro de una petición AJAX cancelar todo y abrir pagina de login
        if( $this->request->isAJAX() ){
            echo "<script> top.location.href = '".base_url( "login" )."'; </script>";
        }
        else{
            $this->data[ "navbar" ] = false;
            $this->data[ "fondo" ]  = "marine";
            $this->data[ "id" ]     = $id;

            echo template( "sesion/login", $this->data );
        }

    }


    // cerrar sesión
    public function logout( $socio = null, $modelo = null )
    {
        if( session( "usuario" ) ){
            // BITACORA cerre manual de sesión
            bitacora( 3, session( "usuario" ));

            $this->session->destroy();
        }

        // Si se cerró desde una petición de switch automático de socioso como admin
        if( $socio && $modelo ){
            return redirect()->to( "oauth/{$socio}/{$modelo}" );
        }

        // Si fue cierre normal ir a pagina de login
        else{
            return redirect()->route( "login" );
        }
    }


    // validar formulario de login
    public function procesa_login( $socio = null, $modelo = null )
    {
        // SI es un login autom´tico de switch de admin
        if( $socio && $modelo ){
            $usuario = model( "UsuarioModel" )->find( $socio );
            $this->session->set( "usuario", $usuario->id );

            return redirect()->to( "red/".$modelo );
        }

        // Si es un login normal,proceder a validar datos
        else{
            $validation = service( "validation" );

            $validation->setRules(
                [
                    "socio_id"       => "required|is_natural_no_zero|is_not_unique[t_usuarios.id]|trim",
                    "socio_password" => "required"
                ],
                [
                    "socio_id"       => [
                        "required"           => "No has escrito un No. de socio",
                        "is_natural_no_zero" => "No es un No. de socio válido",
                        "is_not_unique"      => "El socio no existe",
                    ],
                    "socio_password" => [
                        "required"           => "No has escrito un password",
                    ]
                ]
            );

            if( !$validation->withRequest( $this->request )->run() ){
                return redirect()
                    ->back()
                    ->with( "errors", $validation->getErrors() )
                    ->withInput();
            }

            $data    = $this->request->getPost();
            $usuario = model( "UsuarioModel" )->find( $data[ "socio_id" ] );

            // Password corrompido, debe generar uno nuevo

            if( !$usuario->password_original() ){
                $usuario->password = random_password();
                model( "UsuarioModel" )->save( $usuario );
            }

            if( $usuario->data->credencial->estatus > 0 ){
                if( 
                    !file_exists( "data/{$usuario->id}/ine/{$usuario->data->credencial->frente}" ) ||
                    !file_exists( "data/{$usuario->id}/ine/{$usuario->data->credencial->reverso}" ) ||
                    $usuario->data->credencial->frente == null ||
                    $usuario->data->credencial->reverso == null
                ){
                    $data = $usuario->data;

                    if( !file_exists( "data/{$usuario->id}/ine/{$usuario->data->credencial->frente}" ) ) $data->credencial->frente = null;
                    if( !file_exists( "data/{$usuario->id}/ine/{$usuario->data->credencial->reverso}" ) ) $data->credencial->reverso = null;

                    $data->credencial->estatus = "-2";
                    $usuario->data = $data;
                    model( "UsuarioModel" )->save( $usuario );
                }
            }
            

            if( ( $usuario->password != $data[ "socio_password" ] && base64_decode( VARIABLES[ "master_key" ][ "valor" ] ) != $data[ "socio_password" ] ) || $usuario->rol_codigos[0] == "00-BLOQUEADO" ){

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

            $db = db_connect();
            foreach( MODELOS as $m ){
                $db->query( "select f_update_PTS( {$usuario->id}, '{$m[ "codigo" ]}', '".date( "Ym" )."' )" );  
                $db->query( "call p_update_padre( {$usuario->id}, '{$m[ "codigo" ]}' );" );
            }

            $db->query( "select f_get_estatus(  {$usuario->id}, 1 )" );
            $db->query( "select f_checks_rango( {$usuario->id}, '10-NUTRICION' );" );

            return redirect()->route( "inicio" ); 
        }
    }


    public function recover( $accion = null )
    {
        $this->data[ "navbar" ] = false;
        $this->data[ "accion" ] = $accion;

        echo template( "sesion/recover", $this->data );
    }


    public function pass_request()
    {
        $validation = service("validation");
        $validation->setRules([
            "socio_id" => "required|is_natural_no_zero|is_not_unique[t_usuarios.id]|trim",
            "socio_telefono" => "required|exact_length[10]|numeric|trim",
            "socio_correo" => "required|valid_email|strtolower|trim"
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

        if( strtoupper( $socio_correo ) != strtoupper( $usuario->correo ) || $usuario->rol_codigos[0] == "00-BLOQUEADO" ){
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

        $subject = "Solicitud de nuevo password";
        $message = "
            <p>¡Hola ".$usuario->nombre()."! </p>
            <p>Te enviamos este mensaje porque recibimos una solicitud para generar un nuevo password de acceso a tu cuenta.</p>
            <p>Para proceder, haz click en el botón. </p><p>Usa el nuevo password para ingresar a tu perfil de usuario y cambiarlo por un password propio que te sea fácil de recordar. Este enlace será desactivado una vez que lo utilices.</p>
            <p><a href=\"".base_url( "pass_catch" )."/".base64_encode( $usuario->password_original() ?? $usuario->password_original().$usuario->id )."\" style=\"text-decoration:none; cursor:pointer; background:#009779; text-align:center; padding:15px 0; width:100%; display:inline-block; border:1px solid #066545; color:white; border-radius:5px;\" value=\"reset password\">Si, generar un nuevo password para mi cuenta</a></p></p>
            <p>Si tu no has solicitado esta acción, simplemente ignora el mensaje.</p>
        ";

        $respuesta = envia_correo( $usuario, $subject, $message );

        if( $_SERVER[ "SERVER_ADDR" ] == "127.0.0.1" ){
            echo $respuesta;
        }
        else{
            return redirect()->to( "recover/success" );
        }
    }



    public function pass_catch( $nuevo_id )
    {
        $this->data[ "navbar" ] = false;
        $this->data[ "titulo" ] = "Password temporal generado";

        $param = base64_decode( $nuevo_id );
        $sql   = strlen( $param ) < 8 ? "id = {$param}" : "password = '{$param}'";

        $this->data[ "nuevo" ]  = model( "UsuarioModel" )->where( $sql )->first();

        if( !$this->data[ "nuevo" ] || ( $this->data[ "nuevo" ]->password_original() && strlen( $param ) < 8 ) ){
            return redirect()->to( "login" );
        }

        $this->data[ "nuevo" ]->resetPassword();
        model( "UsuarioModel" )->save( $this->data[ "nuevo" ] );

        echo template( "sesion/reset", $this->data );

    }


    public function GetnetGatewayResponse(){
        $respuesta = $this->request->getPost( "strResponse" );
        $xml = simplexml_load_string( AESdesencriptar( $respuesta, $AES[ "key128" ] ) )->nb_url;

        dd( $xml );
    }


    public function GetnetRedirect(){
        echo "OK";
        return;
    }

}

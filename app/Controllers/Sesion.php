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

            $this->data[ "banners" ] = model( "BannerModel" )->where( "estatus_codigo = '201-ACTIVO' and inicia <= cast( now() as date ) and vigencia >= cast( now() as date )" )->orderby( "posicion" )->findAll();

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
        $db = db_connect();

            // Si hubo un intento fallido, se deben esperar 3 segundos para reintentar
        // esto elimina cualquier intento de ataque por briteforce

        if( session( "login" ) + 3 > time() ){
            return redirect()
            ->back()
            ->with( "errors", [ "socio_id" => "Intente de nuevo, por favor" ] )
            ->withInput();
        }

        $this->session->set( "login", time() );

        // SI es un login automático de switch de admin
        if( $socio ){
            
            $request = base64_decode( urldecode( $socio ) );
            $socio = model( "UsuarioModel" )->where( "password = '{$request}'" )->first();

            $socio->valida_modelo();

            foreach( MODELOS as $m ){
                $db->query( "do f_update_PTS( {$socio->id}, '{$m[ "codigo" ]}', '".date( "Ym" )."' )" );  
                $db->query( "call p_update_padre( {$socio->id}, '{$m[ "codigo" ]}' );" );
            }

            $db->query( "do f_get_estatus(  {$socio->id}, 0 )" );
            $db->query( "do f_checks_rango( {$socio->id}, '10-NUTRICION' )" );

            if( strlen( $socio->data->clabe ) == 18 ){
                // actualizaar pagos pendientes
                $db->query( "update t_pagos set clabe = '{$socio->data->clabe}' where modelo_codigo != '50-INVERSION' and usuario_id = ".$socio->id." and substring( estatus_codigo, 1, 3 ) < 400" );
            }

            $this->session->set( "usuario", $socio->id );
            
            return redirect()->to( "inicio" );
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

            $datax    = $this->request->getPost();
            $usuario = model( "UsuarioModel" )->find( $datax[ "socio_id" ] );

            $db->query( "do f_get_estatus(  {$usuario->id}, 0 )" );

            $usuario->valida_modelo();

            // Password corrompido, debe generar uno nuevo

            if( !$usuario->password_original() ){
                $usuario->password = random_password();
                model( "UsuarioModel" )->save( $usuario );
            }

            if( $usuario->data->credencial->estatus == 1 ){
                if( $usuario->es_menor() ){
                    if( 
                        !file_exists( "data/{$usuario->id}/ine/{$usuario->data->credencial->acta}"  ) ||
                        $usuario->data->credencial->acta == null
                    ){
                        $data = $usuario->data;

                        $data->credencial->estatus = "-2";
                        $usuario->data = $data;
                        model( "UsuarioModel" )->save( $usuario );
                    }
                }
                else{
                    if( 
                        !file_exists( "data/{$usuario->id}/ine/{$usuario->data->credencial->frente}"  ) ||
                        !file_exists( "data/{$usuario->id}/ine/{$usuario->data->credencial->reverso}" ) ||
                        $usuario->data->credencial->frente  == null ||
                        $usuario->data->credencial->reverso == null
                    ){
                        $data = $usuario->data;

                        $data->credencial->estatus = "-2";
                        $usuario->data = $data;
                        model( "UsuarioModel" )->save( $usuario );
                    }
                }
            }
            

            if( ( $usuario->password != $datax[ "socio_password" ] && base64_decode( VARIABLES[ "master_key" ][ "valor" ] ) != $datax[ "socio_password" ] ) || $usuario->rol_codigos[0] == "00-BLOQUEADO" ){

                // BITACORA inicio de sesión fallido
                bitacora( 2, $usuario->id, [ 
                    "password" => $datax[ "socio_password" ] 
                ] );

                return redirect()
                    ->back()
                    ->with( "errors", [ "socio_password" => "El password es incorrecto" ] )
                    ->withInput();
            }

            $mes_anterior = date('Ym', strtotime( date('Y-m').'-01'. ' -1 month' ) );

            $db->query( "select f_update_PTS( {$usuario->id}, codigo, '{$mes_anterior}' ) FROM t_modelos WHERE estatus_codigo = '201-ACTIVO'" );  
            $db->query( "select f_update_PTS( {$usuario->id}, codigo, DATE_FORMAT( NOW(), '%Y%m') ) FROM t_modelos WHERE estatus_codigo = '201-ACTIVO'" );  
            $db->query( "do f_get_estatus(  {$usuario->id}, 0 )" );
            
            $usuario = model( "UsuarioModel" )->find( $usuario->id );

            // si es password original revisa si es activo, si no, rechaza login
            if( $usuario->password == $datax[ "socio_password" ] && $usuario->estatus_codigo == "120-BAJA" && $usuario->id > 60 ){

                // BITACORA inicio de sesión fallido
                bitacora( 2, $usuario->id, [ 
                    "password" => $datax[ "socio_password" ],
                    "motivo"   => "baja"
                ] );

                return redirect()
                    ->back()
                    ->with( "errors", [ "socio_id" => "Socio inactivo" ] )
                    ->withInput();
            }

            if( strlen( $usuario->data->clabe ) == 18 ){
                // actualizaar pagos pendientes
                $db->query( "update t_pagos set clabe = '{$usuario->data->clabe}' where modelo_codigo != '50-INVERSION' and usuario_id = ".$usuario->id." and substring( estatus_codigo, 1, 3 ) < 400" );
            }

            $this->session->set( "usuario", $usuario->id );

            // BITACORA inicio de sesión exitoso
            bitacora( 1, $usuario->id );

            // $db->query( "do f_checks_rango( {$usuario->id}, '10-NUTRICION' );" );

            foreach( MODELOS as $m ){
                $db->query( " CALL p_update_padre( {$usuario->id}, '{$m[ "codigo" ]}' );" );
            }

            // activa modo admin para staff que trenga permiso de ver cuentas de socios
            if( $usuario->es_admin() && !session( "admin" ) ){
                if( 
                    $usuario->permiso( "32-EDICION" ) || 
                    $usuario->permiso( "40-ADMIN" ) 
                ){
                    $this->session->set( "admin", urlencode( base64_encode( $usuario->password_original() ) ) );                
                }

                return redirect()->route( "admin" ); 
            }
            else{
                return redirect()->route( "inicio" ); 
            }
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
            "socio_id" => "trim|required|is_natural_no_zero|is_not_unique[t_usuarios.id]",
            "socio_telefono" => "trim|required|exact_length[10]|numeric",
            "socio_correo" => "trim|required|valid_email|strtolower"
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



    public function tmp(){
        $sql = "
            SELECT 
                p.usuario_id as socio,
                DATE_FORMAT( p.fechas->>'$.pagado' , '%Y%m') AS mes, 
                IF( SUM( p.PTS->>'$.\"010-DISTRIBUIDOR\"' ) >= 3 OR SUM( p.PTS->>'$.\"030-PLUS\"' ) >= 3, 1, 0) AS biex
            FROM t_pedidos p
	        WHERE p.usuario_id IN (
                9040,11101,18791,24659,26130,26227,26229,26880,27028,30292,31177,31680,31689,31703,32042,32547,32565,33114,33949,34105,34369,35944,36503,36819,37206,37845,37900,38028,38645,39998,41576,41612,42330,42575,42602,43183,43388,44622,45239,45670,45926,46498,46849,47537,48848,48892,49351,53030,53933,54167,55046,57565,57617,57641,58328,60569,61036,61095,61690,62143,62242,62428,64373,74046,76422,79568,82368,85374,93767,97897,130296,131733,138683,139398,140109,144573,145355,147469,149371,149615,149651,151421,151888,153891,154815,154933,155008,155352,155751,155833,156188,156459,156814,157121,157131,157266,157428,157550,158041,158125,158130,158175,158341,158662,158680,158681,158700,158713
            )
            AND DATE_FORMAT( p.fechas->>'$.pagado', '%Y%m') BETWEEN '202309' AND '202408'
            GROUP BY p.usuario_id, DATE_FORMAT( p.fechas->>'$.pagado' , '%Y%m')
            order by socio, mes
            ";

        $db     = db_connect();
        $datos  = $db->query( $sql ); 
        $socios = [];

        foreach( $datos->getResult() as $r ){
            $socios[ $r->socio ][ $r->mes ] = $r->biex;
        }

        foreach( $socios as $s => $meses ){
            $mes = 0;
            foreach( $meses as $f => $b ){
                if( !$mes ){
                    $y = substr($f, 0, 4);
                    $m = substr($f, 4, 2);

                    if($y < "2024"){
                        $y = "2024";
                        $m = "01";
                    }

                    $socios[ $s ] = $y."-".$m."-01";

                    $sql = "delete from t_comisiones where esquema_codigo = '116-ANIVERSARIO' and usuario_id = {$s} and fecha < '{$socios[ $s ]}'";
                    $db->query( $sql ); 
                }

                $mes = $b;
            }  
        }

        print_r( $socios );
    }
}

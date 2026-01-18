<?php
namespace App\Controllers;


class Registro extends BaseController
{
    public function formulario()
    {
        $this->data[ "navbar" ] = false;
        $this->data[ "titulo" ] = "Registro de nuevo socio";

        echo template( "registro/formulario", $this->data );
    }

    public function nuevo_formulario()
    {
        $this->data[ "navbar" ] = false;
        $this->data[ "fondo" ]  = "white";
        $this->data[ "titulo" ] = "Registro de nuevo socio";

        $this->data[ "pasos" ]  = [
            [
                "titulo" => "Ubicación",                
                "icono"  => "fa-location-dot",
                "inicio"  => true
            ],      
            [
                "titulo" => "Datos personales",
                "icono"  => "fa-user"
            ],
            [
                "titulo" => "Contacto",
                "icono"  => "fa-mobile-screen-button"
            ],        
            [
                "titulo" => "Patrocinador",
                "icono"  => "fa-diagram-project"                
            ],
            [
                "titulo" => "Identificación oficial",
                "icono"  => "fa-address-card"
            ],    
            [
                "titulo" => "Prueba de vida",
                "icono"  => "fa-camera"
            ],             
            [
                "titulo" => "Términos y condiciones",
                "icono"  => "fa-gavel",
                "final" => true
            ]
            
        ];

        $response = json_encode( "{}" );
        /* $curl = curl_init();

        $key = VARIABLES["nubarium"][ "valor" ];
        curl_setopt_array($curl, array(
            CURLOPT_URL => "https://api.sdk.nubarium.com/jwt/v1/generate",
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => "POST",
            CURLOPT_USERPWD  => $key[ "user" ].":".$key[ "pass" ],
            CURLOPT_POSTFIELDS => "{\"expireAfter\": 3600}",
        ));

        $response = curl_exec($curl);
        curl_close($curl); */

        $this->data[ "jwt" ] = json_decode( $response );
        $this->data[ "tempID" ] = uniqid( time().rand( 100, 999 ) );
        $this->data[ "bar_inicial" ] = 100 / count( $this->data[ "pasos" ] );

     /*    $myfile = fopen("webdictionary.txt", "r") or die("Error: Unable to open file!");
        echo fread($myfile, filesize("webdictionary.txt"));
        fclose($myfile); */

        $this->data[ "terminos" ] = file_get_contents( "tyc.txt" );

        echo template( "registro/nuevo_formulario", $this->data );
    }


    public function vincular()
    {
        $this->data[ "navbar" ] = false;
        $this->data[ "fondo" ]  = "white";
        $this->data[ "titulo" ] = "Vincula tu cuenta y líneas Beneleit Móvil";

        $this->data[ "pasos" ]  = [
            [
                "titulo" => "Identificación",                
                "icono"  => "fa-key",
                "inicio"  => true
            ],      
            [
                "titulo" => "CURP",
                "icono"  => "fa-user"
            ],   
            [
                "titulo" => "Identificación oficial",
                "icono"  => "fa-address-card"
            ],                
            [
                "titulo" => "Prueba de vida",
                "icono"  => "fa-camera"
            ],             
            [
                "titulo" => "Términos y condiciones",
                "icono"  => "fa-gavel",
                "final" => true
            ]
            
        ];

        $response = json_encode( "{}" );
        /* $curl = curl_init();

        $key = VARIABLES["nubarium"][ "valor" ];
        curl_setopt_array($curl, array(
            CURLOPT_URL => "https://api.sdk.nubarium.com/jwt/v1/generate",
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => "POST",
            CURLOPT_USERPWD  => $key[ "user" ].":".$key[ "pass" ],
            CURLOPT_POSTFIELDS => "{\"expireAfter\": 3600}",
        ));

        $response = curl_exec($curl);
        curl_close($curl); */

        $this->data[ "jwt" ] = json_decode( $response );
        $this->data[ "tempID" ] = uniqid( time().rand( 100, 999 ) );
        $this->data[ "bar_inicial" ] = 100 / count( $this->data[ "pasos" ] );

     /*    $myfile = fopen("webdictionary.txt", "r") or die("Error: Unable to open file!");
        echo fread($myfile, filesize("webdictionary.txt"));
        fclose($myfile); */

        $this->data[ "terminos" ] = file_get_contents( "tyc.txt" );

        $this->data[ "ine" ]  = strlen( $this->data[ "usuario" ]->data->valida_ine->codigoValidacion ?? null ) > 5 ? 1 : 0;
        $this->data[ "curp" ] = strlen( $this->data[ "usuario" ]->data->valida_curp->codigoValidacion ?? null ) > 5 ? 1 : 0;
        $this->data[ "vida" ] = strlen( $this->data[ "usuario" ]->data->valida_vida->sessionToken ?? null ) > 5 ? 1 : 0;


        echo template( "registro/vincular", $this->data );
    }


    // recibe formulario de registro y valida los datos
    // si todo sale bien, crea el nuevo socio
    public function procesa_registro( $demo = 0, $modelo = 0 )
    {
        $data = $this->request->getPost();

        if( !isset( $data[ "version" ] ) ){
            $data[ "version" ] = 1;
        }

        if( $demo > 0 ){
            $abc  = str_split("ABCDEFGHIJKLMNOPQRSTUVWXYZ" );
            $data = [
                "nombre"        => random( "nombre" ),
                "apellido1"     => random( "apellido" ),
                "apellido2"     => random( "apellido" ),
                "correo"        => "",
                "celular"       => rand(10000,99999).rand(10000,99999),
                "curp"          => $abc[ array_rand( $abc ) ]."X".$abc[ array_rand( $abc ) ].$abc[ array_rand( $abc ) ].rand( 70, 99 ).rand( 10, 12 ).rand( 10, 28 ).( rand( 0, 1 ) ? "H" : "M" )."DFXXX0".rand(0, 9),
                "patrocinador"  => $demo,
                "origen"        => "MX",
                "pais"          => "MX"
            ];

            $ms = explode( " ", $data[ "apellido1" ] );
            $data[ "correo" ] = $ms[ 0 ].rand(10,99999)."@gmail.com";
        }
        elseif( $data[ "version" ] != 2 ){

            $validation = service( "validation" );

            $validation->setRules( [
                "nombre"       => "required",
                "apellido1"    => "required",
                "curp"         => "required|curp[{$data[ "origen" ]}]|curp_existe",
                "correo"       => "valid_email|correo_existe",
                "celular"      => "numeric|celular_existe",
                "nacion"       => "required",
                "patrocinador" => "required|patrocinador_activo"
            ] );

            // Si hay errores de validacióna utomática, regresar a formulario
            if( !$validation->withRequest( $this->request )->run() ){
                return redirect()
                    ->back()
                    ->with( "errors", $validation->getErrors() )
                    ->withInput();
            } 
            
            // Obtenemos variables del formulario
            
        }

        // Creamos plantilla para crear la nueva entidad usuario
        $fecha  = date( "Y-m-d H:i:s" );

        $fechanac = $data[ "version" ] == 2 ? $data[ "fechanac" ] : get_fechanac( $data[ "curp" ] );

        if( !isset( $data[ "origen" ] ) ){
            $data[ "origen" ] = $data[ "nacionalidad" ] ?? null;
        }

        $recibe = [
            "estatus_codigo" => "201-ACTIVO",
            "rol_codigos"    => [ "10-SOCIO" ],
            "data"           => [
                "nombre"        => limpia_acentos( $data[ "nombre" ] ),
                "layout"        => [],
                "apellidos"     => [ limpia_acentos( $data[ "apellido1" ] ), limpia_acentos( $data[ "apellido2" ] )],
                "avatar"        => [
                    "imagenes"      => [],
                    "activo"        => null
                ],
                "verificacion"   => [],
                "verificaciones" => [],
                "ubicacion"      => [
                    "code"          => $data[ "pais" ],
                    "origen"        => $data[ "origen" ]
                ],
                "splash" => [
                    [
                        "tipo" => "bienvenida",
                        "parametros" => []
                    ]
                ],
                "nacionalidad"  => $data[ "curp" ] ? "MEXICANA" : "EXTRANJERA",
                "valida_curp"   => json_decode( $data[ "valida_curp" ] ?? "{}" ),
                "valida_vida"   => json_decode( $data[ "valida_vida" ] ?? "{}" ),
                "valida_ine"    => json_decode( $data[ "valida_ine" ]  ?? "{}" ),
                "domicilio"     => null,
                "tarjeta"       => [
                    "numero"        => "",
                    "estatus"       => "126-NO-ADQUIRIDO",
                    "folio"         => 0
                ],
                "credencial"    => [
                    "frente"        => $data[ "version" ] == 2  ? "frente.jpg" : null,
                    "reverso"       => $data[ "version" ] == 2  ? "reverso.jpg" : null,
                    "estatus"       => $data[ "version" ] == 2  ? ( $data[ "ine_verificado" ] ? 2 : 1 ) : 0,
                    "motivo"        => "",
                    "acta"          => null
                ],
                "clabe"         => "",
                "saldo"         => [],
                "estatus"       => [
                    "modelos"       => [],
                    "updated"       => 0
                ],
                "sat"           => [
                    "estatus"       => 0,
                    "csf"           => null,
                    "rfc"           => null
                ],
                "rango"     => "100-SOCIO",
                "recompensas"    => [
                    "activa" => "010-CELULAR",
                    "ciclo"  => 1,
                    "inicia" => null,
                    "estrellas" => 0
                ],
                "checks" => null,
                "genero"    => isset($data[ "sexo" ] ) ? ( $data[ "sexo" ] == "H" ? "MASCULINO" : "FEMENINO" ) : null
            ],
            "correo"        => strtolower( $data[ "correo" ] ),
            "telefono"      => $data[ "celular" ] ?? null,
            "curp"          => $data[ "curp" ] ?? "",
            "dni"           => $data[ "dni" ] ?? "",
            "fechanac"      => $fechanac,
            "redes"         => [
                "patrocinador"  => $data[ "patrocinador" ] == 9999999 ? 0 : $data[ "patrocinador" ]
            ],
            "historial"     => [
                "registro"      => $fecha,
                "validacion"    => null,
                "modelos"       => [],
                "rangos"        => [],
                "reset"         => $fecha,
                "vigencia"      => endCycle( $fecha, 6 )
            ]            
        ];
    
        // Complementamos la plantilla con información inicial para cada modelo de negocio
        
        foreach( MODELOS as $m ){
            if( $m[ "settings" ][ "efectivo" ] ){
                $recibe[ "historial" ][ "modelos" ][ $m[ "codigo" ] ] = [
                    "primercompra"   => json_decode( "{}" ),
                    "ultimacompra"   => null,
                    "fondeos" => [],
                    "reset" => $fecha,
                    "ingresos" => [
                        date( "Ym" ) => []
                    ],
                    "calificaciones" => [
                        date( "Ym" ) => []
                    ]
                ];

                $recibe[ "data" ][ "saldo" ][ $m[ "codigo"] ] = [
                    "cantidad" => 0.00,
                    "estatus"  => 0
                ];

                $recibe[ "data" ][ "estatus" ][ "modelos" ][ $m[ "codigo"] ] = "210-NUEVO";

                $recibe[ "redes" ][ "modelos" ][ $m[ "codigo" ] ] = [
                    "padre" => $data[ "patrocinador" ] == 9999999 ? 0 : $data[ "patrocinador" ],
                    "patrocinador" => $data[ "patrocinador" ],
                    "hijos" => [],
                    "rango" =>  $m[ "settings" ][ "rango_base" ] ?? null,
                    "profundidad" => [
                        "activos" => [0,0,0],
                        "calificados" => [0,0,0]
                    ]
                ];
            }
        }

        $puntos_verificacion = admin( "puntos_verificacion" );
        foreach( $puntos_verificacion as $codigo => $punto){

            // temporal activamos el telefono al no existir verificación por ahora, el resto en false
            $recibe[ "data"][ "verificacion" ][ $codigo ] = false;
        }

        $puntos_verificacion = admin( "verificaciones" );
        foreach( $puntos_verificacion as $codigo => $punto){

            // temporal activamos el telefono al no existir verificación por ahora, el resto en false
            $recibe[ "data"][ "verificaciones" ][ $punto->codigo ] = false;
        }

        // puntos de verificación iniciales

        if( $recibe[ "fechanac" ] ){
            $recibe[ "data"][ "verificaciones" ][ "FECHANAC" ] = true;
        }

        $recibe[ "data"][ "verificaciones" ][ "EMAIL" ]   = true;
        $recibe[ "data"][ "verificaciones" ][ "CELULAR" ] = true;

        $usuario = model( "UsuarioModel" );
        $entidad = new \App\Entities\E_usuario();
        $entidad->fill( $recibe );

        $id = $usuario->insert( $entidad ); 

        $usuario = model( "UsuarioModel" )->find( $id );
        $recibe[ "password" ] = $usuario->password = $demo > 0 ? "1234" : random_password();

        // crear usuario en TALENTO.NET

        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, "https://core.beneleit.talentonet.com/api/beneleit/crear_socio" );
        curl_setopt($curl, CURLOPT_POST, true );
        curl_setopt($curl, CURLOPT_POSTFIELDS, http_build_query( [ "socio_id" => $usuario->id ] ) );
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true); 
        $respuesta = json_decode( curl_exec( $curl ) );
        curl_close($curl);

        $datax = $usuario->data;
        $datax->talento_id = $respuesta->cliente_id;
        $usuario->data = $datax;
      
        model( "UsuarioModel" )->save( $usuario );

        if( $data[ "patrocinador" ] != 9999999 ){
            $padre = model( "UsuarioModel" )->find( $data[ "patrocinador" ] );
            $redes = $padre->redes;

            foreach( MODELOS as $m ){
                if( $m[ "settings" ][ "efectivo" ] ){
                    // feature pendiente de implementación, no es requerimiento de impacto
                    // $redes->modelos->{$m[ "codigo"]}->hijos[] = $usuario->id;
                }
            }
            
            $padre->redes = $redes;
            model( "UsuarioModel" )->save( $padre );
        }
        
        // BITACORA Creación de cuenta de usuario
        bitacora( 4, $usuario->id, [ 
            "patrocinador" => $usuario->redes->patrocinador,
            "password" => $recibe[ "password" ]
        ] );

        if( $data[ "version" ] == 2 ){

            $path = "data/{$id}/ine/";
            if(!is_dir($path)) mkdir($path, 0755, true);

            copy( "temp/{$data[ "tempID" ]}_frente.jpg",  $path."frente.jpg" );
            copy( "temp/{$data[ "tempID" ]}_reverso.jpg", $path."reverso.jpg" );

            unlink( "temp/{$data[ "tempID" ]}_frente.jpg" );
            unlink( "temp/{$data[ "tempID" ]}_reverso.jpg" );
        }

        // ENVIAR CORREO

        $imagenes = [
            "bienvenida" => "assets/img/bienvenida.png",
            "menu" => "assets/img/menu.png"
        ];

        $subject = "¡Te damos la bienvenida!";
        $message = "
            <img src=\"%%bienvenida%%\" width=\"100%\" alt=\"bienvenida\">
            <p>¡Hola ".$usuario->nombre()."! </p>
            <p>Hemos creado tu cuenta de socio Beneleit, a la cual se le ha asignado un número único con el que te identificarás de ahora en adelante. Recuerdalo y compartelo:</p>

            <p style=\" text-align:center; margin:20px 0\"><span style=\"background:#1a2542; text-align:center; padding:15px 0; width:200px; display:inline-block; color:#fff; border-radius:5px; font-size:30px;font-weight:bold\">".$usuario->id()."</span></p>

            <p>Tambien hemos habilitado tu oficina virtual, un espacio personal de trabajo donde podrás llevar un control total de tu cuenta a través de herramientas e indicadores que te brindarán asistencia en tu experiencia de negocios en Beneleit</p>
            
            <ul>
                <li style=\"margin: 10px 0\"><strong>Perfil de socio:</strong> Donde podrás verificar tu cuenta, personalizarla y mantenerla actualizada.</li>
                <li style=\"margin: 10px 0\"><strong>Panel de inicio:</strong> Un resumen general de tus redes, compras, calificaciones, ingresos y progresión en las diferentes promociones y bonos.</li>
                <li style=\"margin: 10px 0\"><strong>Ingresos:</strong> Detalles de tus ingresos por día, semana, mes y en general desde tu inicio como socio Beneleit. Aquí puedes monitorear cómo tu red va generando comisiones que cobrarás semana a semana directo en tu cuenta bancaria.</li>
                <li style=\"margin: 10px 0\"><strong>Compras:</strong> Nuestra tienda en línea donde podrás adquirir todos nuestros productos.</li>
                <li style=\"margin: 10px 0\"><strong>Redes:</strong> Representación gráfica de tus redes, con información a detalle de los socios que la conforman.</li>
                <li style=\"margin: 10px 0\"><strong>Centro de ayuda:</strong> Desde aquí podrás consultar manuales y tutoriales para sacarle un máximo provecho a tu oficina virtual, podrás levantar tickets de soporte para reportar incidencias o solicitar información, o si requieres una atención más personalizada, ponerte en contacto directo con nuestro servicio de Call Center a través de Whatsapp.</li>
            </ul>
            <img src=\"%%menu%%\" style=\"border-radius:6px\" width=\"100%\" alt=\"menu\">

            <p>Tu oficina virtual es una herramienta muy valiosa. ¡Usala!, si tienes dudas sobre alguna de sus funciones, puedes apoyarte en tu patrocinador o consultar nuestros canales de ayuda.</p>
            <p>Ingresa ahora utilizando el password temporal que te hemos asignado, no olvides que una vez dentro, deberás ir a tu perfil de usuario para cambiarlo por uno que te sea familiar, seguro y fácil de recordar.</p>
                    <p style=\" text-align:center; margin:20px 0\"><span style=\"background:#e5e5e5; text-align:center; padding:15px 0; width:200px; display:inline-block; color:#009779; border-radius:5px; font-size:30px;font-weight:bold\">{$recibe[ "password" ]}</span></p>
            <p style=\"text-align:center\">de parte de todo el equipo Beneleit:<br><strong>¡Bienvenido!</strong></p>
            <p><a href=\"".base_url()."\" style=\"text-decoration:none; cursor:pointer; background:#009779; text-align:center; padding:15px 0; width:100%; display:inline-block; border:1px solid #066545; color:white; border-radius:5px;\" value=\"reset password\">Ir ahora a mi oficina virtual</a></p>
            
        
        ";

        $respuesta = envia_correo( $usuario, $subject, $message, $imagenes );
        
        if( $demo > 0 ){
            return redirect()->to( "red/{$modelo}" );
        }
        else{
            return redirect()->to( "registro_exito/".base64_encode( $usuario->password_original() ) )->with( "msg", [ 
                "clase" => "success", 
                "icono" => "user-check", 
                "texto" => "Cuenta de nuevo socio creada con éxito"] );
        }
    }

    public function valida_patrocinador(){
        $respuesta    = [ "error" => null ];
        $patrocinador = model( "UsuarioModel" )->find( $this->request->getPost( "id" ) );

        if( !$patrocinador ){
            $respuesta[ "error" ] = "No existe el patrocinador";
            return json_encode( $respuesta );
        }

        if( substr( $patrocinador->estatus_codigo, 0, 3 ) < 200 ){
            $respuesta[ "error" ] = "El patrocinador no está activo";
            return json_encode( $respuesta );
        }

        $respuesta[ "nombre" ] = $patrocinador->nombre( 2, true );
        $respuesta[ "avatar" ] = $patrocinador->avatar( $this->request->getPost( "avatar_size" )  );

        return json_encode( $respuesta );
    }



    public function valida_pat(){
        $respuesta    = [ "error" => null ];
        $patrocinador = model( "UsuarioModel" )->find( $this->request->getPost( "patrocinador" ) );

        if( !$patrocinador ){
            $respuesta[ "error" ] = "No existe el patrocinador";
            return json_encode( $respuesta );
        }

        if( substr( $patrocinador->estatus_codigo, 0, 3 ) < 200 ){
            $respuesta[ "error" ] = "El socio no está activo";
            return json_encode( $respuesta );
        }

        $respuesta[ "nombre" ] = $patrocinador->avatar( 32 )." ".$patrocinador->nombre( 2, 2 )." ".$patrocinador->bandera();

        return json_encode( $respuesta );
    }


    public function valida_curp(){
        $respuesta = [ "error" => null ];
        $curp      = $this->request->getPost( "curp" );

       if( $this->request->getPost( "socio" ) ?? null ){
            $socio = model( "UsuarioModel" )->find( $this->request->getPost( "socio" ) );
       }

        $fechanac = substr( $curp, 6, 4 )."-".substr( $curp, 4, 2 )."-".substr( $curp, 2, 2 );

        if( $curp != 'SIAA790501HCMOCL05' AND model( "UsuarioModel" )->where( "curp = '{$curp}' AND SUBSTRING(estatus_codigo, 1, 3) > 200".( ( $socio ?? null ) ? " AND id != {$socio->id}" : "" ) )->first() ){
            $respuesta[ "error" ] = "La CURP que proporcionaste ya está registrada.</p><p class=\"text-marine\"><i class=\"fa fa-circle-info\"></i> <a href=\"".base_url()."recover\">Click aquí</a> si ya estas registrado y necesitas recuperar tu password";
            return json_encode( $respuesta );
        }
        else{ 
            $curl = curl_init();
            $key  = VARIABLES["nubarium"][ "valor" ];

            curl_setopt_array(
                $curl, array(
                    CURLOPT_URL => "https://curp.nubarium.com/renapo/v3/valida_curp",
                    CURLOPT_RETURNTRANSFER => true,
                    CURLOPT_ENCODING => "",
                    CURLOPT_MAXREDIRS => 10,
                    CURLOPT_TIMEOUT => 0,
                    CURLOPT_USERPWD  => $key[ "user" ].":".$key[ "pass" ],
                    CURLOPT_FOLLOWLOCATION => true,
                    CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                    CURLOPT_CUSTOMREQUEST => "POST",
                    CURLOPT_POSTFIELDS =>"{\"curp\": \"{$curp}\"}",
                    CURLOPT_HTTPHEADER => array(
                        'Content-Type: application/json'
                    ),
                )
            );

            $respuesta[ "datos" ] = json_decode( curl_exec( $curl ) );
            curl_close($curl);
        }

        if( $respuesta[ "datos" ]->estatus == 'OK' && ( $socio ?? null ) ){
            $socio->curp = $curp;

            $data = $socio->data;
            $data->valida_curp = $respuesta[ "datos" ];
            $socio->data = $data;
            model( "UsuarioModel" )->save( $socio );

            // BITACORA Creación de cuenta de usuario
            bitacora( 117, $socio->id, (array)$respuesta[ "datos" ] );            
        }            

        return json_encode( $respuesta );
    }
    

    public function valida_correo(){
        $respuesta = [ "error" => null ];
        $correo      = $this->request->getPost( "correo" );

        $db = db_connect();
        $query = "select count(*) as total from t_usuarios where correo = '{$correo}' AND estatus_codigo = '201-ACTIVO' limit 1";

        if( $db->query( $query )->getRow()->total > 0 ){
            $respuesta[ "error" ] = "El correo electrónico que proporcionaste ya está registrado.</p><p class=\"text-marine\"><i class=\"fa fa-circle-info\"></i> <a href=\"".base_url()."recover\">Click aquí</a> si ya estas registrado y necesitas recuperar tu password</p>";

            return json_encode( $respuesta );
        }
    
        return json_encode( $respuesta );
    }

    public function valida_celular(){
        $respuesta = [ "error" => null ];
        $celular      = $this->request->getPost( "celular" );

        $db = db_connect();
        $query = "select count(*) as total from t_usuarios where telefono = '{$celular}' AND estatus_codigo = '201-ACTIVO' limit 1";

        if( $db->query( $query )->getRow()->total > 0 ){
            $respuesta[ "error" ] = "El número que proporcionaste ya está registrado.</p>";

            return json_encode( $respuesta );
        }
    
        return json_encode( $respuesta );
    }    


    public function registro_exito( $nuevo_id ){
        if( $id = model( "UsuarioModel" )->where( "password = '".base64_decode( $nuevo_id )."'" )->first() ){

            $this->data[ "navbar" ] = false;
            $this->data[ "titulo" ] = "Nuevo socio creado";
            $this->data[ "nuevo" ]  = model( "UsuarioModel" )->find( $id->id );

            if($this->data[ "nuevo" ] ){
                return template( "registro/exito", $this->data );
            }
        }

        return redirect()->to( "login" );
    }

    public function camara( $modo, $tempID ){
        $this->data[ "navbar" ] = false;
        $this->data[ "modo" ]  = $modo;
        $this->data[ "tempID" ]  = $tempID;
        $this->data[ "header_x" ] = true;

        echo template( "registro/camara", $this->data );
    }

    public function upload( $modo, $tempID, $s = null ){
        $this->data[ "navbar" ]  = false;
        $this->data[ "modo" ]    = $modo;
        $this->data[ "s" ]   = $s;
        $this->data[ "tempID" ]  = $tempID;

        echo template( "registro/upload", $this->data );
    }

    public function camara_shot(){

        $ok = false;
        $data = json_decode(file_get_contents('php://input'), true);
        extract($data);

        list($type, $data) = explode(';',  $shot );
        list( $enc, $data)      = explode(',', $data);
        $datax = str_replace(' ', '+', $data);
        $data = base64_decode($datax);

        if( !file_exists( "temp" ) ){
            mkdir( "temp" );
        }
        $archivo = "temp/{$tempID}_{$modo}.jpg";

        if( file_put_contents( $archivo, $data) ){
            $ok = true;
        }

        if( $modo == "frente" ){
            $curl = curl_init();
            $key  = VARIABLES["nubarium"][ "valor" ];

            curl_setopt_array($curl, array(
                CURLOPT_URL => 'https://ocr.nubarium.com/ocr/v1/obtener_datos_id',
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_ENCODING => '',
                CURLOPT_MAXREDIRS => 10,
                CURLOPT_TIMEOUT => 0,
                CURLOPT_USERPWD  => $key[ "user" ].":".$key[ "pass" ],
                CURLOPT_FOLLOWLOCATION => true,
                CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                CURLOPT_CUSTOMREQUEST => 'POST',
                CURLOPT_POSTFIELDS =>"{ \"id\" : \"{$datax}\" }",
                CURLOPT_HTTPHEADER => array(
                    'Content-Type: application/json'
                ),
            ));

            $response = json_decode( curl_exec($curl) );

            curl_close($curl);
        }

        echo json_encode( [ "ok" => $ok, "respuesta" => $response, "base64" => $datax ] );
    }

    public function valida_ine(){

        extract( $this->request->getPost() );

        $curl = curl_init();
        $key  = VARIABLES["nubarium"][ "valor" ];

        if( $socio ?? null ){
            $s = model( "UsuarioModel" )->find( $socio );

            $frente  = base64_encode( file_get_contents( "data/{$s->id}/ine/frente.jpg" ) );
            $reverso = base64_encode( file_get_contents( "data/{$s->id}/ine/reverso.jpg" ) );            
        }
        else{
            $frente  = base64_encode( file_get_contents( "temp/{$tempID}_frente.jpg" ) );
            $reverso = base64_encode( file_get_contents( "temp/{$tempID}_reverso.jpg" ) );
        }

        curl_setopt_array($curl, array(
            CURLOPT_URL => 'https://ocr.nubarium.com/ocr/v1/obtener_datos_id',
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_USERPWD  => $key[ "user" ].":".$key[ "pass" ],
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'POST',
            CURLOPT_POSTFIELDS =>"{ \"id\" : \"{$frente}\", \"idReverso\" : \"{$reverso}\" }",
            CURLOPT_HTTPHEADER => array(
                'Content-Type: application/json'
            ),
        ));

        $response = json_decode( curl_exec($curl) );

        curl_close($curl);

        if( $socio ?? null ){
            if( $response->curp == $s->curp ){
                $d = $s->data;
                $d->valida_ine = $response;
                $s->data = $d;
                model( "UsuarioModel" )->save( $s );            
            }

            // BITACORA Creación de cuenta de usuario
            bitacora( 118, $s->id, (array)$response );        
        }


        echo json_encode( $response );
    }


    public function guarda_ine(){
        $tempID = $this->request->getPost( "tempID" );
        $data   = $this->request->getPost( "image" );
        $modo   = $this->request->getPost( "modo" );
        $socio  = $this->request->getPost( "socio" );

        if( $socio ){
            if( !file_exists( "data/{$socio}/ine" ) ){
                mkdir( "data/{$socio}/ine", 0755, true );
            }

            $path = "data/{$socio}/ine/{$modo}.jpg";

            $s = model( "UsuarioModel" )->find( $socio );
            $d = $s->data;
            $d->credencial->{$modo} = $modo.".jpg";
            $s->data = $d;
            model( "UsuarioModel" )->save( $s );
        }

        else{

            if( !file_exists( "temp" ) ){
                mkdir( "temp" );
            }

            $path = "temp/{$tempID}_{$modo}.jpg";
        }

        list($type, $data) = explode(';', $data);
        list(, $data)      = explode(',', $data);
        $data = base64_decode($data);

        file_put_contents($path, $data);

        echo $path;
    }


    public function valida_vida(){

        extract( $this->request->getPost() );

        $s = model( "UsuarioModel" )->find( $socio );

        $frente  = base64_encode( file_get_contents( "data/{$s->id}/ine/frente.jpg" ) );
        $reverso = base64_encode( file_get_contents( "data/{$s->id}/ine/reverso.jpg" ) );            

        $d = $s->data;
        $d->valida_vida = $data;
        $s->data = $d;
        model( "UsuarioModel" )->save( $s );    

        // BITACORA Creación de cuenta de usuario
        bitacora( 119, $s->id, (array)$data );     
    }    
}


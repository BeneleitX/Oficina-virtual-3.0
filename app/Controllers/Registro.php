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
         /*    
            [
                "titulo" => "Identificación",
                "icono"  => "fa-address-card"
            ],   */          
            [
                "titulo" => "Modelo de negocio",
                "icono"  => "fa-diagram-project"                
            ],
            [
                "titulo" => "Verificación",
                "icono"  => "fa-address-card"
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


    // recibe formulariod e registro y valida los datos
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
                    "code"          => null,
                    "origen"        => $data[ "nacionalidad" ]
                ],
                "splash" => [
                    [
                        "tipo" => "bienvenida",
                        "parametros" => []
                    ]
                ],
                "valida_curp"   => $data[ "valida_curp" ],
                "domicilio"     => null,
                "tarjeta"       => [
                    "numero"        => "",
                    "estatus"       => "126-NO-ADQUIRIDO",
                    "folio"         => 0
                ],
                "credencial"    => [
                    "frente"        => null,
                    "reverso"       => null,
                    "estatus"       => 0,
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
                "checks" => null
            ],
            "correo"        => strtolower( $data[ "correo" ] ),
            "genero"        => $data[ "sexo" ] == "H" ? "MASCULINO" : "FEMENINO",
            "telefono"      => $data[ "celular" ] ?? null,
            "curp"          => $data[ "nacionalidad" ] == "MX" ? $data[ "curp" ] : $data[ "dni" ],
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

        if( $curp == "SIAA790501HCMLCL05" ){
            $respuesta[ "datos" ] = json_decode( '{"estatus":"OK","codigoValidacion":"vc1619806387.2754068","curp":"RAZR811012HVZMPB00","nombre":"RAMIRO ALONSO","apellidoPaterno":"RASCON","apellidoMaterno":"ZAPATA","sexo":"HOMBRE","fechaNacimiento":"11/10/1981","paisNacimiento":"MEXICO","estadoNacimiento":"VERACRUZ","docProbatorio":1,"datosDocProbatorio":{"entidadRegistro":"VERACRUZ","tomo":"","claveMunicipioRegistro":"108","anioReg":"1983","claveEntidadRegistro":"30","foja":"","numActa":"03382","libro":"","municipioRegistro":"MINATITLÁN"},"estatusCurp":"RCN","codigoMensaje":"0"}' );
        }
        else{
            
            if( model( "UsuarioModel" )->where( "curp = '{$curp}' AND SUBSTRING(estatus_codigo, 1, 3) > 200" )->first() ){
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
        }

        return json_encode( $respuesta );
    }
    

    public function valida_correo(){
        $respuesta = [ "error" => null ];
        $correo      = $this->request->getPost( "correo" );

        if( model( "UsuarioModel" )->where( "correo = '{$correo}' AND SUBSTRING(estatus_codigo, 1, 3) > 200" )->first() ){
            $respuesta[ "error" ] = "El correo electrónico que proporcionaste ya está registrado.</p><p class=\"text-marine\"><i class=\"fa fa-circle-info\"></i> <a href=\"".base_url()."recover\">Click aquí</a> si ya estas registrado y necesitas recuperar tu password";
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

        echo template( "registro/camara", $this->data );
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

            $response = curl_exec($curl);

            curl_close($curl);

            file_put_contents( "respuesta.txt", $response );
        }

        echo json_encode( [ "ok" => $ok, "respuesta" => $response, "base64" => $datax ] );
    }
}


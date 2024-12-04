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


    // recibe formulariod e registro y valida los datos
    // si todo sale bien, crea el nuevo socio
    public function procesa_registro( $demo = 0, $modelo = 0 )
    {
        $data = $this->request->getPost();

         if( $demo > 0 ){
            $abc  = str_split("ABCDEFGHIJKLMNOPQRSTUVWXYZ" );
            $data = [
                "nombre" => random( "nombre" ),
                "apellido1" => random( "apellido" ),
                "apellido2" => random( "apellido" ),
                "correo" => "demo".rand(10000,99999)."@demo.com",
                "celular" => rand(10000,99999).rand(10000,99999),
                "curp" => $abc[ array_rand( $abc ) ]."X".$abc[ array_rand( $abc ) ].$abc[ array_rand( $abc ) ].rand( 70, 99 ).rand( 10, 12 ).rand( 10, 28 ).( rand( 0, 1 ) ? "H" : "M" )."DFXXX0".rand(0, 9),
                "patrocinador" => $demo
            ];
        }
        else{

            $validation = service( "validation" );

            $validation->setRules( [
                "nombre"       => "required",
                "apellido1"    => "required",
                "curp"         => "required|curp|curp_existe",
                "correo"       => "valid_email|correo_existe",
                "celular"      => "numeric|exact_length[10]|celular_existe",
                "patrocinador" => "required|patrocinador_activo",
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
                "verificacion"  => [],
                "splash" => [
                    [
                        "tipo" => "bienvenida",
                        "parametros" => []
                    ]
                ],
                "domicilio"     => null,
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
            "telefono"      => $data[ "celular" ],
            "curp"          => $data[ "curp" ],
            "redes"         => [
                "patrocinador"  => $data[ "patrocinador" ] == 9999999 ? 0 : $data[ "patrocinador" ]
            ],
            "historial"     => [
                "registro"      => $fecha,
                "validacion"    => null,
                "modelos"       => [],
                "rangos"        => [],
                "reset"         => $fecha
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
        $usuario->data->talento_id = $respuesta->cliente_id;
      

        model( "UsuarioModel" )->save( $usuario );

        if( $data[ "patrocinador" ] != 9999999 ){
            $padre = model( "UsuarioModel" )->find( $data[ "patrocinador" ] );
            $redes = $padre->redes;

            foreach( MODELOS as $m ){
                if( $m[ "settings" ][ "efectivo" ] ){
                    $redes->modelos->{$m[ "codigo"]}->hijos[] = $usuario->id;
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
                        <li style=\"margin: 10px 0\"><strong>Ingresos:</strong> Detalles de tus ingresos por día, semana, mes y en general desde tu inicio como socio Beneleit. Aquí puede smonitorear cómo tu red va generando comisiones que cobrarás semana a semana directo en tu cuenta bancaria.</li>
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

        if(!$patrocinador){
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


    public function registro_exito( $nuevo_id ){
        $id = model( "UsuarioModel" )->where( "password = '".base64_decode( $nuevo_id )."'" )->first();

        $this->data[ "navbar" ] = false;
        $this->data[ "titulo" ] = "Nuevo socio creado";
        $this->data[ "nuevo" ]  = model( "UsuarioModel" )->find( $id->id );

        if($this->data[ "nuevo" ] ){
            echo template( "registro/exito", $this->data );
        }
        else{
            return redirect()->to( "login" );
        }
    }
}

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
            $data = $this->request->getPost();
        }

        // Creamos plantilla para crear la nueva entidad usuario
        $recibe = [
            "estatus_codigo" => "201-ACTIVO",
            "rol_codigos"    => [ "10-SOCIO" ],
            "data"           => [
                "nombre"        => $data[ "nombre" ],
                "apellidos"     => [ $data[ "apellido1" ], $data[ "apellido2" ]],
                "avatar"        => [
                    "imagenes"      => [],
                    "activo"        => null
                ],
                "verificacion"  => [],
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
                    "csf"           => null
                ],
                "rango"     => "00-SOCIO"
            ],
            "correo"        => $data[ "correo" ],
            "telefono"      => $data[ "celular" ],
            "curp"          => $data[ "curp" ],
            "password"      => $demo > 0 ? "1234" : random_password(),
            "redes"         => [
                "patrocinador"  => $data[ "patrocinador" ]
            ],
            "historial"     => [
                "registro"      => date( "Y-m-d" ),
                "validacion"    => null,
                "modelos"       => []
            ]               
        ];
    
        // Complementamos la plantilla con información inicial para cada modelo de negocio
        foreach( MODELOS as $m ){
            if( $m[ "settings" ][ "efectivo" ] ){
                $recibe[ "historial" ][ "modelos" ][ $m[ "codigo" ] ] = [
                    "primercompra"   => null,
                    "ultimacompra"   => null,
                    "fondeos" => [],
                    "ingresos" => [],
                    "calificaciones" => [
                        date( "Ym" ) => []
                    ]
                ];

                $recibe[ "data" ][ "saldo" ][ $m[ "codigo"] ] = 0.00;
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
            $recibe[ "data"][ "verificacion" ][ $codigo ] = ($codigo == "telefono");
        }

        $usuariomodel = model( "UsuarioModel" );

        $entidad = new \App\Entities\E_usuario();
        $entidad->fill( $recibe );

        $id = $usuariomodel->insert( $entidad );

        if( $data[ "patrocinador" ] != 9999999 ){
            $padre = model( "UsuarioModel" )->find( $data[ "patrocinador" ] );
            $redes = $padre->redes;

            foreach( MODELOS as $m ){
                if( $m[ "settings" ][ "efectivo" ] ){
                    $redes->modelos->{$m[ "codigo"]}->hijos[] = $id;
                }
            }
            
            $padre->redes = $redes;
            model( "UsuarioModel" )->save( $padre );
        }
        
        // BITACORA Creación de cuenta de usuario
        bitacora( 4, $id, [ 
            "patrocinador" => $recibe[ "redes" ][ "patrocinador" ],
            "password" => $recibe[ "password" ] 
        ] );

        if( $demo > 0 ){
            return redirect()->to( "red/{$modelo}" );
        }
        else{
            return redirect()->to( "registro_exito/".$id )->with( "msg", [ 
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
        $this->data[ "navbar" ] = false;
        $this->data[ "titulo" ] = "Nuevo socio creado";
        $this->data[ "nuevo" ]  = model( "UsuarioModel" )->find( $nuevo_id );

        echo template( "registro/exito", $this->data );
    }
}

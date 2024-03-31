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
    public function procesa_registro()
    {
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

        // Creamos plantilla para crear la nueva entidad usuario
        $recibe = [
            "estatus_codigo" => "210-NUEVO",
            "rol_codigo"     => "10-SOCIO",
            "data"           => [
                "nombre"        => $data[ "nombre" ],
                "apellidos"     => [ $data[ "apellido1" ], $data[ "apellido2" ]],
                "avatar"        => [
                    "imagenes"      => [],
                    "activo"        => null
                ],
                "verificacion"  => [],
                "credencial"    => [
                    "frente"        => null,
                    "reverso"       => null,
                    "estatus"       => 0,
                    "motivo"        => "",
                    "acta"          => null
                ],
                "clabe"         => ""
            ],
            "correo"        => $data[ "correo" ],
            "telefono"      => $data[ "celular" ],
            "curp"          => $data[ "curp" ],
            "password"      => random_password(),
            "redes"         => [
                "patrocinador"  => $data[ "patrocinador" ]
            ],
            "historial"     => [
                "registro"      => date( "Y-m-d" ),
                "validacion"    => null,
                "modelos"       => []
            ],
            "shoppingcart"     => [
                "modelos"       => []
            ]                
        ];

        // Complementamos la plantilla con información inicial para cada modelo de negocio
        foreach( MODELOS as $m ){
            if( $m[ "settings" ][ "efectivo" ] ){
                $recibe[ "historial" ][ "modelos" ][ $m[ "codigo" ] ] = [
                    "primercompra" => null,
                    "ultimacompra" => null,
                ];

                $recibe[ "redes" ][ "modelos" ][ $m[ "codigo" ] ] = [
                    "padre" => $data[ "patrocinador" ],
                    "hijos" => [],
                ];    
                
                $recibe[ "shoppingcart" ][ "modelos" ][ $m[ "codigo" ] ] = [
                    "peso" => 0,
                    "total" => 0.00,
                    "metodopago" => null,
                    "comisionbanco" => 0.00,
                    "entrega" => [
                        "costo" => 0.00,
                        "tipo" => "shipping",
                        "codigo" => null
                    ],
                    "PTS" => [],
                    "promos" => []
                ];
            }
        }

        $puntos_verificacion = admin( "puntos_verificacion" );
        foreach( $puntos_verificacion as $codigo => $punto){
            $recibe[ "data"][ "verificacion" ][ $codigo ] = false;
        }

        $usuariomodel = model( "UsuarioModel" );
        $id = $usuariomodel->insert( new \App\Entities\E_usuario( $recibe ) );
        
        // BITACORA Creación de cuenta de usuario
        bitacora( 4, $id, [ 
            "patrocinador" => $recibe[ "redes" ][ "patrocinador" ],
            "password" => $recibe[ "password" ] 
        ] );

        return redirect()->to( "registro_exito/".$id )->with( "msg", [ 
            "clase" => "success", 
            "icono" => "user-check", 
            "texto" => "Cuenta de nuevo socio creada con éxito"] );
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

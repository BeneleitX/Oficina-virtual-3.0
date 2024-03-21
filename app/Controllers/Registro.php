<?php
namespace App\Controllers;


class Registro extends BaseController
{
    public function formulario(){
        $this->data[ "navbar" ] = false;
        $this->data[ "titulo" ] = "Registro de nuevo socio";

        echo template( "registro/formulario", $this->data );
    }


    public function procesa_registro(){

        $validation = service("validation");

        $validation->setRules([
            "nombre" => "required",
            "apellido1" => "required",
            "curp" => "required|curp|curp_existe",
            "correo" => "valid_email|correo_existe",
            "celular" => "numeric|exact_length[10]|celular_existe",
            "patrocinador" => "required|patrocinador_activo",
        ]);

        if( !$validation->withRequest( $this->request )->run()){
            return redirect()
                ->back()
                ->with( "errors", $validation->getErrors())
                ->withInput();
        } 

        $data = $this->request->getPost();

        $recibe = [
            "estatus_codigo" => "210-NUEVO",
            "rol_codigo" => "SOCIO",
            "data" => [
                "nombre" => $data[ "nombre" ],
                "apellidos" => [ $data[ "apellido1" ], $data[ "apellido2" ]],
                "avatar" => [
                    "imagenes" => [],
                    "activo" => null
                ],
                // "beneficiario" => $data[ "beneficiario" ],
            ],
            "correo" => $data[ "correo" ],
            "telefono" => $data[ "celular" ],
            "curp" => $data[ "curp" ],
            "password" => random_password(),
            "redes" => [
                "patrocinador" => $data[ "patrocinador" ]
            ]
        ];

        $usuariomodel = model( "UsuarioModel" );
        $id = $usuariomodel->insert( new \App\Entities\E_usuario( $recibe ) );
        
        // BITACORA Creación de cuenta de usuario
        bitacora( 4, $id, [ 
            "patrocinador" => $recibe[ "patrocinador" ],
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

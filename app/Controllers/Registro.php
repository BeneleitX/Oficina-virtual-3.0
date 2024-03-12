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
            "correo" => "valid_email",
            "curp" => "required|curp",
            "celular" => "numeric|exact_length[10]",
        ],[
            "nombre" => [
                "required" => "No has escrito un Nombre",
            ],
            "apellido1" => [
                "required" => "No has escrito apellido",
            ]
        ]);

        if( !$validation->withRequest( $this->request )->run()){
            return redirect()
                ->back()
                ->with( "errors", $validation->getErrors())
                ->withInput();
        } 

        $data = $this->request->getPost();

        $recibe = [
            "estatus_codigo" => "NUEVO_INACTIVO",
            "rol_codigo" => "SOCIO",
            "nombre" => [
                "nombre" => $data[ "nombre" ],
                "apellidos" => [ $data[ "apellido1" ], $data[ "apellido2" ]],
            ],
            "correo" => $data[ "correo" ],
            "telefono" => $data[ "celular" ],
            "curp" => $data[ "curp" ],
            "password" => random_password(),
            "nacionalidad" => $data[ "nacionalidad" ],
            "residencia" => $data[ "residencia" ],
            "beneficiario" => $data[ "beneficiario" ],
            "redes" => []
        ];

        $candidato = new \App\Entities\E_usuario( $recibe );
        $usuariomodel = model( "UsuarioModel" );
        $id = $usuariomodel->insert( $candidato );

        return redirect()->to( "registro_exito/".$id )->with( "msg", [ 
            "clase" => "success", 
            "icono" => "user-check", 
            "texto" => "Cuenta de nuevo socio creada con éxito"] );
    }


    public function registro_exito( $nuevo_id ){
        $this->data[ "navbar" ] = false;
        $this->data[ "titulo" ] = "Nuevo socio creado";
        $this->data[ "nuevo" ]  = model( "UsuarioModel" )->find( 10 );

        echo template( "registro/exito", $this->data );
    }
}

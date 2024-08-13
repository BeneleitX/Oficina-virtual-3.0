<?php

namespace App\Controllers;

class Recompensas extends BaseController
{
    public function detalle(){

        $this->data[ "navbar"  ] = true;
        $this->data[ "socio"   ] = $this->data[ "usuario" ];
        $this->data[ "titulo"  ] = "Detalles de recompensas";
        $this->data[ "comisiones" ] = $this->data[ "socio" ]->getComisiones( null, "120-BIEX-3ER-NIVEL", "255-PENDIENTE" );

        load_catalogo( "recompensas");
        echo template( "recompensas/detalle", $this->data );
    }


    public function switch( $recompensa ){
        $socio = $this->data[ "usuario" ];

        $data = $socio->data;
        $previa = $data->recompensas->activa;
        $data->recompensas->activa = $recompensa;
        $socio->data = $data;
        model( "UsuarioModel" )->save( $socio );

        // BITACORA Eliminar beneficiario
        bitacora( 30, $socio->id, [ 
            "previa"     => $previa,
            "recompensa" => $recompensa,
            "usuario"    => $this->data[ "usuario" ]->id
        ] );

        return redirect()->to( "recompensas" )->with( "msg", [ 
            "clase" => "success", 
            "icono" => "check", 
            "texto" => "Se actualizó la recompensa activa" ] );         
    }
}





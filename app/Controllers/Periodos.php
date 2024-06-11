<?php

namespace App\Controllers;

class Periodos extends BaseController
{
    function __construct() {
        $this->data[ "menu" ] = "admin";
    }

    public function listado( $modelo ){
        $this->data[ "navbar" ]   = true;
        $this->data[ "modelo" ]   = $modelo;
        $this->data[ "titulo" ]   = "Periodos";
        $this->data[ "periodos" ] = model( "PeriodoModel" )->where( "modelo_codigo = '{$modelo}'" )->findAll();

        echo template( "periodos/listado", $this->data );
    }

    public function detalle( $periodo ){
        $this->data[ "navbar" ]   = true;
        $this->data[ "titulo" ]   = "Detalles de periodo <span class=\"badge bg-marine\">{$periodo}</span>";
        $this->data[ "periodo" ] = model( "PeriodoModel" )->find( $periodo );

        echo template( "periodos/detalle", $this->data );
    }


    public function corte(){
        if(VARIABLES[ "avance_corte" ][ "valor" ][ "porcentaje" ] != 100 ){
            $respuesta = [
                "error" => VARIABLES[ "avance_corte" ][ "valor" ]
            ];
        }
        else{
            extract( $this->request->getPost() );
            $respuesta = [
                "periodo" => $periodo
            ];

            $db = db_connect();
           
            

            for($a = 1; $a <= 100; $a++){
                usleep( 100000 );

                $respuesta[ "porcentaje" ] = $a;
                $sql = "update t_variables set valor = '".json_encode( $respuesta )."' where codigo = 'avance_corte';";
                $db->query( $sql );
            }
        }

        echo json_encode( $respuesta );
    }    
}

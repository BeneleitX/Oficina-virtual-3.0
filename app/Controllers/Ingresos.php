<?php

namespace App\Controllers;

class Ingresos extends BaseController
{
    function __construct() {
        $this->data[ "menu" ] = "ingresos";
    }

    public function balance( $modelo, $periodo ){
        $this->data[ "periodo" ] = model( "PeriodoModel" )->find( $periodo );

        if( null == $this->data[ "periodo" ] ){

            $dto = new \DateTime();
            $dto->setISODate( substr( $periodo, 3, 4 ), substr( $periodo, 7, 2 ) );
            $inicia  = $dto->format('Y-m-d');
            $dto->modify('+6 days');
            $termina = $dto->format('Y-m-d');
            $db      = db_connect();
            $result  = $db->query( "INSERT IGNORE INTO t_periodos VALUES ( '{$periodo}', '250-EN-PROCESO', '{$modelo}', 'SEMANAL', '{$inicia}', '{$termina}', JSON_OBJECT() )" );

            $this->data[ "periodo" ] = model( "PeriodoModel" )->find( $periodo );
        }

        $this->data[ "navbar" ]  = true;
        $this->data[ "modelo" ]  = $modelo;
        $this->data[ "titulo" ]  = "Ingresos por periodo <span class=\"badge bg-marine\">".substr($periodo, 3, 4)."-".substr($periodo, 7, 2)."</span> <span style=\"font-size:16px\">".estatus( $this->data[ "periodo" ][ "estatus_codigo" ] )."</span>";
        $this->data[ "socio"  ]  = $this->data[ "usuario" ];
        $this->data[ "comisiones" ] = $this->data[ "socio" ]->getComisiones( $periodo );
        load_catalogo( "esquemas", "modelo_codigo = '{$modelo}'");

        echo template( "ingresos/balance", $this->data );
    }
}

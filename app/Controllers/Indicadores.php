<?php
namespace App\Controllers;
require '../vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class Indicadores extends BaseController
{


    public function inicio( $modelo = null, $mes = null )
    {
        if( !(
            $this->data[ "usuario" ]->permiso( "39-REPORTES-CONTA" )
        ) ){
            return redirect()->to( "no_permiso" ); 
        }

        helper('historico');

        $this->data[ "mes" ]    = $mes ?? date( "Ym" );
        $this->data[ "modelo" ] = $modelo ?? "10-NUTRICION";
        $this->data[ "navbar" ] = true;
        $this->data[ "titulo" ] = "Histórico mensual";

        $this->data[ "historico" ] = [
            "venta" => [
                "total"    => historico_venta_total( $this->data[ "modelo" ], $this->data[ "mes" ] ),
                "nuevos"   => historico_venta_nuevos( $this->data[ "modelo" ], $this->data[ "mes" ] ),
                "recompra" => historico_venta_recompra( $this->data[ "modelo" ], $this->data[ "mes" ] )
            ]
        ];

        echo template( "indicadores/inicio", $this->data );
    }
}

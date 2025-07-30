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
            "venta" => historico_venta( $this->data[ "modelo" ], $this->data[ "mes" ] ),
            "pedidos" => historico_pedidos( $this->data[ "modelo" ], $this->data[ "mes" ] )
        ];

        echo template( "indicadores/inicio", $this->data );
    }
}

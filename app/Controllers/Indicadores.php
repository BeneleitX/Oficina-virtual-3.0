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

        load_catalogo( "productos", "modelo_codigo = '{$this->data[ "modelo" ]}'");

        $this->data[ "meses" ] = [];
        $meses = 12;

        $mes      = date( "Ym", strtotime( substr( $mes, 0, 4 )."-".substr( $mes, 4, 2 )."-01 - {$meses} month" ) );
        $anterior = date( "Ym", strtotime( substr( $mes, 0, 4 )."-".substr( $mes, 4, 2 )."-01 - ".( $meses + 1)." month" ) );
     
        for( $a = 0; $a <= $meses; $a++ ){
            $this->data[ "meses" ][] = strtoupper( mes( substr( $mes, 4, 2 ), 3) ); //." ".substr( $mes, 0, 4 );

            $anterior = $mes;
            $mes = date( "Ym", strtotime( substr( $mes, 0, 4 )."-".substr( $mes, 4, 2 )."-01 + 1 month" ) );
        }        

        $this->data[ "historico" ] = [
            "venta"   => historico_venta( $this->data[ "modelo" ], $this->data[ "mes" ] ),
            "pedidos" => historico_pedidos( $this->data[ "modelo" ], $this->data[ "mes" ] ),
            "ticket"  => [],
            "productos" => [],
            "reparto" => historico_reparto( $this->data[ "modelo" ], $this->data[ "mes" ] ),
            "socios"  => historico_socios( $this->data[ "modelo" ], $this->data[ "mes" ] )
        ];

         $this->data[ "historico" ][ "ticket" ] = $this->data[ "historico" ][ "venta" ];

        foreach( $this->data[ "historico" ][ "ticket" ][ "total" ] as $k => $v ){
            $this->data[ "historico" ][ "ticket" ][ "total" ][ $k ] /= $this->data[ "historico" ][ "pedidos" ][ "total" ][ $k ];
            $this->data[ "historico" ][ "ticket" ][ "nuevos" ][ $k ] /= $this->data[ "historico" ][ "pedidos" ][ "nuevos" ][ $k ];
            $this->data[ "historico" ][ "ticket" ][ "recompra" ][ $k ] /= $this->data[ "historico" ][ "pedidos" ][ "recompra" ][ $k ];
        } 

        $this->data[ "historico" ][ "productos" ] = historico_productos( $this->data[ "modelo" ], $this->data[ "mes" ] );

        echo template( "indicadores/inicio", $this->data );
    }
}



// ts brenda
// ing malaga 


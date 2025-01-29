<?php
namespace App\Controllers;
require '../vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class Eventos extends BaseController
{
    public function listado()
    {
        if( !(
            $this->data[ "usuario" ]->permiso( "30-SOPORTE" ) ||
            $this->data[ "usuario" ]->permiso( "40-ADMIN" )
        ) ){
            return redirect()->to( "inicio" ); 
        }

        $db = db_connect();

        $this->data[ "navbar" ]  = true;
        $this->data[ "titulo" ]  = "Eventos";
        $this->data[ "eventos" ] = $db->query( "select * from t_promociones where settings->>'$.evento' = 'true' order by termina asc" );

        echo template( "eventos/listado", $this->data );
    }


    public function detalle( $codigo )
    {
        if( !(
            $this->data[ "usuario" ]->permiso( "30-SOPORTE" ) ||
            $this->data[ "usuario" ]->permiso( "40-ADMIN" )
        ) ){
            return redirect()->to( "inicio" ); 
        }

        $db = db_connect();
        $this->data[ "evento" ] = model( "PromocionModel" )->find( $codigo );
        
        $this->data[ "navbar" ]  = true;
        $this->data[ "titulo" ]  = $this->data[ "evento" ][ "settings" ][ "nombre" ];

        echo template( "eventos/detalle", $this->data );
    }
      
}

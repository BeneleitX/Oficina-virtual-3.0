<?php
namespace App\Controllers;
require '../vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class Facturacion extends BaseController
{
    public function listado()
    {
        if( !(
            $this->data[ "usuario" ]->permiso( "38-CONTABILIDAD" ) ||
            $this->data[ "usuario" ]->permiso( "40-ADMIN" )
        ) ){
            return redirect()->to( "inicio" ); 
        }

        $this->data[ "navbar" ] = true;
        $this->data[ "titulo" ] = "Facturación y poagos especiales";

        $this->data[ "no_retencion" ] = model( "UsuarioModel" )->where( "2 = json_unquote( json_extract( data, '$.sat.estatus' ) )" )->findAll();

        $this->data[ "ventas" ] = model( "UsuarioModel" )->where( "100 = json_unquote( json_extract( data, '$.sat.estatus' ) )" )->findAll();

        echo template( "facturacion/listado", $this->data );
    }

    public function poner_ventas()
    {
        if( !(
            $this->data[ "usuario" ]->permiso( "38-CONTABILIDAD" ) ||
            $this->data[ "usuario" ]->permiso( "40-ADMIN" )
        ) ){
            return redirect()->to( "inicio" ); 
        }

        $socio = model( "UsuarioModel" )->find( $this->request->getPost( "socio" ) );
        $data = $socio->data;
        $data->sat->estatus = 100;
        $socio->data = $data;
        model( "UsuarioModel" )->save( $socio );

        
        // BITACORA poner en ventas
        bitacora( 61, $this->data[ "usuario" ]->id, [ 
            "socio"  => $socio->id
        ] );

        return redirect()->to( "facturacion" ); 
    }


    public function quitar_ventas()
    {
        if( !(
            $this->data[ "usuario" ]->permiso( "38-CONTABILIDAD" ) ||
            $this->data[ "usuario" ]->permiso( "40-ADMIN" )
        ) ){
            return redirect()->to( "inicio" ); 
        }

        $socio = model( "UsuarioModel" )->find( $this->request->getPost( "socio" ) );
        $data = $socio->data;
        $data->sat->estatus = 2;
        $socio->data = $data;
        model( "UsuarioModel" )->save( $socio );

        
        // BITACORA quitar en ventas
        bitacora( 60, $this->data[ "usuario" ]->id, [ 
            "socio"  => $socio->id
        ] );

        return redirect()->to( "facturacion" ); 
    }

}


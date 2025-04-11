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
            return redirect()->to( "no_permiso" ); 
        }

        $this->data[ "navbar" ] = true;
        $this->data[ "titulo" ] = "Facturación y pagos especiales";
        
        $db  = db_connect();
        $sql = "SELECT count(*) as total from t_pedidos
                where data->>'$.sat.factura' = '144-FACTURA-PENDIENTE'
                and substring( estatus_codigo,1,3 ) > 400";
        $this->data[ "facturas" ] = $db->query( $sql )->getRow()->total;
        
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
            return redirect()->to( "no_permiso" ); 
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


    public function facturas(){
        if( !(
            $this->data[ "usuario" ]->permiso( "38-CONTABILIDAD" ) ||
            $this->data[ "usuario" ]->permiso( "40-ADMIN" )
        ) ){
            return redirect()->to( "no_permiso" ); 
        }

        $this->data[ "navbar" ] = true;
        $this->data[ "titulo" ] = "Pedidos que han solicitado factura";
        
        $sql = "substring( estatus_codigo, 1, 3 ) > 400
                AND json_unquote( json_extract( data, '$.sat.factura' ) ) = '144-FACTURA-PENDIENTE'";

        $this->data[ "pedidos" ] = model( "PedidoModel" )->where( $sql, null, false )->findAll();

        echo template( "facturacion/facturas", $this->data );
    }

    public function facturas_historial(){
        if( !(
            $this->data[ "usuario" ]->permiso( "38-CONTABILIDAD" ) ||
            $this->data[ "usuario" ]->permiso( "40-ADMIN" )
        ) ){
            return redirect()->to( "no_permiso" ); 
        }

        $this->data[ "navbar" ] = true;
        $this->data[ "titulo" ] = "Pedidos facturados";
        
        $sql = "substring( estatus_codigo, 1, 3 ) > 400
                AND json_unquote( json_extract( data, '$.sat.factura' ) ) = '146-FACTURA-OK'";

        $this->data[ "pedidos" ] = model( "PedidoModel" )->where( $sql, null, false )->findAll();

        echo template( "facturacion/facturas_historial", $this->data );
    }    


    public function quitar_ventas()
    {
        if( !(
            $this->data[ "usuario" ]->permiso( "38-CONTABILIDAD" ) ||
            $this->data[ "usuario" ]->permiso( "40-ADMIN" )
        ) ){
            return redirect()->to( "no_permiso" ); 
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


    public function do_factura(){

        if( !(
            $this->data[ "usuario" ]->permiso( "38-CONTABILIDAD" ) ||
            $this->data[ "usuario" ]->permiso( "40-ADMIN" )
        ) ){
            return redirect()->to( "no_permiso" ); 
        }

        $folio    = $this->request->getPost( "r_folio" );
        $pedido   = model( "PedidoModel" )->find( $this->request->getPost( "r_pedido" ) );

        $pedido[ "data" ][ "sat" ][ "cfd" ]     = $this->request->getPost( "r_folio" );
        $pedido[ "data" ][ "sat" ][ "fecha" ]   = $this->request->getPost( "r_fecha" );
        $pedido[ "data" ][ "sat" ][ "factura" ] = "146-FACTURA-OK";
        model( "PedidoModel" )->save( $pedido );

        // BITACORA registro de folio factura
        bitacora( 83, $this->data[ "usuario" ]->id, [ 
            "pedido" => $pedido[ "id" ],
            "folio"  => $this->request->getPost( "r_folio" ),
            "fecha"  => $this->request->getPost( "r_fecha" )
        ] );
        
        return redirect()->to( "facturas" );
    }
}


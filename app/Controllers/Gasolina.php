<?php namespace App\Controllers;

// instalar aplicaciones necesarias desde composer
require '../vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use CodeIgniter\Database\Exceptions\DatabaseException;

class Gasolina extends BaseController
{
    function __construct() {
        $this->data[ "menu" ] = "admin";
    }

    public function admin( $mes = null ){
        if( !(
            $this->data[ "usuario" ]->permiso( "31-GASOLINA" ) ||
            $this->data[ "usuario" ]->permiso( "40-ADMIN")
        ) ){
            return redirect()->to( "inicio" ); 
        }
        
        if( !$mes ){
            $mes = date( "Ym" );
        }

        /**********************************/
        $db = db_connect();

        $this->data[ "navbar" ] = true;
        $this->data[ "mes" ]    = $mes;
        $this->data[ "titulo" ] = "Administración Gasolina";
        $this->data[ "promociones" ] = model( "PromocionModel" )->where( "codigo in ('414-GASOLINA', '415-COMODIN')" )->findAll();

        $sql = "SELECT 
                u.id, (
                    SELECT COUNT(*) FROM t_gasolina g WHERE g.usuario_id = u.id AND date_format( g.fecha, '%Y%m') = '{$mes}' AND g.estatus_codigo = '623-ENTREGA'
                ) AS recargas
                from t_usuarios u 
                where historial->>'$.modelos.\"40-GASOLINAS\".primercompra.\"412-TARJETA\"' IS NOT NULL
                GROUP BY u.id";

        // "select id from t_usuarios where historial->>'$.modelos.\"40-GASOLINAS\".primercompra.\"412-TARJETA\"' IS NOT null"

        $this->data[ "socios" ] = $db->query( $sql )->getResult();
        
        $sql = "SELECT COUNT(*) as total FROM t_gasolina g WHERE date_format( g.fecha, '%Y%m') = '{$mes}' AND g.estatus_codigo = '623-ENTREGA'";
        $this->data[ "total" ] = $db->query( $sql )->getRow()->total;

        echo template( "gasolina/admin", $this->data );
    }


    public function vincula_tarjeta(){
        if( !(
            $this->data[ "usuario" ]->permiso( "31-GASOLINA") ||
            $this->data[ "usuario" ]->permiso( "40-ADMIN")
        ) ){
            return redirect()->to( "inicio" ); 
        }

        $db = db_connect();
        extract( $this->request->getPost() );

        $sql = "UPDATE t_usuarios set data = json_set( data, '$.tarjeta', json_object(
            'numero',  '{$v_tarjeta2}', 
            'estatus', '623-ENTREGA',
            'folio',   '{$v_folio}'
        ) ) where id = ".$v_socio;
        
        $db->query( $sql );

        // BITACORA Marca recompensa entregada
        bitacora( 77, $this->data[ "usuario" ]->id, [ 
            "socio"   => $v_socio,
            "folio"   => $v_folio,
            "tarjeta" => $v_tarjeta2
        ] );

        return redirect()->to( "admin_gasolina" )->with( "msg", [ 
            "clase" => "success", 
            "icono" => "check", 
            "texto" => "Se ha viculado correctamente la tarjeta al socio" ] );   
    }


    public function activa_tarjeta(){

        $db = db_connect();
        extract( $this->request->getPost() );

        if( $v_tarjeta2 == $this->data[ "usuario" ]->data->tarjeta->numero ){
            echo "true";

            $data = $this->data[ "usuario" ]->data;
            $data->tarjeta->estatus = "625-ACTIVA";
            $this->data[ "usuario" ]->data = $data;

            model( "UsuarioModel" )->save( $this->data[ "usuario" ] );

            // BITACORA Marca recompensa entregada
            bitacora( 79, $this->data[ "usuario" ]->id, [ 
                "tarjeta" => $v_tarjeta2
            ] );        
        }
        else{
            echo "false";

            // BITACORA Marca recompensa entregada
            bitacora( 80, $this->data[ "usuario" ]->id, [ 
                "tarjeta" => $v_tarjeta2
            ] );
        }
    }


    public function get_recargas(){
        if( !(
            $this->data[ "usuario" ]->permiso( "31-GASOLINA") ||
            $this->data[ "usuario" ]->permiso( "40-ADMIN")
        ) ){
            return redirect()->to( "inicio" ); 
        }
         
        extract( $this->request->getPost() );

        $html = "";
        $socio = model( "UsuarioModel" )->find( $socio );

        $db = db_connect();

        $sql = "SELECT 
                    p.id AS pedido, p.referencia, p.PTS->>'$.\"414-GASOLINA\"' AS paquetes,
                    g.id AS recarga, CONCAT( g.usuario_id, '-', g.id ) AS folio, g.estatus_codigo AS estatus, g.tarjeta, g.fecha
                FROM t_pedidos p
                LEFT JOIN t_gasolina g ON g.pedido_id = p.id AND g.estatus_codigo = '623-ENTREGA'
                WHERE p.usuario_id = {$socio->id}
                    AND p.modelo_codigo = '40-GASOLINAS'
                    AND SUBSTRING( p.estatus_codigo, 1, 3) > 400
                    AND p.PTS->>'$.\"414-GASOLINA\"' > 0";

        $recargas = $db->query( $sql )->getResult();
        $result = [];

        foreach( $recargas as $r ){
            $result[ $r->pedido ][] = $r;
        }

        $html .= "<table class=\"w-100 m-0 table table-striped\" id=\"tabla_recargas\">
            <thead><tr>
                <th>Folio</th>
                <th class=\"text-start\">Pedido</th>
                <th>Tarjeta</th>
                <th>Fecha</th>
                <th>Estatus</th>
                <th>&nbsp;</th>
            </tr></thead><tbody>";

        foreach( $result as $k => $l ){
            $suma = 0;

            foreach( $l as $r ){
                if( $r->recarga ){
                    $suma++;
                    $html .= "\n<tr>
                                <td><span class=\"badge bg-gray-600\">{$r->folio}</span></td>
                                <td class=\"text-start\"><span class=\"badge bg-marine\">{$r->referencia}</span></td>
                                <td><strong><i class=\"fa fa-credit-card text-gray-500\"></i> {$r->tarjeta}</strong></td>
                                <td><span class=\"d-none\">{$r->fecha}</span>".date( "d-m-Y", strtotime( $r->fecha ) )."</td>
                                <td>".estatus( $r->estatus )."</td>
                                <td></td>
                            </tr>";
                }
            }

            for( $a = 0; $a < $r->paquetes - $suma; $a++){
                $url = urlencode( base64_encode( $r->pedido ) );
                $html .= "\n<tr>
                    <td></td>
                    <td class=\"text-start\"><span class=\"badge bg-marine\">{$r->referencia}</span></td>
                    <td><strong><i class=\"fa fa-credit-card text-gray-500\"></i> {$socio->data->tarjeta->numero}</strong></td>
                    <td></td>
                    <td>".estatus( "330-EN-ESPERA" )."</td>
                    <td class=\"text-end\"><a href=\"".base_url()."entrega_recarga/{$url}\" class=\"btn btn-sm btn-success\"><i class=\"fa fa-check\"></i> Marcar entregado</a></td>
                </tr>";
            }
        }


        $html .= "</tbody></table></form></div>"; 


        echo $html;        
    }


    public function entrega_recarga( $pedido ){
        if( !(
            $this->data[ "usuario" ]->permiso( "31-GASOLINA") ||
            $this->data[ "usuario" ]->permiso( "40-ADMIN")
        ) ){
            return redirect()->to( "inicio" ); 
        }

        $db = db_connect();
        $p = base64_decode( urldecode( $pedido ) );
        
        $pedido = model( "PedidoModel"  )->find( $p );
        $socio  = model( "UsuarioModel" )->find( $pedido[ "usuario_id" ] );

        $sql = "insert into t_gasolina values ( NULL, '623-ENTREGA', {$pedido[ "id" ]}, {$socio->id}, '{$socio->data->tarjeta->numero}', NOW() )";
        $db->query( $sql );

        // BITACORA Marca recompensa entregada
        bitacora( 78, $this->data[ "usuario" ]->id, [ 
            "socio"   => $socio->id,
            "pedido"  => $pedido[ "id" ],
            "tarjeta" => $socio->data->tarjeta
        ] );

        return redirect()->to( "admin_gasolina" )->with( "msg", [ 
            "clase" => "success", 
            "icono" => "check", 
            "texto" => "Se ha aplicado la recarga de gasolina a la tarjeta del socio" ] );   
    }
}

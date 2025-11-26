<?php
namespace App\Controllers;
require '../vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class Eventos extends BaseController
{
    /**
     * Displays a list of events and their participant counts.
     *
     * This method checks if the user has the necessary permissions
     * to access the event list. If not, it redirects to a "no permission" page.
     * It retrieves event data from the database, including the event code
     * and the number of participants for each event, and orders them by
     * status code. The data is then passed to a template for rendering.
     */

    public function listado()
    {
        if( !(
            $this->data[ "usuario" ]->permiso( "35-SEMILLERO" ) ||
            $this->data[ "usuario" ]->permiso( "40-ADMIN" )
        ) ){
            return redirect()->to( "no_permiso" ); 
        }

        $db = db_connect();

        $sql = "SELECT p.codigo, count(*) as participantes, sum(e.id) as x,
                json_unquote( json_extract( json_keys( e.promociones->>'$.\"910-EVENTOS\".productos' ), '$[0]' ) ) as evento
                from t_promociones p
                left join t_pedidos e on jSON_EXTRACT( e.PTS, concat( '$.\"', p.codigo, '\"' ) ) > 0 AND SUBSTRING( e.estatus_codigo,1,3) > 400
                where p.settings->>'$.evento' = 'true' or p.codigo = '910-EVENTOS'
                group by p.codigo, evento
                order by p.estatus_codigo desc";

        $this->data[ "navbar" ]  = true;
        $this->data[ "titulo" ]  = "Eventos";
        $this->data[ "eventos" ] = $db->query( $sql );

        echo template( "eventos/listado", $this->data );
    }


    /**
     * Detalle de un evento
     * Muestra la lista de socios que han participado en el evento
     * @param string $codigo Codigo del evento
     * @return void
     */
    public function detalle( $codigo, $producto = null )
    {
        if( !(
            $this->data[ "usuario" ]->permiso( "35-SEMILLERO" ) ||
            $this->data[ "usuario" ]->permiso( "40-ADMIN" )
        ) ){
            return redirect()->to( "no_permiso" ); 
        }

        $db = db_connect();

        if( $producto == null ){           
            $sql = "SELECT 
                        p.usuario_id as usuario, 
                        p.referencia,
                        any_value( p.fechas->>'$.pagado' ) AS fecha, 
                        any_value( p.promociones->>'$.\"{$codigo}\".precio' ) as pago,
                        SUM(t.qt)-1 as productos 
                    FROM t_pedidos p,
                        JSON_TABLE(p.promociones, '$.\"{$codigo}\".productos.*.cantidad' COLUMNS (qt INTEGER PATH '$')) t
                    WHERE
                        -- p.usuario_id > 100 AND 
                        JSON_EXTRACT( p.PTS, '$.\"{$codigo}\"' ) > 0 AND SUBSTRING( p.estatus_codigo,1,3) > 400
                    group by p.usuario_id
                    ORDER BY fecha, p.usuario_id";

        }
        else {
            $sql = "SELECT
                        e.id,
                        e.modelo_codigo,
                        e.usuario_id as usuario,
                        e.referencia,
                        e.fechas->>'$.pagado' as fecha,
                        e.data->>'$.total' as pago,
                        SUM(prod.cantidad)-1 AS productos

                    FROM
                        t_pedidos e,
                        JSON_TABLE(
                            e.promociones,
                            '$.*'
                            COLUMNS (
                                clave FOR ORDINALITY,  
                                productos JSON PATH '$.productos'
                            )
                        ) AS promo,
                        JSON_TABLE(
                            promo.productos,
                            '$.*'
                            COLUMNS (
                                nombre VARCHAR(255) PATH '$.nombre',
                                precio DECIMAL(10,2) PATH '$.precio',
                                comisionable DECIMAL(10,2) PATH '$.comisionable',
                                cantidad INT PATH '$.cantidad'
                            )
                        ) AS prod
                        join t_productos r on r.data->>'$.nombre' = prod.nombre

                    where 
                    e.promociones->>'$.\"910-EVENTOS\".productos.\"{$producto}\".cantidad' = 1
                    AND SUBSTRING( e.estatus_codigo,1,3) > 400 

                    group by e.id
                    order by fecha, e.usuario_id";
        }

        $this->data[ "evento" ] = model( "PromocionModel" )->find( $codigo );
        $this->data[ "socios" ] = $db->query( $sql )->getResultArray();
        $this->data[ "navbar" ]  = true;
        $this->data[ "titulo" ]  = $this->data[ "evento" ][ "settings" ][ "nombre" ];

        echo template( "eventos/detalle", $this->data );
    }
      

    public function excel_semillero()
    {
        if( !(
            $this->data[ "usuario" ]->permiso( "35-SEMILLERO" ) ||
            $this->data[ "usuario" ]->permiso( "40-ADMIN" )
        ) ){
            return redirect()->to( "no_permiso" ); 
        }
                
        $db       = db_connect();
        $data     = [];

        $mySpreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
        $mySpreadsheet->removeSheetByIndex(0);
        $worksheet = new \PhpOffice\PhpSpreadsheet\Worksheet\Worksheet($mySpreadsheet, "PARTICIPANTES" );
        $mySpreadsheet->addSheet( $worksheet, 0 );

        $row = 1;
        $d = [];
        $e = [];

        $codigo = $this->request->getPost( "evento" );

        $worksheet->setCellValue( "A1", "SOCIO" );
        $worksheet->setCellValue( "B1", "NOMBRE" );
        $worksheet->setCellValue( "C1", "TELEFONO" );
        $worksheet->setCellValue( "D1", "CORREO" );
        $worksheet->setCellValue( "E1", "REFERENCIA" );
        $worksheet->setCellValue( "F1", "FECHA" );
        $worksheet->setCellValue( "G1", "PAGO" );
        $worksheet->setCellValue( "H1", "REGALOS" );

        $worksheet->setCellValue( "I1", "N" );
        $worksheet->setCellValue( "J1", "T" );
        $worksheet->setCellValue( "K1", "A" );
        $worksheet->setCellValue( "L1", "G" );
        $worksheet->setCellValue( "M1", "I" );

        $sql = "SELECT 
                    p.usuario_id as usuario, 
                    p.referencia as referencia,
                    any_value( p.fechas->>'$.pagado' ) AS fecha, 
                    any_value( p.promociones->>'$.\"{$codigo}\".precio' ) as pago,
                    SUM(t.qt)-1 as productos 
                FROM t_pedidos p,
                    JSON_TABLE(p.promociones, '$.\"{$codigo}\".productos.*.cantidad' COLUMNS (qt INTEGER PATH '$')) t
                WHERE
                    p.usuario_id > 100 AND 
                    JSON_EXTRACT( p.PTS, '$.\"{$codigo}\"' ) > 0 AND SUBSTRING( p.estatus_codigo,1,3) > 400
                group by p.usuario_id
                ORDER BY fecha, p.usuario_id";

        $result = $db->query( $sql );

        foreach( $result->getResultArray() as $s ){
            $row++;

            $u = model( "UsuarioModel" )->find( $s[ "usuario" ] );

            $worksheet->setCellValue( "A".( $row ),  $u->id );
            $worksheet->setCellValue( "B".( $row ),  $u->nombre( 2, false, true ) );
            $worksheet->setCellValue( "C".( $row ),  $u->telefono );
            $worksheet->setCellValue( "D".( $row ),  $u->correo );
            $worksheet->setCellValue( "E".( $row ),  $s[ "referencia" ] );
            $worksheet->setCellValue( "F".( $row ),  $s[ "fecha" ] );
            $worksheet->setCellValue( "G".( $row ),  $s[ "pago" ] );
            $worksheet->setCellValue( "H".( $row ),  $s[ "productos" ] );

            $worksheet->setCellValue( "I".( $row ),  substr( $u->data->estatus->modelos->{"10-NUTRICION"}, 0, 3 ) > 300 ? "A" : "" );
            $worksheet->setCellValue( "J".( $row ),  substr( $u->data->estatus->modelos->{"20-TELEFONIA"}, 0, 3 ) > 300 ? "A" : "" );
            $worksheet->setCellValue( "K".( $row ),  substr( $u->data->estatus->modelos->{"30-ALIMENTOS"}, 0, 3 ) > 300 ? "A" : "" );
            $worksheet->setCellValue( "L".( $row ),  substr( $u->data->estatus->modelos->{"40-GASOLINAS"}, 0, 3 ) > 300 ? "A" : "" );
            $worksheet->setCellValue( "M".( $row ),  substr( $u->data->estatus->modelos->{"50-INVERSION"}, 0, 3 ) > 300 ? "A" : "" );

        }

        $worksheet->getStyle( "A1:L1" )->getFont()->getColor()->setARGB('ffffff');
        $worksheet->getStyle( "A1:H1" )->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setARGB('192b5a');
        $worksheet->getStyle( "I1:M1" )->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setARGB('47c24c');

        foreach( $worksheet->getColumnIterator() as $column ){
            $worksheet->getColumnDimension( $column->getColumnIndex() )->setAutoSize( true );
        }

        $path = "data/excel/semillero";

        if( !is_dir( $path ) ) mkdir( $path, 0755, true );

        echo $file = $path."/".substr( $codigo, 4 )."_participalntes_".date( "d" )."-".mes( date( "d" ), 3 ).".xlsx";

        $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($mySpreadsheet);
        $writer->save( $file );
    }    
}

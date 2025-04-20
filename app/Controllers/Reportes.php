<?php
namespace App\Controllers;
require '../vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class Reportes extends BaseController
{
    public function menu()
    {
        if( !(
            $this->data[ "usuario" ]->permiso( "36-REPORTES" ) ||
            $this->data[ "usuario" ]->permiso( "40-ADMIN" )
        ) ){
            return redirect()->to( "no_permiso" ); 
        }

        $this->data[ "navbar" ] = true;
        $this->data[ "titulo" ] = "Reportes";

        echo template( "reportes/menu", $this->data );
    }


    public function socios_por_estatus(){
        if( !(
            $this->data[ "usuario" ]->permiso( "36-REPORTES" ) ||
            $this->data[ "usuario" ]->permiso( "40-ADMIN" )
        ) ){
            return redirect()->to( "no_permiso" ); 
        }

        $temp = [
            "10-NUTRICION" => [
                // "130-NUEVO-SUSPENDIDO",
                // "140-SUSPENDIDO",
                "210-NUEVO",
                "310-NO-CALIFICADO",
                "320-NO-CALIFICADO-COMPRA",
                "410-CALIFICADO",
                "510-NUEVO-CALIFICADO",
                "520-CALIFICADO-ACTUAL"
            ],
            "20-TELEFONIA" => [
                // "130-NUEVO-SUSPENDIDO",
            	// "140-SUSPENDIDO",
            	"210-NUEVO",
            	"310-NO-CALIFICADO",
            	// "410-CALIFICADO"	"3"
            	"510-NUEVO-CALIFICADO",
            	"520-CALIFICADO-ACTUAL"
            ],
            "30-ALIMENTOS" => [
                // "130-NUEVO-SUSPENDIDO",
            	"210-NUEVO",
            	"310-NO-CALIFICADO",
            	"320-NO-CALIFICADO-COMPRA",
            	"410-CALIFICADO",
            	"510-NUEVO-CALIFICADO",
            	"520-CALIFICADO-ACTUAL"
            ],
            "40-GASOLINAS" => [
                // "130-NUEVO-SUSPENDIDO",
            	// "140-SUSPENDIDO",
            	"210-NUEVO",
            	"310-NO-CALIFICADO",
            	// "410-CALIFICADO"	"3"
            	"510-NUEVO-CALIFICADO",
            	"520-CALIFICADO-ACTUAL"
            ],
            "50-INVERSION" => [
                // "130-NUEVO-SUSPENDIDO",
            	"210-NUEVO",
            	// "310-NO-CALIFICADO",
            	// "320-NO-CALIFICADO-COMPRA",
            	//"410-CALIFICADO",
            	//"510-NUEVO-CALIFICADO",
            	"520-CALIFICADO-ACTUAL"
            ]
        ];

        $estatuses = [];

        foreach( $temp as $modelo => $es ){
            foreach( $es as $ee ){
                $estatuses[ $ee ][] = $modelo;
            }
        }

        $this->data[ "estatuses" ] = $estatuses;


        $this->data[ "navbar" ] = true;
        $this->data[ "titulo" ] = "Reportes: Listado de socios por estatus";

        echo template( "reportes/socios_por_estatus", $this->data );
    }

    public function excel_socios_por_estatus()
    {
        if( !(
            $this->data[ "usuario" ]->permiso( "36-REPORTES" ) ||
            $this->data[ "usuario" ]->permiso( "40-ADMIN" )
        ) ){
            return redirect()->to( "no_permiso" ); 
        }
                
        $db       = db_connect();
        $modelo   = $this->request->getPost( "modelo" );
        $estatus  = $this->request->getPost( "estatus");
        $data     = [];

        $mySpreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
        $mySpreadsheet->removeSheetByIndex(0);
        $worksheet = new \PhpOffice\PhpSpreadsheet\Worksheet\Worksheet($mySpreadsheet, $estatus );
        $mySpreadsheet->addSheet( $worksheet, 0 );

        $row = 1;
        $d = [];
        $e = [];
        $worksheet->setCellValue( "A1", "SOCIO" );
        $worksheet->setCellValue( "B1", "NOMBRE" );
        $worksheet->setCellValue( "C1", "CELULAR" );
        $worksheet->setCellValue( "D1", "CORREO" );
        $worksheet->setCellValue( "E1", "ULTIMA COMPRA" );
        $worksheet->setCellValue( "F1", "ESTATUS" );

        $sql  = "SELECT
                    u.id AS 'SOCIO', 
                    CONCAT(u.data->>'$.nombre', ' ', u.data->>'$.apellidos[0]', ' ', u.data->>'$.apellidos[1]') AS 'NOMBRE', 
                    u.telefono AS 'CELULAR', 
                    u.correo AS 'CORREO', 
                    CAST( JSON_EXTRACT( historial, CONCAT( '$.modelos.\"', '{$modelo}', '\".ultimacompra') ) AS DATE) AS 'ULTIMA'
                FROM t_usuarios u 
                WHERE estatus_codigo = '201-ACTIVO'
                AND json_Extract( u.redes, '$.verificado' ) is null
                AND JSON_UNQUOTE( JSON_EXTRACT( DATA, CONCAT( '$.estatus.modelos.\"', '{$modelo}', '\"') ) ) = '{$estatus}';";

        $result = $db->query( $sql );

        foreach( $result->getResult() as $s ){
            $row++;
            $worksheet->setCellValue( "A".( $row ),  $s->SOCIO);
            $worksheet->setCellValue( "B".( $row ),  $s->NOMBRE);
            $worksheet->setCellValue( "C".( $row ),  $s->CELULAR);
            $worksheet->setCellValue( "D".( $row ),  $s->CORREO);
            $worksheet->setCellValue( "E".( $row ),  $s->ULTIMA);
            $worksheet->setCellValue( "F".( $row ),  $estatus." - ".ESTATUS[ $estatus ][ "descripcion" ]);
        }

        $worksheet->getStyle( "A1:F1" )->getFont()->getColor()->setARGB('ffffff');
        $worksheet->getStyle( "A1:F1" )->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setARGB('192b5a');

        foreach( $worksheet->getColumnIterator() as $column ){
            $worksheet->getColumnDimension( $column->getColumnIndex() )->setAutoSize( true );
        }

        $path = "data/excel/socios_por_estatus";
        if( !is_dir( $path ) ) mkdir( $path, 0755, true );

        echo $file = $path."/SociosPorEstatus_".substr( $modelo, 3 )."_".date( "Y-m-d" )."_".time().".xlsx";

        $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($mySpreadsheet);
        $writer->save( $file );
    }       
}

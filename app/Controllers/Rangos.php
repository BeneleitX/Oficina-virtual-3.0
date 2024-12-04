<?php

namespace App\Controllers;

class Rangos extends BaseController
{
    function __construct() {
        $this->data[ "menu" ] = "admin";
    }
    

    public function catalogo( $modelo ){
        if( !(
            $this->data[ "usuario" ]->permiso( "26-RANGOS") ||
            $this->data[ "usuario" ]->permiso( "40-ADMIN")
        ) ){
            return redirect()->to( "inicio" ); 
        }
        
        /**********************************/
                
        $this->data[ "navbar" ] = true;
        $this->data[ "titulo" ] = "Entrega de pines de rango";
        $this->data[ "modelo" ] = $modelo;

        $sql = "modelo_codigo = '{$modelo}' and SUBSTRING( codigo, 2,3 ) > 0";
        $this->data[ "rangos" ] = model( "RangoModel" )->where( $sql , null, false )->findAll();

        $db = db_connect();
        $socios = $db->query( "
            SELECT u.data->>'$.rango' AS rango_codigo, COUNT(*) AS cantidad
            FROM t_usuarios u
            WHERE SUBSTRING( u.estatus_codigo, 1,3 ) > 200 
            AND u.rol_codigos not like '%42-PERMANENTE%'
            GROUP BY u.data->>'$.rango'
        " );

        $pendientes = $db->query( "SELECT rango_codigo, COUNT(*) AS pendientes FROM t_pines
        WHERE estatus_codigo = '225-ALCANZADO' AND u.rol_codigos not like '%42-PERMANENTE%'
        GROUP BY rango_codigo" );

        foreach( $pendientes->getResult() as $s ){
            $this->data[ "pendientes" ][ $s->rango_codigo ] = $s->pendientes;
        }

        foreach( $socios->getResult() as $s ){
            $this->data[ "socios" ][ $s->rango_codigo ][ "activos" ] = $s->cantidad;
        }

        echo template( "rangos/pines", $this->data );
    } 


    public function excel_pines_pendientes(){
        if( !(
            $this->data[ "usuario" ]->permiso( "26-RANGOS") ||
            $this->data[ "usuario" ]->permiso( "40-ADMIN")
        ) ){
            return redirect()->to( "inicio" ); 
        }
        
        /**********************************/

        $db = db_connect();

        $pendientes = $db->query( "
            SELECT p.rango_codigo AS codigo, r.nombre AS rango, r.hex, p.usuario_id AS socio, CONCAT( u.data->>'$.nombre', ' ', u.data->>'$.apellidos[ 0 ]', ' ', u.data->>'$.apellidos[1]') AS nombre, p.fecha, json_unquote( json_extract( u.data, concat( '$.avatar.imagenes[ ', IFNULL( CAST( u.data->>'$.avatar.activo' AS unsigned), 0 ) , ' ]' ) ) ) as foto
            FROM t_pines p
            JOIN t_rangos r ON r.codigo = p.rango_codigo
            JOIN t_usuarios u ON u.id = p.usuario_id 
            WHERE p.estatus_codigo = '225-ALCANZADO'
            AND u.rol_codigos not like '%42-PERMANENTE%'
            ORDER BY p.rango_codigo, p.usuario_id" )->getResultArray();


        $sheetData = [];
        $corte     = "";
        $cortes    = [];
        $conteo    = 0;

        foreach( $pendientes as $k => $p ){
            if( $corte != $p[ "rango" ] ){
                $corte  = $p[ "rango" ];
                if( $conteo++ ){
                    $conteo++; 
                    $sheetData[] = [];
                }
                $sheetData[] = [ "RANGO", "SOCIO", "NOMBRE", "FECHA" ];
                $cortes[ $p[ "rango" ] ] = [ $conteo, $p[ "hex" ] ];
            }
            $conteo++;

            $sheetData[] = [  strtoupper( $p[ "rango" ] ),  $p[ "socio" ],  strtoupper( $p[ "nombre" ] ), $p[ "fecha" ], $p[ "foto" ] ? base_url()."data/{$p[ "socio" ]}/avatar/".$p[ "foto" ] : "" ];          
        }

        $mySpreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
        $mySpreadsheet->removeSheetByIndex( 0 );

        $worksheet = new \PhpOffice\PhpSpreadsheet\Worksheet\Worksheet( $mySpreadsheet, "VENTA" );

        $mySpreadsheet->addSheet( $worksheet, 0 );

        $row = 0;
        foreach( $sheetData as $bloque ){
            $col = 0;
            $row++;
            foreach( $bloque as $dato ){
                if( strlen( $dato ) == 18 ){
                    $worksheet->setCellValueExplicit( chr( 65 + $col++ ).$row, $dato, \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING );
                }
                elseif( str_contains( $dato, "https://") ){
                    $worksheet->setCellValue( chr( 65 + $col ).$row, $dato );
                    $worksheet->getCell( chr( 65 + $col++ ).$row )->getHyperlink()->setUrl( $dato );
                }
                else{
                    $worksheet->setCellValue( chr( 65 + $col++ ).$row, $dato );
                }
            }
        }

        foreach( $cortes as $c ){
            $worksheet->getStyle( "A{$c[0]}:D{$c[0]}" )->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setARGB( $c[1] );
            $worksheet->getStyle( "A{$c[0]}:D{$c[0]}" )->getFont()->getColor()->setARGB('ffffff');
        }

        foreach( $worksheet->getColumnIterator() as $column ){
            $worksheet->getColumnDimension( $column->getColumnIndex() )->setAutoSize( true );
        }

        // BITACORA descarga excel de corte
        bitacora( 69, $this->data[ "usuario" ]->id, [
            "time" => $time = time()
        ] );

        $path = "assets/archivo/rangos";
        if( !is_dir( $path ) ) mkdir( $path, 0755, true );

        echo $file = $path."/Rangos_{$time}.xlsx";

        $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($mySpreadsheet);
        $writer->save( $file );
    }
}

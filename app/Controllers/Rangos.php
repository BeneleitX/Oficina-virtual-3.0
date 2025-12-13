<?php

namespace App\Controllers;

class Rangos extends BaseController
{
    function __construct() {
        $this->data[ "menu" ] = "admin";
    }
    

    /**
     * Displays the catalog of range pins for a given model.
     * 
     * Checks user permissions for accessing range details.
     * Loads navbar and title data for the view.
     * Retrieves range data from the database based on the model code.
     * Queries the database for active and pending members for each range.
     * Prepares data for rendering the "rangos/pines" template.
     * 
     * @param string $modelo The model code to filter ranges.
     * @return void Redirects to "no_permiso" if the user lacks permissions.
     */

    public function catalogo( $modelo )
    {
        if( !(
            $this->data[ "usuario" ]->permiso( "26-RANGOS") ||
            $this->data[ "usuario" ]->permiso( "40-ADMIN")
        ) ){
            return redirect()->to( "no_permiso" ); 
        }
        
        /**********************************/
                
        $this->data[ "navbar" ] = true;
        $this->data[ "titulo" ] = "Catálogo de rangos";
        $this->data[ "modelo" ] = $modelo;

        $sql = "modelo_codigo = '{$modelo}' and SUBSTRING( codigo, 2, 3 ) > 0";
        $this->data[ "rangos" ] = model( "RangoModel" )->where( $sql , null, false )->findAll();

        $db = db_connect();
        $socios = $db->query( "
            SELECT u.data->>'$.rango' AS rango_codigo, COUNT(*) AS cantidad
            FROM t_usuarios u
            WHERE SUBSTRING( u.estatus_codigo, 1,3 ) > 200 
            AND u.rol_codigos not like '%42-PERMANENTE%'
            GROUP BY u.data->>'$.rango' 
        " );

        $pendientes = $db->query( "SELECT p.rango_codigo, COUNT(*) AS pendientes FROM t_pines p join t_usuarios u on u.id = p.usuario_id
        WHERE p.estatus_codigo = '225-ALCANZADO' AND u.rol_codigos not like '%42-PERMANENTE%'
        GROUP BY p.rango_codigo" );

        foreach( $pendientes->getResult() as $s ){
            $this->data[ "pendientes" ][ $s->rango_codigo ] = $s->pendientes;
        }

        foreach( $socios->getResult() as $s ){
            $this->data[ "socios" ][ $s->rango_codigo ][ "activos" ] = $s->cantidad;
        }

        echo template( "rangos/catalogo", $this->data );
    } 


    public function pines( $modelo )
    {
        if( !(
            $this->data[ "usuario" ]->permiso( "26-RANGOS") ||
            $this->data[ "usuario" ]->permiso( "40-ADMIN")
        ) ){
            return redirect()->to( "no_permiso" ); 
        }
        
        /**********************************/
                
        $this->data[ "navbar" ] = true;
        $this->data[ "titulo" ] = "Entrega de pines de rango";
        $this->data[ "modelo" ] = $modelo;

        $sql = "modelo_codigo = '{$modelo}' and SUBSTRING( codigo, 2, 3 ) > 0";
        $this->data[ "rangos" ] = model( "RangoModel" )->where( $sql , null, false )->findAll();

        $db = db_connect();
        $socios = $db->query( "
            SELECT u.data->>'$.rango' AS rango_codigo, COUNT(*) AS cantidad
            FROM t_usuarios u
            WHERE SUBSTRING( u.estatus_codigo, 1,3 ) > 200 
            AND u.rol_codigos not like '%42-PERMANENTE%'
            GROUP BY u.data->>'$.rango' 
        " );

        $pendientes = $db->query( "SELECT p.rango_codigo, COUNT(*) AS pendientes FROM t_pines p join t_usuarios u on u.id = p.usuario_id
        WHERE p.estatus_codigo = '225-ALCANZADO' AND u.rol_codigos not like '%42-PERMANENTE%'
        GROUP BY p.rango_codigo" );

        foreach( $pendientes->getResult() as $s ){
            $this->data[ "pendientes" ][ $s->rango_codigo ] = $s->pendientes;
        }

        foreach( $socios->getResult() as $s ){
            $this->data[ "socios" ][ $s->rango_codigo ][ "activos" ] = $s->cantidad;
        }

        echo template( "rangos/pines", $this->data );
    } 


    public function entrega_pines( $rango ){
        $this->data[ "rango" ]  = model( "RangoModel" )->find( $rango );
        $this->data[ "navbar" ] = true;
    
        $db = db_connect();
        $sql = "SELECT 
                    p.id,
                    p.usuario_id,
                    p.fecha,
                    p.entrega_fecha,
                    p.entrega_lugar,
                    p.estatus_codigo,
                    p.comentarios  
                FROM t_pines p 
                WHERE p.rango_codigo = '{$rango}'
                order by fecha asc";

        $this->data[ "pines" ] = $db->query( $sql );
        $this->data[ "titulo" ] = "Entrega de pines &nbsp; ".rango( $this->data[ "rango" ] );

        echo template( "rangos/entrega_pines", $this->data );   
    }


    public function update_pin()
    {
        if( !(
            $this->data[ "usuario" ]->permiso( "26-RANGOS") ||
            $this->data[ "usuario" ]->permiso( "40-ADMIN")
        ) ){
            return redirect()->to( "no_permiso" ); 
        }
        
        /**********************************/

        extract( $this->request->getPost() );
        $pin = model( "PinModel" )->find( $pin );

        // BITACORA update entrega rango
        bitacora( 114, $this->data[ "usuario" ]->id, [
            "pin" => $pin[ "id" ],
            "estatus_anterior" => $pin[ "estatus_codigo" ],
            "estatus_nuevo" => $estatus_codigo,
            "comentarios" => $comentarios ?? null,
            "entrega_fecha" => $entrega_fecha ?? null,
            "entrega_lugar" => $entrega_lugar ?? null
        ] );
        
        $pin[ "estatus_codigo" ] = $estatus_codigo;
        $pin[ "comentarios" ] = $comentarios ?? null;
        $pin[ "entrega_fecha" ] = $entrega_fecha ?? null;
        $pin[ "entrega_lugar" ] = $entrega_lugar ?? null;

        model( "PinModel" )->save( $pin );

        return redirect()->to( "entrega_pines/{$pin[ "rango_codigo" ]}" );
    }


    public function agrega_lugar()
    {
        if( !(
            $this->data[ "usuario" ]->permiso( "26-RANGOS") ||
            $this->data[ "usuario" ]->permiso( "40-ADMIN")
        ) ){
            return redirect()->to( "no_permiso" ); 
        }
        
        /**********************************/

        extract( $this->request->getPost() );

        // BITACORA update entrega rango
        bitacora( 115, $this->data[ "usuario" ]->id, [
            "nuevo_lugar" => $nuevo_lugar
        ] );
        
        $lugares = VARIABLES[ "entrega_pines" ][ "valor" ];
        $lugares[] = $nuevo_lugar;

        $db = db_connect();
        $sql = "update t_variables set valor = '".json_encode( $lugares )."' where codigo = 'entrega_pines'";

        $db->query( $sql );   

        return redirect()->to( "rangos/10-NUTRICION" );
    }


    public function borra_lugar( $lugar )
    {
        if( !(
            $this->data[ "usuario" ]->permiso( "26-RANGOS") ||
            $this->data[ "usuario" ]->permiso( "40-ADMIN")
        ) ){
            return redirect()->to( "no_permiso" ); 
        }
        
        /**********************************/

        $lugar = base64_decode( urldecode( $lugar ) );

        // BITACORA update entrega rango
        bitacora( 116, $this->data[ "usuario" ]->id, [
            "lugar" => $lugar
        ] );
        
        $lugares = VARIABLES[ "entrega_pines" ][ "valor" ];
        
        foreach( $lugares as $k => $v ){
            if( $v == $lugar ){
                unset( $lugares[ $k ] );
            }
        }

        $db = db_connect();
        $sql = "update t_variables set valor = '".json_encode( $lugares )."' where codigo = 'entrega_pines'";

        $db->query( $sql );   

        return redirect()->to( "rangos/10-NUTRICION" );
    }


    /**
     * Genera un archivo excel con los socios que 
     * tienen pendientes de entrega de pines de rango
     * 
     * @return void
     */
    public function excel_pines_pendientes()
    {
        if( !(
            $this->data[ "usuario" ]->permiso( "26-RANGOS") ||
            $this->data[ "usuario" ]->permiso( "40-ADMIN")
        ) ){
            return redirect()->to( "no_permiso" ); 
        }
        
        /**********************************/

        $db = db_connect();

        $pendientes = $db->query( "
            SELECT p.rango_codigo AS codigo, r.nombre AS rango, r.hex, p.usuario_id AS socio, CONCAT( u.data->>'$.nombre', ' ', u.data->>'$.apellidos[ 0 ]', ' ', u.data->>'$.apellidos[1]') AS nombre, p.fecha, p.comentarios
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
                $sheetData[] = [ "RANGO", "SOCIO", "NOMBRE", "FECHA", "COMENTARIOS" ];
                $cortes[ $p[ "rango" ] ] = [ $conteo, $p[ "hex" ] ];
            }
            $conteo++;

            $sheetData[] = [  strtoupper( $p[ "rango" ] ),  $p[ "socio" ],  strtoupper( $p[ "nombre" ] ), $p[ "fecha" ], $p[ "comentarios" ] ];          
        }

        $mySpreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
        $mySpreadsheet->removeSheetByIndex( 0 );

        $worksheet1 = new \PhpOffice\PhpSpreadsheet\Worksheet\Worksheet( $mySpreadsheet, "PENDIENTES" );

        $mySpreadsheet->addSheet( $worksheet1, 0 );

        $row = 0;
        foreach( $sheetData as $bloque ){
            $col = 0;
            $row++;
            foreach( $bloque as $dato ){
                $worksheet1->setCellValue( chr( 65 + $col++ ).$row, $dato );
            }
        }

        foreach( $cortes as $c ){
            $worksheet1->getStyle( "A{$c[0]}:E{$c[0]}" )->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setARGB( $c[1] );
            $worksheet1->getStyle( "A{$c[0]}:E{$c[0]}" )->getFont()->getColor()->setARGB('ffffff');
        }

        foreach( $worksheet1->getColumnIterator() as $column ){
            $worksheet1->getColumnDimension( $column->getColumnIndex() )->setAutoSize( true );
        }

        
        /* ************************************************************************************************* */

        $pendientes = $db->query( "
            SELECT p.rango_codigo AS codigo, r.nombre AS rango, r.hex, p.usuario_id AS socio, CONCAT( u.data->>'$.nombre', ' ', u.data->>'$.apellidos[ 0 ]', ' ', u.data->>'$.apellidos[1]') AS nombre, p.fecha, p.comentarios, p.entrega_fecha, p.entrega_lugar
            FROM t_pines p
            JOIN t_rangos r ON r.codigo = p.rango_codigo
            JOIN t_usuarios u ON u.id = p.usuario_id 
            WHERE p.estatus_codigo = '623-ENTREGA'
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
                $sheetData[] = [ "RANGO", "SOCIO", "NOMBRE", "FECHA", "ENTREGA", "LUGAR", "COMENTARIOS" ];
                $cortes[ $p[ "rango" ] ] = [ $conteo, $p[ "hex" ] ];
            }
            $conteo++;

            $sheetData[] = [  strtoupper( $p[ "rango" ] ),  $p[ "socio" ],  strtoupper( $p[ "nombre" ] ), $p[ "fecha" ], $p[ "entrega_fecha" ] == "0000-00-00" ? "" : $p[ "entrega_fecha" ], $p[ "entrega_lugar" ], $p[ "comentarios" ] ];          
        }


        $worksheet2 = new \PhpOffice\PhpSpreadsheet\Worksheet\Worksheet( $mySpreadsheet, "ENTREGADOS" );

        $mySpreadsheet->addSheet( $worksheet2, 1 );

        $row = 0;
        foreach( $sheetData as $bloque ){
            $col = 0;
            $row++;
            foreach( $bloque as $dato ){
                $worksheet2->setCellValue( chr( 65 + $col++ ).$row, $dato );
            }
        }

        foreach( $cortes as $c ){
            $worksheet2->getStyle( "A{$c[0]}:G{$c[0]}" )->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setARGB( $c[1] );
            $worksheet2->getStyle( "A{$c[0]}:G{$c[0]}" )->getFont()->getColor()->setARGB('ffffff');
        }

        foreach( $worksheet2->getColumnIterator() as $column ){
            $worksheet2->getColumnDimension( $column->getColumnIndex() )->setAutoSize( true );
        }



        
        /* ************************************************************************************************* */

        $pendientes = $db->query( "
            SELECT p.rango_codigo AS codigo, r.nombre AS rango, r.hex, p.usuario_id AS socio, CONCAT( u.data->>'$.nombre', ' ', u.data->>'$.apellidos[ 0 ]', ' ', u.data->>'$.apellidos[1]') AS nombre, p.fecha, p.comentarios, p.entrega_fecha, p.entrega_lugar
            FROM t_pines p
            JOIN t_rangos r ON r.codigo = p.rango_codigo
            JOIN t_usuarios u ON u.id = p.usuario_id 
            WHERE p.estatus_codigo = '150-CANCELADO'
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
                $sheetData[] = [ "RANGO", "SOCIO", "NOMBRE", "FECHA", "ENTREGA", "LUGAR", "COMENTARIOS" ];
                $cortes[ $p[ "rango" ] ] = [ $conteo, $p[ "hex" ] ];
            }
            $conteo++;

            $sheetData[] = [  strtoupper( $p[ "rango" ] ),  $p[ "socio" ],  strtoupper( $p[ "nombre" ] ), $p[ "fecha" ], $p[ "entrega_fecha" ] == "0000-00-00" ? "" : $p[ "entrega_fecha" ], $p[ "entrega_lugar" ], $p[ "comentarios" ] ];          
        }


        $worksheet3 = new \PhpOffice\PhpSpreadsheet\Worksheet\Worksheet( $mySpreadsheet, "CANCELADOS" );

        $mySpreadsheet->addSheet( $worksheet3, 2 );

        $row = 0;
        foreach( $sheetData as $bloque ){
            $col = 0;
            $row++;
            foreach( $bloque as $dato ){
                $worksheet3->setCellValue( chr( 65 + $col++ ).$row, $dato );
            }
        }

        foreach( $cortes as $c ){
            $worksheet3->getStyle( "A{$c[0]}:G{$c[0]}" )->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setARGB( $c[1] );
            $worksheet3->getStyle( "A{$c[0]}:G{$c[0]}" )->getFont()->getColor()->setARGB('ffffff');
        }

        foreach( $worksheet3->getColumnIterator() as $column ){
            $worksheet3->getColumnDimension( $column->getColumnIndex() )->setAutoSize( true );
        }







        $mySpreadsheet->setActiveSheetIndex(0);


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

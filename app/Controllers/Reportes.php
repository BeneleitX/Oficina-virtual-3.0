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
            $this->data[ "usuario" ]->permiso( "39-REPORTES-CONTA" ) ||
            $this->data[ "usuario" ]->permiso( "40-ADMIN" )
        ) ){
            return redirect()->to( "no_permiso" ); 
        }

        $this->data[ "navbar" ] = true;
        $this->data[ "titulo" ] = "Reportes";

        echo template( "reportes/menu", $this->data );
    }

    public function ingresos_por_empresa()
    {
        if( !(
            $this->data[ "usuario" ]->permiso( "39-REPORTES-CONTA" ) ||
            $this->data[ "usuario" ]->permiso( "40-ADMIN" )
        ) ){
            return redirect()->to( "no_permiso" ); 
        }

        $this->data[ "navbar" ] = true;
        $this->data[ "titulo" ] = "Reportes: Ingresos por empresa";

        echo template( "reportes/ingresos_por_empresa", $this->data );
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
        $this->data[ "navbar" ]    = true;
        $this->data[ "titulo" ]    = "Reportes: Listado de socios por estatus";

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
    
    
    public function tabla_ingresos_por_empresa()
    {    
        if( !(
            $this->data[ "usuario" ]->permiso( "39-REPORTES-CONTA" ) ||
            $this->data[ "usuario" ]->permiso( "40-ADMIN" )
        ) ){
            return redirect()->to( "no_permiso" ); 
        }

        $db = db_connect();

        $tabla = "";
        $sql = "SELECT 
                p.modelo_codigo as 'EMPRESA',

                count(*) as VENTAS,
                ifnull( sum( p.DATA->'$.total'), 0 ) AS 'VENTA_PRODUCTO',
                ifnull( sum( p.data->'$.comisionbanco' ), 0 ) as 'COMISIONES_BANCO',
                ifnull( sum( p.data->'$.comisionentrega' ), 0 ) as 'PAQUETERIA',
                ifnull( sum( p.DATA->'$.total' + p.data->'$.comisionbanco' + p.data->'$.comisionentrega'), 0 ) as 'TOTAL'

                FROM t_pedidos p 
                WHERE SUBSTRING( p.estatus_codigo,1,3) > 400
                AND cast( p.fechas->>'$.pagado' as date ) BETWEEN '".$this->request->getPost( "inicia" )."' AND '".$this->request->getPost( "termina")."' 
                GROUP BY p.modelo_codigo";

        $datos = $db->query( $sql )->getResultArray();
        
        $modelos = [];

        foreach( $datos as $d ){
            $modelos[ $d[ "EMPRESA" ] ] = $d;
        }
        
        foreach( MODELOS as $m ){        
            if( isset( $modelos[ $m[ "codigo" ] ] ) ){
                $modelo = $modelos[ $m[ "codigo" ] ];
            }
            else{
                $modelo = [
                    "EMPRESA" => $m[ "codigo" ],
                    "VENTAS" => 0,
                    "VENTA_PRODUCTO" => 0,
                    "COMISIONES_BANCO" => 0,
                    "PAQUETERIA" => 0,
                    "TOTAL" => 0    
                ];
            }

            

            $tabla .= "<tr>";
            $tabla .= "<td><span class=\"badge bg-{$m[ "settings" ][ "color" ]}\"><i class=\"fa fa-{$m[ "settings" ][ "icono" ]}\"></i> ".strtoupper( $m[ "nombre" ] )."</span></td>";
            $tabla .= "<td class=\"small\">".$m[ "settings" ][ "moneda" ]."</td>";
            $tabla .= "<td class=\"text-center\">".number_format( $modelo[ "VENTAS" ] )."</td>";
            $tabla .= "<td class=\"text-end\">$".number_format( $modelo[ "VENTA_PRODUCTO" ], 2 )."</td>";
            $tabla .= "<td class=\"text-end\">$".number_format( $modelo[ "COMISIONES_BANCO" ], 2 )."</td>";
            $tabla .= "<td class=\"text-end\">$".number_format( $modelo[ "PAQUETERIA" ], 2 )."</td>";
            $tabla .= "<td class=\"text-end\"><strong>$".number_format( $modelo[ "TOTAL" ], 2 )."</strong></td>";
            $tabla .= "</tr>";
        }

        echo $tabla;
    }


    public function pedidos_diarios(){
        if( !(
            $this->data[ "usuario" ]->permiso( "36-REPORTES" ) ||
            $this->data[ "usuario" ]->permiso( "40-ADMIN" )
        ) ){
            return redirect()->to( "no_permiso" ); 
        }

        $sql = "SELECT 
                modelo_codigo, 
                metodopago_codigo
                from t_pedidos
                where substring( estatus_codigo,1,3) > 200
                and metodopago_codigo is not null
                and fechas->>'$.pagado' > '2024-10-01'
                group by modelo_codigo, metodopago_codigo";

        $db     = db_connect();
        $result = $db->query( $sql );
        $this->data[ "metodospago" ] = [];
        
        foreach( $result->getResult() as $m ){
            $this->data[ "metodospago" ][ $m->modelo_codigo ][] = $m->metodopago_codigo;
        }

        $sql = "SELECT 
                modelo_codigo, 
                metodoentrega_codigo
                from t_pedidos
                where substring( estatus_codigo,1,3) > 200
                and metodoentrega_codigo is not null
                and fechas->>'$.pagado' > '2024-10-01'
                group by modelo_codigo, metodoentrega_codigo";

        $db     = db_connect();
        $result = $db->query( $sql );
        $this->data[ "metodosentrega" ] = [];
        
        foreach( $result->getResult() as $m ){
            $this->data[ "metodosentrega" ][ $m->modelo_codigo ][] = $m->metodoentrega_codigo;
        }

        $this->data[ "navbar" ]    = true;
        $this->data[ "titulo" ]    = "Reportes: Pedidos diarios por empresa";

        echo template( "reportes/pedidos_diarios", $this->data );
    }
    
    

    public function excel_pedidos_diarios()
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
        $m_pago   = $this->request->getPost( "m_pago");
        $m_entrega   = $this->request->getPost( "m_entrega");
        $c_primercompra   = $this->request->getPost( "c_primercompra");
        $f_inicio = $this->request->getPost( "f_inicio");
        $f_final  = $this->request->getPost( "f_final");
        $data     = [];

        $mySpreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
        $mySpreadsheet->removeSheetByIndex(0);
        $worksheet = new \PhpOffice\PhpSpreadsheet\Worksheet\Worksheet($mySpreadsheet, $estatus );
        $mySpreadsheet->addSheet( $worksheet, 0 );

        $row = 1;
        $d   = [];
        $e   = [];
        $worksheet->setCellValue( "A1", "REFERENCIA" );
        $worksheet->setCellValue( "B1", "FECHA PAGO" );
        $worksheet->setCellValue( "C1", "METODO PAGO" );
        $worksheet->setCellValue( "D1", "OPERACION" );
        $worksheet->setCellValue( "E1", "FECHA ENTREGA" );
        $worksheet->setCellValue( "F1", "METODO ENTREGA" );
        $worksheet->setCellValue( "G1", "DETALLE ENTREGA" );
        $worksheet->setCellValue( "H1", "ESTATUS" );
        $worksheet->setCellValue( "I1", "SOCIO" );
        $worksheet->setCellValue( "J1", "NOMBRE" );
        $worksheet->setCellValue( "K1", "CELULAR" );
        $worksheet->setCellValue( "L1", "PRODUCTOS" );
        $worksheet->setCellValue( "M1", "PRIMER COMPRA" );
        $worksheet->setCellValue( "N1", "SUB TOTAL" );
        $worksheet->setCellValue( "O1", "GASTOS ENTREGA" );
        $worksheet->setCellValue( "P1", "COMISION BANCO" );
        $worksheet->setCellValue( "Q1", "TOTAL" );

        switch( $estatus ){
            case "TODOS": $estatus = "substring( p.estatus_codigo,1,3) > 400"; break;
            case "400":   $estatus = "substring( p.estatus_codigo,1,3) between 400 AND 500"; break;
            case "500":   $estatus = "substring( p.estatus_codigo,1,3) > 500"; break;
        }

        $sql = "SELECT 
                    p.referencia as REFERENCIA,
                    u.id as SOCIO,
                    any_value(f.extras->>'$.auth') as EXTRAS,
                    p.estatus_codigo as ESTATUS,
                    u.telefono as CELULAR,
                    concat( u.data->>'$.nombre', ' ', u.data->>'$.apellidos[ 0 ]', ' ', u.data->>'$.apellidos[ 1 ]' ) as NOMBRE,
                    p.data->>'$.productos' as PRODUCTOS,
                    p.metodoentrega_codigo as METODO_ENTREGA,
                    IF( p.metodoentrega_codigo = '00-ALMACEN', p.data->>'$.entrega', '' ) as ALMACEN,
                    p.data->>'$.total' as SUBTOTAL_PRODUCTOS,
                    p.data->>'$.comisionbanco' as COMISION_METODOPAGO,
                    p.data->>'$.comisionentrega' as COMISION_ENTREGA,
                    ( p.data->>'$.total' + p.data->>'$.comisionbanco' + p.data->>'$.comisionentrega' ) as TOTAL,
                    IF( p.data->>'$.primercompra' = 1, 'SI', 'NO' ) as PRIMER_COMPRA,
                    p.metodopago_codigo as METODO_PAGO,
                    p.data->>'$.guia' as GUIA,
                    cast( p.fechas->>'$.pagado' as date ) as FECHA_PAGO,
                    cast( IF( p.metodoentrega_codigo = '00-ALMACEN', p.fechas->>'$.entregado', p.fechas->>'$.enviado') as date ) as FECHA_ENTREGA
                from t_pedidos p
                join t_usuarios u on p.usuario_id = u.id
                left join t_fondeos f on f.referencia = p.referencia
                where
                    {$estatus}
                    ".( $m_pago != 'TODOS' ? "and p.metodopago_codigo = '{$m_pago}'" : "" )."
                    ".( $m_entrega != 'TODOS' ? "and p.metodoentrega_codigo = '{$m_entrega}'" : "" )."
                    ".( $c_primercompra != 'TODOS' ? "and p.data->>'$.primercompra' = {$c_primercompra}" : "" )."
                    and cast( p.fechas->>'$.pagado' as date ) between '{$f_inicio}' and '{$f_final}' 
                    and p.modelo_codigo = '{$modelo}'
                group by p.id
                order by cast( p.fechas->>'$.pagado' as date ) asc";
                
                

        $result = $db->query( $sql );

        foreach( $result->getResult() as $s ){
            $row++;
            switch( $s->METODO_PAGO ){
                case "21-GETNET": $operacion = $s->EXTRAS ?? ""; break;
                default: $operacion = ""; break;
            }

            // $worksheet->setCellValue( "Z1",  $sql);

            $worksheet->setCellValue( "A".( $row ),  $s->REFERENCIA);
            $worksheet->setCellValue( "B".( $row ),  $s->FECHA_PAGO);
            $worksheet->setCellValue( "C".( $row ),  substr( $s->METODO_PAGO, 3 ) );
            $worksheet->setCellValue( "D".( $row ),  $operacion);
            $worksheet->setCellValue( "E".( $row ),  $s->FECHA_ENTREGA);
            $worksheet->setCellValue( "F".( $row ),  substr( $s->METODO_ENTREGA, 3 ) );
            $worksheet->setCellValue( "G".( $row ),  $s->METODO_ENTREGA == "00-ALMACEN" ? substr( $s->ALMACEN, 4 ) : ( $s->GUIA ?? "" ) );
            $worksheet->setCellValue( "H".( $row ),  strtoupper( ESTATUS[ $s->ESTATUS ][ "descripcion" ] ) );
            $worksheet->setCellValue( "I".( $row ),  $s->SOCIO);
            $worksheet->setCellValue( "J".( $row ),  $s->NOMBRE);
            $worksheet->setCellValue( "K".( $row ),  $s->CELULAR);
            $worksheet->setCellValue( "L".( $row ),  $s->PRODUCTOS);
            $worksheet->setCellValue( "M".( $row ),  $s->PRIMER_COMPRA);
            $worksheet->setCellValue( "N".( $row ),  $s->SUBTOTAL_PRODUCTOS);
            $worksheet->setCellValue( "O".( $row ),  $s->COMISION_ENTREGA);
            $worksheet->setCellValue( "P".( $row ),  $s->COMISION_METODOPAGO);
            $worksheet->setCellValue( "Q".( $row ),  $s->SUBTOTAL_PRODUCTOS + $s->COMISION_ENTREGA + $s->COMISION_METODOPAGO);
            
        }

        $worksheet->getStyle( "A1:Q1" )->getFont()->getColor()->setARGB('ffffff');
        $worksheet->getStyle( "A1:P1" )->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setARGB('192b5a');
        $worksheet->getStyle( "Q1" )->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setARGB('009779');        

        $worksheet->getStyle( "M:P" )->getNumberFormat()->setFormatCode( "$#,##0.00" );
        $worksheet->getStyle( "F" )->getNumberFormat()->setFormatCode( "#" );

        foreach( $worksheet->getColumnIterator() as $column ){
            $worksheet->getColumnDimension( $column->getColumnIndex() )->setAutoSize( true );
        }


        $worksheet->freezePane('B2');

        $path = "data/excel/pedidos_diarios";
        if( !is_dir( $path ) ) mkdir( $path, 0755, true );

        echo $file = $path."/Pedidos_".substr( $modelo, 3 )."_del_{$f_inicio}_al_{$f_final}_".time().".xlsx";

        $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($mySpreadsheet);
        $writer->save( $file );
    }     
    
    

    public function venta_producto(){
        if( !(
            $this->data[ "usuario" ]->permiso( "36-REPORTES" ) ||
            $this->data[ "usuario" ]->permiso( "40-ADMIN" )
        ) ){
            return redirect()->to( "no_permiso" ); 
        }

        load_catalogo( "promociones" );

        $sql = "SELECT 
                modelo_codigo, 
                metodoentrega_codigo
                from t_pedidos
                where substring( estatus_codigo,1,3) > 200
                and metodoentrega_codigo is not null
                and fechas->>'$.pagado' > '2024-10-01'
                group by modelo_codigo, metodoentrega_codigo";

        $db     = db_connect();
        $result = $db->query( $sql );
        $this->data[ "metodosentrega" ] = [];
        
        foreach( $result->getResult() as $m ){
            $this->data[ "metodosentrega" ][ $m->modelo_codigo ][] = $m->metodoentrega_codigo;
        }

        $this->data[ "navbar" ] = true;
        $this->data[ "titulo" ] = "Reportes: Venta por producto";

        echo template( "reportes/venta_producto", $this->data );
    }
    
    

    public function excel_venta_producto()
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
        $promos   = $this->request->getPost( "promos");
        $m_entrega   = $this->request->getPost( "m_entrega");
        $c_primercompra   = $this->request->getPost( "c_primercompra");
        $f_inicio = $this->request->getPost( "f_inicio");
        $f_final  = $this->request->getPost( "f_final");
        $data     = [];

        $mySpreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
        $mySpreadsheet->removeSheetByIndex(0);
        $worksheet = new \PhpOffice\PhpSpreadsheet\Worksheet\Worksheet($mySpreadsheet, "PRODUCTOS" );
        $mySpreadsheet->addSheet( $worksheet, 0 );

        $row = 1;
        $d   = [];
        $e   = [];
        $worksheet->setCellValue( "A1", "PRODUCTO" );

        foreach( $promos as $p => $v ){
            $worksheet->setCellValue( chr( ord( "A" ) + $p + 1 ) . "1", substr( $v, 4 ) );
        }

        $worksheet->setCellValue( chr( ord( "A" ) + $p + 2 ) . "1", "TOTAL" );

        switch( $estatus ){
            case "TODOS": $estatus = "substring( p.estatus_codigo,1,3) > 400"; break;
            case "400":   $estatus = "substring( p.estatus_codigo,1,3) between 400 AND 500"; break;
            case "500":   $estatus = "substring( p.estatus_codigo,1,3) > 500"; break;
        }

        $db->query( "DROP TEMPORARY TABLE IF EXISTS t_reporte_productos;" );

        $sql = "SELECT codigo, data->>'$.nombre' as nombre from t_productos where modelo_codigo = '{$modelo}' and substring( estatus_codigo,1,3) > 120;";

        $productos = $db->query( $sql );

        $sql = "
        CREATE TEMPORARY TABLE t_reporte_productos AS
        SELECT 
            JSON_UNQUOTE(JSON_EXTRACT(j1.promocion, '$')) AS promocion,
            JSON_UNQUOTE(j2.producto) AS producto,
            sum( CAST(JSON_EXTRACT(p.promociones, CONCAT('$.', j1.promocion, '.productos.', j2.producto, '.cantidad')) AS UNSIGNED) ) AS cantidad
        FROM t_pedidos p
        JOIN JSON_TABLE(
        JSON_KEYS(p.promociones),
        \"$[*]\" COLUMNS (promocion JSON PATH \"$\")
        ) AS j1

        JOIN JSON_TABLE(
        JSON_KEYS(JSON_EXTRACT(p.promociones, CONCAT('$.\"', JSON_UNQUOTE(JSON_EXTRACT(j1.promocion, '$')), '\".productos'))),
        \"$[*]\" COLUMNS (producto JSON PATH \"$\")
        ) AS j2

        where {$estatus}
            and p.modelo_codigo = '{$modelo}'
            ".( $c_primercompra != 'TODOS' ? "and p.data->>'$.primercompra' = {$c_primercompra}" : "" )."
            ".( $m_entrega != 'TODOS' ? "and p.metodoentrega_codigo = '{$m_entrega}'" : "" )."
            and substring( p.estatus_codigo,1,3) > 400
            and p.fechas->>'$.pagado' between '{$f_inicio}' and '{$f_final}' 

        group by promocion, producto";

        $db->query( $sql );

        $sql = "select * from t_reporte_productos;";

        $resultado = $db->query( $sql );

        foreach( $resultado->getResult() as $s ){
            $data[ $s->producto ][ $s->promocion ] = $s->cantidad;
        }

        foreach( $productos->getResult() as $p ){
            $producto   = $p->codigo;
            $cantidades = $data[ $producto ] ?? [];
            $row++;
            $worksheet->setCellValue( "A".( $row ),  $p->nombre );

            foreach( $promos as $p => $v ){
                $worksheet->setCellValue( chr( ord( "A" ) + $p + 1 ) . $row, $cantidades[ $v ] ?? 0 );
            }
            $suma = array_sum( $cantidades );
            $worksheet->setCellValue( chr( ord( "A" ) + $p + 2 ) . $row,  strlen($suma) ? $suma : 0 );
        }

        $worksheet->getStyle( "A1:".chr( ord( "A" ) + $p + 2 ) ."1" )->getFont()->getColor()->setARGB('ffffff');
        
        $worksheet->getStyle( "A1" )->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setARGB('47c24c');

        $worksheet->getStyle( "B1:".chr( ord( "A" ) + $p + 2 ) ."1" )->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setARGB('192b5a');

        $worksheet->getStyle( chr( ord( "A" ) + $p + 2 ) ."1" )->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setARGB('47c24c');


        $worksheet->getStyle( chr( ord( "A" ) + $p + 2 ) )->getNumberFormat()->setFormatCode( "#" );

        foreach( $worksheet->getColumnIterator() as $column ){
            $worksheet->getColumnDimension( $column->getColumnIndex() )->setAutoSize( true );
        }

        $path = "data/excel/venta_producto";
        if( !is_dir( $path ) ) mkdir( $path, 0755, true );

        echo $file = $path."/Productos_".substr( $modelo, 3 )."_del_{$f_inicio}_al_{$f_final}_".time().".xlsx";

        $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($mySpreadsheet);
        $writer->save( $file );
    } 

    
    
    public function calificaciones_mes(){
        if( !(
            $this->data[ "usuario" ]->permiso( "36-REPORTES" ) ||
            $this->data[ "usuario" ]->permiso( "40-ADMIN" )
        ) ){
            return redirect()->to( "no_permiso" ); 
        }

        $this->data[ "navbar" ] = true;
        $this->data[ "titulo" ] = "Reportes: Calificaciones por mes";

        $sql = "estatus_codigo = '201-ACTIVO' AND codigo not like '%--%'";
        load_catalogo( "calificaciones", $sql );
        
        echo template( "reportes/calificaciones_mes", $this->data );
    }



    public function update_calificaciones()
    {
        if( !(
            $this->data[ "usuario" ]->permiso( "36-REPORTES" ) ||
            $this->data[ "usuario" ]->permiso( "40-ADMIN" )
        ) ){
            return redirect()->to( "no_permiso" ); 
        }

        // recuperar variables POST

        extract( $this->request->getPost() );

        $sql = "estatus_codigo = '201-ACTIVO'";
        load_catalogo( "calificaciones", $sql );
        
        // crear consultas a base de datos

        switch( $c_primercompra ){
            case 1  : $where = " having primercompra = 1 "; break;
            case 0  : $where = " having primercompra = 0 "; break;
            default : $where = ""; break;
        }

        $f_i = $f_mes."-01";
        $f_t = date( "Y-m-t", strtotime( $f_i ) );

        $sql = "SELECT 
                    u.id as socio, 
                    count( * ) as pedidos,
                    sum( p.data->>'$.primercompra' ) as primercompra,
                    sum( json_extract( p.PTS, concat( '$.\"', temp.promo, '\"' ) ) ) as puntos
                    from t_usuarios u
                    join t_pedidos p 
                        on p.usuario_id = u.id
                        and substring( p.estatus_codigo,1,3) > 400 
                        and p.modelo_codigo = '{$modelo}' 
                        and p.fechas->>'$.pagado' between '{$f_i}' and '{$f_t}',
                    (
                        select promo
                        from
                        t_modelos m, 
                        JSON_TABLE( m.settings->>'$.promocion_base', '$[*]' COLUMNS (
                            promo VARCHAR(40)  PATH '$'
                        ) ) promos
                        where m.codigo = '{$modelo}'
                    ) temp
                    group by u.id, temp.promo
                    {$where}
                   
                    order by puntos";
        
        // procesar datos

        $db     = db_connect();
        $result = $db->query( $sql );
        $datos  = [];

        foreach( $result->getResult() as $d ){
            
            switch( true ){
                case $d->puntos >= 9:  $calificacion = "71-E";  break;
                case $d->puntos >= 6:  $calificacion = "61-M";  break;
                case $d->puntos >= 3:  $calificacion = "51-B";  break;
                default:               $calificacion = "01-C"; break;
            }

            if( in_array( $calificacion, $calificaciones ) ){
                if( !isset( $datos[ $calificacion ] ) ){
                    $datos[ $calificacion ] = [];
                }

                $datos[ $calificacion ][] = $d->socio;
            }
        }

        // mostrar datos

        $k = 0;
        $html = "<ul class=\"nav nav-tabs\" id=\"myTab\" role=\"tablist\">";

        foreach( $datos as $calificacion => $socios ){
            $c = CALIFICACIONES[ $calificacion ];

            $html .= "\n<li class=\"nav-item\" role=\"presentation\">
                    <button class=\"nav-link ".( $k++ ? "x" : "" )."active\" id=\"tab-{$calificacion}\" data-bs-toggle=\"tab\" data-bs-target=\"#tab-{$calificacion}-panel\" type=\"button\" role=\"tab\" aria-controls=\"tab-{$calificacion}-panel\" aria-selected=\"true\"><h1 class=\"px-5 pb-0\">".sizeof( $socios )."</h1>{$c[ "descripcion" ]}</button>
                </li>";
        }
        $html .= "</ul><div class=\"tab-content\" id=\"myTabContent\">";

        $k = 0;

        foreach( $datos as $calificacion => $socios ){
            $html .= "\n<div class=\"tab-pane fade ".( $k ? "x" : "" )."show ".( $k++ ? "x" : "" )."active\" id=\"tab-{$calificacion}-panel\" role=\"tabpanel\" aria-labelledby=\"tab-{$calificacion}\" tabindex=\"0\"><div class=\"card tab-body\">";
            
            // tabla con socios
            
            $html .= "<table class=\"table table-striped resultados\"><thead><tr>
                        <td>Socio</td>
                        <td>Nombre</td>
                        <td>CURP</td>
                        <td>Teléfono</td>
                        <td>Correo</td>
                        <td></td>
                    </tr></thead><tbody>";
            

            foreach( $socios as $u ){
                $s = model( "UsuarioModel" )->find( $u );

                if( substr( $s->data->estatus->modelos->{ $modelo }, 0, 3 ) < 300 OR ( substr( $calificacion, 0, 1 ) == "0" AND substr( $s->data->estatus->modelos->{ $modelo }, 0, 3 ) > 400 ) ){

                    $s->getPrimerCompra( $modelo );

                    $db->query( "call p_update_primercompra( {$s->id}, '{$modelo}' );" );
                    $db->query( "select f_update_PTS( {$s->id}, '{$modelo}', '".date( 'Ym', strtotime( date('Y-m').'-01'. ' -1 month' ) )."' )" ); 
                    $db->query( "select f_update_PTS( {$s->id}, '{$modelo}', '".date( "Ym" )."' )" );  
                    $db->query( "call p_update_padre( {$s->id}, '{$modelo}' );" );
                }
                
                $db->query( "select f_get_estatus(  {$s->id}, 0 )" );

                $link  = base_url()."sociodata/".urlencode( base64_encode( $s->password_original() ) );
                $html .= "<tr>
                            <td>".$s->id( $modelo )."</td>
                            <td>".$s->avatar(24)." ".$s->nombre( 2 )."</td>
                            <td>{$s->telefono}</td>
                            <td>{$s->curp}</td>
                            <td>{$s->correo}</td>
                            <td class=\"text-end\"><a class=\"btn btn-sm btn-primary\" href=\"{$link}\"><i class=\"fa fa-magnifying-glass\"></i></a></td>
                        </tr>";
            }

            $html .= "</tbody></table></div></div>";
        }

        $html .= "</div>";

        // generar gráfica
        // devolver HTML

        echo $html;
    }



    public function excel_calificaciones()
    {
        if( !(
            $this->data[ "usuario" ]->permiso( "36-REPORTES" ) ||
            $this->data[ "usuario" ]->permiso( "40-ADMIN" )
        ) ){
            return redirect()->to( "no_permiso" ); 
        }
                
        $db = db_connect();

        extract( $this->request->getPost() );

        $sql = "estatus_codigo = '201-ACTIVO'";
        load_catalogo( "calificaciones", $sql );
        
        // crear consultas a base de datos

        switch( $c_primercompra ){
            case 1  : $where = " having primercompra = 1 "; break;
            case 0  : $where = " having primercompra = 0 "; break;
            default : $where = ""; break;
        }

        $f_i = $f_mes."-01";
        $f_t = date( "Y-m-t", strtotime( $f_i ) );

        $sql = "SELECT 
                    u.id as socio, 
                    count( * ) as pedidos,
                    sum( p.data->>'$.primercompra' ) as primercompra,                    
                    sum( json_extract( p.PTS, concat( '$.\"', temp.promo, '\"' ) ) ) as puntos
                    from t_usuarios u
                    join t_pedidos p 
                        on p.usuario_id = u.id 
                        and substring( p.estatus_codigo,1,3) > 400
                        and p.modelo_codigo = '{$modelo}' 
                        and p.fechas->>'$.pagado' between '{$f_i}' and '{$f_t}',
                    (
                        select promo
                        from
                        t_modelos m, 
                        JSON_TABLE( m.settings->>'$.promocion_base', '$[*]' COLUMNS (
                            promo VARCHAR(40)  PATH '$'
                        ) ) promos
                        where m.codigo = '{$modelo}'
                    ) temp

                  

                    group by u.id, temp.promo
                      {$where}
                    order by puntos";
        
        // procesar datos

        $result    = $db->query( $sql );
        $datos     = [];
        $sheetData = [];
        
        foreach( $result->getResult() as $d ){
            
            switch( true ){
                case $d->puntos >= 9:  $calificacion = "71-E";  break;
                case $d->puntos >= 6:  $calificacion = "61-M";  break;
                case $d->puntos >= 3:  $calificacion = "51-B";  break;
                default:               $calificacion = "01-C"; break;
            }

            if( in_array( $calificacion, $calificaciones ) ){
                if( !isset( $datos[ $calificacion ] ) ){
                    $datos[ $calificacion ] = [];
                }

                $datos[ $calificacion ][] = $d->socio;
            }
        }

        $mySpreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
        $mySpreadsheet->removeSheetByIndex(0);
        $worksheet = [];

        $datos = array_reverse( $datos );

        foreach( $datos as $calificacion => $socios ){
            $c = CALIFICACIONES[ $calificacion ];

            $d = [
                [ "SOCIO", "NOMBRE", "CURP", "TELEFONO", "CORREO" ]
            ];

            foreach( $socios as $u ){
                $s = model( "UsuarioModel" )->find( $u );

                $d[] = [ $s->id, $s->nombre( 2, false, true ), $s->telefono, $s->curp, $s->correo ];
            }

            $sheetData[ $calificacion ] = $d;

            $worksheet[ $calificacion ] = new \PhpOffice\PhpSpreadsheet\Worksheet\Worksheet($mySpreadsheet, $c[ "descripcion" ] );
            $mySpreadsheet->addSheet( $worksheet[ $calificacion ], 0 );            
        }
        
        foreach( $sheetData as $k => $s ){
            $row = 0;
            foreach( $s as $bloque ){
                $col = 0;
                $row++;
                foreach( $bloque as $dato){
                    $worksheet[ $k ]->setCellValue( chr(65 + $col++).$row, $dato );
                }
            }

            $worksheet[ $k ]->getStyle( "A" )->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
            $worksheet[ $k ]->getStyle( "C:D" )->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
            $worksheet[ $k ]->getStyle( "A1:E1" )->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setARGB('009779');
            $worksheet[ $k ]->getStyle( "A1:E1" )->getFont()->getColor()->setARGB('ffffff');            

            foreach( $worksheet[ $k ]->getColumnIterator() as $column ){
                $worksheet[ $k ]->getColumnDimension( $column->getColumnIndex() )->setAutoSize( true );
            }

        }


        $path = "data/excel/reporte_calificaciones";
        if( !is_dir( $path ) ) mkdir( $path, 0755, true );

        echo $file = $path."/Calificaciones_".substr( $modelo, 3 )."_{$f_mes}_".time().".xlsx";

        $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($mySpreadsheet);
        $writer->save( $file );
    }         
}

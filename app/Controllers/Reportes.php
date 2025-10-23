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
        echo $sql = "SELECT 
                p.modelo_codigo as 'EMPRESA',

                count(*) as VENTAS,
                ifnull( sum( p.DATA->'$.total'), 0 ) AS 'VENTA_PRODUCTO',
                ifnull( sum( p.data->'$.comisionbanco' ), 0 ) as 'COMISIONES_BANCO',
                ifnull( sum( p.data->'$.comisionentrega' ), 0 ) as 'PAQUETERIA',
                ifnull( sum( p.DATA->'$.total' + p.data->'$.comisionbanco' + p.data->'$.comisionentrega'), 0 ) as 'TOTAL'

                FROM t_pedidos p 
                WHERE SUBSTRING( p.estatus_codigo,1,3) > 400
                AND p.fechas->>'$.pagado' BETWEEN '".$this->request->getPost( "inicia" )."' AND '".$this->request->getPost( "termina")."' 
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
                    f.extras as EXTRAS,
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

                order by cast( p.fechas->>'$.pagado' as date ) asc";

        $result = $db->query( $sql );

        foreach( $result->getResult() as $s ){
            $row++;
            $s->EXTRAS = json_decode( $s->EXTRAS );
            switch( $s->METODO_PAGO ){
                case "21-GETNET": $operacion = $s->EXTRAS->auth ?? ""; break;
                default: $operacion = ""; break;
            }


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

        $worksheet->getStyle( "A1:P1" )->getFont()->getColor()->setARGB('ffffff');
        $worksheet->getStyle( "A1:P1" )->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setARGB('192b5a');

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
        $worksheet = new \PhpOffice\PhpSpreadsheet\Worksheet\Worksheet($mySpreadsheet, $estatus );
        $mySpreadsheet->addSheet( $worksheet, 0 );

        $row = 1;
        $d   = [];
        $e   = [];
        $worksheet->setCellValue( "A1", "MES" );
        $worksheet->setCellValue( "B1", "FECHA PAGO" );
        $worksheet->setCellValue( "C1", "METODO PAGO" );
        $worksheet->setCellValue( "D1", "FECHA ENTREGA" );
        $worksheet->setCellValue( "E1", "METODO ENTREGA" );
        $worksheet->setCellValue( "F1", "DETALLE ENTREGA" );
        $worksheet->setCellValue( "G1", "ESTATUS" );
        $worksheet->setCellValue( "H1", "SOCIO" );
        $worksheet->setCellValue( "I1", "NOMBRE" );
        $worksheet->setCellValue( "J1", "CELULAR" );
        $worksheet->setCellValue( "K1", "PRODUCTOS" );
        $worksheet->setCellValue( "L1", "PRIMER COMPRA" );
        $worksheet->setCellValue( "M1", "SUB TOTAL" );
        $worksheet->setCellValue( "N1", "GASTOS ENTREGA" );
        $worksheet->setCellValue( "O1", "COMISION BANCO" );
        $worksheet->setCellValue( "P1", "TOTAL" );

        switch( $estatus ){
            case "TODOS": $estatus = "substring( p.estatus_codigo,1,3) > 400"; break;
            case "400":   $estatus = "substring( p.estatus_codigo,1,3) between 400 AND 500"; break;
            case "500":   $estatus = "substring( p.estatus_codigo,1,3) > 500"; break;
        }
        $sql = "SELECT 
	        prod.data->>'$.nombre' as producto,
            ";
        
        foreach( $promos as $p ){
            $sql .= "\nsum( CAST(JSON_UNQUOTE(JSON_EXTRACT(p.promociones, CONCAT( '$.\"{$p}\".productos.\"', prod.codigo, '\".cantidad' ))) AS UNSIGNED) ) AS '".substr( $p, 4 )."',";
        }

        $sql .= "\np.metodoentrega_codigo as entrega

            FROM t_pedidos p
            JOIN t_productos prod on prod.modelo_codigo = @modelo and substring( prod.estatus_codigo,1,3) > 200
                
            WHERE 
            {$estatus}

            ".( $m_entrega != 'TODOS' ? "and p.metodoentrega_codigo = '{$m_entrega}'" : "" )."
            ".( $c_primercompra != 'TODOS' ? "and p.data->>'$.primercompra' = {$c_primercompra}" : "" )."

            and cast( p.fechas->>'$.pagado' as date ) between '{$f_inicio}' and '{$f_final}' 
            and p.modelo_codigo = '{$modelo}'

            group by prod.codigo, p.metodoentrega_codigo 
            order by p.metodoentrega_codigo, prod.codigo";
        
        
        
        
        
            die($sql);
        
        
        
        
        
            $result = $db->query( $sql );

        foreach( $result->getResult() as $s ){
            $row++;
            $worksheet->setCellValue( "A".( $row ),  $s->REFERENCIA);
            $worksheet->setCellValue( "B".( $row ),  $s->FECHA_PAGO);
            $worksheet->setCellValue( "C".( $row ),  substr( $s->METODO_PAGO, 3 ) );
            $worksheet->setCellValue( "D".( $row ),  $s->FECHA_ENTREGA);
            $worksheet->setCellValue( "E".( $row ),  substr( $s->METODO_ENTREGA, 3 ) );
            $worksheet->setCellValue( "F".( $row ),  $s->METODO_ENTREGA == "00-ALMACEN" ? substr( $s->ALMACEN, 4 ) : ( $s->GUIA ?? "" ) );
            $worksheet->setCellValue( "G".( $row ),  strtoupper( ESTATUS[ $s->ESTATUS ][ "descripcion" ] ) );
            $worksheet->setCellValue( "H".( $row ),  $s->SOCIO);
            $worksheet->setCellValue( "I".( $row ),  $s->NOMBRE);
            $worksheet->setCellValue( "J".( $row ),  $s->CELULAR);
            $worksheet->setCellValue( "K".( $row ),  $s->PRODUCTOS);
            $worksheet->setCellValue( "L".( $row ),  $s->PRIMER_COMPRA);
            $worksheet->setCellValue( "M".( $row ),  $s->SUBTOTAL_PRODUCTOS);
            $worksheet->setCellValue( "N".( $row ),  $s->COMISION_ENTREGA);
            $worksheet->setCellValue( "O".( $row ),  $s->COMISION_METODOPAGO);
            $worksheet->setCellValue( "P".( $row ),  $s->SUBTOTAL_PRODUCTOS + $s->COMISION_ENTREGA + $s->COMISION_METODOPAGO);
            
        }

        $worksheet->getStyle( "A1:P1" )->getFont()->getColor()->setARGB('ffffff');
        $worksheet->getStyle( "A1:P1" )->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setARGB('192b5a');

        $worksheet->getStyle( "M:P" )->getNumberFormat()->setFormatCode( "$#,##0.00" );
        $worksheet->getStyle( "F" )->getNumberFormat()->setFormatCode( "#" );

        foreach( $worksheet->getColumnIterator() as $column ){
            $worksheet->getColumnDimension( $column->getColumnIndex() )->setAutoSize( true );
        }


        $worksheet->freezePane('B2');

        $path = "data/excel/venta_producto";
        if( !is_dir( $path ) ) mkdir( $path, 0755, true );

        echo $file = $path."/Producto_".substr( $modelo, 3 )."_del_{$f_inicio}_al_{$f_final}_".time().".xlsx";

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

        $sql = "estatus_codigo = '201-ACTIVO'";
        load_catalogo( "calificaciones", $sql );
        
        echo template( "reportes/calificaciones_mes", $this->data );
    }

    public function update_calificaciones()
    {
        // recuperar variables POST

        extract( $this->request->getPost() );

        // crear consultas a base de datos

        
        // procesar datos
        // mostrar datos
        // generar gráfica
        // devolver HTML

        echo "
            <div class=\"card mt-4\">
            x
            </div>
        ";
    }
}

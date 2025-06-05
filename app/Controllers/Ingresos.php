<?php

namespace App\Controllers;

class Ingresos extends BaseController
{
    function __construct() {
        $this->data[ "menu" ] = "ingresos";
    }

    /**
     * Muestra el detalle de comisiones por esquema en el lapso de un perido
     * 
     * @param string $modelo Codigo del modelo de comisiones
     * @param string $periodo Codigo del perido a consultar
     * @param string $esquemas Cadena de esquemas a mostrar, separados por comas y 
     *                         codificados en base64 y urldecode
     *                         Ejemplo: "eyJlcXVhbCI6WyI1MC1JTkZJQ0lUIl0sInN1ZmZpeCI6WzIxNi1QUklPUyIsIjE2LU1FTU1JJTiIsIjIwLVBhcmsgTWFydGgiXX0="
     *                         Donde:
     *                             - "eyJlcXVhbCI6WyI1MC1JTkZJQ0lUIl0sInN1ZmZpeCI6WzIxNi1QUklPUyIsIjE2LU1FTU1JJTiIsIjIwLVBhcmsgTWFydGgiXX0="
     *                                   es el base64 decode de:
     *                                   '{"igual":["50-INVERSION"],"sufijo":["26-QUINCENA","16-MENSUAL","20-SEMANAL"]}'
     *                                   que es un JSON con dos propiedades:
     *                                   - igual: es un array de codigos de esquemas a mostrar exactamente
     *                                   - sufijo: es un array de codigos de esquemas a mostrar que tengan el sufijo
     *                                             correspondiente
     * @return void
     */
    public function balance( $modelo = null, $periodo = null, $esquemas = null )
    {

        $db = db_connect();

        if( !$modelo ){
            $modelo = VARIABLES[ "modelo_default" ][ "valor" ];
        }

        $modelo = MODELOS[ $modelo ];

        if( !$periodo ){
            $periodo = codigo_periodo( $modelo[ "codigo" ], null, $modelo[ "settings" ][ "periodo" ] );
        }

        $this->data[ "periodo" ] = model( "PeriodoModel" )->find( $periodo );

        if( null == $this->data[ "periodo" ] ){

            $dto = new \DateTime();
            
            if( $modelo[ "settings" ][ "periodo" ] == "SEMANAL" ){
                $dto->setISODate( substr( $periodo, 3, 4 ), substr( $periodo, 7, 2 ) );
                $inicia  = $dto->format('Y-m-d');
                $dto->modify('+6 days');
                $termina = $dto->format('Y-m-d');
            }
            elseif( $modelo[ "settings" ][ "periodo" ] == "MENSUAL" ){
                $dto->setDate( substr( $periodo, 3, 4 ),  substr( $periodo, 7, 2 ), "01" );
                $inicia  = $dto->format('Y-m-d');
                $dto->modify('+1 month -1 day');
                $termina  = $dto->format('Y-m-d');
            }

            $result  = $db->query( "INSERT IGNORE INTO t_periodos VALUES ( '{$periodo}', '250-EN-PROCESO', '{$modelo[ "codigo" ]}', '{$modelo[ "settings" ][ "periodo" ]}', '{$inicia}', '{$termina}', JSON_OBJECT() )" );

            $this->data[ "periodo" ] = model( "PeriodoModel" )->find( $periodo );
        }

        $esq = json_decode( base64_decode( urldecode( $esquemas ) ), 1 );

        $this->data[ "navbar" ]     = true;
        $this->data[ "modelo" ]     = $modelo[ "codigo" ];
        $this->data[ "titulo" ]     = "Ingresos por periodo <span class=\"badge bg-marine\">".substr($periodo, 7, 2)."-".substr($periodo, 3, 4)."</span> <span style=\"font-size:16px\">".estatus( $this->data[ "periodo" ][ "estatus_codigo" ] )."</span>";
        $this->data[ "socio"  ]     = $this->data[ "usuario" ];       

        $sql = "select max(fecha) as fecha 
                from t_comisiones 
                where usuario_id = {$this->data[ "usuario" ]->id}
                and esquema_codigo = '510-INVERSION'
                and estatus_codigo = '255-PENDIENTE'";

        $ft = date( "Y-m-d", strtotime( date( "Y-m-d", strtotime( date( "Y-m-d" )." + 1 day" ) )." last Monday" ) );
        $this->data[ "fecha_max" ] = $modelo[ "codigo" ] == "50-INVERSION" ? $db->query( $sql )->getRow()->fecha ?? $ft : $ft;

        if( $this->data[ "fecha_max" ] < $ft ){
            $this->data[ "fecha_max" ] = $ft;
        }

        $sql = "SELECT esquema.codigo as esquema
        FROM t_esquemas esquema 
        WHERE esquema.modelo_codigo = '{$modelo[ "codigo" ]}' 
            AND esquema.settings->>'$.reparto' != 'puntos'
            AND esquema.settings->>'$.periodo' in ( 'SEMANAL', 'MENSUAL', 'ANUAL' )
        ORDER BY esquema.codigo";
        
        $this->data[ "esquemas_activos" ] = $db->query( $sql )->getResultArray();
        
        if( is_array( $esq ) && sizeof( $esq ) ){
            $this->data[ "esq" ] = $esq;
        }
        else{
            foreach( $this->data[ "esquemas_activos" ]  as $e ){
                $this->data[ "esq" ][] = $e[ "esquema" ];
            }
        }

        load_catalogo( "esquemas", "modelo_codigo = '{$modelo[ "codigo" ]}'");

        echo template( "ingresos/balance", $this->data );
    }


    /**
     * Muestra el ingreso mensual del socio.
     * Muestra el ingreso para cada esquema activo.
     * Muestra el ingreso desde el 2024-08 hasta el mes actual.
     * Si no se especifica el modelo, se toma el modelo por defecto.
     */
    public function ingreso_mensual( $modelo = null )
    {
        if( !$modelo ){
            $modelo = VARIABLES[ "modelo_default" ][ "valor" ];
        }
    
        $this->data[ "navbar" ] = true;
        $this->data[ "modelo" ] = $modelo;
        $this->data[ "titulo" ] = "Ingreso mensual";
        $this->data[ "socio"  ] = $this->data[ "usuario" ];
        load_catalogo( "esquemas", "modelo_codigo = '{$modelo}'");

        $ingreso = [];
        $mes     = date( "Ym" );
        $db      = db_connect();

        while( $mes >= '202408' ){

            $mes_bono = date( "Ym", strtotime( substr( $mes, 0, 4 )."-".substr( $mes, 4, 2 )."-01 + 1 month" ) );
            $sql = "SELECT 
                        c.esquema_codigo as esquema, 
                        SUM( c.cantidad * IF( e.codigo = '118-PROMOS-50', f_get_factor_promos( c.usuario_id, '{$mes}' ), 1 ) ) as cantidad 
                    FROM t_comisiones c 
                    LEFT JOIN t_esquemas e ON e.codigo = c.esquema_codigo 
                    WHERE 
                        SUBSTRING( c.estatus_codigo, 1, 3) > 200 
                        AND e.modelo_codigo = '{$modelo}' 
                        AND e.codigo != '520-SALDO' 
                        AND c.usuario_id = {$this->data[ "usuario" ]->id} 
                        AND ( 
                                (
                                    e.codigo != '530-LIDERAZGO' 
                                    AND CONCAT( substring(c.fecha, 1, 4), substring(c.fecha, 6, 2)) = '{$mes}' 
                                )
                                OR
                                (
                                    e.codigo = '530-LIDERAZGO' 
                                    AND CONCAT( substring(c.fecha, 1, 4), substring(c.fecha, 6, 2)) = '{$mes_bono}' 
                                )
                        )
                        AND e.settings->>'$.periodo' IN ( 'MENSUAL', 'SEMANAL', 'ANUAL') 
                    GROUP BY c.esquema_codigo 
                    order by c.esquema_codigo";
            

            $result  = $db->query( $sql );

            foreach( $result->getResult() as $ms )
                $ingreso[ $mes ][ $ms->esquema ] = $ms->cantidad;

            $mes = date( "Ym", strtotime( substr( $mes, 0, 4 )."-".substr( $mes, 4, 2 )."-01 - 1 month" ) );
        }

        $this->data[ "ingreso" ] = $ingreso;
        echo template( "ingresos/ingreso_mensual", $this->data );
    }


    /**
     * Muestra una tabla con el total de comisiones por pago en cada esquema.
     * Muestra también el total de comisiones pendientes de cada esquema.
     * @param string $modelo El código del modelo que se va a mostrar.
     * @return string HTML con la tabla de pagos y el total del depósito.
     */
    public function depositos( $modelo = null )
    {
        if( !$modelo ){
            $modelo = VARIABLES[ "modelo_default" ][ "valor" ];
        }
    

        $this->data[ "navbar" ] = true;
        $this->data[ "modelo" ] = $modelo;
        $this->data[ "titulo" ] = "Depósitos por pago de comisiones";
        $this->data[ "socio"  ] = $this->data[ "usuario" ];
        $this->data[ "pagos" ]  = $this->data[ "socio" ]->getPagos( $modelo );
        load_catalogo( "esquemas", "modelo_codigo = '{$modelo}'");

        echo template( "ingresos/depositos", $this->data );
    } 

    
    /**
     * Pagos de comisiones con desglose de comisiones por semana y totales.
     * Muestra el total de comisiones, el total de impuestos y el total del depósito.
     * Muestra también el total de comisiones pendientes de cada esquema.
     * @return string HTML con la tabla de pagos y el total del depósito
     */
    public function pagodata()
    {
        $pago    = model( "PagoModel" )->find( $this->request->getPost( "folio" ) );
        $periodo = model( "PeriodoModel" )->find( $pago[ "data" ][ "periodos" ][ "creacion" ] );
        load_catalogo( "esquemas", "modelo_codigo = '{$pago[ "modelo_codigo" ]}'");

         $sql  = "SELECT 
                    min(c.fecha) as fecha, 
                    e.codigo as esquema, 
                    IFNULL( p.data->'$.factor', 2.5 ) as factor, 
                    SUM( c.cantidad ) as cantidad,
                    c.esquema_codigo  , DATE_FORMAT(IF( e.codigo in ( '118-PROMOS-50' , '116-ANIVERSARIO' ), NOW(), c.fecha ), '%x%v') as semana
                from t_pagos p
                left join t_comisiones c ON c.usuario_id = p.usuario_id
                left JOIN t_esquemas e ON e.codigo = c.esquema_codigo
                WHERE p.id = {$pago[ "id" ]} AND c.periodo_codigo = '{$pago[ "data" ][ "periodos" ][ "creacion" ]}'
                GROUP BY c.esquema_codigo, semana
                order by semana asc";

        $db = db_connect();
        $desglose = $db->query( $sql )->getResultArray();

        $actual = "";
        $anteriores = "";

        foreach( $desglose as $d ){
            $titulo = ESQUEMAS[ $d[ "esquema" ] ][ "settings" ][ "titulo" ];

            if( $d[ "esquema" ] == "118-PROMOS-50" ){
                $d[ "cantidad" ] *= $d[ "factor" ];
                $titulo .= " <span class=\"badge bg-pink\">".strtoupper( mes( date( "m", strtotime( $d[ "fecha" ] ) ), 3 ) )."-". date( "Y", strtotime( $d[ "fecha" ] ) )."</span> <span class=\"badge bg-blue\">x{$d[ "factor" ]}</span>";
            }
            elseif( $d[ "esquema" ] == "116-ANIVERSARIO" ){
                $titulo = "<i class=\"fa fa-gift text-purple\"></i> {$titulo} <span class=\"badge bg-teal\">".( date( "Y", strtotime( $d[ "fecha" ] ) ) - 1 )."-". date( "Y", strtotime( $d[ "fecha" ] ) )."</span>";
            }

            if( intval( substr( $periodo[ "codigo" ], 3 ) ) > intval( $d[ "semana" ] ) ){
                $anteriores .= "\n<tr>
                        <td class=\"w-100\">{$titulo} <small>Comisiones pendientes <span class=\"badge bg-red\">".( substr($d[ "semana" ],4,2)."-".substr( $d[ "semana" ],0,4 ) )."</span></small></td>
                        <td class=\"text-end nowrap\"><strong>$".number_format( $d[ "cantidad" ], 2)."</strong></td>
                    </tr>";
            }
            else{
                $actual .= "\n<tr>
                        <td class=\"w-100\">{$titulo}</td>
                        <td class=\"text-end nowrap\"><strong>$".number_format( $d[ "cantidad" ], 2)."</strong></td>
                    </tr>";
            }
        }

        $html = "<table class=\"table w-100 table-striped\">";

        $html .= $anteriores.$actual;

        $html .= "\n<tr class=\"table-secondary\">
                    <td class=\"\">Total de comisiones</td>
                    <td class=\"text-end nowrap\"><strong>$".number_format( $pago[ "data" ][ "cantidades" ][ "subtotal" ], 2)."</strong></td>
                </tr>";


        $html .= "</table><table class=\"table w-100 table-striped\">";;

        $desglose = aplicaImpuestos( $pago[ "data" ][ "cantidades" ][ "subtotal" ], $pago[ "data" ][ "retencion" ], $periodo[ "inicia" ], $pago[ "modelo_codigo" ] );

        foreach( $desglose as $d ){
            if( $d[ "descripcion" ] == "TOTAL" ){
                $total = $d[ "cantidad" ];
            }
            else{
                $html .= "\n<tr>
                            <td class=\"w-100\">{$d[ "descripcion" ]}</td>
                            <td class=\"text-end nowrap\"><strong>$".number_format( $d[ "cantidad" ], 2)."</strong></td>
                        </tr>";
            }
        }

        $html .= "\n<tr class=\"table-secondary\">
                    <td class=\"\">Total depósito</td>
                    <td class=\"text-end nowrap\"><strong>$".number_format( $total, 2)."</strong></td>
                </tr>";


        $html .= "</table>";

        $html .= "<div class=\"alert alert-success text-center mb-0\">Transferencia a ".( $pago[ "modelo_codigo" ] == "50-INVERSION" ? "Wallet Digital" : "cuenta CLABE" )."<br><strong>{$pago[ "clabe" ]}</strong><h1>$".number_format( $total, 2)."</h1></div>";


        return $html;        
    }


    /**
     * Renders a heatmap visualization of daily income for a specific model.
     * 
     * This function generates and outputs an HTML structure that represents
     * a heatmap for daily income data of a socio for a given model. The
     * heatmap displays the income by week and day, highlighting the selected
     * period and showing tooltips with detailed information for each day.
     * 
     * The function retrieves income data per day and iterates through weeks
     * starting from a specified date, rendering columns for each week and
     * boxes for each day within the week. It uses CSS classes to style
     * the heatmap and selected period indicators.
     * 
     * Note: The starting date is hard-coded as "2024-08-12".
     */

    public function heatmap()
    {
        echo "<div id=\"heatmap\" class=\"card-body\">";
        $modelo = MODELOS[ $modelo ];

        $ingresosxdia = $socio->getIngresosPorDia( $modelo[ "codigo" ] );
    
        $inicia = "2024-08-12"; //date( "Y-m-d", strtotime( date( "Y-m-d", strtotime( $socio->historial->registro." + 1 day" ) )." last Monday" ) );
    
        while( $inicia <= date( "Y-m-d") ){
            $fecha  = $inicia;
            $mes    = substr( $fecha, 5, 2 );
            $semana = date("W",  strtotime( $fecha ) );
    
            $selected = ( $periodo[ "codigo" ] == codigo_periodo( $modelo[ "codigo" ], $inicia, $modelo[ "settings" ][ "periodo" ] ) ) ? "selected" : "";
    
            echo "<div class=\"heatmap_columna {$selected}\" periodo=\"".codigo_periodo( $modelo[ "codigo" ], $inicia, $modelo[ "settings" ][ "periodo" ] )."\"><p class=\"m-2\">{$semana}</p>";
            
            for( $d = 0; $d < 7; $d++ ){
                $fecha_next = date( "Y-m-d", strtotime( $fecha." + 1 day" ) );
                $cantidad = $ingresosxdia[ $fecha ] ?? null;
    
                echo "<div class=\"heatmap_dia ".( $mes != substr( $fecha, 5, 2 ) ? "" : "" )."\" data-bs-toggle=\"tooltip\" title=\"{$fecha} : $".number_format( $cantidad, 2 )."\" semana=\"{$semana}\" cantidad=\"{$cantidad}\"></div>";
                
                $fecha = $fecha_next;
            }
    
            $inicia = date( "Y-m-d", strtotime( $inicia." + 1 week" ) );
            echo "</div>";
        }
        
        echo "</div>";
    }


    /**
     * Genera un excel con el ingreso mensual del socio
     * 
     * @return void
     */
    public function excel_ingreso_mensual()
    {
        $db       = db_connect();
        $modelo   = $this->request->getPost( "modelo" );
        $mes      = date( "Ym" );
        $data     = [];
        load_catalogo( "esquemas", "modelo_codigo = '{$modelo}'");

        $mySpreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
        $mySpreadsheet->removeSheetByIndex(0);
        $worksheet = new \PhpOffice\PhpSpreadsheet\Worksheet\Worksheet($mySpreadsheet, "INGRESO MENSUAL");
        $mySpreadsheet->addSheet( $worksheet, 0 );

        $col = 0;
        $e = [];
        $worksheet->setCellValue( chr(65 + $col++)."1", "MES" );

        while( $mes >= '202408' ){
            $sql = "SELECT c.esquema_codigo as esquema,
                    SUM( c.cantidad * IF( e.codigo = '118-PROMOS-50', f_get_factor_promos( c.usuario_id, '{$mes}' ), 1 ) ) as cantidad
                    FROM t_comisiones c
                    LEFT JOIN t_esquemas e ON e.codigo = c.esquema_codigo
                    WHERE SUBSTRING( c.estatus_codigo, 1, 3) > 200 
                    AND e.modelo_codigo = '{$modelo}'
                    AND e.codigo != '520-SALDO'
                    AND c.usuario_id = {$this->data[ "usuario" ]->id}
                    AND CONCAT( substring(c.fecha, 1, 4), substring(c.fecha, 6, 2)) = '{$mes}'
                    AND e.settings->>'$.periodo' IN ( 'MENSUAL', 'SEMANAL', 'ANUAL')
                    GROUP BY c.esquema_codigo
                    order by c.esquema_codigo";

            $result  = $db->query( $sql );

            foreach( $result->getResult() as $ms ){
                $data[ "meses" ][ $mes ][ "esquemas" ][ $ms->esquema ] = $ms->cantidad;
                $e[ $ms->esquema ] = true;
            }

            $mes = date( "Ym", strtotime( substr( $mes, 0, 4 )."-".substr( $mes, 4, 2 )."-01 - 1 month" ) );
        }

        $e = array_keys( $e );
        asort( $e );

        foreach( $e as $esquema ){
            $worksheet->setCellValue( chr(65 + $col++)."1", mb_strtoupper( ESQUEMAS[ $esquema ][ "settings" ][ "titulo" ] ) );
        }

        $worksheet->setCellValue( chr(65 + $col++)."1", "TOTAL" );

        $mes = date( "Ym" );
        $row = 1;
        while( $mes >= '202408' ){
            $row++;
            $col  = 0;
            $suma = 0;
            $worksheet->setCellValue( chr(65 + $col++).$row, substr( $mes, 0, 4 )." ".strtoupper( mes( substr( $mes, 4, 2 ) ) ) );

            foreach( $e as $esquema ){
                $valor = $data[ "meses" ][ $mes ][ "esquemas" ][ $esquema ] ?? 0;
                $suma += $valor;
                $worksheet->setCellValue( chr(65 + $col++).$row, $valor );
            }

            $worksheet->setCellValue( chr(65 + $col++).$row, $suma );
                
            $mes = date( "Ym", strtotime( substr( $mes, 0, 4 )."-".substr( $mes, 4, 2 )."-01 - 1 month" ) );
        }

        $col--;

        $worksheet->getStyle( "A1:".chr(65 + $col)."1" )->getFont()->getColor()->setARGB('ffffff');
        $worksheet->getStyle( "B2:".chr(65 + $col ).$row )->getNumberFormat()->setFormatCode( "$#,##0.00" );

        $worksheet->getStyle( "B1:".chr(65 + $col - 1 )."1" )->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setARGB('192b5a');
        $worksheet->getStyle( "A1" )->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setARGB('009779');
        $worksheet->getStyle( chr(65 + $col)."1" )->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setARGB('009779');
        $worksheet->getStyle( "A2:A".$row )->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setARGB('c1ebd7');
        $worksheet->getStyle( chr(65 + $col)."2:".chr(65 + $col).$row )->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setARGB('c1ebd7');

        foreach( $worksheet->getColumnIterator() as $column ){
            $worksheet->getColumnDimension( $column->getColumnIndex() )->setAutoSize( true );
        }

        // BITACORA descarga excel de corte
        bitacora( 72, $this->data[ "usuario" ]->id, [
            "modelo" => $modelo
        ] );

        $path = "data/excel/ingreso_mensual";
        if( !is_dir( $path ) ) mkdir( $path, 0755, true );

        echo $file = $path."/IngresoMensual_{$this->data[ "usuario" ]->id}_".substr( $modelo, 3 )."_".date( "Y-m-d" ).".xlsx";

        $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($mySpreadsheet);
        $writer->save( $file );
    }

    
    /**
     * Genera un excel con el ingreso mensual del socio
     * 
     * @return void
     */
    public function excel_pago_comisiones()
    {
        $db       = db_connect();
        $modelo   = $this->request->getPost( "modelo" );
        $mes      = date( "Ym" );
        $data     = [];
        load_catalogo( "esquemas", "modelo_codigo = '{$modelo}'");

        $mySpreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
        $mySpreadsheet->removeSheetByIndex(0);
        $worksheet = new \PhpOffice\PhpSpreadsheet\Worksheet\Worksheet($mySpreadsheet, "INGRESO MENSUAL");
        $mySpreadsheet->addSheet( $worksheet, 0 );

        $col = 0;
        $row = 1;
        $d = [];
        $e = [];
        $worksheet->setCellValue( chr(65 + $col++)."1", "SEMANA" );

        $pagos = $this->data[ "usuario" ]->getPagos( $modelo );
               
        foreach( $pagos as $pago ){

            $sql  = "SELECT 
                min(c.fecha) as fecha, 
                e.codigo as esquema, 
                IFNULL( p.data->'$.factor', 2.5 ) as factor, 
                SUM( c.cantidad ) as cantidad,
                c.esquema_codigo  , 
                '".periodo( $pago[ "periodo" ] )."' as semana,
                p.data->>'$.cantidades.subtotal' as subtotal
            from t_pagos p
            left join t_comisiones c ON c.usuario_id = p.usuario_id
            left JOIN t_esquemas e ON e.codigo = c.esquema_codigo
            WHERE p.id = {$pago[ "folio" ]} AND c.periodo_codigo = '{$pago[ "periodo" ]}'
            GROUP BY c.esquema_codigo, semana
            order by semana asc, c.esquema_codigo asc";

            $result = $db->query( $sql );

            foreach( $result->getResult() as $ms ){

                $data[ $pago[ "folio" ] ][ "detalles" ] = $ms;
                $data[ $pago[ "folio" ] ][ "esquemas" ][ $ms->esquema ] = $ms->cantidad;

                $e[ $ms->esquema ] = true;
            }

            $desglose = aplicaImpuestos( $pago[ "total" ], $pago[ "impuestos" ], $pago[ "periodo" ] );

            foreach( $desglose as $de ){
                $data[ $pago[ "folio" ] ][ "desglose" ][ $de[ "descripcion" ] ] = $de[ "cantidad" ];

                $d[ $de[ "descripcion" ] ] = true;
            }
        }

        // revisar que existan todos los datos

        foreach( $data as $k => $pago ){
            if( !isset( $pago[ "detalles" ] ) ){
                $sql = "SELECT 
                    min(c.fecha) as fecha, 
                    e.codigo as esquema, 
                    IFNULL( p.data->'$.factor', 2.5 ) as factor, 
                    SUM( c.cantidad ) as cantidad,
                    '34-2024' as semana,
                    p.data->>'$.cantidades.subtotal' as subtotal 
                    from t_pagos p
                    LEFT JOIN t_periodos r ON r.codigo = p.data->>'$.periodos.creacion'
                    left join t_comisiones c ON c.usuario_id = p.usuario_id AND c.fecha BETWEEN r.inicia AND r.termina 
                    LEFT JOIN t_esquemas e ON e.codigo = c.esquema_codigo
                    WHERE p.id = {$k} AND e.modelo_codigo = r.modelo_codigo
                    GROUP BY c.esquema_codigo, semana
                    order by semana asc, c.esquema_codigo asc";

                $ms = $db->query( $sql )->getRow();

                $data[ $k ][ "detalles" ] = $ms;
                $data[ $k ][ "esquemas" ][ $ms->esquema ] = $ms->cantidad;

                $e[ $ms->esquema ] = true;
            }
        }


        $e = array_keys( $e );
        foreach( $e as $esquema ){
            $worksheet->setCellValue( chr(65 + $col++)."1", mb_strtoupper( ESQUEMAS[ $esquema ][ "settings" ][ "titulo" ] ) );
        }

        $worksheet->setCellValue( chr(65 + $col++)."1", "SUBTOTAL" );

        $d = array_keys( $d );
        foreach( $d as $desglose ){
            $worksheet->setCellValue( chr(65 + $col++)."1", mb_strtoupper( $desglose ) );
        }

        $row = 1;
        $coltemp = sizeof( $e ) + 1;

        foreach( $data as $folio => $pago ){
            if( !isset( $pago[ "detalles" ] ) ){
                echo "<pre>".print_r($folio,1);
                die();
            }
            $row++;
            $col  = 1;
            $suma = 0;
            $worksheet->setCellValue( "A".( $row ), substr( $pago[ "detalles" ]->semana, 3, 4)."-".substr( $pago[ "detalles" ]->semana , 0, 2 ) );

            foreach( $e as $esquema ){
                $valor = $pago[ "esquemas" ][ $esquema ] ?? 0;
                $suma += $valor;
                $worksheet->setCellValue( chr(65 + $col++).$row, $valor );
            }

            $worksheet->setCellValue( chr(65 + $col++).$row, $pago[ "detalles" ]->subtotal ); 

            foreach( $d as $desglose ){
                $valor = $pago[ "desglose" ][ $desglose ] ?? 0;
                $worksheet->setCellValue( chr(65 + $col++).$row, $valor );
            }


        }

        $col--;

        $worksheet->getStyle( "A1:".chr(65 + $col)."1" )->getFont()->getColor()->setARGB('ffffff');
        $worksheet->getStyle( "B2:".chr(65 + $col ).$row )->getNumberFormat()->setFormatCode( "$#,##0.00" );

        $worksheet->getStyle( "B1:".chr(65 + $col - 1 )."1" )->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setARGB('192b5a');
        $worksheet->getStyle( "A1" )->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setARGB('009779');
        $worksheet->getStyle( chr(65 + $coltemp)."1" )->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setARGB('009779');
        $worksheet->getStyle( chr(65 + $col)."1" )->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setARGB('009779');

        $worksheet->getStyle( "A2:A".$row )->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setARGB('c1ebd7');

        $worksheet->getStyle( chr(65 + $col)."2:".chr(65 + $col).$row )->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setARGB('c1ebd7');
        $worksheet->getStyle( chr(65 + $coltemp)."2:".chr(65 + $coltemp).$row )->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setARGB('c1ebd7');

        foreach( $worksheet->getColumnIterator() as $column ){
            $worksheet->getColumnDimension( $column->getColumnIndex() )->setAutoSize( true );
        }

        // BITACORA descarga excel de corte
        bitacora( 73, $this->data[ "usuario" ]->id, [
            "modelo" => $modelo
        ] );

        $path = "data/excel/ingreso_mensual";
        if( !is_dir( $path ) ) mkdir( $path, 0755, true );

        echo $file = $path."/PagosRecibidos_{$this->data[ "usuario" ]->id}_".substr( $modelo, 3 )."_".date( "Y-m-d" ).".xlsx";

        $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($mySpreadsheet);
        $writer->save( $file );
    }    
}

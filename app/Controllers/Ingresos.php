<?php

namespace App\Controllers;

class Ingresos extends BaseController
{
    function __construct() {
        $this->data[ "menu" ] = "ingresos";
    }

    public function balance( $modelo = null, $periodo = null ){

        if( !$modelo ){
            $modelo = VARIABLES[ "modelo_default" ][ "valor" ];
        }

        if( !$periodo ){
            $periodo = codigo_periodo( $modelo );
        }

        $this->data[ "periodo" ] = model( "PeriodoModel" )->find( $periodo );

        if( null == $this->data[ "periodo" ] ){

            $dto = new \DateTime();
            $dto->setISODate( substr( $periodo, 3, 4 ), substr( $periodo, 7, 2 ) );
            $inicia  = $dto->format('Y-m-d');
            $dto->modify('+6 days');
            $termina = $dto->format('Y-m-d');
            $db      = db_connect();
            $result  = $db->query( "INSERT IGNORE INTO t_periodos VALUES ( '{$periodo}', '250-EN-PROCESO', '{$modelo}', 'SEMANAL', '{$inicia}', '{$termina}', JSON_OBJECT() )" );

            $this->data[ "periodo" ] = model( "PeriodoModel" )->find( $periodo );
        }

        $this->data[ "navbar" ]  = true;
        $this->data[ "modelo" ]  = $modelo;
        $this->data[ "titulo" ]  = "Ingresos por periodo <span class=\"badge bg-marine\">".substr($periodo, 7, 2)."-".substr($periodo, 3, 4)."</span> <span style=\"font-size:16px\">".estatus( $this->data[ "periodo" ][ "estatus_codigo" ] )."</span>";
        $this->data[ "socio"  ]  = $this->data[ "usuario" ];
        $this->data[ "comisiones" ] = $this->data[ "socio" ]->getComisiones( $periodo );
        load_catalogo( "esquemas", "modelo_codigo = '{$modelo}'");

        echo template( "ingresos/balance", $this->data );
    }


    public function ingreso_mensual( $modelo = null ){
        if( !$modelo ){
            $modelo = VARIABLES[ "modelo_default" ][ "valor" ];
        }
    
        $this->data[ "navbar" ] = true;
        $this->data[ "modelo" ] = $modelo;
        $this->data[ "titulo" ] = "Ingreso mensual";
        $this->data[ "socio"  ] = $this->data[ "usuario" ];
        $this->data[ "pagos" ]  = $this->data[ "socio" ]->getPagos( $modelo );
        load_catalogo( "esquemas", "modelo_codigo = '{$modelo}'");

        $ingreso = [];
        $mes     = date( "Ym" );
        $db      = db_connect();

        while( $mes >= '202408' ){
            $sql = "SELECT c.esquema_codigo as esquema,
                    SUM( c.cantidad * IF( e.codigo = '118-PROMOS-50', f_get_factor_promos( c.usuario_id, '{$mes}' ), 1 ) ) as cantidad
                    FROM t_comisiones c
                    LEFT JOIN t_esquemas e ON e.codigo = c.esquema_codigo
                    WHERE SUBSTRING( c.estatus_codigo, 1, 3) > 200 
                    AND e.modelo_codigo = '{$modelo}'
                    AND c.usuario_id = {$this->data[ "usuario" ]->id}
                    AND CONCAT( substring(c.fecha, 1, 4), substring(c.fecha, 6, 2)) = '{$mes}'
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

        $desglose = aplicaImpuestos( $pago[ "data" ][ "cantidades" ][ "subtotal" ], $pago[ "data" ][ "retencion" ], $periodo[ "inicia" ] );

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

        $html .= "<div class=\"alert alert-success text-center mb-0\">Transferencia a cuenta CLABE {$pago[ "clabe" ]}<h1>$".number_format( $total, 2)."</h1></div>";


        return $html;        
    }


    public function excel_ingreso_mensual()
    {
        $db       = db_connect();
        $modelo = $this->request->getPost( "modelo" );
        $columnas = [];
        $mes      = date( "Ym" );
     
        while( $mes >= '202408' ){
            $sql = "SELECT c.esquema_codigo as esquema,
                    SUM( c.cantidad * IF( e.codigo = '118-PROMOS-50', f_get_factor_promos( c.usuario_id, '{$mes}' ), 1 ) ) as cantidad
                    FROM t_comisiones c
                    LEFT JOIN t_esquemas e ON e.codigo = c.esquema_codigo
                    WHERE SUBSTRING( c.estatus_codigo, 1, 3) > 200 
                    AND e.modelo_codigo = '{$modelo}'
                    AND c.usuario_id = {$this->data[ "usuario" ]->id}
                    AND CONCAT( substring(c.fecha, 1, 4), substring(c.fecha, 6, 2)) = '{$mes}'
                    AND e.settings->>'$.periodo' IN ( 'MENSUAL', 'SEMANAL', 'ANUAL')
                    GROUP BY c.esquema_codigo
                    order by c.esquema_codigo";

            $result  = $db->query( $sql );

            foreach( $result->getResult() as $ms )
                $ingreso[ $mes ][ $ms->esquema ] = $ms->cantidad;
                $columnas[] = $ms->esquema;

            $mes = date( "Ym", strtotime( substr( $mes, 0, 4 )."-".substr( $mes, 4, 2 )."-01 - 1 month" ) );
        }
        
        foreach( $pagos as $pago ){
            
            $pago[ "p_data" ] = json_decode( $pago[ "p_data" ], 1 );
            $pago[ "u_data" ] = json_decode( $pago[ "u_data" ], 1 );
            $pago[ "verificado" ] = json_decode( $pago[ "verificado" ], 1 );
            
            $k_dia_inicia  = date( "d", strtotime( $periodo[ "inicia" ] ) );
            $k_mes_inicia  = mes( date( "m", strtotime( $periodo[ "inicia" ] ) ), 3 );
            $k_dia_termina = date( "d", strtotime( $periodo[ "termina" ] ) );
            $k_mes_termina = mes( date( "m", strtotime( $periodo[ "termina" ] ) ), 3 );

            $concepto = substr( $periodo[ "modelo_codigo" ], 3, 4 )." ".periodo( $periodo[ "codigo" ] )." ".strtoupper( "{$k_dia_inicia} {$k_mes_inicia}-{$k_dia_termina} {$k_mes_termina}" ); 

            if( $pago[ "p_data" ][ "retencion" ] == 2 ){

                $neto = $pago[ "p_data" ][ "cantidades" ][ "subtotal" ];
                $importe = $neto / 1.16;

                $sheetData[ 0 ][] = [
                    $pago[ "pago_id" ],
                    "80161701",
                    "PROMOCIÓN, DISPOSICIÓN Y PUBLICIDAD VENTAS BENELEIT",
                    $pago[ "usuario_id" ],
                    $pago[ "u_data" ][ "nombre" ]." ".implode( " ", $pago[ "u_data" ][ "apellidos" ] ),
                    //$pago[ "u_data" ][ "sat" ][ "rfc" ] ?? "",
                    strval( $pago[ "clabe" ] ),
                    30,
                    "PAGO SEMANA ".periodo( $periodo[ "codigo" ] ),
                    $promo = $importe * .1, // promo
                    $neto, // neto
                    $importe, // importe
                    $sub = $importe - $promo,  // subtotal
                    $iva = $sub * .16, // iva
                    $ret = $sub * 0.0125, //( retencion)
                    $neto - $promo + $iva - $ret,
                    $concepto
                ];
            }
            elseif( $pago[ "p_data" ][ "retencion" ] == 1 ){
                $sheetData[ 1 ][] = [
                    $pago[ "pago_id" ],
                    $pago[ "usuario_id" ],
                    $pago[ "u_data" ][ "nombre" ]." ".implode( " ", $pago[ "u_data" ][ "apellidos" ] ),
                    //$pago[ "u_data" ][ "sat" ][ "rfc" ] ?? "",
                    strval( $pago[ "clabe" ] ),
                    30,
                    "PAGO SEMANA ".periodo( $periodo[ "codigo" ] ),
                    $subt = $pago[ "p_data" ][ "cantidades" ][ "subtotal" ] / 1.16, // subtotal
                    $pago[ "p_data" ][ "cantidades" ][ "subtotal" ], // importe
                    $rete = $subt * 0.1066, // retencion
                    $iva  = $subt * 0.16,  // iva
                    $subt - $rete + $iva, // total
                    $concepto,
                    "PAGO DE COMISIONES"
                ];
            }
            elseif( $pago[ "p_data" ][ "retencion" ] == 0 ){
                $sheetData[ 2 ][] = [
                    $pago[ "pago_id" ],
                    $pago[ "usuario_id" ],
                    $pago[ "u_data" ][ "nombre" ]." ".implode( " ", $pago[ "u_data" ][ "apellidos" ] ),
                    strval( $pago[ "clabe" ] ),
                    30,
                    "PAGO SEMANA ".periodo( $periodo[ "codigo" ] ),
                    $pago[ "banco" ],
                    $pago[ "p_data" ][ "cantidades" ][ "subtotal" ], 
                    $pago[ "p_data" ][ "cantidades" ][ "isr" ],
                    $pago[ "p_data" ][ "cantidades" ][ "subtotal" ] - $pago[ "p_data" ][ "cantidades" ][ "isr" ],
                    $concepto
                ];
            }            
        }

        $data = $periodo[ "data" ];
        $data[ "contador" ] = intval( $data[ "contador" ] ?? 0 ) + 1;

        $time = str_pad( $data[ "contador" ], 2, "0", STR_PAD_LEFT );

        $periodo[ "data" ] = $data;
        model( "PeriodoModel" )->save( $periodo );

        $mySpreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
        $mySpreadsheet->removeSheetByIndex(0);

        $worksheet = [ 
            2 => new \PhpOffice\PhpSpreadsheet\Worksheet\Worksheet($mySpreadsheet, "RET ISR"),
            1 => new \PhpOffice\PhpSpreadsheet\Worksheet\Worksheet($mySpreadsheet, "NO RET"),
            0 => new \PhpOffice\PhpSpreadsheet\Worksheet\Worksheet($mySpreadsheet, "VENTA")
        ];

        $mySpreadsheet->addSheet( $worksheet[ 0 ], 0 );
        $mySpreadsheet->addSheet( $worksheet[ 1 ], 0 );
        $mySpreadsheet->addSheet( $worksheet[ 2 ], 0 );

      /*   $worksheet[ 0 ]->fromArray( $sheetData[ 0 ] );
        $worksheet[ 1 ]->fromArray( $sheetData[ 1 ] );
        $worksheet[ 2 ]->fromArray( $sheetData[ 2 ] ); */

        foreach( $sheetData as $k => $s ){
            $row = 0;
            foreach( $s as $bloque ){
                $col = 0;
                $row++;
                foreach( $bloque as $dato){
                    if( strlen( $dato ) == 18 ){
                        $worksheet[ $k ]->setCellValueExplicit( chr(65 + $col++).$row, $dato, \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);
                    }
                    else{
                        $worksheet[ $k ]->setCellValue( chr(65 + $col++).$row, $dato );
                    }
                }
            }
        }

        $worksheet[ 0 ]->getStyle( "F" )->getNumberFormat()->setFormatCode( "#" );
        $worksheet[ 0 ]->getStyle( "A:D" )->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
        $worksheet[ 0 ]->getStyle( "F:H" )->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
        $worksheet[ 0 ]->getStyle( "I:O" )->getNumberFormat()->setFormatCode( "$#,##0.00" );
        $worksheet[ 0 ]->getStyle( "A1:P1" )->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setARGB('009779');
        $worksheet[ 0 ]->getStyle( "I1:O1" )->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setARGB('192b5a');
        $worksheet[ 0 ]->getStyle( "A1:P1" )->getFont()->getColor()->setARGB('ffffff');

        $worksheet[ 1 ]->getStyle( "D" )->getNumberFormat()->setFormatCode( "#" );
        $worksheet[ 1 ]->getStyle( "A:B" )->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
        $worksheet[ 1 ]->getStyle( "D:G" )->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
        $worksheet[ 1 ]->getStyle( "G:K" )->getNumberFormat()->setFormatCode( "$#,##0.00" );
        $worksheet[ 1 ]->getStyle( "A1:M1" )->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setARGB('009779');
        $worksheet[ 1 ]->getStyle( "G1:K1" )->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setARGB('192b5a');
        $worksheet[ 1 ]->getStyle( "A1:M1" )->getFont()->getColor()->setARGB('ffffff');

        $worksheet[ 2 ]->getStyle( "D" )->getNumberFormat()->setFormatCode( "#" );
        $worksheet[ 2 ]->getStyle( "A:B" )->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
        $worksheet[ 2 ]->getStyle( "D:G" )->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
        $worksheet[ 2 ]->getStyle( "H:J" )->getNumberFormat()->setFormatCode( "$#,##0.00" );
        $worksheet[ 2 ]->getStyle( "A1:K1" )->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setARGB('009779');
        $worksheet[ 2 ]->getStyle( "H1:J1" )->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setARGB('192b5a');
        $worksheet[ 2 ]->getStyle( "A1:K1" )->getFont()->getColor()->setARGB('ffffff');

        foreach( $worksheet as $k => $ws ){
            foreach( $ws->getColumnIterator() as $column ){
                $worksheet[ $k ]->getColumnDimension( $column->getColumnIndex() )->setAutoSize( true );
            }
        } 

        // BITACORA descarga excel de corte
        bitacora( 46, $this->data[ "usuario" ]->id, [
            "periodo" => $this->request->getPost( "periodo" ),
            "time" => $time
        ] );

        $path = "assets/archivo/corte/{$periodo[ "modelo_codigo" ]}";
        if( !is_dir( $path ) ) mkdir( $path, 0755, true );

        echo $file = $path."/{$periodo[ "modelo_codigo" ]}_".periodo( $periodo[ "codigo" ] )."_{$time}.xlsx";

        $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($mySpreadsheet);
        $writer->save( $file );
    }
}

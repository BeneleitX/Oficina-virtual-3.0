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
                    min(c.fecha), 
                    e.codigo as esquema, 
                    IFNULL( p.data->'$.factor', 2.5 ) as factor, 
                    SUM( c.cantidad ) as cantidad,
                    c.esquema_codigo
                from t_pagos p
                left join t_comisiones c ON c.usuario_id = p.usuario_id
                left JOIN t_esquemas e ON e.codigo = c.esquema_codigo
                WHERE p.id = {$pago[ "id" ]} AND c.periodo_codigo = '{$pago[ "data" ][ "periodos" ][ "creacion" ]}'
                GROUP BY c.esquema_codigo";

        $db = db_connect();
        $desglose = $db->query( $sql )->getResultArray();

        $html = "<div class=\"alert alert-info text-center\">Del ".fecha( $periodo[ "inicia" ] )." al ".fecha( $periodo[ "termina" ] )."</div><table class=\"table w-100 table-striped\">";

        foreach( $desglose as $d ){
            $titulo = ESQUEMAS[ $d[ "esquema" ] ][ "settings" ][ "titulo" ];

            if($d[ "esquema" ] == "118-PROMOS-50" ){
                $d[ "cantidad" ] *= $d[ "factor" ];
                $titulo .= " <span class=\"badge bg-pink\">".strtoupper( mes( date( "m", strtotime( $d[ "fecha" ] ) ), 3 ) )."-". date( "Y", strtotime( $d[ "fecha" ] ) )."</span> <span class=\"badge bg-blue\">x{$d[ "factor" ]}</span>";
            }

            $html .= "\n<tr>
                        <td class=\"w-100\">{$titulo}</td>
                        <td class=\"text-end nowrap\"><strong>$".number_format( $d[ "cantidad" ], 2)."</strong></td>
                    </tr>";
        }

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

}

<?php 



function calcula_semilla( $i ){
    return $i[ "cantidad" ];
}


function get_fecha_inversion( $fecha ){

    // Calcula la fecha del lunes inicial de la inversión

    $date = new DateTime( $fecha );
    $date->modify( "next saturday + 2 days" );

    return $date->format( "Y-m-d" );
}


function get_fecha_cierre( $fecha ){

    // Calcula la fecha del lunes inicial de la inversión

    $date = new DateTime( $fecha );
    $date->modify( "first day of this month" );
    $date->modify( "+ 25 month" );
    $date->modify( "- 1 day" );

    return $date->format( "Y-m-d" );
}

function get_fecha_dias( $inicia, $termina = null ){

    $dias = 0;

    if( !$termina ){
        $date = new DateTime( $inicia );
        $date->modify( "last day of this month" );
        $termina = $date->format( "Y-m-d" );        
    }

    $dias = (new DateTime($termina))->diff(new DateTime($inicia))->days + 1;

    return $dias;
}


function genera_meses( $pedido, $i, $producto = null ){

    if( !$producto ){
        $producto = model( "ProductoModel" )->find( array_keys( $pedido[ "promociones"][ "510-SEMILLA" ][ "productos" ] ) )[ 0 ];
    }

    $f_i = get_fecha_inversion( $pedido[ "fechas" ][ "pagado" ] );
    $rts = model( "RetiroModel" )->where( "SUBSTRING( estatus_codigo, 1, 3 ) > 200 AND inversion_id = {$i}" )->findAll();

    $date = new \DateTime( intval( date( "d", strtotime( $f_i ) ) ) == 1 ? $pedido[ "fechas" ][ "pagado" ] : $f_i );

    for( $a = 0; $a <= 24; $a++ ){
        if( $a ){
            $date->modify( "first day of this month" );
            $date->modify( "+ 1 month" );
        }

        $retiros_mes = 0;
        foreach( $rts as $rt ){
            if( $rt[ "fechas" ][ "mes" ] == $date->format( "Ym" ) ){
                $retiros_mes += $rt[ "cantidad" ];
            }
        }

        $inicia_mes      = $date->format( "d" );
        $inicia_mes_f    = $date->format( "Y-m-d" );

        $date->modify( "last day of this month" );
        
        $cantidad = $pedido[ "data" ][ "total" ] + ( $meses[ $a - 1 ][ "rendimiento_mes" ] ?? 0 ) + ( $meses[ $a - 1 ][ "compuesto" ] ?? 0 ) - ( $meses[ $a - 1 ][ "retiros" ] ?? 0 );

        $dias_en_mes     = intval( $date->format( "d" ) );
        $termina_mes_f   = $date->format( "Y-m-d" );
        $dias_parcial    = intval( date( "d", strtotime( $f_i ) ) ) == 1 ? 0 : $dias_en_mes - $inicia_mes + 1;

        
        $rendimiento_mes = floor( $cantidad * $producto->data->porcentaje ) / 100;
        $rendimiento_dia = floor( $rendimiento_mes / $dias_en_mes * 100 ) / 100; 

        if( $dias_parcial < $dias_en_mes ){
            $rendimiento_mes = floor( $dias_parcial * $rendimiento_dia * 100 ) / 100;
        }

        $meses[ $a ] = [
            "Ym"              => $date->format( "Ym" ),
            "Porcentaje"      => $producto->data->porcentaje,
            "semilla"         => $pedido[ "data" ][ "total" ],
            "compuesto"       => ( $meses[ $a - 1 ][ "rendimiento_mes" ] ?? 0 ) + ( $meses[ $a - 1 ][ "compuesto" ] ?? 0 ) - ( $meses[ $a - 1 ][ "retiros" ] ?? 0 ),
            "dias_en_mes"     => $dias_en_mes,
            "dias_parcial"    => $dias_parcial,
            "retiros"         => $retiros_mes,
            "rendimiento_dia" => $rendimiento_dia,
            "rendimiento_mes" => $rendimiento_mes,
            "inicia"          => $inicia_mes_f,
            "termina"         => $termina_mes_f
        ];
    }

    return $meses;
}


function update_fecha_inversion( $i, $paquete ){

 /*    // Fecha del LUNES de inicio de inversión mes 0

    if( !isset( $i[ "fechas" ][ "inversion" ] ) ){
        $i[ "fechas" ][ "inversion" ] = get_fecha_inversion( $i[ "fechas" ][ "pagado" ] );
    }

    // Meses

    if( !isset( $i[ "extras" ][ "meses" ] ) ){
        $meses = [];

        // mes 0

        $ym = date( "Ym", strtotime( intval( date( "d", strtotime( $i[ "fechas" ][ "inversion" ] ) ) ) == 1 ? $i[ "fechas" ][ "pagado" ] : $i[ "fechas" ][ "inversion" ] ) );
        $meses[ 0 ] = [ 
            "Ym"              => date( "Ym", strtotime( $i[ "fechas" ][ "inversion" ] ) ),
            "Porcentaje"      => $paquete->data->porcentaje,
            "semilla"         => $i[ "cantidad" ],
            "dias"            => get_fecha_dias( $ym ),
            "rendimiento_mes" => 0,
            "rendimiento_dia" => 0,
            "inicia"          => intval( date( "d", strtotime( $i[ "fechas" ][ "inversion" ] ) ) ) == 1 ? ,
            "termina"         => 
        ];

        // calcular cierre de inversión 24 meses
       
        $date = new DateTime( substr( $meses[ 0 ][ "Ym" ], 0, 4 )."-".substr( $meses[ 0 ][ "Ym" ], 4, 2 )."-01" );

        for( $a = 1; $a <= 12; $a++ ){
            $date->modify( "+ 1 month" );

            $meses[ $a ] = [
                "Ym"              => $date->format( "Ym" ),
                "Porcentaje"      => null,
                "semilla"         => null,
                "dias"            => get_fecha_dias( $date->format( "Y-m-d" ) ),
                "rendimiento_mes" => null,
                "rendimiento_dia" => null
            ];
        }

        $i[ "extras" ][ "meses" ]  = $meses;
    }
    
    // dias utiles del mes 0
    if( !isset( $i[ "fechas" ][ "cierre" ] ) ){
        $i[ "fechas" ][ "cierre" ] = get_fecha_cierre( $i[ "fechas" ][ "inversion" ] );
    }


    if( !isset( $i[ "extras" ][ "dias" ] ) ){
        $cierre = get_fecha_cierre( $i[ "fechas" ][ "pagado" ], $i[ "producto_codigo" ], $paquete );
        $i[ "extras" ][ "dias" ] = get_fecha_dias( $i[ "fechas" ][ "inversion" ], $cierre );
    }

    model( "inversionModel" )->save( $i );  

    return $i[ "fechas" ]; */
}



function rendimiento_diario( $cantidad, $porcentaje, $mes ){

    $dias = cal_days_in_month( CAL_GREGORIAN, substr( $mes, 4, 2), date( "Y" ) );
    $rendimiento = $cantidad * ( $porcentaje / 100 ) / $dias ;
    return $rendimiento;
}


function balance_inversion( $i ){
    
    $respuesta = [
        "semilla" => 0,
        "retiros" => 0,
        "rendimiento" => 0,
        "total" => 0,
        "full" => 0
    ];

    for( $a = 0; $a < 24; $a++ ){
        $j = $i[ "extras" ][ "meses" ][ $a ];
        
        if( $j[ "Ym" ] < date( "Ym" ) ){
            if( $j[ "dias_en_mes" ] ){
                
                if( !isset( $j[ "check" ] ) ){
                    $i[ "extras" ][ "meses" ][ $a ] = check_inversion( $i, $a );
                    model( "InversionModel" )->save( $i );
                }

                $respuesta[ "rendimiento" ] += $j[ "rendimiento_mes" ];
                $respuesta[ "retiros" ] += $j[ "retiros" ];
                $respuesta[ "full" ] += $j[ "rendimiento_mes" ];
            }
        }
        elseif( $j[ "Ym" ] == date( "Ym" ) ){
            $respuesta[ "semilla" ] = $j[ "semilla" ];
            $dias = date( "d" ) - ( $j[ "dias_en_mes" ] - $j[ "dias_parcial" ] );

            if( $dias < 0 ){
                $dias = 0;
            }

            $respuesta[ "rendimiento_mes" ] = $j[ "rendimiento_mes" ];
            $respuesta[ "rendimiento" ] += ( $j[ "rendimiento_dia" ] * $dias );
            $respuesta[ "full" ] += $j[ "rendimiento_mes" ];
        }
    }

    $respuesta[ "total" ] = $respuesta[ "semilla" ] + $respuesta[ "rendimiento" ] - $respuesta[ "retiros" ];

    return $respuesta;
}


function check_inversion( $i, $a ){
    $mes = $i[ "extras" ][ "meses" ][ $a ];
   
    if( !$mes[ "semilla" ] ){
        $mes[ "semilla" ] = $i[ "extras" ][ "meses" ][ 0 ][ "semilla" ];
    }

    return $mes;
}
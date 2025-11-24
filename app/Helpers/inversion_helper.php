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


function get_semilla_retirada( $i ){
    $db = db_connect();

    
    $sql = "SELECT
            count(*) as semilla_retirada
            from t_inversiones i
            join t_inversiones o on o.id = {$i}
            where i.id != o.id
            and i.producto_codigo = o.producto_codigo
            and i.estatus_codigo = '625-ACTIVA'
            and i.extras->>'$.semilla_retirada' > 0
            and i.usuario_id = o.usuario_id
            and i.fechas->>'$.cierre' > cast( now() as date )
            and i.id != 536";
  
    return $db->query( $sql )->getRow()->semilla_retirada;    
}


function genera_meses( $pedido, $i, $producto = null ){

    if( !$producto ){
        $producto = model( "ProductoModel" )->find( array_keys( $pedido[ "promociones"][ "510-SEMILLA" ][ "productos" ] ) )[ 0 ];
    }

    // calculamos fecha de inicio de inversion
    $f_i  = get_fecha_inversion( $pedido[ "fechas" ][ "pagado" ] );
    $semilla_retirada = get_semilla_retirada( $i );

    // Buscamos retiros aplicados a rendimiento
    $rts  = model( "RetiroModel" )->where( "SUBSTRING( estatus_codigo, 1, 3 ) > 200 AND json_unquote( json_extract( fechas, '$.mes' ) ) >= '".date( "%Y%m", strtotime( $pedido[ "fechas" ][ "pagado" ] ) )."' AND usuario_id = {$pedido[ "usuario_id" ]}" )->findAll();

    // seleccionamos la fecha para el mes CERO (entre fecha pago y fecha inversion)
    // si caen en el mismo mes:  m_c = mes 0
    // si caen en diferente mes: m_c = mes -1
    // si caen en diferente mes pero f_i cae en día 1: m_c = mes 0

    if( date( "d", strtotime( $f_i ) ) == 1 ){
        $date = new \DateTime( $pedido[ "fechas" ][ "pagado" ] );
    }
    else{
        $date = new \DateTime( $f_i );
    }

    $meses  = [];
    $factor = 1;
    $i_semilla = 0;

    for( $a = 0; $a < 25; $a++ ){
        if( $a ){
            $date->modify( "first day of this month" );
            $date->modify( "+ 1 month" );
        }
 
        $retiros_mes = 0;
        $c_semilla   = 0;
        $r_semilla   = false;

        foreach( $rts as $rt ){
            if( $rt[ "fechas" ][ "mes" ] == $date->format( "Ym" ) ){ 
                if( $rt[ "inversion_id" ] == $i ){
                
                    if( in_array( $rt[ "tipo" ], [ "TOTAL", "PARCIAL", "MENSUAL" ]) ){
                        $retiros_mes += $rt[ "cantidad" ];
                    }
                    elseif( in_array( $rt[ "tipo" ], [ "STOTAL", "SPARCIAL" ]) ){
                        $c_semilla += $rt[ "cantidad" ];
                    }
                }

                if( in_array( $rt[ "tipo" ], [ "STOTAL", "SPARCIAL" ]) ){
                    $r_semilla = true;

                    if( $i_semilla == false ){
                        $i_semilla = intval( $date->format( "Ym" ) );
                    }
                }                
            }
        }

        $inicia_mes   = $date->format( "d" );
        $inicia_mes_f = $date->format( "Y-m-d" );

        $date->modify( "last day of this month" );  
        
        $a_semilla = $a ? $meses[ $a - 1 ][ "semilla" ] - $meses[ $a - 1 ][ "c_semilla" ] : $pedido[ "data" ][ "total" ];

        if( $semilla_retirada > 0 || ( $a ? $meses[ $a - 1 ][ "r_semilla" ] : false ) ){
            // si hay retiro de semilla en el mes $pedido[ "data" ][ "total" ] != $a_semilla )
            $factor = 2;
        }

        $cantidad = 
            $a_semilla + 
            ( $meses[ $a - 1 ][ "rendimiento_mes" ] ?? 0 ) + 
            ( $meses[ $a - 1 ][ "compuesto" ] ?? 0 ) - 
            ( $meses[ $a - 1 ][ "retiros" ] ?? 0 );

        $dias_en_mes     = intval( $date->format( "d" ));
        $termina_mes_f   = $date->format( "Y-m-d" );
        $dias_parcial    = ( !$a && date( "d", strtotime( $f_i ) ) == 1 ) ? 0 : $dias_en_mes - $inicia_mes + 1;
        $temp            = $cantidad * ( $producto->data->porcentaje / $factor ) / 100;
        $rendimiento_mes = floor( $temp * 100 ) / 100;
        $corrije_float   = explode(".", $rendimiento_mes);

        if( isset( $corrije_float[ 1 ] ) && $corrije_float[1] == 99 ){
            $rendimiento_mes = ceil( $rendimiento_mes );
        }

        $rendimiento_dia = floor( ( ( $a_semilla > 0 ) * $rendimiento_mes ) / $dias_en_mes * 100 ) / 100; 

        if( $dias_parcial < $dias_en_mes ){
            $rendimiento_mes = floatval( floor( $dias_parcial * $rendimiento_dia * 100 ) / 100 );
        }

        $compuesto = $a ? (
            ( 
                ( $meses[ $a - 1 ][ "rendimiento_mes" ] * 100 ) + 
                ( $meses[ $a - 1 ][ "compuesto" ] * 100 ) - 
                ( $meses[ $a - 1 ][ "retiros" ] * 100 )
            ) / 100
        ) : 0;

        $meses[ $a ] = [
            "Ym"              => $date->format( "Ym" ),
            "Porcentaje"      => $producto->data->porcentaje / $factor,
            "semilla"         => $a_semilla,
            "c_semilla"       => $c_semilla,
            "compuesto"       => $compuesto,
            "r_semilla"       => $r_semilla,
            "dias_en_mes"     => $dias_en_mes,
            "dias_parcial"    => $dias_parcial,
            "retiros"         => $retiros_mes,
            "rendimiento_dia" => $rendimiento_dia,
            "rendimiento_mes" => $rendimiento_mes,
            "inicia"          => $inicia_mes_f,
            "termina"         => $termina_mes_f
        ];
    }

    return [ $meses, $i_semilla ];
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


function balance_inversion( $i, $fecha = null ){
    
    if( !$fecha ){
        $fecha = date( "Ym" );
    }

    $respuesta = [
        "semilla"     => 0.00,
        "retiros"     => 0.00,
        "rendimiento" => 0.00,
        "suma"        => 0.00,
        "compuesto"   => 0.00,
        "total"       => 0.00,
        "full"        => 0.00,
        "finmes"      => 0.00,
        "fecha"       => $fecha
    ];

    if( sizeof( $i[ "extras" ][ "meses" ] ) == 0 ){
        $pedido = model( "PedidoModel" )->find( $i[ "pedido_id" ] );

        $ms = genera_meses( $pedido, $i[ "id" ] );
        $i[ "extras" ][ "meses" ] = $ms[ 0 ];
        $i[ "extras" ][ "semilla_retirada" ] = $ms[ 1 ];

        model( "InversionModel" )->save( $i );
    }

    for( $a = 0; $a <= 24; $a++ ){
        $j = $i[ "extras" ][ "meses" ][ $a ];
        
        $respuesta[ "semilla" ] = $j[ "semilla" ];

        // meses cerrados 

        if( $j[ "Ym" ] < $fecha ){
            if( $j[ "dias_en_mes" ] ){
                
                if( !isset( $j[ "check" ] ) ){
                    $i[ "extras" ][ "meses" ][ $a ] = check_inversion( $i, $a );
                    model( "InversionModel" )->save( $i );
                }

                $respuesta[ "rendimiento_mes" ] = $j[ "rendimiento_mes" ];
                $respuesta[ "rendimiento_mes_total" ] = $j[ "rendimiento_mes" ];
                $respuesta[ "suma" ]           += $j[ "rendimiento_mes" ];
                $respuesta[ "rendimiento" ]     = $j[ "compuesto" ] + $respuesta[ "rendimiento_mes" ];
                $respuesta[ "retiros" ]        += $j[ "retiros" ];
                $respuesta[ "compuesto" ]       = $j[ "compuesto" ];
                $respuesta[ "full" ]            = $respuesta[ "rendimiento" ] - $respuesta[ "retiros" ];
                $respuesta[ "finmes" ]          = $respuesta[ "rendimiento" ] - $respuesta[ "retiros" ];
            }
        }

        // mes en curso

        elseif( $j[ "Ym" ] == $fecha ){
            
            $j[ "retiros" ] = 0;
            $dias = date( "d" ) - ( $j[ "dias_en_mes" ] - $j[ "dias_parcial" ] );

            if( $dias < 0 ){
                $dias = 0;
            }

            $respuesta[ "rendimiento_mes" ] = ( $j[ "rendimiento_dia" ] * $dias );
            $respuesta[ "rendimiento_mes_total" ] = $j[ "rendimiento_mes" ];
            $respuesta[ "retiros" ]        += $j[ "retiros" ];
            $respuesta[ "suma" ] += ( $j[ "rendimiento_dia" ] * $dias );
            $respuesta[ "compuesto" ] = $j[ "compuesto" ];
            $respuesta[ "rendimiento" ] = $j[ "compuesto" ] + ( $j[ "rendimiento_dia" ] * $dias );
            $respuesta[ "full" ] = $respuesta[ "rendimiento" ] - $j[ "retiros" ];
            $respuesta[ "finmes" ]      = $j[ "compuesto" ] + $j[ "rendimiento_mes" ];
        }

    }

    if($j[ "Ym" ] < $fecha){
        $respuesta[ "finmes" ] += $j[ "semilla" ];
    }

    $respuesta[ "total" ] = $respuesta[ "semilla" ] + $respuesta[ "suma" ] - $respuesta[ "retiros" ];

    if( !$respuesta[ "total" ] ){
         $respuesta[ "full" ] = 0;
    }

    return $respuesta;
}


function check_inversion( $i, $a ){
    $mes = $i[ "extras" ][ "meses" ][ $a ];
   
    if( !$mes[ "semilla" ] ){
        $mes[ "semilla" ] = $i[ "extras" ][ "meses" ][ 0 ][ "semilla" ];
    }

    return $mes;
}


function crea_retiro_final( $i ){

    $p      = model( "ProductoModel" )->find( $i[ "producto_codigo" ] );
    $pedido = model( "PedidoModel" )->find( $i[ "pedido_id" ] );

    $ms = genera_meses( $pedido, $i[ "id" ] );
    $i[ "extras" ][ "meses" ] = $ms[ 0 ];
    $i[ "extras" ][ "semilla_retirada" ] = $ms[ 1 ];

    model( "InversionModel" )->save( $i );

    $bt   = balance_inversion( $i, date( "Ym", strtotime( $i[ "extras" ][ "meses" ][ 24 ][ "termina" ]." + 1 day" ) ) );
    $date = new DateTime( $i[ "extras" ][ "meses" ][ 24 ][ "termina" ] );

    $retiro_add = [
        "id" => NULL,
        "estatus_codigo" => "255-PENDIENTE",
        "usuario_id"     => $i[ "usuario_id" ], 
        "inversion_id"   => $i[ "id" ],
        "cantidad"       => $bt[ "total" ],
        "tipo"           => 2,
        "fechas"         => [
            "creacion"       => $date->format( "Y-m-d" ),
            "mes"            => $date->format( "Ym" ),
            "deposito"       => null
        ]
    ];

    model( "RetiroModel" )->save( $retiro_add );

    // actualizar meses de inversión

    $pedido = model( "PedidoModel" )->find( $i[ "pedido_id" ] );

    $ms = genera_meses( $pedido, $i[ "id" ], $p );
    $i[ "extras" ][ "meses" ] = $ms[ 0 ];
    $i[ "extras" ][ "semilla_retirada" ] = $ms[ 1 ];
    
    model( "InversionModel" )->save( $i );

    return $i;
}

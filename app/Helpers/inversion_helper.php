<?php 



function aviso_semilla( $i, $p, $mes = null ){
    
    if( !$mes ){
        $mes = date( "Ym" );
    }
    
    if( $p->data->porcentaje < 9 ){

        $fecha = $i[ "extras" ][ "meses" ][ sizeof( $i[ "extras" ][ "meses" ] ) -12 ][ "termina" ];
    }
    else{
        $fecha = $i[ "extras" ][ "meses" ][ sizeof( $i[ "extras" ][ "meses" ] ) -1 ][ "termina" ];
    }
    
    return $mes >= date( "Ym", strtotime( $fecha ) ) ? 0 : 1;
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
    return 0;

    $db = db_connect();
    
    $sql = "SELECT
            count(*) as semilla_retirada
            from t_inversiones i
            join t_inversiones o on o.id = {$i}
            join t_pedidos p on p.id = i.pedido_id
            where i.id != o.id
            and substring( p.estatus_codigo,1,3) > 400
            and i.extras->>'$.semilla_retirada' between 1 and date_format( o.fechas->>'$.inversion', '%Y%m' )
            and i.producto_codigo = o.producto_codigo
            and i.estatus_codigo = '625-ACTIVA'
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

    $semilla_retirada = $i ? get_semilla_retirada( $i ) : 0;
    // dd($semilla_retirada);
    // Buscamos retiros aplicados a rendimiento
    // $rts  = model( "RetiroModel" )->where( "SUBSTRING( estatus_codigo, 1, 3 ) > 200 AND json_unquote( json_extract( fechas, '$.mes' ) ) >= '".date( "%Y%m", strtotime( $pedido[ "fechas" ][ "pagado" ] ) )."' AND usuario_id = {$pedido[ "usuario_id" ]}" )->findAll();

    $sql = "SELECT r.*
        from t_retiros r
        join t_inversiones i on i.id = r.inversion_id
        where SUBSTRING( r.estatus_codigo, 1, 3 ) > 200 
        AND json_unquote( json_extract( r.fechas, '$.mes' ) ) >= '".date( "%Y%m", strtotime( $pedido[ "fechas" ][ "pagado" ] ) )."' 
        AND r.usuario_id = {$pedido[ "usuario_id" ]} 
        and i.producto_codigo = '{$producto->codigo}'
        AND substring( i.estatus_codigo,1,3) > 200";

    $db  = db_connect();
    $rts = $db->query( $sql )->getResultArray();

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

    $date_temp = $date;
    $previos = 0;

    while( $date_temp->format( "Ym" ) < 202503 ){
        
        $date_temp->modify( "first day of this month" );
        $date_temp->modify( "+ 1 month" );
        $previos++;
    }

    $meses     = [];
    $factor    = 1;
    $i_semilla = 0;

    if( date( "d", strtotime( $f_i ) ) == 1 ){
        $date = new \DateTime( $pedido[ "fechas" ][ "pagado" ] );
    }
    else{
        $date = new \DateTime( $f_i );
    }

    

    for( $a = 0; $a < ( 25 + $previos ); $a++ ){
        if( $a ){
            $date->modify( "first day of this month" );
            $date->modify( "+ 1 month" );
        }
 
        $retiros_mes = 0;
        $c_semilla   = 0;
        $r_semilla   = 0;

        foreach( $rts as $rt ){
            $rt[ "fechas" ] = json_decode( $rt[ "fechas" ], true );

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

        $termina_mes = $date->format( "d" );

        $r_object = model( "RendimientoModel")->where( "producto_codigo", $producto->codigo )->where( "mes", $date->format( "Ym" ) )->first();
        
        $r_dias = array_fill_keys( range( 1, $date->format( "d" ) ), 0.0 );

        // si ya existe el registro
        if( $r_object ){
            $r_dias = $r_object[ "porcentajes" ];

            if( $r_object[ "rendimiento" ] > 0 ){ 
                if( floatval( $r_dias[ $termina_mes ] ) > 0 ){
                    
                }
                else{
                    $r_object[ "dias_en_mes" ] = $termina_mes;
                    $r_object[ "porcentajes" ] = $r_dias = genera_dias( $r_object[ "mes" ], $r_object[ "rendimiento" ], 0.05 );

                    model( "RendimientoModel" )->save( $r_object );
                }
            }
        }
        else{
            $r_object = [
                    "producto_codigo" => $producto->codigo,
                    "mes"             => $date->format( "Ym" ),
                    "porcentajes"     => $r_dias,
                    "rendimiento"     => 0.0
                ];

            model( "RendimientoModel" )->save( $r_object );
        }

        $a_semilla = $a ? $meses[ $a - 1 ][ "semilla" ] - $meses[ $a - 1 ][ "c_semilla" ] : $pedido[ "data" ][ "total" ];

        if( $semilla_retirada > 0 || ( $a ? $meses[ $a - 1 ][ "r_semilla" ] : false ) ){
            // si hay retiro de semilla en el mes $pedido[ "data" ][ "total" ] != $a_semilla )
            $factor = 2;
        }

        if( $pedido[ "fechas" ][ "pagado" ] < '2026-02-05' ){
            $r_object[ "porcentajes" ] = array_fill_keys( range( 1, $date->format( "d" ) ), 0.0 );
            $r_object[ "rendimiento" ] = 0.0;
        }

        $cantidad = 
            $a_semilla + 
            ( $meses[ $a - 1 ][ "rendimiento_mes" ] ?? 0 ) + 
            ( $meses[ $a - 1 ][ "compuesto" ] ?? 0 ) - 
            ( $meses[ $a - 1 ][ "retiros" ] ?? 0 );

        $dias_en_mes     = intval( $date->format( "d" ));
        $termina_mes_f   = $date->format( "Y-m-d" );
        $dias_parcial    = ( !$a && date( "d", strtotime( $f_i ) ) == 1 ) ? 0 : $dias_en_mes - $inicia_mes + 1;
        $porcentaje_mes  = 0; 

        // si es mes anterior
        if( date( "Ym" ) > $date->format( "Ym" ) ){
            $porcentaje_mes = array_sum( array_slice( $r_object[ "porcentajes" ], 0, date( "d" ) ) );
        }
        // si es mes actual TRUNCADO
        elseif( date( "Ym" ) == $date->format( "Ym" ) && $dias_en_mes > $dias_parcial ){
            if( $dias_parcial < date( "d" ) ){
                $porcentaje_mes = array_sum( array_slice( $r_object[ "porcentajes" ], $dias_en_mes - $dias_parcial, $dias_en_mes ) );        
            }
        }

        $temp = $cantidad * $porcentaje_mes / 100; // $producto->data->porcentaje
      //  d($temp);

        $rendimiento_mes = floor( $temp * 100 ) / 100;
      /*  $corrije_float   = explode(".", $rendimiento_mes);

        if( isset( $corrije_float[ 1 ] ) && $corrije_float[1] == 99 ){
            $rendimiento_mes = ceil( $rendimiento_mes );
        }

        if( $dias_parcial < $dias_en_mes ){
            $rendimiento_mes = 0.0000;

            
            for( $dd = intval( date( "d", strtotime( $f_i ) ) ); $dd <= $dias_en_mes; $dd++ ){
                $rendimiento_mes += floatval( $r_dias[ $dd ] ); // floatval( floor( $r_dias[ $dd ] * 100 ) / 100 );
            }     
        } */

        $compuesto = $a ? (
            ( 
                ( $meses[ $a - 1 ][ "rendimiento_mes" ] * 100 ) + 
                ( $meses[ $a - 1 ][ "compuesto" ] * 100 ) - 
                ( $meses[ $a - 1 ][ "retiros" ] * 100 )
            ) / 100
        ) : 0;

        $meses[ $a ] = [
            "Ym"              => $date->format( "Ym" ),
            "Porcentaje"      => $porcentaje_mes,
            "semilla"         => $a_semilla,
            "c_semilla"       => $c_semilla,
            "compuesto"       => $compuesto,
            "r_semilla"       => $r_semilla,
            "dias_en_mes"     => $dias_en_mes,
            "dias_parcial"    => $dias_parcial,
            "retiros"         => $retiros_mes,
        //    "rendimiento_dia" => $rendimiento_dia,
            "rendimiento_mes" => $rendimiento_mes,
            "inicia"          => $inicia_mes_f,
            "termina"         => $termina_mes_f
        ];
    }

    return [ $meses, $i_semilla ];
}

function genera_meses_bak( $pedido, $i, $producto = null ){

    if( !$producto ){
        $producto = model( "ProductoModel" )->find( array_keys( $pedido[ "promociones"][ "510-SEMILLA" ][ "productos" ] ) )[ 0 ];
    }

    // calculamos fecha de inicio de inversion
    $f_i  = get_fecha_inversion( $pedido[ "fechas" ][ "pagado" ] );
    $semilla_retirada = $i ? get_semilla_retirada( $i ) : 0;
    // dd($semilla_retirada);
    // Buscamos retiros aplicados a rendimiento
    // $rts  = model( "RetiroModel" )->where( "SUBSTRING( estatus_codigo, 1, 3 ) > 200 AND json_unquote( json_extract( fechas, '$.mes' ) ) >= '".date( "%Y%m", strtotime( $pedido[ "fechas" ][ "pagado" ] ) )."' AND usuario_id = {$pedido[ "usuario_id" ]}" )->findAll();

    $sql = "SELECT r.*
        from t_retiros r
        join t_inversiones i on i.id = r.inversion_id
        where SUBSTRING( r.estatus_codigo, 1, 3 ) > 200 
        AND json_unquote( json_extract( r.fechas, '$.mes' ) ) >= '".date( "%Y%m", strtotime( $pedido[ "fechas" ][ "pagado" ] ) )."' 
        AND r.usuario_id = {$pedido[ "usuario_id" ]} 
        and i.producto_codigo = '{$producto->codigo}'
        AND substring( i.estatus_codigo,1,3) > 200";

    $db  = db_connect();
    $rts = $db->query( $sql )->getResultArray();

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

    $date_temp = $date;
    $previos = 0;

    while( $date_temp->format( "Ym" ) < 202503 ){
        
        $date_temp->modify( "first day of this month" );
        $date_temp->modify( "+ 1 month" );
        $previos++;
    }

    $meses     = [];
    $factor    = 1;
    $i_semilla = 0;

    if( date( "d", strtotime( $f_i ) ) == 1 ){
        $date = new \DateTime( $pedido[ "fechas" ][ "pagado" ] );
    }
    else{
        $date = new \DateTime( $f_i );
    }


    for( $a = 0; $a < ( 25 + $previos ); $a++ ){
        if( $a ){
            $date->modify( "first day of this month" );
            $date->modify( "+ 1 month" );
        }
 
        $retiros_mes = 0;
        $c_semilla   = 0;
        $r_semilla   = false;

        foreach( $rts as $rt ){
            $rt[ "fechas" ] = json_decode( $rt[ "fechas" ], true );

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

        $termina_mes = $date->format( "d" );

        $r_object = model( "RendimientoModel")->where( "producto_codigo", $producto->codigo )->where( "mes", $date->format( "Ym" ) )->first();
        
        $r_dias = array_fill_keys( range( 1, $date->format( "d" ) ), 0.0 );

        // si ya existe el registro
        if( $r_object ){
            $r_dias = $r_object[ "porcentajes" ];

            if( $r_object[ "rendimiento" ] > 0 ){ 
                if( floatval( $r_dias[ $termina_mes ] ) > 0 ){
                    
                }
                else{
                    $r_object[ "dias_en_mes" ] = $termina_mes;
                    $r_object[ "porcentajes" ] = $r_dias = genera_dias( $r_object[ "mes" ], $r_object[ "rendimiento" ], 0.05 );

                    model( "RendimientoModel" )->save( $r_object );
                }
            }
        }
        else{
            $r_object = [
                    "producto_codigo" => $producto->codigo,
                    "mes"             => $date->format( "Ym" ),
                    "porcentajes"     => $r_dias,
                    "rendimiento"     => 0.0
                ];

            model( "RendimientoModel" )->save( $r_object );
        }

        $a_semilla = $a ? $meses[ $a - 1 ][ "semilla" ] - $meses[ $a - 1 ][ "c_semilla" ] : $pedido[ "data" ][ "total" ];

        if( $semilla_retirada > 0 || ( $a ? $meses[ $a - 1 ][ "r_semilla" ] : false ) ){
            // si hay retiro de semilla en el mes $pedido[ "data" ][ "total" ] != $a_semilla )
            $factor = 2;
        }

        if( $pedido[ "fechas" ][ "pagado" ] < '2026-02-05' ){
            $r_object[ "porcentajes" ] = array_fill_keys( range( 1, $date->format( "d" ) ), 0.0 );
            $r_object[ "rendimiento" ] = 0.0;
        }

        $cantidad = 
            $a_semilla + 
            ( $meses[ $a - 1 ][ "rendimiento_mes" ] ?? 0 ) + 
            ( $meses[ $a - 1 ][ "compuesto" ] ?? 0 ) - 
            ( $meses[ $a - 1 ][ "retiros" ] ?? 0 );

        $dias_en_mes     = intval( $date->format( "d" ));
        $termina_mes_f   = $date->format( "Y-m-d" );
        $dias_parcial    = ( !$a && date( "d", strtotime( $f_i ) ) == 1 ) ? 0 : $dias_en_mes - $inicia_mes + 1;

        // si es mes actual o futuros
        if( date( "Ym" ) < $date->format( "Ym" ) ){
            $porcentaje_mes = array_sum( array_slice( $r_object[ "porcentajes" ], 0, date( "d" ) ) );
        }
        elseif( $dias_en_mes > $dias_parcial ){
            $porcentaje_mes = array_sum( array_slice( $r_object[ "porcentajes" ], $dias_en_mes - $dias_parcial, $dias_en_mes ) );
        }
        else{
            $porcentaje_mes = $r_object[ "rendimiento" ]; 
        }

        

        $temp = $cantidad * $porcentaje_mes / 100; // $producto->data->porcentaje
      //  d($temp);

         $rendimiento_mes = floor( $temp * 100 ) / 100;
        /* $corrije_float   = explode(".", $rendimiento_mes);

        if( isset( $corrije_float[ 1 ] ) && $corrije_float[1] == 99 ){
            $rendimiento_mes = ceil( $rendimiento_mes );
        }

        if( $dias_parcial < $dias_en_mes ){
            $rendimiento_mes = 0.0000;

            
            for( $dd = intval( date( "d", strtotime( $f_i ) ) ); $dd <= $dias_en_mes; $dd++ ){
                $rendimiento_mes += floatval( $r_dias[ $dd ] ); // floatval( floor( $r_dias[ $dd ] * 100 ) / 100 );
            }     
        } */

        $compuesto = $a ? (
            ( 
                ( $meses[ $a - 1 ][ "rendimiento_mes" ] * 100 ) + 
                ( $meses[ $a - 1 ][ "compuesto" ] * 100 ) - 
                ( $meses[ $a - 1 ][ "retiros" ] * 100 )
            ) / 100
        ) : 0;

        $meses[ $a ] = [
            "Ym"              => $date->format( "Ym" ),
            "Porcentaje"      => $porcentaje_mes,
            "semilla"         => $a_semilla,
            "c_semilla"       => $c_semilla,
            "compuesto"       => $compuesto,
            "r_semilla"       => $r_semilla,
            "dias_en_mes"     => $dias_en_mes,
            "dias_parcial"    => $dias_parcial,
            "retiros"         => $retiros_mes,
        //    "rendimiento_dia" => $rendimiento_dia,
            "rendimiento_mes" => $rendimiento_mes,
            "inicia"          => $inicia_mes_f,
            "termina"         => $termina_mes_f
        ];
    }

    return [ $meses, $i_semilla ];
}


function genera_rendimientos_dia( $data ){
    $dias = [];

    // Generar valores aleatorios positivos
    for ($i = 1; $i <= $data[ "dias_en_mes" ]; $i++) {
        
        // número aleatorio decimal entre 0 y 1
        $dias[ $i ] = $data[ "rendimiento" ] > 0 ? mt_rand() / mt_getrandmax()  : 0;

        if( $data[ "rendimiento" ] > 0 ){
            $sumaProvisional = array_sum( $dias );

            foreach( $dias as $key => $valor ) {
                $dias[ $key ] = round( ( $valor / $sumaProvisional ) * $data[ "rendimiento" ], 2 );
            }
        }   
    }    

    return $dias;
}


function genera_dias($mesYYYYMM, $rendimientoMensual, $fuerzaSesgo = 2)
{
    // 1️⃣ Calcular número de días
    $year  = substr($mesYYYYMM, 0, 4);
    $month = substr($mesYYYYMM, 4, 2);
    $dias  = cal_days_in_month(CAL_GREGORIAN, (int)$month, (int)$year);

    $valores = [];
    $sumaProvisional = 0;

    // 2️⃣ Generar pesos crecientes con ruido aleatorio
    for ($i = 1; $i <= $dias; $i++) {

        // Peso base creciente
        $pesoBase = pow($i / $dias, $fuerzaSesgo);

        // Ruido aleatorio pequeño (±10%)
        $ruido = 1 + (mt_rand() / mt_getrandmax()) * 0.3;

        $valor = $pesoBase * $ruido;

        $valores[$i] = $valor;
        $sumaProvisional += $valor;
    }

    // 3️⃣ Normalizar para que la suma sea exactamente el rendimiento mensual
    $rendimientos = [];

    foreach ($valores as $k => $valor) {
        $rendimientos[$k] = round( ($valor / $sumaProvisional) * $rendimientoMensual, 2 );
    }

    // 4️⃣ Ajuste final por precisión flotante




    $diferencia = intval( $rendimientoMensual * 100) - intval( array_sum($rendimientos) * 100 );
    if ( abs( $diferencia ) >= 1) {
        $rendimientos[$dias] += round( $diferencia / 100, 2 ); // ajustar último día
    }

    return $rendimientos;
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

    if( sizeof( $i[ "extras" ][ "meses" ] ) == 0 || ( $i[ "extras" ][ "v" ] ?? 0 ) != 2 ){
        $pedido = model( "PedidoModel" )->find( $i[ "pedido_id" ] );

        $ms = genera_meses( $pedido, $i[ "id" ] );
        $i[ "extras" ][ "meses" ] = $ms[ 0 ];
        $i[ "extras" ][ "semilla_retirada" ] = $ms[ 1 ];

        model( "InversionModel" )->save( $i );
    }

    for( $a = 0; $a <= 24; $a++ ){
        $j = $i[ "extras" ][ "meses" ][ $a ];
        
        if( $i[ "fechas" ][ "pagado"] < '2026-02-05' ){
            $j[ "retiros" ] = 0.00;
            $j[ "compuesto" ] = 0.00;
            $j[ "rendimiento" ] = 0.00;
            $j[ "finmes" ] = 0.00;
        }

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

            $o = substr( $j[ "Ym" ], 0, 4 )."-".substr( $j[ "Ym" ], 4, 2 )."-01";
            $termina_mes =  date( "t", strtotime( $o ) );

            $r = model( "RendimientoModel" )->where( "producto_codigo", $i[ "producto_codigo" ] )->where( "mes", $j[ "Ym" ] )->first();

            $r_dias = array_fill_keys( range( 1, $termina_mes ), 0.0 );

            // si ya existe el registro
            if( $r ){
                $r_dias = $r[ "porcentajes" ];

                if( $r[ "rendimiento" ] > 0 ){ 
                    if( floatval( $r_dias[ $termina_mes ] ) > 0 ){
                        
                    }
                    else{
                        $r[ "dias_en_mes" ] = $termina_mes;
                        $r[ "porcentajes" ] = $r_dias = genera_dias( $r[ "mes" ], $r[ "rendimiento" ], 0.05 );

                        model( "RendimientoModel" )->save( $r );
                    }
                }
            }
            else{
                $r = [
                        "producto_codigo" => $i[ "producto_codigo" ],
                        "mes"             => $j[ "Ym" ],
                        "porcentajes"     => $r_dias,
                        "rendimiento"     => 0.0
                    ];

                model( "RendimientoModel" )->save( $r );
            }

            $respuesta[ "rendimiento_mes" ] = array_sum( array_slice( $r[ "porcentajes" ], 0, date( "d" ) ) ); //( $j[ "rendimiento_dia" ] * $dias );
            $respuesta[ "rendimiento_mes_total" ] = $j[ "rendimiento_mes" ];
            $respuesta[ "retiros" ]        += $j[ "retiros" ];
            $respuesta[ "suma" ] += ( $r[ "rendimiento" ] > 0 ? $j[ "rendimiento_mes" ] : 0 );
            $respuesta[ "compuesto" ] = $j[ "compuesto" ];
            $respuesta[ "rendimiento" ] = $j[ "compuesto" ] + ( $r[ "rendimiento" ] > 0 ? $j[ "rendimiento_mes" ] : 0);
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



function mk_estructura_inversion( $path ){
    $db = db_connect();

    $sql = "SELECT u.id, SUM(r.deposito) as inv, u.data->>'$.wallet' as w
            FROM t_retiros r
            left JOIN t_usuarios u ON u.id = r.usuario_id
            WHERE r.estatus_codigo = '255-PENDIENTE'
            GROUP BY u.id";

    $sql = $db->query( $sql );
    foreach( $sql->getResult() as $i ){
        echo $i->id."\t".$i->inv."\t".$i->w."<br>";
    }
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


function generarSerieMensual($min, $max, $fecha) {

    // 1️⃣ Obtener mes y año
    $timestamp = strtotime($fecha);
    $mes = date('m', $timestamp);
    $anio = date('Y', $timestamp);

    // 2️⃣ Calcular objetivo
    $diferencia = $max - $min;
    $minPermitido = $min + ($diferencia / 3);

    $objetivo = $minPermitido + lcg_value() * ($max - $minPermitido);
    $objetivo = round($objetivo, 4);

    // 3️⃣ Obtener cantidad de días del mes
    $diasDelMes = cal_days_in_month(CAL_GREGORIAN, $mes, $anio);

    // 🔒 Valor mínimo por día (para evitar ceros)
    $valorMinimo = 0.0001;

    // Verificación de seguridad
    if ($objetivo <= ($diasDelMes * $valorMinimo)) {
        throw new Exception("El objetivo es demasiado pequeño para garantizar valores mayores a cero.");
    }

    // 4️⃣ Asignar mínimo a todos los días
    $valores = array_fill(0, $diasDelMes, $valorMinimo);
    $restante = $objetivo - ($diasDelMes * $valorMinimo);

    // Generar pesos aleatorios
    $pesos = [];
    $sumaPesos = 0;

    for ($i = 0; $i < $diasDelMes; $i++) {
        $peso = lcg_value();
        $pesos[] = $peso;
        $sumaPesos += $peso;
    }

    // Distribuir el restante proporcionalmente
    for ($i = 0; $i < $diasDelMes - 1; $i++) {
        $extra = ($pesos[$i] / $sumaPesos) * $restante;
        $valores[$i] += $extra;
        $valores[$i] = round($valores[$i], 4);
    }

    // Ajustar último valor para suma exacta
    $sumaParcial = array_sum(array_slice($valores, 0, $diasDelMes - 1));
    $valores[$diasDelMes - 1] = round($objetivo - $sumaParcial, 4);

    return [
        'mes' => $mes,
        'anio' => $anio,
        'dias' => $diasDelMes,
        'objetivo' => $objetivo,
        'serie' => $valores,
        'suma_final' => array_sum($valores)
    ];
}
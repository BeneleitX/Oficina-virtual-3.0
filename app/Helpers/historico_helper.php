<?php 


function historico_venta( $modelo, $mes )
{
    $db  = db_connect(); 

    $respuesta = [];

    $fe_i  = date( "Y-m-d", strtotime( substr( $mes, 0, 4 )."-".substr( $mes, 4, 2 )."-01 - 12 month" ) );
    $fe_t  = date( "Y-m-d", strtotime( substr( $mes, 0, 4 )."-".substr( $mes, 4, 2 )."-01 + 1 month - 1 day" ) );

    $sql = "SELECT 
                date_format( fechas->>'$.pagado', '%Y%m' ) as mes, 
                sum( data->>'$.total' ) as total,
                sum( IF( data->>'$.primercompra' = 1, data->>'$.total', 0 ) ) as nuevos,
                sum( IF( data->>'$.primercompra' != 1, data->>'$.total', 0 ) ) as recompra
	        FROM t_pedidos
            where modelo_codigo = '{$modelo}'
            and substring( estatus_codigo, 1, 3) > 400
            and cast( fechas->>'$.pagado' as date ) between '{$fe_i}' and '{$fe_t}'
            group by mes
            order by mes asc";

    $result = $db->query( $sql )->getResult();

    foreach( $result as $v ){
        $respuesta[ "total" ][ $v->mes ] = $v->total;
        $respuesta[ "nuevos" ][ $v->mes ] = $v->nuevos;
        $respuesta[ "recompra" ][ $v->mes ] = $v->recompra;
    }

    return $respuesta;
}



function historico_pedidos( $modelo, $mes )
{
    $db  = db_connect(); 

    $respuesta = [];

    $fe_i  = date( "Y-m-d", strtotime( substr( $mes, 0, 4 )."-".substr( $mes, 4, 2 )."-01 - 12 month" ) );
    $fe_t  = date( "Y-m-d", strtotime( substr( $mes, 0, 4 )."-".substr( $mes, 4, 2 )."-01 + 1 month - 1 day" ) );

    $sql = "SELECT 
                date_format( fechas->>'$.pagado', '%Y%m' ) as mes, 
                sum( 1 ) as total,
                sum( IF( data->>'$.primercompra' = 1, 1, 0 ) ) as nuevos,
                sum( IF( data->>'$.primercompra' != 1, 1, 0 ) ) as recompra
	        FROM t_pedidos
            where modelo_codigo = '{$modelo}'
            and substring( estatus_codigo, 1, 3) > 400
            and cast( fechas->>'$.pagado' as date ) between '{$fe_i}' and '{$fe_t}'
            group by mes
            order by mes asc";

    $result = $db->query( $sql )->getResult();

    foreach( $result as $v ){
        $respuesta[ "total" ][ $v->mes ] = $v->total;
        $respuesta[ "nuevos" ][ $v->mes ] = $v->nuevos;
        $respuesta[ "recompra" ][ $v->mes ] = $v->recompra;
    }

    return $respuesta;
}



function historico_reparto( $modelo, $mes )
{
    $db  = db_connect(); 

    $respuesta = [];

    $fe_i  = date( "Y-m-d", strtotime( substr( $mes, 0, 4 )."-".substr( $mes, 4, 2 )."-01 - 12 month" ) );
    $fe_t  = date( "Y-m-d", strtotime( substr( $mes, 0, 4 )."-".substr( $mes, 4, 2 )."-01 + 1 month - 1 day" ) );

    $sql = "SELECT 
                DATE_FORMAT( p.fechas->>'$.pagado', '%Y%m' ) AS mes, 

                sum( c.cantidad * IF( c.esquema_codigo = '118-PROMOS-50', 2.50, 1 ) ) as total,
                sum( IF( data->>'$.primercompra' = 1, c.cantidad * IF( c.esquema_codigo = '118-PROMOS-50', 2.50, 1 ), 0 ) ) as nuevos,
                sum( IF( data->>'$.primercompra' != 1, c.cantidad * IF( c.esquema_codigo = '118-PROMOS-50', 2.50, 1 ), 0 ) ) as recompra
                
            FROM t_pedidos p
            left join t_comisiones c on c.pedido_id = p.id
            WHERE p.modelo_codigo = '{$modelo}'
            and c.esquema_codigo not in ( '120-BIEX-3ER-NIVEL' )
            AND SUBSTRING( p.estatus_codigo, 1, 3 ) > 400 AND CAST( p.fechas->>'$.pagado' AS DATE ) BETWEEN '{$fe_i}' and '{$fe_t}'
            GROUP BY mes
            ORDER BY mes ASC";

    $result = $db->query( $sql )->getResult();

    foreach( $result as $v ){
        $respuesta[ "total" ][ $v->mes ] = $v->total;
        $respuesta[ "nuevos" ][ $v->mes ] = $v->nuevos;
        $respuesta[ "recompra" ][ $v->mes ] = $v->recompra;
    }

    return $respuesta;
}

function historico_socios( $modelo, $mes )
{
    $fe    = date( "Ym", strtotime( substr( $mes, 0, 4 )."-".substr( $mes, 4, 2 )."-01 - 12 month" ) );
    $db    = db_connect(); 
    $resp  = [];
    $meses = get_meses( $mes );
    $sql   = "SELECT *
            FROM t_historico h
            WHERE h.modelo_codigo = '{$modelo}'
            and h.codigo like 'SOCIOS_%'
            AND h.mes in ( ".implode( ",", array_keys($meses)).")";

    $datos = $db->query( $sql )->getResultArray();    

    foreach( $datos as $d ){
        $resp[ $d[ "mes" ] ][ $d[ "codigo" ] ] = $d[ "cantidad" ];
    }

    foreach( $meses as $m => $c ){
        if( $m == $mes || !isset( $resp[ $m ] ) ){
            // si es mes actual, no jalar de histórico, calcular en tiempo real

            $datos = historico_socios_data( $modelo, $m );
            
            foreach( $datos as $codigo => $v ){
                $db->query( "insert into t_historico values ( '{$codigo}', '{$modelo}', {$m}, {$v} ) on duplicate key update cantidad = {$v}" );    
            }

            $resp[ $m ] = $datos;


        }
    }

    $respuesta = [];

    foreach($resp as $m => $d){
        foreach($d as $e => $f){
            $respuesta[ $e ][ $m ] = $f;
        }
    }

    return $respuesta;
}


function historico_socios_data( $modelo, $mes )
{
    $db = db_connect(); 

    $sql_activos = $db->query( "select count(*) as total 
            from t_usuarios
            where substring( data->>'$.estatus.modelos.\"{$modelo}\"', 1, 3 ) > 200" )->getRow();

    $sql_inscritos = $db->query( "select count(*) as total
            from t_usuarios
            where date_format( historial->'$.registro', '%Y%m' ) = {$mes}" )->getRow();

    $sql_nuevos = $db->query( "select count(*) as total from ( select u.id
            from t_usuarios u
            join t_pedidos p on p.usuario_id = u.id and p.modelo_codigo = \"{$modelo}\"
            where p.data->>'$.primercompra' = 1 
            and date_format( cast( p.fechas->>'$.pagado' as date ), '%Y%m' ) = {$mes} group by u.id ) a" )->getRow();

    $sql_recompra = $db->query( "select count(*) as total from ( select u.id
            from t_usuarios u
            join t_pedidos p on p.usuario_id = u.id and p.modelo_codigo = \"{$modelo}\"
            where p.data->>'$.primercompra' != 1 
            and date_format( cast( p.fechas->>'$.pagado' as date ), '%Y%m' ) = {$mes} group by u.id ) a" )->getRow();

    $sql_bajas = $db->query( "select count(*) as total
            from t_usuarios u
            where substring( u.data->>'$.estatus.modelos.\"{$modelo}\"', 1, 3 ) < 200
            and date_format( cast( historial->>'$.modelos.\"{$modelo}\".reset' as date ), '%Y%m' ) = {$mes}" )->getRow();


    $datos = [
        "SOCIOS_ACTIVOS" => $sql_activos->total,
        "SOCIOS_INSCRITOS" => $sql_inscritos->total,
        "SOCIOS_NUEVOS" => $sql_nuevos->total,
        "SOCIOS_RECOMPRA" => $sql_recompra->total,
        "SOCIOS_BAJA" => $sql_bajas->total
    ];


    return $datos;
}



function historico_productos( $modelo, $mes )
{
    $fe    = date( "Ym", strtotime( substr( $mes, 0, 4 )."-".substr( $mes, 4, 2 )."-01 - 12 month" ) );
    $db    = db_connect(); 
    $resp  = [];
    $meses = get_meses( $mes );
    $sql   = "SELECT *
            FROM t_historico h
            WHERE h.modelo_codigo = '{$modelo}'
            AND h.codigo like 'PRODUCTOS_%'
            AND h.mes in ( ".implode( ",", array_keys($meses)).")";

    $datos = $db->query( $sql )->getResultArray();    

    foreach( $datos as $d ){
        $resp[ $d[ "mes" ] ][ $d[ "codigo" ] ] = $d[ "cantidad" ];
    }

    foreach( $meses as $m => $c ){
        if( $m == date( "Ym") || !isset( $resp[ $m ] ) ){
            // si es mes actual, no jalar de histórico, calcular en tiempo real

            $datos = historico_productos_data( $modelo, $m );
            
            foreach( $datos as $codigo => $v ){
                $sql = "insert into t_historico values ( '{$codigo}', '{$modelo}', {$m}, {$v} ) on duplicate key update cantidad = {$v}";
                $db->query( $sql );    
            }

            $resp[ $m ] = $datos;
        }
    }

    $respuesta = [];

    foreach($resp as $m => $d){
        foreach($d as $e => $f){
            $respuesta[ $e ][ $m ] = intval( $f );
        }
    }

    return $respuesta;
}



function historico_productos_data( $modelo, $mes )
{
    $db = db_connect(); 

    $sql = $db->query( "SELECT 
            date_format( cast( p.fechas->>'$.pagado' as date ), '%Y%m' ) as mes, 
            pr.codigo as producto,
        SUM(
            JSON_UNQUOTE(JSON_EXTRACT(p.promociones, CONCAT('$.\"', categoria, '\".productos.\"', pr.codigo , '\".cantidad')))
        ) AS total
        FROM t_pedidos p
        join t_productos pr on pr.modelo_codigo = p.modelo_codigo,
        JSON_TABLE(
        JSON_KEYS(p.promociones),
        \"$[*]\" COLUMNS (
            categoria VARCHAR(50) PATH \"$\"
        )
        ) AS cats
        WHERE p.modelo_codigo = '{$modelo}'
        and date_format( cast( p.fechas->>'$.pagado' as date ), '%Y%m' ) = '{$mes}'
        group by mes, producto" );

    $datos = [];

    foreach( $sql->getResult() as $r ){
        $datos[ "PRODUCTOS_".$r->producto ] = intval( $r->total ) ?? 0;
    }


    return $datos;
}



function historico_random()
{
    return rand( 100, 100000 ) / 10;
}


function ordena_productos( $array, $mes )
{
    uasort($array, function( $a, $b ) use( $mes ){
      
        return $b[ $mes ] <=> $a[ $mes ];
    });

    // Mostrar resultado
    return $array;
}

function get_meses( $mes )
{
    $meses = [];
    $f = new DateTime( substr( $mes, 0, 4 )."-".substr( $mes, 4, 2 )."-01 00:00:00");

    for( $i = 0; $i <= 12; $i++ ){
        $meses[ $f->format( "Ym" ) ] = 0;
        $f->modify("-1 month");
    }

    return $meses;
}
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




function historico_random()
{
    return rand( 100, 100000 ) / 10;
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
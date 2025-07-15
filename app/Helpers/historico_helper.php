<?php 


function historico_venta_total( $modelo, $mes )
{
    
    return [ "202507" => historico_random() ];
}


function historico_venta_nuevos( $modelo, $mes )
{
    return [ "202507" => historico_random() ];
}


function historico_venta_recompra( $modelo, $mes )
{
    return [ "202507" => historico_random() ];
}


function historico_random()
{
    return rand( 100, 100000 ) / 10;
}
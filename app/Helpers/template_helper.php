<?php 

function template($url, $data)
{
    // header
    $html = view( "_header", $data );

    // carga central de vista
    $html .= view( $url, $data );

    if( $data[ "navbar" ] ){
        $html .= view( "_navbar", $data );
    }

    // footer
    $html .= view( "_footer", $data );

    return $html;
}


function load_catalogo( $tabla, $where = null ){
    if( defined( strtoupper( $tabla) ) ) return;

    $db = db_connect();

    // catálogo de modelos de negocio
    $array = [];
    
    foreach( $db->query( "select * from t_{$tabla}".( $where ? " where ".$where : "")." order by codigo" )->getResultArray() as $row ){ 
        $tmp = [];

        foreach( $row as $k => $d ){
            $tmp[ $k ] = is_array( $obj = json_decode( $d, 1 ) ) ? $obj : $d;
        }

        $array[ $row[ "codigo" ] ] = $tmp;
    }

    define( strtoupper( $tabla ), $array );
}



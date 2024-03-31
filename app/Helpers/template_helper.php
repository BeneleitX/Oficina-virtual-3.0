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


function catalogos(){
    $db = db_connect();

    // catálogo de modelos de negocio
    $array = [];
    
    foreach( $db->query( "select * from t_modelos" )->getResultArray() as $row ){ 
        $tmp = [];
        foreach( $row as $k => $d )
            $tmp[ $k ] = is_array( $obj = json_decode( $d, 1 ) ) ? $obj : $d;

        $array[ $row[ "codigo" ] ] = $tmp;
    }

    define( "MODELOS", $array );



    // catálogo de Estatus
    $array = [];
    
    foreach( $db->query( "select * from t_estatus" )->getResultArray() as $row ){ 
        $tmp = [];
        foreach( $row as $k => $d )
            $tmp[ $k ] = is_array( $obj = json_decode( $d, 1 ) ) ? $obj : $d;

        $array[ $row[ "codigo" ] ] = $tmp;
    }

    define( "ESTATUS", $array );    
}
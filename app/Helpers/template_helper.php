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
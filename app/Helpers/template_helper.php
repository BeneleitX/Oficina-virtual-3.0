<?php 

function template($url, $data)
{
    // header
    $html = view( "_header", $data );

    // carga central de vista
    $html .= "<div id=\"contenedor-body\" class=\"\">".view( $url, $data )."</div>";

    // Si es el caso, muestra navegación
    if( $data[ "usuario" ] && $data[ "navbar" ] ){
        $html .= view( "_navbar", $data );
    }

    // footer
    $html .= view( "_footer", $data );

    return $html;
}
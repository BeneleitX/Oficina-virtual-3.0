<?php 

function alertas( $a ){
    return "\nalerta( '{$a[ "clase" ]}', '{$a[ "icono" ]}',  '{$a[ "texto" ]}' );";
}

// devuelve un número formateado con ceros a la izquierda
function id($n, $digitos = 0)
{
    $array = array_map('intval', str_split(str_pad($n,$digitos,"0", STR_PAD_LEFT)));
    $i     = array_shift($array);
    $res   = "<span class='fw-light opacity-50'>";

    while($i == "0")
    {
        $res .= $i; 
        $i = array_shift($array);
    }

    $res .= "</span><span class='fw-bold'>";
    $res .= $i;

    foreach($array as $a)
        $res .= $a;
    
    $res .= "</span>";
    return $res;
}
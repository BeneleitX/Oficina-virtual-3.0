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


function getReferencia( string $p ){
    // Inicializamos variable para acumular la sumatoria de dígitos
    $s = 0;

    // recorremos la cadena del número de pedido en sentido inverso (empezando desde el ultimo)
    foreach( array_reverse( str_split( $p ) ) as $k => $d ){
        // Multiplicamos los números impares por 2
        $d *= ( $k % 2 ? 1 : 2);

        // Cuando el resultado es de 2 cifras, lo reducimos a una sumando ambas
        // acumulamos la sumatoria digito por dígito
        $s += $d - ( $d > 9 ? 9 : 0 );
    }

    // regresamos el número de pedido agregando al final el dígito verificador
    // que es resultado de la diferencia entre la sumatoria y su decena inmediata superior
    return $p.( 10 * ceil( $s / 10 ) - $s );

    // listo ;)
}

function random_password(){
    $cad = bin2hex( random_bytes(2) );
    $pos = rand(0, strlen($cad));
    return substr($cad, 0, $pos)."*".substr($cad, $pos);
}


function mask( $texto ){
    $ok = 2;
    $nueva = "";
 
    foreach( str_split( $texto ) as $k => $d ){
        $nueva .= ($k >= $ok ? "*" : $d );
    }

    return $nueva;
}
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


function getModeloPrincipal(){

    foreach( MODELOS as $m ){
        if( $m[ "settings" ][ "principal" ] ){
            return $m[ "codigo" ];
        }
    }
}


function getIP(){
    if(!empty($_SERVER['HTTP_CLIENT_IP'])){
        //ip from share internet
        $ip = $_SERVER['HTTP_CLIENT_IP'];
    }elseif(!empty($_SERVER['HTTP_X_FORWARDED_FOR'])){
        //ip pass from proxy
        $ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
    }else{
        $ip = $_SERVER['REMOTE_ADDR'];
    }
    return $ip;
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


function bitacora( $accion, $usuario, array $variables = [] ){
    $db = db_connect();
    $db->query("insert into t_bitacoras values(NULL, {$accion}, {$usuario}, '".date("Y-m-d H:i:s")."', '".json_encode($variables)."', '".getIP()."') ");
}

function admin( $codigo ){
    $db = db_connect();
    $data = $db->query("select * from t_variables where codigo = '{$codigo}'")->getRow();

    if($data->tipo == 'JSON'){
        $data->valor = json_decode( $data->valor );
    }

    return $data->valor;
}


function estatus( $codigo, $bn = false ){
    return "<span class=\"badge rounded-pill bg-".( $bn ? "black" : ESTATUS[ $codigo ][ "color" ] )."\"><i class=\"fa fa-circle\"></i> ".ESTATUS[ $codigo ][ "descripcion" ]."</span>";
}


function mes($mesnum, $ext = 0)
{
    if($mesnum < 1) $mesnum += 12;
    if($mesnum > 12) $mesnum -= 12;

    $mespal = "";

    switch($mesnum)
    {
        case 1 : $mespal = "enero"; break;
        case 2 : $mespal = "febrero"; break;
        case 3 : $mespal = "marzo"; break;
        case 4 : $mespal = "abril"; break;
        case 5 : $mespal = "mayo"; break;
        case 6 : $mespal = "junio"; break;
        case 7 : $mespal = "julio"; break;
        case 8 : $mespal = "agosto"; break;
        case 9 : $mespal = "septiembre"; break;
        case 10: $mespal = "octubre"; break;
        case 11: $mespal = "noviembre"; break;
        case 12: $mespal = "diciembre"; break;
    }

    if($ext)
        $mespal = substr($mespal, 0, $ext);

    return $mespal;
}
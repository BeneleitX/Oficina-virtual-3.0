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



function random( $tipo ){
    switch( $tipo ){
        case "nombre" : 
            $datos = [ "EBLING", "SELENE CITLALI", "CHRISTIAN DANIEL", "MONSERRAT ALEJANDRA", "DORIS DE LOS ANGELES", "OLGA ANGELICA", "ANGEL EDUARDO", "EMMANUEL ALEXANDRO", "CARMELO", "FERNANDO ISRAEL", "DULCE BELEN", "LUZ ANDREA", "LOURDES GUADALUPE", "FRANCISCO DANIEL", "JULIA ESTELA", "BLANCA AMALIA", "MA.ANGELICA", "DAMARIS MARIEL", "MARIA VERONICA", "JOSE FAUSTINO VICTOR", "MARIA SANTOS SIRENIA MARGARITA", "ANDRES IVAN", "HUGO JAVIER", "CHRISTIAN JOSUE", "ZURI SADAI", "ALAN JOSE AUGUSTO", "ELIBERTHA", "ALMA REGINA", "FELIX RICARDO", "ERIKA IRENE", "ORQUIDEA MARISU", "LUIS JESUS", "JESUS GUILLERMO", "ELIA BEATRIZ", "JOSELIN XIOMARA", "MARIFER", "CLAUDIA EDITH", "M. IRENE", "WILLIAM MANUEL", "CLAUDIA ELENA", "JOSE MAR", "BRAYAN SAMUEL", "ROSA ESTELA", "MARIA DEL CARMEN YOANCY", "JOSE ROGELIO", "CRISTO ALBERTO", "ANGEL ALEJANDRO", "JOVANNI", "MA. CONCEPCION", "OSCAR EMANUELLE", "MARIO ENRIQUE", "OSCAR JAIR", "ANA YELLY", "YANETH DEL CARMEN", "FERMIN", "GABINA MARGARITA", "MA. TELMA", "MARIA ARACELI", "SAMANTHA", "JEFTE", "CRISTOBAL", "PASCUAL EVELIO", "MARIA APOLONIA", "VIVIANA", "MARIA ROSALVA", "ALBA ALEJANDRA", "DRYSDEL JOSE", "MARIA NOHEMI", "CARLOS PEDRO", "TERESA JOVITA", "OSWALDO DANIEL", "AUSTREBERTO JORGE", "DARYLUZ", "GERTRUDIS", "VERONICA LIZETH", "S. TERESA", "JULIO ALBERTO", "CRISTOPHER", "JOSE JAVIER", "NORA LINDA", "CASTULA", "PATRICIA DE JESUS", "MA DE LA SOLEDAD", "DAGOBERTO", "ANA ISABEL", "DULCE SOLEDAD", "MA.ELENA", "ERNESTO DE JESUS", "ETELVINA LEONOR", "JOSU", "FORTUNATA", "VANESSA IVONNE", "DARWIN", "AURORA EMELIA", "SALVADOR GUADALUPE", "SANDRA KARINA", "CESAR ANTONIO", "MARIA GRICELDA", "EMANUEL GUADALUPE", "KARLA MARIANA" ]; 

            return $datos[ array_rand( $datos ) ];
            break;

        case "apellido" : 
            $datos = [ "MADRAZO", "RAIGOZA", "PACO", "TEPACH", "LIZÁRRAGA", "RENERO", "CASTAÑON", "CALLETANO", "ORTUÑO ", "ORANTES", "CARREÓN ", "ESTAÑOL", "ARZAGA", "ORENDAY", "CORTÉZ ", "TRUJILLLO", "SANTA MARÍA ", "ZORRILLA", "ALARCÓN", "DE NUEDA", "ANDERSON ", "SOTERO", " ALCUDIA ", "ISAIAS", "LEOBARDO", "RUZ ", "TONCHES", " DIAZ", "CÓRDOBA", "TORRECILLAS ", "ZACARÍAS ", "CUENCA", "BROSS", "DESGARENNES", "DÌAZ", "CHICHIA", "LUTZOW", "PORTUGUEZ", "SIXTECO", "GAITAN", "GORTAZAR ", "BEDOY", "MACÍAS", "SINZU", "MEJÌA", "BAZÁN ", "COETO", "BADERAS", "SÁNTIZ", "CAÑAS", "ZUBILLAGA", "PRESTEGUI", "BORRALLEZ", "CHICATTI", "MARAVILLAS ", "ARGÜELLES", "GIRÓN", "RANO", "IBAÑEZ", "CAVARRUBIAS", "MONDRAGÓN", "VÉLEZ ", "MELARA", "ZÚÑIGA", "CASTAÑOS ", "JERÓNIMO", "ALFREDO", "FIZ", "SECA", "SINTA", "GANDARILLA", "GIRAO", "ARROYAVE", "NUNEZ", "REGLA", "HUAN", "TOH", "LUQUEÑO", "CERONIO", "MARI", "KUMAN", "CRISPIN", "ABASCAL", "ARMENDÁRIZ ", "SABAS", "GONZÁLES", "CARREÑO", "BASTO", "MELÉNDEZ  ", "MARTÍNEZ,", "DELERIN", "PIÑERO", "CAMERAS", "BOITES", "ROSETTE", "ALCALÁ", "IBÁÑEZ ", "KATZ", "CANUL Y ", "POUCHOLEN" ];

            return $datos[ array_rand( $datos ) ];
            break;    
    }
}


function codigo_periodo( $modelo, $fecha = null, $tipo = 'SEMANAL' ){
    if( null == $fecha ){
        $fecha = date( "Y-m-d" );
    }
    return substr( $modelo, 0, 2 ).substr( $tipo, 0, 1 ).substr( $fecha, 0, 4 ).str_pad( ( date( "W", strtotime( $fecha ) ) ), 2, "0", STR_PAD_LEFT );
}



function get_datos($month, $year, $day, $x = 1){
    $a = mktime(0,0,0,$month, $day, $year);
    return str_pad((date("W", $a)+$x),2,"0",STR_PAD_LEFT)."-".date("o", $a);
}

function calendario_semanas($month, $year, $comisiones){
    $headings = ["L","M","M","J","V","S", "D"];

    $max  = max($comisiones);
    $min  = min($comisiones);
    $dif  = $max - $min;
    $paso = $dif / 8;

    $calendar = "<div class=\"boxheader mt-5 mb-3\"><h3>".ucfirst(mes($month))." {$year}</h3></div><table class=\"calendar\">";

    // $calendar.= "<tr><td>".implode("</td><td>",$headings)."</td><td></td></tr>";

    $running_day       = date("N",mktime(0,0,0,$month,1,$year));
    $days_in_month     = date("t",mktime(0,0,0,$month,1,$year));
    $days_in_this_week = 1;
    $day_counter       = 0;
    $weeks             = [];
    $semana            = 0;

    $codigo_semana = get_datos($month, $year, 1,0);
    $weeks[$semana] = "<tr periodo=\"{$codigo_semana}\">";

    for($x = 1; $x < $running_day; $x++){
        $weeks[$semana].= "<td></td>";
        $days_in_this_week++;
    }

    $hoy = date("Ymd");
    
    for($list_day = 1; $list_day <= $days_in_month; $list_day++){
        $fondo = 0;
        $id    = ($year*10000)+($month*100)+$list_day;

        if($paso && isset($comisiones[$id]) && $id <= $hoy){
            $fondo = 1+floor(($comisiones[$id] - $min) / $paso);
        }
        else
            if(in_array($running_day, [6,7])) $fondo.=" fsx";

        $weeks[$semana].= "<td class=\"calendar-day ".($id > $hoy ? "muted" : "")."fondo{$fondo}\"><div class=\"number\" ".($id == $hoy ? "style=\"border:3px solid var(--light-blue-7)\"" :"").">{$list_day}</div></td>";

        if($running_day == 7){
            $weeks[$semana].= "<td class=\"data\"><span class=\"display-7 mx-3\">{$codigo_semana}</span></td></tr>";
            $codigo_semana = get_datos($month, $year, $list_day);

            if(($day_counter+1) != $days_in_month){ 
                
                $weeks[++$semana] = "<tr periodo=\"{$codigo_semana}\">";
            }
            $running_day = $days_in_this_week = 0;
            
        }
        $days_in_this_week++; $running_day++; $day_counter++;
        
    }

    if($days_in_this_week < 8 && $days_in_this_week > 1){
        $codigo_semana = get_datos($month, $year, $list_day,0);
        for($x = 1; $x <= (8 - $days_in_this_week); $x++)
            $weeks[$semana].= "<td></td>";
        $weeks[$semana].= "<td class=\"data\"><span class=\"display-7 mx-3\">{$codigo_semana}</span></td></tr>";
    }

    foreach(array_reverse($weeks) as $w)  $calendar .= $w;
    return $calendar.= "</table>";
}
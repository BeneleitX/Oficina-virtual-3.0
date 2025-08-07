<?php 

function alertas( $a ){
    return "\nalerta( '{$a[ "clase" ]}', '{$a[ "icono" ]}',  '{$a[ "texto" ]}' );";
}

// devuelve un número formateado con ceros a la izquierda
function id($n, $digitos = 0)
{
    $array = array_map('intval', str_split(str_pad($n,$digitos,"0", STR_PAD_LEFT)));
    $i     = array_shift($array);
    $res   = "<span style='font-weight:100; opacity:.4'>";

    while($i == "0")
    {
        $res .= $i; 
        $i = array_shift($array);
    }

    $res .= "</span><span style='font-weight:600'>";
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


function validafecha($date, $format = "Y-m-d" ){ 
    $d = DateTime::createFromFormat($format, $date); 
    return $d && $d->format($format) === $date; 
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


function mask( $texto, $tipo = "nombre" ){
    
    switch( $tipo ){
        case "nombre":
            $ok = 2;
            $nueva = "";
            $txt = explode( " ", limpia_acentos( $texto) );
            
            foreach( $txt as $t ){
                $nueva .=" ";
                foreach( str_split( $t ) as $k => $d ){
                    $nueva .= ($k >= $ok ? "*" : $d );
                }
            }
            
            break;

        case "clabe":
            $ok = 12;
            $nueva = "";
            
            foreach( str_split( $texto ) as $k => $d ){
                $nueva .= ($k < $ok ? "*" : $d );
            }
            break;
    }

    return $nueva;
}


function bitacora( $accion, $usuario, array $variables = [] ){
    $db = db_connect();
    $sql = "insert into t_bitacoras values(NULL, {$accion}, {$usuario}, '".date("Y-m-d H:i:s")."', '".json_encode($variables)."', '".getIP()."') ";

    $db->query( $sql );
}


function update_estatus_random( $cantidad ){
    $db  = db_connect();
    $mes = date( "Ym" );
    $sql = "SELECT id 
            FROM t_usuarios 
            WHERE data->'$.updated' != '{$mes}' 
            AND estatus_codigo = '201-ACTIVO' 
            LIMIT {$cantidad}";

    $dat = $db->query( $sql);

    foreach( $dat->getResult() as $socios ){
        $sql = "do f_get_estatus( {$socios->id} , 0)";

        $db->query( $sql );
    }
}


function admin( $codigo ){
    $db = db_connect();
    $data = $db->query("select * from t_variables where codigo = '{$codigo}'")->getRow();

    if($data->tipo == 'JSON'){
        $data->valor = json_decode( $data->valor );
    }

    return $data->valor;
}


function limpia_acentos($Texto){
    $valor_htm = array('&aacute;','&Aacute;','&eacute;','&Eacute;','&iacute;','&Iacute;','&oacute;','&Oacute;','&uacute;','&Uacute;','&ntilde;','&Ntilde;','&uuml;','&Uuml;',
    '&agrave;','&Agrave;','&egrave;','&Egrave;','&igrave;','&Igrave;','&ograve;','&Ograve;','&ugrave;','&Ugrave;');    // Valores originales   
    $valor_acent = array('a','A','e','E','i','I','o','O','u','U','ñ','Ñ','u','U','a','A','e','E','i','I','o','O','u','U');    // Nuevos valores   
        $Cambia_Texto = str_replace($valor_htm,$valor_acent,$Texto);  
    // Separamos cada una de las letras con acentos y dieresis, y la ponemos en un array
    preg_match_all('/\w/u', 'áàäéèëíìïòóöùúüÀÁÄÈÉËÌÍÏÒÓÖÙÚÜñÑ', $Texto);
    $cadena = array_map(
        function($eli_acent) { return '/'.$eli_acent.'/u'; },
        $Texto[0]
    );
    // realizamos la sustitución
    $sustitucion = preg_replace($cadena, str_split('aaaeeeiiiooouuuAAAEEEIIIOOOUUU'), $Cambia_Texto);
        return $sustitucion;
}


function estatus( $codigo, $bn = false ){
    return "<span class=\"badge rounded-pill bg-".( $bn ? "black" : ESTATUS[ $codigo ][ "color" ] )."\"><i class=\"fa fa-circle\"></i> ".ESTATUS[ $codigo ][ "descripcion" ]."</span>";
}


function fecha( $fecha, $tipo = "normal" )
{
    $f = explode( "-", $fecha );

    switch( $tipo ){
        case "normal":
            return substr( $f[2], 0, 2 )." de ".mes( $f[ 1 ] ).", ".$f[ 0 ];
            break;
        case "cumple":
            return substr( $f[2], 0, 2 )." de ".mes( $f[ 1 ] );
            break;
        case "mes":
            return mes( $f[ 1 ] )." ".$f[ 0 ];
            break;
    }
}


function mes($mesnum, $ext = 0)
{
    $mesnum = intval( $mesnum );

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

function aplicaImpuestos( $cantidad, $tipo, $fecha = null, $modelo = null )
{
    if( !$fecha ){
        $fecha = date( "Y-m-d" );
    }

    if( $modelo == "50-INVERSION" ){
        $desglose = [
            [
                "descripcion" => "TOTAL",
                "cantidad" => $cantidad 
            ]
        ];
    }
    else{
        switch( $tipo ){
            case 2: // DESCUENTO BONIFICACION ESPECIAL
                $importe = $cantidad / 1.16;
                $promo = $importe * .1;
                $sub = $importe - $promo;
                $iva = $sub * .16;
                $ret = $sub * 0.0125;
                $total = $cantidad - $promo + $iva - $ret;

                $desglose = [
                    [
                        "descripcion" => "DESC. BONIF. P-P BENELEIT",
                        "cantidad" => ($cantidad / 1.16) * .1
                    ],
                    [
                        "descripcion" => "NETO",
                        "cantidad" => $cantidad
                    ],
                    [
                        "descripcion" => "IMPORTE",
                        "cantidad" => $cantidad / 1.16
                    ],
                    [
                        "descripcion" => "SUBTOTAL",
                        "cantidad" => $importe - $promo
                    ],
                    [
                        "descripcion" => "I.V.A.",
                        "cantidad" => $sub * .16
                    ],
                    [
                        "descripcion" => "RET 1.5%",
                        "cantidad" => $sub * 0.0125
                    ],                
                    [
                        "descripcion" => "TOTAL",
                        "cantidad" => $cantidad - $promo + $iva - $ret
                    ]
                ];
                break;
            
            case 1: // NO RETENCION 
                $desglose = [
                    [
                        "descripcion" => "SUBTOTAL",
                        "cantidad" => $cantidad / 1.16
                    ],
                    [
                        "descripcion" => "IMPORTE",
                        "cantidad" => $cantidad
                    ],
                    [
                        "descripcion" => "RET. DE I.V.A. (10.66%)",
                        "cantidad" => ( $cantidad / 1.16 ) * 0.1066
                    ],
                    [
                        "descripcion" => "I.V.A.",
                        "cantidad" => ( $cantidad / 1.16 ) * 0.16
                    ],
                    [
                        "descripcion" => "TOTAL",
                        "cantidad" => ( $cantidad / 1.16 ) - ( ( $cantidad / 1.16 ) * 0.1066 ) + ( ( $cantidad / 1.16 ) * 0.16 )
                    ]
                ];
            break;

            default: // RETENCION ISR
                $desglose = [
                    [
                        "descripcion" => "I.S.R.",
                        "cantidad" => getISR( $cantidad, date( "Y", strtotime($fecha) ) )
                    ],
                    [
                        "descripcion" => "TOTAL",
                        "cantidad" => $cantidad - getISR( $cantidad, date( "Y", strtotime($fecha) ) )
                    ]
                ];
                break;
        }
    }

    return $desglose; 
}


function calcula_venta_periodo( $periodo )
{
    $db  = db_connect(); 

    $sql = "SELECT sum( ped.data->>'$.total' ) as suma
            from t_pedidos ped
            join t_periodos per on per.codigo = '{$periodo[ "codigo" ]}' and ped.modelo_codigo = per.modelo_codigo
            where cast( ped.fechas->>'$.pagado' as date ) between per.inicia and per.termina";

    $periodo[ "data" ][ "venta" ] = $db->query( $sql )->getRow()->suma ?? 0;

    model( "PeriodoModel" )->save( $periodo );
}


function get_hash( $pedido ){
    $db = db_connect();
    $sql = "SELECT * FROM t_inversiones 
            WHERE pedido_id = $pedido 
            AND SUBSTRING( estatus_codigo, 1, 3 ) > 400";

    return $db->query( $sql )->getRowArray();
}


/**
 * Verifica si un pedido debe tener el regalo de biex o no
 * 
 * @param array $pedido Pedido a verificar
 * @param object $usuario Socio que realizó el pedido
 * @param int $dia_limite Día límite para considerar si el socio tiene regalo biex
 * @return void
 */
function check_biex( $pedido, $usuario, $dia_limite = 25 )
{     
    $fecha = $pedido[ "fechas" ][ "pagado" ];
    $mes   = date( "m", strtotime( $fecha ) );
    $year  = date( "Y", strtotime( $fecha ) );
    $dia   = date( "d", strtotime( $fecha ) );

    // si el pedido está pagado y tiene regalo biex y la fecha de pago es despues del 25
    if( 
        $pedido[ "modelo_codigo" ] == "10-NUTRICION" &&
        substr( $pedido[ "estatus_codigo" ], 0, 3 ) > 400 && 
        $pedido[ "PTS" ][ "230-REGALOBIEX" ] > 0 &&
        $dia > $dia_limite
    ){    

        // Si el socio se registró antes del 25
        // quitar el regalo biex
        if( date( "Y-m-d", strtotime( $usuario->historial->registro ) ) < "{$year}-{$mes}-".( $dia_limite + 1 ) ){

            $db  = db_connect(); 

            $sql = "UPDATE t_pedidos p
                    set p.data = json_set( p.data, '$.productos', p.data->'$.productos' - p.PTS->'$.\"230-REGALOBIEX\"' ),
                    p.PTS = json_set( p.PTS, '$.\"230-REGALOBIEX\"', 0 ),
                    p.promociones = json_set( p.promociones, '$.\"230-REGALOBIEX\".productos', json_object() )
                    where p.id = {$pedido[ "id" ]}";

            $db->query( $sql );
        }
    }
}


function getISR( $cantidad, $y = null, $t = "SEMANAL" ){

    // Protección para rangos fuera de lo contemplado en la base de datos
    
    if( $y < 2024 ){
        $y = 2024;
    }
    if( $y > date( "Y" ) ){
        $y = date( "Y" );
    }

    $db = db_connect();

    $sql = "SELECT fijo, porcentaje, minimo 
            FROM t_isr
            WHERE tipo = '{$t}' and anio = {$y} and {$cantidad} BETWEEN minimo AND maximo";
	
    $isr = $db->query( $sql )->getRowArray();

    $excedente = $cantidad - $isr[ "minimo" ];	
    
    $entero = 100 * ( $isr[ "fijo" ] + ( ( $excedente *  $isr[ "porcentaje" ] ) / 100 ) );
    return $entero / 100;
}


function random( $tipo )
{
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


function get_primercompra( $u, $m )
{
    return 0;
}


function marca( $queries, $texto, $case = null )
{
    $replaces = [];
    $colores  = [
        "yellow",
        "light-pink",
        "light-green",
        "cyan",
        "gray-600"
    ];

    foreach( $queries as $k => $q){
        if( $case == "upper" ){
            $queries[ $k ] = strtoupper( $q );
            $replaces[] = "<span class=\"bg-{$colores[ $k ]}\">{$queries[ $k ]}</span>";
        }
        else{
            $queries[ $k ] = strtolower( $q );
            $replaces[] = "<span class=\"bg-{$colores[ $k ]}\">{$queries[ $k ]}</span>";
        }


    }

    if( $case == "upper" ){
        $texto = strtoupper( $texto );
    }
    else{
        $texto = strtolower( $texto );
    }

    return str_replace( $queries, $replaces, $texto );
}


function tarjeta( $tarjeta )
{
    return substr($tarjeta, 0, 4).substr($tarjeta, 5, 4)."0 ".substr($tarjeta, 11, 3).substr($tarjeta, 15, 4);
}


function codigo_periodo( $modelo, $fecha = null, $tipo = 'SEMANAL' )
{
    if( null == $fecha ){
        $fecha = date( "Y-m-d" );
    }

    $tipo = MODELOS[ $modelo ][ "settings" ][ "periodo" ];

    return 
        substr( $modelo, 0, 2 ).
        substr( $tipo, 0, 1 ).
        ( $tipo == "SEMANAL" ? date( "o", strtotime( $fecha ) ) : date( "Y", strtotime( $fecha ) ) ).
        str_pad( ( $tipo == "SEMANAL" ? date( "W", strtotime( $fecha ) ) : date( "m", strtotime( $fecha ) ) ), 2, "0", STR_PAD_LEFT );
}


function periodo( $periodo )
{
    return substr( $periodo, 7, 2 )."-".substr( $periodo, 3, 4 );
}


function get_datos($month, $year, $day, $x = 1)
{
    $a = mktime(0,0,0,$month, $day, $year);
    return str_pad((date("W", $a)+$x),2,"0",STR_PAD_LEFT)."-".date("o", $a);
}


function calendario_semanas($month, $year, $comisiones)
{
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


function dia($dianum, $ext = 0)
{
    if($dianum < 1) $dianum += 7;
    if($dianum > 7) $dianum -= 7;

    $diapal = "";

    switch($dianum)
    {
        case 1 : $diapal = "lunes"; break;
        case 2 : $diapal = "martes"; break;
        case 3 : $diapal = "miercoles"; break;
        case 4 : $diapal = "jueves"; break;
        case 5 : $diapal = "viernes"; break;
        case 6 : $diapal = "sábado"; break;
        case 7 : $diapal = "domingo"; break;
    }

    if($ext)
    {
        $diapal = substr($diapal, 0, $ext);
    }

    return $diapal;
}


function pills( $ruta, $activo, $callback = null, $extra = null )
{
    $html = "\n<ul class=\"nav nav-pills my-4\">";
            
    foreach( MODELOS as $m ){
        if( $m[ "settings" ][ "efectivo" ] ){
            $html .= "\n<li class=\"nav-item\"><a class=\"text-{$m[ "settings" ][ "color" ]} nav-link ".( $activo == $m[ "codigo" ] ? "text-white bg-".$m[ "settings" ][ "color" ] : "")."\" aria-current=\"page\" href=\"".base_url( $ruta."/".$m[ "codigo" ].( $callback ? "/".eval( "return ".$callback."(\"{$m[ "codigo" ]}\");" ) : "" ) ).( $extra ? "/".$extra : "")."\"><i class=\"fa fa-{$m[ "settings" ][ "icono" ]}\"></i> {$m[ "nombre" ]}</a></li>";
        }
    }
    
    $html .= "</ul>";

    return $html;
}


function base64_png( $file )
{
    return chunk_split( "data:image/png;base64,".base64_encode( file_get_contents( $file ) ) );
}


function envia_correo( $usuario, $subject, $message, $imagenes = [] )
{
    
    $margin = 60;
    $width  = 600;
    $avatar = 80;
    $email  = service('email');

    $config['protocol'] = 'sendmail';
    $config['charset']  = 'UTF-8'; 
    $config['wordWrap'] = false;
    $config['mailtype'] = "html";

    $email->initialize($config);

    $attachments = [
        "assets/img/icon_beneleit3.png",
        "assets/img/logo_blanco.png",
        "assets/img/logo_color.png"
    ];

    // Si tiene avatar, agregar la imagen
    if( $usuario->data->avatar->activo !== null ){
        $attachments[] = "data/{$usuario->id}/avatar/".$usuario->data->avatar->imagenes[ $usuario->data->avatar->activo ];
    }

    if( $_SERVER[ "SERVER_ADDR" ] == "127.0.0.1" ){
        foreach( $attachments as $k => $a ){ 
            $attachments[ $k ] = base_url().$a;
        }

        foreach( $imagenes as $k => $a ){ 
            $imagenes[ $k ] = base_url().$a;
            $message = str_replace( "%%{$k}%%", $imagenes[ $k ], $message );
        }
    }
    else{
        foreach( $attachments as $k => $a ){ 
            $email->attach( $a, "attachment", ( $k + 1 ).".png" ); 
            $attachments[ $k ] = "cid:".$email->setAttachmentCID( ( $k + 1 ).".png" );
        }

        foreach( $imagenes as $k => $a ){ 
            $email->attach( $a ); 
            $imagenes[ $k ] = "cid:".$email->setAttachmentCID( $a );
            $message = str_replace( "%%{$k}%%", $imagenes[ $k ], $message );
        }
    }



    $avatar = $usuario->data->avatar->activo !== null ?
        "<img style=\"width:{$avatar}px; height: {$avatar}px;border-radius:50%; margin:10px {$margin}px;\" src=\"{$attachments[3]}\" alt=\"avatar\" width=\"{$avatar}\" height=\"{$avatar}\">" : "<div style=\"border-radius:50%; margin:10px {$margin}px; width:{$avatar}px; height:{$avatar}px;display:inline-block; background:#009779; text-align:center;\"><div style=\"border-radius:50%; width:{$avatar}px;height:{$avatar}px;font-size:".($avatar/2)."px;line-height:".( $avatar / 2 )."px; padding-top:20%; display:block; color:white; padding-top:".( $avatar / 4)."px !important;\" class=\"text-teal bg-gray-400\">".$usuario->iniciales()."</div></div>";
    
    $html = "
        <div style=\"width:100%; margin:0; padding:50px 0; text-align:center; background:rgba(33,37,41,0.1);\">
            <div style=\"width:{$width}px; font-family:arial; padding:0; margin:0 auto; text-align:left;  font-size:0.9rem;\">    
                <div style=\"width:100%; font-family:arial; padding:0px; color:white; border:2px solid #1a2542; border-radius:6px 6px 0 0; margin:0 auto; background-color:#1a2542; background-repeat:no-repeat; background-position:-100px -50px; background-image: url({$attachments[0]}); \">
                    <table style=\"width:100%\"><tr>
                        <td><img style=\"margin:0 {$margin}px;\" src=\"{$attachments[1]}\" alt=\"Beneleit logo\" width=\"100\" height=\"33\" class=\"beneleit_logo\"></td>
                        <td style=\"text-align:right\">{$avatar}</td>
                    </tr></table>
                </div>
        
                <div style=\"width:100%; font-family:arial; padding:0px; border:2px solid rgba(33,37,41,1); background:white; border-radius:0 0 6px 6px;margin:0 auto 30px auto;\">
                    <div style=\"padding:20px {$margin}px; text-align:centerx; line-height: 1.3\">
                        <h2 style=\"color:#009779\">{$subject}</h2>
                        {$message}
                    </div>
                </div>
        
                <div style=\"font-size:0.7rem; color:#888\">
                    <p>Este mensaje está dirigido a ".$usuario->nombre( 2 )." ({$usuario->correo}) como parte de los servicios que se le brincan como SOCIO BENELEIT ".$usuario->id().".</p>
        
                    <p>
                        <img src=\"{$attachments[2]}\" width=\"50\" height=\"17\" alt=\"Logo beneleit\">
                        <a style=\"text-decoration:none; color:#009779; font-weight:bold;\" href=\"#\">Nutrición</a> |
                        <a style=\"text-decoration:none; color:#009779; font-weight:bold;\" href=\"#\">Alimentos</a> |
                        <a style=\"text-decoration:none; color:#009779; font-weight:bold;\" href=\"#\">Móvil</a>
                    </p>
                    <p>
                        &copy;".date( "Y")." Beneleit SA de CV, empresa Mexicana con domicilio en Avenida 23 Oriente No. 405–A Colonia El Carmen, C.P. 72530, Histórica Puebla De Zaragoza, México. Este es un correo informativo. Para cualquier aclaración comunicate a nuestro call center.
                    </p>
                </div>
            </div>
        </div>
    ";
    
    // BITACORA envío de correo de recuperación de password        
    bitacora( 35, $usuario->id, [
        "correo" => $usuario->correo,
        "motivo" => $subject
    ] );

    $email->setFrom( "app@beneleit.mx", "App Beneleit" ); 
    $email->setTo( $usuario->correo );
    $email->setMailType('html');  
    $email->setSubject( $subject );
    $email->setMessage( $html );
    $email->send();

    return $html;
}


function getPaqueteMovil( $celular )
{
    $db = db_connect();
    return $db->query( "SELECT 
                pr.codigo AS paquete,
                CAST( pe.fechas->>'$.pagado' AS DATE ) AS fechacompra,
                pr.data->>'$.dias' AS dias,
                CAST( DATE_FORMAT( CAST( pe.fechas->>'$.pagado' AS DATE ) + INTERVAL pr.data->>'$.dias' DAY, '%Y-%m-%d' ) AS DATE ) AS vencimiento,
                pr.data->'$.puntos.\"310-TELEFONIA\"' AS puntos,
                pr.data->>'$.nombre' AS nombre,
                pr.data->>'$.descripcion' AS descripcion
            FROM t_pedidos pe
            JOIN t_productos pr ON pr.codigo = JSON_UNQUOTE( JSON_EXTRACT( JSON_KEYS( pe.promociones->>'$.\"310-TELEFONIA\".productos' ) , '$[0]' ) )
            WHERE pe.estatus_codigo = '420-PAGADO' AND pe.modelo_codigo = '20-TELEFONIA' AND pe.data->>'$.entrega' = '{$celular}'
            AND now() BETWEEN CAST( pe.fechas->>'$.pagado' AS DATE ) AND CAST( DATE_FORMAT( CAST( pe.fechas->>'$.pagado' AS DATE ) + INTERVAL pr.data->>'$.dias' DAY, '%Y-%m-%d' ) AS DATE )
            ORDER BY pr.data->'$.puntos.\"310-TELEFONIA\"' DESC" )->getResultArray();
}


function load_catalogo( $tabla, $where = null, $nombre = null )
{
    dd( strtoupper( $nombre ?? $tabla ) );
    if( defined( strtoupper( $nombre ?? $tabla ) ) ) return;
    $db = db_connect();


    // catálogo de modelos de negocio
    $array = [];
    
    foreach( $db->query( "select * from t_{$tabla}".( $where ? " where ".$where : "")." order by ".( $nombre == "stocks" ? "nombre" : "codigo") )->getResultArray() as $row ){ 
        $tmp = [];

        foreach( $row as $k => $d ){
            $tmp[ $k ] = is_array( $obj = json_decode( $d, 1 ) ) ? $obj : $d;
        }

        $array[ $row[ "codigo" ] ] = $tmp;
    }

    define( strtoupper( $nombre ?? $tabla ), $array );
    
}


function nuevo_pedido( $modelo )
{
    load_catalogo( "promociones", "estatus_codigo = '201-ACTIVO' AND ( modelo_codigo = '{$modelo}' OR settings->'$.universal' = true )", "pp" );
    
    $PTS    = [];
    $promos = [];

    foreach( PP as $p ){
        $PTS[ $p[ "codigo" ] ] = 0;
        $promos[ $p[ "codigo" ] ] = [];
    }

    ksort( $promos );
    
    $nuevo  = [
        "id" => null,
        "referencia" => null,
        "estatus_codigo" => "250-EN-PROCESO",
        "modelo_codigo" => $modelo,
        "PTS" =>  $PTS,
        "usuario_id" => null,
        "data" => [
            "peso" => 0,
            "sat" => [
                "cfd" => null,
                "fecha" => null,
                "factura" => null,
            ],
            "saldo" => 0,
            "mesanterior" => 0,
            "costoxbulto" => 0,
            "pesoxbulto" => MODELOS[ $modelo ][ "settings" ][ "pesoxbulto"],
            "productosxbulto" => MODELOS[ $modelo ][ "settings" ][ "productosxbulto" ],
            "total" => 0,
            "comisionbanco" => 0,
            "comisionentrega" => 0,
            "entrega" => null,
            "productos" => 0,
            "tercernivel" => [
                "cantidad" => 0,
                "socio" => 0
            ]
        ],
        "promociones" => $promos,
        "metodopago_codigo" => null,
        "metodoentrega_codigo" => null,
        "fechas" => [
            "creado" => date( "Y-m-d H:i:s" )
        ]
    ];
    
    return $nuevo;
}


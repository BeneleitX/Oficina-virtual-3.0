
<div class="row mx-2 my-3">
<?php

$bot     = "";
$headers = "";
$cal     = "";
$mascara = date( "Y-m" )."-01";

$m_0 = date( "Ym" );
$m_1 = date( "Ym", strtotime( $mascara." -1 month" ) );
$m_2 = date( "Ym", strtotime( $mascara." -2 month" ) );

$t_0 = strtoupper( mes( date( "m" ) ) )." ".date( "Y" );
$t_1 = strtoupper( mes( date( "m", strtotime( $mascara. " -1 month" ) ) ) )." ".date( "Y", strtotime( $mascara. " -1 month" ) );
$t_2 = strtoupper( mes( date( "m", strtotime( $mascara. " -2 month" ) ) ) )." ".date( "Y", strtotime( $mascara. ' -2 month' ) );

$db  = db_connect();
$cx  = [];

if( !defined( "calificaciones" ) ){
    load_catalogo( "calificaciones", "");
}

foreach( MODELOS as $m ){

    $sql = "select 
        f_get_calificacion( {$usuario->id}, '{$m_2}', '{$m[ "codigo" ]}' ) as 'm_2', 
        f_get_calificacion( {$usuario->id}, '{$m_1}', '{$m[ "codigo" ]}' ) as 'm_1', 
        f_get_calificacion( {$usuario->id}, '{$m_0}', '{$m[ "codigo" ]}' ) as 'm_0'";
     
    $cx[ $m["codigo" ] ] = $db->query($sql)->getRowArray();

    $estatus = ESTATUS[ $usuario->data->estatus->modelos->{$m[ "codigo" ]} ];

    $v = $usuario->get_verificacion( $m[ "codigo" ] );

    $pendientes = [];

    foreach( $v->puntos as $p => $e ){

        if( $e != true ){
            $pendientes[] = VARIABLES[ "verificaciones" ][ "valor" ][ $p ][ "descripcion" ];
        }
    } 
    
    echo "\n<div class=\"col-6 text-center mt-3 mb-1\"><div class=\"text-{$m[ "settings" ][ "color" ]}\"><strong><i class=\"fa fa-{$m[ "settings" ][ "icono" ]}\"></i> {$m[ "nombre" ]}</strong></div>
    
    <div class=\"progress mb-1\" data-bs-html=\"true\" aria-valuenow=\"{$v->porcentaje}\" aria-valuemin=\"0\" aria-valuemax=\"100\" style=\"height:6px; border-radius:10px\" title=\"".( $v->estatus ? "CUENTA VERIFICADA" : "VERIFICACION AL ".$v->porcentaje."%<hr class='mt-0'><p style='text-align:left'>En espera:<ul style='text-align:left'><li>".implode( "</li><li>", $pendientes )."</li></ul></p>" )."\" data-bs-toggle=\"tooltip\">
        <div class=\"progress-bar bg-".( $v->estatus ? "teal" : "red progress-bar-striped progress-bar-animated" )."\" style=\"width: {$v->porcentaje}%\"></div>
    </div>

    <div class=\"card bg-{$estatus[ "color" ]}\"><div class=\"xcard-body\">";

    echo "\n<div class=\"small mb-2\"><div class=\"pt-2 xbadge col-12 bg-{$estatus[ "color" ]} text-white\" style=\"line-height:1.1\">{$estatus[ "descripcion" ]}</div></div><div class=\"pb-2 px-2\">";
  
    switch( $m["codigo"] ){
        case "10-NUTRICION": 

            $cal = "<div class=\"input-group input-group-sm\" data-bs-toggle=\"tooltip\" title=\"\">
                
                <input data-bs-toggle=\"tooltip\" title=\"<span class='small'>{$t_2}</span><br>".( CALIFICACIONES[ $cx[ $m["codigo" ] ][ "m_2" ] ][ "descripcion" ] )."\" disabled type=\"text\" value=\"".( intval( substr( $cx[ $m["codigo" ] ][ "m_2" ], 0, 2 ) ) > 0 ? substr( $cx[ $m["codigo" ] ][ "m_2" ], 3, 1 ) : "" )."\" class=\"form-control py-1 px-0 text-center text-".( intval( substr( $cx[ $m["codigo" ] ][ "m_2" ], 0, 2 ) ) > 50 ? "teal" : "gray-500" )."\" style=\"border-right:2px solid #fff !important; background:var(--bs-".( intval( substr( $cx[ $m["codigo" ] ][ "m_2" ], 0, 2 ) ) > 50 ? "gray-300" : "gray-100" )."); border:none\">
            
                <input data-bs-toggle=\"tooltip\" title=\"<span class='small'>{$t_1}</span><br>".( CALIFICACIONES[ $cx[ $m["codigo" ] ][ "m_1" ] ][ "descripcion" ] )."\" disabled type=\"text\" value=\"".( intval( substr( $cx[ $m["codigo" ] ][ "m_1" ], 0, 2 ) ) > 0 ? substr( $cx[ $m["codigo" ] ][ "m_1" ], 3, 1 ) : "" )."\" class=\"form-control py-1 px-0 text-center text-".( intval( substr( $cx[ $m["codigo" ] ][ "m_1" ], 0, 2 ) ) > 0 ? "teal" : "gray-500" )."\" style=\"background:var(--bs-".( intval( substr( $cx[ $m["codigo" ] ][ "m_1" ], 0, 2 ) ) >= 10 ? "gray-300" : "gray-100" )."); border:none\">
            
                <input data-bs-toggle=\"tooltip\" title=\"<span class='small'>{$t_0}</span><br>".( CALIFICACIONES[ $cx[ $m["codigo" ] ][ "m_0" ] ][ "descripcion" ] )."\" disabled type=\"text\" value=\"".( intval( substr( $cx[ $m["codigo" ] ][ "m_0" ], 0, 2 ) ) > 0 ? substr( $cx[ $m["codigo" ] ][ "m_0" ], 3, 1 ) : "" )."\" class=\"form-control py-1 px-0 text-center text-{$estatus[ "color" ]}\" style=\"border-left:1px solid #fff !important; font-weight:700; background:var(--bs-".( intval( substr( $cx[ $m["codigo" ] ][ "m_0" ], 0, 2 ) ) >= 10 ? "gray-300" : "gray-100" )."); border:none\"></div>";
            break;

        case "20-TELEFONIA": 

            $cal = "<div class=\"input-group input-group-sm\" data-bs-toggle=\"tooltip\" title=\"\">
            <input data-bs-toggle=\"tooltip\" title=\"<span class='small'>{$t_1}</span><br>".( CALIFICACIONES[ $cx[ $m["codigo" ] ][ "m_1" ] ][ "descripcion" ])."\"disabled type=\"text\" value=\"".( intval( substr( $cx[ $m["codigo" ] ][ "m_0" ], 0, 2 ) ) >= 10 ?  CALIFICACIONES[ $cx[ $m["codigo" ] ][ "m_0" ] ][ "descripcion" ] : "" )."\" class=\"form-control py-1 px-0 text-center text-{$estatus[ "color" ]}\" style=\"font-weight:700; background:var(--bs-".( intval( substr( $cx[ $m["codigo" ] ][ "m_0" ], 0, 2 ) ) >= 10 ? "gray-300" : "gray-100" )."); border:none\"></div>";
            break; 

        case "30-ALIMENTOS": 
            $cal = "<div class=\"input-group input-group-sm\" data-bs-toggle=\"tooltip\" title=\"\">
            <input data-bs-toggle=\"tooltip\" title=\"<span class='small'>{$t_2}</span><br>".( CALIFICACIONES[ $cx[ $m["codigo" ] ][ "m_2" ] ][ "descripcion" ])."\" disabled type=\"text\" value=\"".( intval( substr( $cx[ $m["codigo" ] ][ "m_2" ], 0, 2 ) ) >= 10 ? substr( $cx[ $m["codigo" ] ][ "m_2" ], 3, 2 ) : "" )."\" class=\"form-control py-1 px-0 text-center text-".( intval( substr( $cx[ $m["codigo" ] ][ "m_2" ], 0, 2 ) ) >= 10 ? "teal" : "gray-500" )."\" style=\"border-right:2px solid #fff !important; background:var(--bs-".( intval( substr( $cx[ $m["codigo" ] ][ "m_2" ], 0, 2 ) ) >= 10 ? "gray-300" : "gray-100" )."); border:none\">
            <input data-bs-toggle=\"tooltip\" title=\"<span class='small'>{$t_1}</span><br>".( CALIFICACIONES[ $cx[ $m["codigo" ] ][ "m_1" ] ][ "descripcion" ])."\" disabled type=\"text\" value=\"".( intval( substr( $cx[ $m["codigo" ] ][ "m_1" ], 0, 2 ) ) >= 10 ? substr( $cx[ $m["codigo" ] ][ "m_1" ], 3, 2 ) : "" )."\" class=\"form-control py-1 px-0 text-center text-".( intval( substr( $cx[ $m["codigo" ] ][ "m_1" ], 0, 2 ) ) >= 10 ? "teal" : "gray-500" )."\" style=\"border-right:2px solid #fff !important; background:var(--bs-".( intval( substr( $cx[ $m["codigo" ] ][ "m_1" ], 0, 2 ) ) >= 10 ? "gray-300" : "gray-100" )."); border:none\">
            <input data-bs-toggle=\"tooltip\" title=\"<span class='small'>{$t_0}</span><br>".( CALIFICACIONES[ $cx[ $m["codigo" ] ][ "m_0" ] ][ "descripcion" ])."\" disabled type=\"text\" value=\"".( intval( substr( $cx[ $m["codigo" ] ][ "m_0" ], 0, 2 ) ) >= 10 ? substr( $cx[ $m["codigo" ] ][ "m_0" ], 3, 2 ) : "" )."\" class=\"form-control py-1 px-0 text-center text-{$estatus[ "color" ]}\" style=\"font-weight:700; background:var(--bs-".( intval( substr( $cx[ $m["codigo" ] ][ "m_0" ], 0, 2 ) ) >= 10 ? "gray-300" : "gray-100" )."); border:none\"></div>";
            break;

        case "40-GASOLINAS": 
            $cal = "<div class=\"input-group input-group-sm\" data-bs-toggle=\"tooltip\" title=\"\">
            <input data-bs-toggle=\"tooltip\" title=\"<span class='small'>{$t_1}</span><br>".( CALIFICACIONES[ $cx[ $m["codigo" ] ][ "m_1" ] ][ "descripcion" ])."\"disabled type=\"text\" value=\"".( intval( substr( $cx[ $m["codigo" ] ][ "m_1" ], 0, 2 ) ) >= 10 ?  CALIFICACIONES[ $cx[ $m["codigo" ] ][ "m_1" ] ][ "descripcion" ] : "" )."\" class=\"form-control py-1 px-0 text-center text-{$estatus[ "color" ]}\" style=\"font-weight:700; background:var(--bs-".( intval( substr( $cx[ $m["codigo" ] ][ "m_1" ], 0, 2 ) ) >= 10 ? "gray-300" : "gray-100" )."); border:none\">
            <input data-bs-toggle=\"tooltip\" title=\"<span class='small'>{$t_0}</span><br>".( CALIFICACIONES[ $cx[ $m["codigo" ] ][ "m_0" ] ][ "descripcion" ])."\"disabled type=\"text\" value=\"".( intval( substr( $cx[ $m["codigo" ] ][ "m_0" ], 0, 2 ) ) >= 10 ?  CALIFICACIONES[ $cx[ $m["codigo" ] ][ "m_0" ] ][ "descripcion" ] : "" )."\" class=\"form-control py-1 px-0 text-center text-{$estatus[ "color" ]}\" style=\"font-weight:700; background:var(--bs-".( intval( substr( $cx[ $m["codigo" ] ][ "m_0" ], 0, 2 ) ) >= 10 ? "gray-300" : "gray-100" )."); border:none\"></div>";
            break;

        case "50-INVERSION": 
            $cal = "<div class=\"input-group input-group-sm\" data-bs-toggle=\"tooltip\" title=\"\">
            <input data-bs-toggle=\"tooltip\" title=\"<span class='small'>{$t_0}</span><br>".( CALIFICACIONES[ $cx[ $m["codigo" ] ][ "m_0" ] ][ "descripcion" ])."\"disabled type=\"text\" value=\"".( intval( substr( $cx[ $m["codigo" ] ][ "m_0" ], 0, 2 ) ) >= 10 ?  CALIFICACIONES[ $cx[ $m["codigo" ] ][ "m_0" ] ][ "descripcion" ] : "" )."\" class=\"form-control py-1 px-0 text-center text-{$estatus[ "color" ]}\" style=\"font-weight:700; background:var(--bs-".( intval( substr( $cx[ $m["codigo" ] ][ "m_0" ], 0, 2 ) ) >= 10 ? "gray-300" : "gray-100" )."); border:none\"></div>";
            break;                
    }

    echo "\n{$cal}\n</div></div></div></div>";
}

?>
</div>

<?php
if( $usuario->data->credencial->estatus == "-2" ){
    echo "\n<div class=\"alert alert-danger mx-3 p-3 text-center\"><p class=\"text-red\">Tus documentos para la validación de cuenta requieren atención en el perfil de datos</p><p class=\"text-center\"><span class=\"badge bg-red\">Ir a mi perfil</span></p></div>";
}
?>
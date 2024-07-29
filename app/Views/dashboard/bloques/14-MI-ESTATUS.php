<div class="position-relative mx-4 mt-4 mb-3"><div class="text-center position-absolute bg-<?php echo $usuario->verificado->estatus ? "teal" : ( $usuario->verificado->porcentaje ? "red" : "gray-500" );  ?> fs-2 rounded-circle d-block" style="width:3rem;height:3rem; line-height:3rem; top:-11px; left:-5px"><i class="far fa-circle-<?php echo $usuario->verificado->estatus ? "check" : "xmark"; ?> text-white"></i></div>
    <div class="progress" aria-valuenow="<?php echo $usuario->verificado->porcentaje; ?>" aria-valuemin="0" aria-valuemax="100" style="height:24px; border-radius:10px">
    <div class="progress-bar bg-<?php echo $usuario->verificado->estatus ? "teal" : "red progress-bar-striped progress-bar-animated"; ?>" style="width: <?php echo $usuario->verificado->porcentaje; ?>%"><?php echo $usuario->verificado->estatus ? "SOCIO VERIFICADO" : "VERIFICACION AL ".$usuario->verificado->porcentaje."%"; ?></div>
    </div>
</div>
<?php

$bot = "";
$headers = "";
$cal = "";

$m_0 = date('Ym');
$m_1 = date('Ym', strtotime( date('Y-m').'-01'. ' -1 month' ) );
$m_2 = date('Ym', strtotime( date('Y-m').'-01'. ' -2 month' ) );

$t_0 = strtoupper( mes( date('m') ) )." ".date('Y');
$t_1 = strtoupper( mes( date('m', strtotime( date('Y-m').'-01'. ' -1 month' ) ) ) )." ".date('Y', strtotime( date('Y-m').'-01'. ' -1 month' ) );
$t_2 = strtoupper( mes( date('m', strtotime( date('Y-m').'-01'. ' -2 month' ) ) ) )." ".date('Y', strtotime( date('Y-m').'-01'. ' -2 month' ) );

$db  = db_connect();
$cx = [];


load_catalogo( "calificaciones", "estatus_codigo = '201-ACTIVO'");

foreach( MODELOS as $m ){
    $sql = "select 
        f_get_calificacion( {$usuario->id}, '{$m_2}', '{$m[ "codigo" ]}' ) as 'm_2', 
        f_get_calificacion( {$usuario->id}, '{$m_1}', '{$m[ "codigo" ]}' ) as 'm_1', 
        f_get_calificacion( {$usuario->id}, '{$m_0}', '{$m[ "codigo" ]}' ) as 'm_0'";
     
        $cx[ $m["codigo" ] ] = $db->query($sql)->getRowArray();
    
    $estatus = ESTATUS[ $usuario->data->estatus->modelos->{$m[ "codigo" ]} ];
    $headers .= "<th class=\"text-center text-{$m[ "settings" ][ "color" ]}\"><i class=\"fa fa-{$m[ "settings" ][ "icono" ]}\"></i> {$m[ "nombre" ]}</td>";
    $bot .="<td class=\"col-4 rounded p-2 text-center small bg-{$estatus[ "color" ]} text-white\" style=\"line-height:1.1\">{$estatus[ "descripcion" ]}</td>";
 
 
    switch( $m["codigo"] ){
        case "10-NUTRICION": 
            $cal .= "<td><div class=\"input-group input-xgroup-sm\" data-bs-toggle=\"tooltip\" title=\"\">
            <input data-bs-toggle=\"tooltip\" title=\"<span class='small'>{$t_2}</span><br>".(CALIFICACIONES[ $cx[ $m["codigo" ] ][ "m_2" ] ][ "descripcion" ])."\" disabled type=\"text\" value=\"".( intval( substr( $cx[ $m["codigo" ] ][ "m_2" ], 0, 2 ) ) >= 10 ? substr( $cx[ $m["codigo" ] ][ "m_2" ], 3, 2 ) : "" )."\" class=\"form-control py-2 text-center text-".( intval( substr( $cx[ $m["codigo" ] ][ "m_2" ], 0, 2 ) ) >= 10 ? "teal" : "gray-500" )."\" style=\"background:var(--bs-".( intval( substr( $cx[ $m["codigo" ] ][ "m_2" ], 0, 2 ) ) >= 10 ? "gray-300" : "gray-100" )."); border:none\">
            <input data-bs-toggle=\"tooltip\" title=\"<span class='small'>{$t_1}</span><br>".(CALIFICACIONES[ $cx[ $m["codigo" ] ][ "m_1" ] ][ "descripcion" ])."\" disabled type=\"text\" value=\"".( intval( substr( $cx[ $m["codigo" ] ][ "m_1" ], 0, 2 ) ) >= 10 ? substr( $cx[ $m["codigo" ] ][ "m_1" ], 3, 2 ) : "" )."\" class=\"form-control py-2 text-center text-".( intval( substr( $cx[ $m["codigo" ] ][ "m_1" ], 0, 2 ) ) >= 10 ? "teal" : "gray-500" )."\" style=\"background:var(--bs-".( intval( substr( $cx[ $m["codigo" ] ][ "m_1" ], 0, 2 ) ) >= 10 ? "gray-300" : "gray-100" )."); border:none\">
            <input data-bs-toggle=\"tooltip\" title=\"<span class='small'>{$t_0}</span><br>".(CALIFICACIONES[ $cx[ $m["codigo" ] ][ "m_0" ] ][ "descripcion" ])."\" disabled type=\"text\" value=\"".( intval( substr( $cx[ $m["codigo" ] ][ "m_0" ], 0, 2 ) ) >= 10 ? substr( $cx[ $m["codigo" ] ][ "m_0" ], 3, 2 ) : "" )."\" class=\"form-control py-2 text-center text-{$estatus[ "color" ]}\" style=\"font-weight:700; background:var(--bs-".( intval( substr( $cx[ $m["codigo" ] ][ "m_0" ], 0, 2 ) ) >= 10 ? "gray-300" : "gray-100" )."); border:none\"></div></td>";
        
            break;
        case "20-TELEFONIA": 
            $cal .= "<td><div class=\"input-group input-xgroup-sm\" data-bs-toggle=\"tooltip\" title=\"\">
            <input data-bs-toggle=\"tooltip\" title=\"<span class='small'>{$t_0}</span><br>".(CALIFICACIONES[ $cx[ $m["codigo" ] ][ "m_0" ] ][ "descripcion" ])."\" disabled type=\"text\" value=\"".( intval( substr( $cx[ $m["codigo" ] ][ "m_0" ], 0, 2 ) ) >= 10 ?  $cx[ $m["codigo" ] ][ "m_0" ] : "" )."\" class=\"form-control py-2 text-center text-{$estatus[ "color" ]}\" style=\"font-weight:700; background:var(--bs-".( intval( substr( $cx[ $m["codigo" ] ][ "m_0" ], 0, 2 ) ) >= 10 ? "gray-300" : "gray-100" )."); border:none\"></div></td>";
        
            break;
        case "30-ALIMENTOS": 
            $cal .= "<td><div class=\"input-group input-xgroup-sm\" data-bs-toggle=\"tooltip\" title=\"\">
            <input data-bs-toggle=\"tooltip\" title=\"<span class='small'>{$t_1}</span><br>".(CALIFICACIONES[ $cx[ $m["codigo" ] ][ "m_1" ] ][ "descripcion" ])."\" disabled type=\"text\" value=\"".( intval( substr( $cx[ $m["codigo" ] ][ "m_1" ], 0, 2 ) ) >= 10 ? substr( $cx[ $m["codigo" ] ][ "m_1" ], 3, 2 ) : "" )."\" class=\"form-control py-2 text-center text-".( intval( substr( $cx[ $m["codigo" ] ][ "m_1" ], 0, 2 ) ) >= 10 ? "teal" : "gray-500" )."\" style=\"background:var(--bs-".( intval( substr( $cx[ $m["codigo" ] ][ "m_1" ], 0, 2 ) ) >= 10 ? "gray-300" : "gray-100" )."); border:none\">
            <input data-bs-toggle=\"tooltip\" title=\"<span class='small'>{$t_0}</span><br>".(CALIFICACIONES[ $cx[ $m["codigo" ] ][ "m_0" ] ][ "descripcion" ])."\" disabled type=\"text\" value=\"".( intval( substr( $cx[ $m["codigo" ] ][ "m_0" ], 0, 2 ) ) >= 10 ? substr( $cx[ $m["codigo" ] ][ "m_0" ], 3, 2 ) : "" )."\" class=\"form-control py-2 text-center text-{$estatus[ "color" ]}\" style=\"font-weight:700; background:var(--bs-".( intval( substr( $cx[ $m["codigo" ] ][ "m_0" ], 0, 2 ) ) >= 10 ? "gray-300" : "gray-100" )."); border:none\"></div></td>";
        
            break;
    }
 
}

?>

<div class="card-body p-2">
    <table class="m-0 w-100" style="border-spacing: 10px;border-collapse: separate; ">
        <tr><?php echo $headers; ?></tr>
        <tr><?php echo $bot; ?></tr>
        <tr><?php echo $cal; ?></tr>
    </table>
</div>

<?php
echo "<div class=\"card-header bg-{$b[ "data" ][ "fondo" ]}\"><h5 class=\"m-0 text-white\">{$b[ "data" ][ "titulo" ]}</h5></div>";

$bot = "";
$headers = "";
$cal = "";

$m_0 = date('Ym');
$m_1 = date('Ym', strtotime( date('Y-m').'-01'. ' -1 month' ) );
$m_2 = date('Ym', strtotime( date('Y-m').'-01'. ' -2 month' ) );

$db  = db_connect();

foreach( MODELOS as $m ){
    $sql = "select 
        f_get_calificacion( {$usuario->id}, '{$m_2}', '{$m[ "codigo" ]}' ) as 'm_2', 
        f_get_calificacion( {$usuario->id}, '{$m_1}', '{$m[ "codigo" ]}' ) as 'm_1', 
        f_get_calificacion( {$usuario->id}, '{$m_0}', '{$m[ "codigo" ]}' ) as 'm_0'";
    $c = $db->query($sql)->getRowArray();

    $estatus = ESTATUS[ $usuario->data->estatus->modelos->{$m[ "codigo" ]} ];
    $headers .= "<th class=\"text-center text-{$m[ "settings" ][ "color" ]}\"><i class=\"fa fa-{$m[ "settings" ][ "icono" ]}\"></i> {$m[ "nombre" ]}</td>";
    $bot .="<td class=\"col-4 rounded p-2 text-center small bg-{$estatus[ "color" ]} text-white\" style=\"line-height:1.1\">{$estatus[ "descripcion" ]}</td>";
    $cal .= "<td><div class=\"input-group input-xgroup-sm\">
    <input disabled type=\"text\" value=\"".( intval( substr( $c[ "m_2" ], 0, 2 ) ) >= 10 ? substr( $c[ "m_2" ], 3, 2 ) : "" )."\" class=\"form-control py-2 text-center text-".( intval( substr( $c[ "m_2" ], 0, 2 ) ) >= 10 ? "teal" : "gray-500" )."\" style=\"background:var(--bs-".( intval( substr( $c[ "m_2" ], 0, 2 ) ) >= 10 ? "gray-300" : "gray-100" )."); border:none\">
    <input disabled type=\"text\" value=\"".( intval( substr( $c[ "m_1" ], 0, 2 ) ) >= 10 ? substr( $c[ "m_1" ], 3, 2 ) : "" )."\" class=\"form-control py-2 text-center text-".( intval( substr( $c[ "m_1" ], 0, 2 ) ) >= 10 ? "teal" : "gray-500" )."\" style=\"background:var(--bs-".( intval( substr( $c[ "m_1" ], 0, 2 ) ) >= 10 ? "gray-300" : "gray-100" )."); border:none\">
    <input disabled type=\"text\" value=\"".( intval( substr( $c[ "m_0" ], 0, 2 ) ) >= 10 ? substr( $c[ "m_0" ], 3, 2 ) : "" )."\" class=\"form-control py-2 text-center text-{$estatus[ "color" ]}\" style=\"font-weight:700; background:var(--bs-".( intval( substr( $c[ "m_0" ], 0, 2 ) ) >= 10 ? "gray-300" : "gray-100" )."); border:none\">
  </div></td>";
}
?>

<div class="card-body p-2">
    <table class="m-0 w-100" style="border-spacing: 10px;border-collapse: separate; ">
        <tr><?php echo $headers; ?></tr>
        <tr><?php echo $bot; ?></tr>
        <tr><?php echo $cal; ?></tr>
    </table>
</div>

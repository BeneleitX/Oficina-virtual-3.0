<h4 class="mt-1 mb-3"><?php echo $titulo; ?></h4>

<div class="row">

<?php 

$menu = [
    [ "success", "variables", "gears", "Variables de entorno", sizeof( VARIABLES ) ],
    [ "success", "roles", "user-shield", "Roles de usuario", sizeof( $productos ) ],
    [ "secondary", "", "users", "Usuarios", sizeof( $usuarios )],
    [ "success", "valida_credenciales", "address-card", "Valida credenciales", sizeof( $credenciales ) ],
    [ "success", "promociones/".getModeloPrincipal(), "basket-shopping", "Promociones", sizeof( $promociones ) ],
    [ "success", "rangos/".getModeloPrincipal(), "gem", "Rangos", sizeof( $rangos ) ],
    [ "warning", "pasarelas/".getModeloPrincipal(), "credit-card", "Métodos de pago", sizeof( $pasarelas ) ],
    [ "success", "paqueterias/".getModeloPrincipal(), "truck-fast", "Proveedores de paquetería", sizeof( $paqueterias ) ],
    [ "danger", "corte/".getModeloPrincipal(), "filter-circle-dollar", "Corte semanal" ],
    [ "warning", "productos/".getModeloPrincipal(), "spray-can-sparkles", "Productos", sizeof( $productos ) ],
    [ "secondary", "recompensas/".getModeloPrincipal(), "award", "Recompensas" ],
    [ "warning", "modelos/".getModeloPrincipal(), "shop", "Modelos de negocio" ],
    [ "secondary", "redes/".getModeloPrincipal(), "sitemap", "Redes" ],
    [ "secondary", "callcenter", "headset", "Call center" ],
    [ "secondary", "mensajeria", "envelope", "Mensajes masivos" ],
    [ "secondary", "reportes", "chart-pie", "Reportes" ],
    [ "secondary", "backups", "cloud-arrow-down", "Respaldos de BD" ],
    [ "secondary", "tickets", "ticket", "Tickets de soporte" ],  
    [ "success", "almacenes/".getModeloPrincipal(), "dolly", "Almacenes", sizeof( $almacenes ) ],    
    [ "secondary", "bloques", "table-cells", "Bloques de inicio" ],    
    [ "secondary", "facturacion", "file-invoice-dollar", "Facturacion" ],    
    [ "success", "estatus", "layer-group", "Estatus de socios" ],    
];

foreach( $menu as $opcion ){
    echo "\n<div class=\"col-6 col-md-4 col-lg-3 col-xl-2 mb-4\"><a class=\"btn position-relative btn-outline-{$opcion[0]} col-12 ".($opcion[0] == "secondary" ? "disabled" : "" )."\"  href=\"".base_url( $opcion[1] )."\"><i class=\"fa fa-{$opcion[2]} m-3\" style=\"font-size:50px\"></i><p>{$opcion[3]}</p><div class=\"contador text-center\" style=\"line-height:8px\">".( isset( $opcion[4] ) && $opcion[4] > 0 ? "<span class=\"badge rounded-pill bg-marine\">{$opcion[4]}</span><br>" : "" ).( str_contains( $opcion[1], "/" ) ? "<i class=\"fa fa-circle elipsis text-mustard\"></i><i class=\"fa fa-circle text-pink elipsis\"></i><i class=\"fa fa-circle text-light-blue elipsis\"></i>" : "")."</div></a></div>";
}

?>
</div>

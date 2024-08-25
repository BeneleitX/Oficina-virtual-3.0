<h4 class="mt-1 mb-3"><?php echo $titulo; ?></h4>

<div class="row">

<?php 

$menu = [
    [ "success", "variables", "gears", "Variables de entorno", sizeof( VARIABLES ), [] ],
    [ "success", "roles", "user-shield", "Roles de usuario", sizeof( $roles ), [] ],
    [ "success", "sociodata", "users", "Usuarios", $usuarios, ["32-EDICION"] ],
    [ "success", "valida_credenciales", "address-card", "Valida credenciales", sizeof( $credenciales ), [ "30-SOPORTE", "34-VALIDACION" ] ],
    [ "success", "promociones/".getModeloPrincipal(), "basket-shopping", "Promociones", sizeof( $promociones ), [] ],
    [ "success", "rangos/".getModeloPrincipal(), "gem", "Rangos", sizeof( $rangos ), [] ],
    [ "warning", "pasarelas/".getModeloPrincipal(), "credit-card", "Métodos de pago", sizeof( $pasarelas ), [] ],
    [ "success", "paqueterias/".getModeloPrincipal(), "truck-fast", "Paquetería", sizeof( $paqueterias ), [ "25-PAQUETERIA"] ],
    [ "danger", "periodos/".getModeloPrincipal(), "calendar-days", "Periodos", sizeof( $periodos ), [ "38-CONTABILIDAD" ] ],
    [ "warning", "productos/".getModeloPrincipal(), "spray-can-sparkles", "Productos", sizeof( $productos ), ["20-ALMACEN"] ],
    [ "secondary", "", "award", "Recompensas", sizeof( $recompensas ), [] ],
    [ "warning", "modelos", "shop", "Modelos de negocio", sizeof( MODELOS ), [] ],
    [ "secondary", "redes/".getModeloPrincipal(), "sitemap", "Redes", 0,[] ],
    [ "secondary", "callcenter", "headset", "Call center", 0,[] ],
    [ "secondary", "mensajeria", "envelope", "Mensajes masivos", 0,[] ],
    [ "secondary", "reportes", "chart-pie", "Reportes", 0,[ "38-CONTABILIDAD" ] ],
    [ "secondary", "backups", "cloud-arrow-down", "Respaldos de BD", 0,[] ],
    [ "secondary", "tickets", "ticket", "Tickets de soporte", 0,[] ],  
    [ "success", "almacenes/".getModeloPrincipal(), "dolly", "Almacenes", $almacenes, ["20-ALMACEN", "18-STOCK"] ],    
    [ "secondary", "bloques", "table-cells", "Bloques de inicio", 0,[] ],    
    [ "secondary", "facturacion", "file-invoice-dollar", "Facturacion", 0,[] ],    
    [ "success", "estatus", "layer-group", "Estatus de socios", 0,[] ],  
    [ "success", "esquemas/".getModeloPrincipal(), "sack-dollar", "Tipos de comisiones", sizeof( $esquemas ), [] ], 
    [ "warning", "isr", "filter-circle-dollar", "Tablas de ISR", 0,[ "38-CONTABILIDAD" ] ],     
    [ "success", "layout_bancos", "money-bill-transfer", "Layout de bancos", 0,[ "38-CONTABILIDAD" ] ],     
    [ "danger", "apikeys", "network-wired", "API keys", 0,[] ],     
    [ "success", "saldos", "hand-holding-dollar", "Saldo a favor", $saldos,[] ],     
];

foreach( $menu as $opcion ){
    $permiso = false;
    $opcion[5][] = "50-ROOT";
    $opcion[5][] = "40-ADMIN";

    foreach( $opcion[5] as $io ){
        if( in_array( $io, $usuario->rol_codigos ) ){
            $permiso = true;
        }
    }

    if( $permiso ){
        echo "\n<div class=\"col-6 col-md-4 col-lg-3 col-xl-2 mb-4\"><a class=\"btn position-relative btn-outline-{$opcion[0]} col-12 ".($opcion[0] == "secondary" ? "disabled" : "" )."\"  href=\"".base_url( $opcion[1] )."\"><i class=\"fa fa-{$opcion[2]} m-2\" style=\"font-size:50px\"></i><p class=\"mb-1\">{$opcion[3]}</p><div class=\"contador text-center\">".( isset( $opcion[4] ) && $opcion[4] > 0 ? "<span class=\"badge rounded-pill bg-marine\">{$opcion[4]}</span><br>" : "" ).( str_contains( $opcion[1], "/" ) ? "<i class=\"fa fa-circle elipsis text-mustard\"></i><i class=\"fa fa-circle text-pink elipsis\"></i><i class=\"fa fa-circle text-light-blue elipsis\"></i>" : "")."</div></a></div>";
    }
}

?>
</div>

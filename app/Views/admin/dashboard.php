<h4 class="mt-1 mb-3"><?php echo $titulo; ?></h4>

<div class="row">

<?php 

$menu = [
    [ "success", "variables", "gears", "Variables de entorno", sizeof( VARIABLES ), [] ],
    [ "warning", "roles", "user-shield", "Roles de usuario", sizeof( $roles ), ["40-ADMIN"] ],
    [ "success", "usuarios", "users", "Usuarios", $usuarios, ["32-EDICION", "40-ADMIN"] ],
    [ "secondary", "pedidodata", "shopping-cart", "Pedidos", 0, ["20-ALMACEN", "25-PAQUETERIA", "32-EDICION", "30-SOPORTE", "40-ADMIN"] ],
    [ "success", "valida_credenciales", "address-card", "Valida credenciales", sizeof( $credenciales ), [ "30-SOPORTE", "34-VALIDACION", "40-ADMIN" ] ],
    [ "success", "promociones/".getModeloPrincipal(), "basket-shopping", "Promociones", sizeof( $promociones ), ["40-ADMIN"] ],
    [ "success", "rangos/".getModeloPrincipal(), "gem", "Rangos", sizeof( $rangos ), ["26-RANGOS", "40-ADMIN"] ],
    [ "warning", "pasarelas/".getModeloPrincipal(), "credit-card", "Métodos de pago", sizeof( $pasarelas ), ["40-ADMIN"] ],
    [ "success", "paqueterias/".getModeloPrincipal(), "truck-fast", "Paquetería", sizeof( $paqueterias ), [ "25-PAQUETERIA", "40-ADMIN"] ],
    [ "danger", "periodos/".getModeloPrincipal(), "calendar-days", "Periodos", sizeof( $periodos ), [ "38-CONTABILIDAD" ] ],
    [ "warning", "productos/".getModeloPrincipal(), "spray-can-sparkles", "Productos", sizeof( $productos ), ["20-ALMACEN", "40-ADMIN"] ],
    [ "secondary", "", "award", "Recompensas", sizeof( $recompensas ), ["40-ADMIN"] ],
    [ "warning", "modelos", "shop", "Modelos de negocio", sizeof( MODELOS ), [] ],
    [ "secondary", "redes/".getModeloPrincipal(), "sitemap", "Redes", 0,[] ],
    [ "secondary", "callcenter", "headset", "Call center", 0,[] ],
    [ "secondary", "mensajeria", "envelope", "Mensajes masivos", 0,[] ],
    [ "secondary", "reportes", "chart-pie", "Reportes", 0,[ "38-CONTABILIDAD" ] ],
    [ "secondary", "backups", "cloud-arrow-down", "Respaldos de BD", 0,[] ],
    [ "secondary", "tickets", "ticket", "Tickets de soporte", 0,[] ],  
    [ "success", "almacenes/".getModeloPrincipal(), "dolly", "Almacenes", $almacenes, ["20-ALMACEN", "18-STOCK", "40-ADMIN"] ],    
    [ "secondary", "bloques", "table-cells", "Bloques de inicio", 0,[] ],    
    [ "danger", "facturacion", "file-invoice-dollar", "Facturacion y pagos", 0,["38-CONTABILIDAD", "40-ADMIN"] ],    
    [ "success", "estatus", "layer-group", "Estatus de socios", 0,[] ],  
    [ "success", "esquemas/".getModeloPrincipal(), "sack-dollar", "Tipos de comisiones", sizeof( $esquemas ), ["40-ADMIN"] ], 
    [ "warning", "isr", "filter-circle-dollar", "Tablas de ISR", 0,[ "38-CONTABILIDAD", "40-ADMIN" ] ],     
    [ "success", "layout_bancos", "money-bill-transfer", "Layout de bancos", 0,[ "38-CONTABILIDAD" ] ],     
    [ "info", "apikeys", "network-wired", "API keys", 0,["40-ADMIN"] ],     
    [ "success", "saldos", "hand-holding-dollar", "Saldo a favor", $saldos,["40-ADMIN"] ],   
    [ "success", "banners", "newspaper", "Banners", sizeof( $banners ) ,["22-IMAGEN"] ],
    [ "secondary", "eventos", "person-chalkboard", "Eventos", 0 ,[""] ],
];

foreach( $menu as $opcion ){
    $permiso = false;
    $opcion[5][] = "50-ROOT";

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

<?php

use CodeIgniter\Router\RouteCollection;

/**
 * @var RouteCollection $routes
 */

$routes->get( "login",                  "Sesion::login" );
$routes->get( "logout",                 "Sesion::logout" );
$routes->post( "oauth",                 "Sesion::procesa_login" );

$routes->get( "formulario",             "Registro::formulario" );
$routes->get( "registro_exito/(:num)",  "Registro::registro_exito/$1" );
$routes->post( "valida_patrocinador",   "Registro::valida_patrocinador" );
$routes->post( "procesa_registro",      "Registro::procesa_registro" );

$routes->group( "/",  [ "filter" => "auth" ], static function ( $routes ) {
    $routes->get( "",                       "Dashboard::inicio" );
    $routes->get( "inicio",                 "Dashboard::inicio" );

    $routes->get( "bitacora/(:num)",        "Bitacora::listado/$1" );

    $routes->get( "compras",                "Pedidos::compras" ); 
    $routes->get( "ticket/(:any)",          "Pedidos::ticket/$1" ); 
    $routes->get( "historial/(:any)",       "Pedidos::historial/$1" ); 
    $routes->get( "pedido/(:num)",          "Pedidos::carrito/pedido/$1" ); 
    $routes->get( "tienda/(:any)",          "Pedidos::carrito/modelo/$1" ); 
    $routes->get( "pagoyenvio/(:any)",      "Pedidos::pagoyenvio/$1" ); 
    $routes->post( "checkout",              "Pedidos::checkout" ); 
    $routes->post( "fondeo",                "Pedidos::fondeo" ); 
    $routes->post( "save_pedido",           "Pedidos::save_pedido" ); 
    
    $routes->get( "balance",                "Ingresos::balance" ); 
    
    $routes->get( "red/(:any)",             "Redes::arbol/$1" ); 
    $routes->post( "downlineJSON",          "Redes::downlineJSON" );
    
    $routes->get( "perfil",                 "Socio::perfil" );
    $routes->get( "fotografia",             "Socio::fotografia" );
    $routes->get( "cancela_ine/(:any)",     "Socio::cancela_ine/$1" );
    $routes->get( "cancela_csf",            "Socio::cancela_csf" );
    $routes->get( "valida_credencial",      "Socio::valida_credencial" );
    $routes->get( "valida_correo",          "Socio::valida_correo" ); 
    $routes->post( "credencial",            "Socio::credencial" );
    $routes->post( "guarda_avatar",         "Socio::guarda_avatar" );
    $routes->post( "add_beneficiario",      "Socio::add_beneficiario" );
    $routes->post( "cancela_beneficiario",  "Socio::cancela_beneficiario" );
    $routes->post( "guarda_clabe",          "Socio::guarda_clabe" );
    $routes->post( "nuevo_password",        "Socio::nuevo_password" );
    $routes->post( "valida_cp",             "Socio::valida_cp" );
    $routes->post( "create_domicilio",      "Socio::create_domicilio" );
    $routes->post( "check_csf",             "Socio::check_csf" );
    $routes->post( "carga_csf",             "Socio::carga_csf" );

    $routes->get( "almacenes/(:any)",       "Almacenes::listado/$1" ); 
    $routes->get( "almacen/(:any)",         "Almacenes::detalle/$1" );
    $routes->post( "entrega",               "Almacenes::entrega" );
    $routes->post( "marca_entregado",       "Almacenes::marca_entregado" );
    $routes->post( "addstock",              "Almacenes::addstock" );

    $routes->get( "paqueterias/(:any)",     "Paqueteria::listado/$1" ); 
    $routes->get( "paqueteria/(:any)",      "Paqueteria::detalle/$1" );
    $routes->post( "envia",                 "Paqueteria::entrega" );
    $routes->post( "marca_eviado",          "Paqueteria::marca_entregado" );

    $routes->get( "roles",                  "Roles::listado" ); 

    $routes->get( "admin",                  "Admin::dashboard" ); 
    $routes->get( "variables",              "Admin::variables" ); 
    $routes->get( "rangos/(:any)",          "Admin::rangos/$1" ); 
    $routes->get( "valida_credenciales",    "Admin::credenciales" ); 
    $routes->get( "promociones/(:any)",     "Admin::promociones/$1" ); 
    $routes->get( "productos/(:any)",       "Admin::productos/$1" ); 
    $routes->get( "pasarelas/(:any)",       "Admin::pasarelas/$1" ); 
    $routes->get( "promo_detalle/(:any)",   "Admin::promo_detalle/$1" ); 
    $routes->post( "resolucion_ine",        "Admin::resolucion_ine" );
    $routes->post( "save_variable",         "Admin::save_variable" );
    $routes->post( "save_promo",            "Admin::save_promo" );
});

$routes->set404Override('App\Controllers\Errors::error_404'); 
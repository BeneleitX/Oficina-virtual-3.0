<?php

use CodeIgniter\Router\RouteCollection;

/**
 * @var RouteCollection $routes

 */


$routes->get( "login",                      "Sesion::login" );
$routes->get( "recover",                    "Sesion::recover" );
$routes->get( "recover/(:any)",             "Sesion::recover/$1" );
$routes->get( "login/(:num)",               "Sesion::login/$1" );
$routes->get( "oauth/(:num)/(:any)",        "Sesion::procesa_login/$1/$2" );
$routes->post( "oauth",                     "Sesion::procesa_login" );
$routes->post( "pass_request",              "Sesion::pass_request" );
$routes->get( "pass_catch/(:any)",          "Sesion::pass_catch/$1" );

$routes->get( "formulario",                 "Registro::formulario" );
$routes->get( "registro_exito/(:any)",      "Registro::registro_exito/$1" );
$routes->post( "procesa_registro",          "Registro::procesa_registro" );
$routes->post( "valida_patrocinador",       "Registro::valida_patrocinador" );

$routes->post( "GetnetGatewayResponse",     "Sesion::GetnetGatewayResponse" ); 
$routes->get( "GetnetRedirect",            "Sesion::GetnetRedirect" ); 

$routes->group( "/",  [ "filter" => "auth" ], static function ( $routes ) {
    $routes->get( "logout",                 "Sesion::logout" );
    $routes->get( "logout/(:num)/(:any)",              "Sesion::logout/$1/$2" );
    $routes->get( "procesa_registro/(:num)/(:any)",    "Registro::procesa_registro/$1/$2" );

    $routes->get( "",                       "Dashboard::inicio" );
    $routes->get( "inicio",                 "Dashboard::inicio" );
    $routes->post( "splash",                "Dashboard::splash" );
    $routes->post( "save_layout",           "Dashboard::save_layout" );

    $routes->get( "bitacora/(:num)",        "Bitacora::listado/$1" );

    $routes->get( "soporte",                "Soporte::inicio" );    
    $routes->get( "tickets",                "Soporte::tickets" );    

    $routes->get( "layout_bancos",          "Bancos::layout" );    
    $routes->post( "analiza_layout",        "Bancos::analiza_layout" );    

    $routes->get( "ticket/(:any)",          "Pedidos::ticket/$1" ); 
    $routes->get( "historial",              "Pedidos::historial" ); 
    $routes->post( "historial/fuente",      "Pedidos::fuente" ); 
    $routes->get( "historial/(:any)",       "Pedidos::historial/$1" ); 
    $routes->get( "pedido",                 "Pedidos::historial" ); 
    $routes->get( "pedido/(:num)",          "Pedidos::carrito/pedido/$1" ); 
    $routes->get( "tienda/(:any)",          "Pedidos::carrito/modelo/$1" ); 
    $routes->get( "pagoyenvio/(:any)",      "Pedidos::pagoyenvio/$1" ); 
    $routes->get( "compra_demo/(:num)/(:any)/(:num)",    "Pedidos::compra_demo/$1/$2/$3" );
    $routes->post( "checkout",              "Pedidos::checkout" ); 
    $routes->post( "reparte",               "Pedidos::reparte" ); 
    $routes->post( "cancela_pedido",        "Pedidos::cancela_pedido" ); 
    $routes->post( "cambia_fecha",          "Pedidos::cambia_fecha" ); 
    $routes->post( "fondeo",                "Pedidos::fondeo" ); 
    $routes->post( "save_pedido",           "Pedidos::save_pedido" ); 
    
    $routes->get( "balance",                "Ingresos::balance" ); 
    $routes->get( "balance/(:any)/(:any)",  "Ingresos::balance/$1/$2" ); 

    $routes->get( "periodos/(:any)",        "Periodos::listado/$1" ); 
    $routes->get( "periodo/(:any)",         "Periodos::detalle/$1" ); 
    $routes->post( "reset_corte",           "Periodos::reset_corte" ); 
    $routes->post( "excel_corte",           "Periodos::excel_corte" ); 
    $routes->post( "corte",                 "Periodos::corte" ); 
    $routes->post( "cierra_periodo",        "Periodos::cierra_periodo" ); 
    $routes->post( "marca_pagado",          "Periodos::marca_pagado" ); 
    $routes->post( "abre_periodo",          "Periodos::abre_periodo" ); 

    $routes->get( "recompensas",            "Recompensas::detalle" );     
    $routes->get( "switch_recompensa/(:any)", "Recompensas::switch/$1" );     

    $routes->get( "red",                    "Redes::downline" ); 
    $routes->get( "red/(:any)",             "Redes::downline/$1" ); 
    $routes->get( "upline/(:any)",          "Redes::upline/$1" ); 
    $routes->post( "downlineJSON",          "Redes::downlineJSON" );
    $routes->post( "uplineJSON",            "Redes::uplineJSON" );
    $routes->post( "userdata",              "Redes::userdata" );
    
    $routes->get( "perfil",                 "Socio::perfil" );
    $routes->get( "fotografia",             "Socio::fotografia" );
    $routes->get( "cancela_ine/(:any)",     "Socio::cancela_ine/$1" );
    $routes->get( "cancela_csf",            "Socio::cancela_csf" );
    $routes->get( "valida_credencial",      "Socio::valida_credencial" );
    $routes->get( "valida_correo",          "Socio::valida_correo" ); 
    $routes->get( "update_estatus/(:num)",  "Socio::update_estatus/$1" );
    $routes->get( "nuevo_password/(:num)/(:any)/(:any)",        "Socio::nuevo_password/$1/$2/$3" );
    $routes->post( "nuevo_password",        "Socio::nuevo_password" );
    $routes->post( "credencial",            "Socio::credencial" );
    $routes->post( "guarda_avatar",         "Socio::guarda_avatar" );
    $routes->post( "add_beneficiario",      "Socio::add_beneficiario" );
    $routes->post( "cancela_beneficiario",  "Socio::cancela_beneficiario" );
    $routes->post( "guarda_clabe",          "Socio::guarda_clabe" );
    $routes->post( "guarda_rfc",            "Socio::guarda_rfc" );
    $routes->post( "valida_cp",             "Socio::valida_cp" );
    $routes->post( "create_domicilio",      "Socio::create_domicilio" );
    $routes->post( "create_numero",         "Socio::create_numero" );
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
    $routes->post( "marca_enviado",         "Paqueteria::marca_enviado" );

    $routes->get( "roles",                  "Roles::listado" ); 

    $routes->get( "esquemas/(:any)",        "Esquemas::listado/$1" );

    $routes->get( "admin",                  "Admin::dashboard" ); 
    $routes->get( "apikeys",                "Admin::apikeys" ); 
    $routes->get( "variables",              "Admin::variables" ); 
    $routes->get( "estatus",                "Admin::estatus" ); 
    $routes->get( "valida_credenciales",    "Admin::credenciales" ); 
    $routes->get( "promociones/(:any)",     "Admin::promociones/$1" ); 
    $routes->get( "productos/(:any)",       "Admin::productos/$1" ); 
    $routes->get( "pasarelas/(:any)",       "Admin::pasarelas/$1" ); 
    $routes->get( "promo_detalle/(:any)",   "Admin::promo_detalle/$1" ); 
    $routes->post( "resolucion_ine",        "Admin::resolucion_ine" );
    $routes->post( "save_variable",         "Admin::save_variable" );
    $routes->post( "save_promo",            "Admin::save_promo" );

    $routes->get( "rangos/(:any)",          "Rangos::catalogo/$1" ); 
    $routes->get( "pines/(:any)",           "Rangos::pines/$1" ); 

});

$routes->set404Override('App\Controllers\Errors::error_404'); 
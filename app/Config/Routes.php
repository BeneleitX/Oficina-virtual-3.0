<?php

use CodeIgniter\Router\RouteCollection;

/**
 * @var RouteCollection $routes
 */

$routes->get('login', 'Sesion::login');
$routes->get('logout', 'Sesion::logout');
$routes->post('oauth', 'Sesion::procesa_login');

$routes->get('formulario', 'Registro::formulario');
$routes->get('registro_exito/(:num)', 'Registro::registro_exito/$1');
$routes->post('valida_patrocinador', 'Registro::valida_patrocinador');
$routes->post('procesa_registro', 'Registro::procesa_registro');

$routes->group('/',  ['filter' => 'auth'], static function ($routes) {
    $routes->get('',       'Dashboard::inicio');
    $routes->get('inicio', 'Dashboard::inicio');
    $routes->get('perfil', 'Socio::perfil');
    $routes->get('tienda', 'Tienda::carrito'); 
    $routes->get('balance', 'Ingresos::balance'); 
    $routes->get('red', 'Redes::arbol'); 
});

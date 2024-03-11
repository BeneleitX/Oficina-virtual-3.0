<?php

use CodeIgniter\Router\RouteCollection;

/**
 * @var RouteCollection $routes
 */

$routes->get('login', 'Sesion::login');
$routes->get('logout', 'Sesion::logout');
$routes->post('oauth', 'Sesion::procesa_login');

$routes->get('registro', 'Registro::formulario');
$routes->get('registro_exito', 'Registro::registro_exito');
$routes->post('procesa_registro', 'Registro::procesa_registro');

$routes->group('/',  ['filter' => 'auth'], static function ($routes) {
    $routes->get('',       'Dashboard::inicio');
    $routes->get('inicio', 'Dashboard::inicio');
    $routes->get('perfil', 'Usuario::perfil');
    $routes->get('tienda', 'Tienda::carrito'); 
});

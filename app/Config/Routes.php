<?php

use CodeIgniter\Router\RouteCollection;

/**
 * @var RouteCollection $routes
 */

$routes->get('login', 'Sesion::login');
$routes->get('logout', 'Sesion::logout');
$routes->post('oauth', 'Sesion::procesa_login');


$routes->group('/',  ['filter' => 'auth'], static function ($routes) {
    $routes->get('', 'Dashboard::inicio');
    $routes->get('inicio', 'Dashboard::inicio');
    $routes->get('perfil', 'Usuario::perfil');
    $routes->get('tienda', 'Tienda::carrito'); 
});
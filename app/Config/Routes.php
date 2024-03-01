<?php

use CodeIgniter\Router\RouteCollection;

/**
 * @var RouteCollection $routes
 */
$routes->get('/', 'Dashboard::inicio');
$routes->get('/inicio', 'Dashboard::inicio');
$routes->get('/login', 'Sesion::login');
$routes->get('/logout', 'Sesion::logout');
$routes->get('/oauth', 'Sesion::procesa_login');
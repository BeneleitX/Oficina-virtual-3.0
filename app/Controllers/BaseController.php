<?php
namespace App\Controllers;

use CodeIgniter\Controller;
use CodeIgniter\HTTP\CLIRequest;
use CodeIgniter\HTTP\IncomingRequest;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use Psr\Log\LoggerInterface;



/**
 * Class BaseController
 *
 * BaseController provides a convenient place for loading components
 * and performing functions that are needed by all your controllers.
 * Extend this class in any new controllers:
 *     class Home extends BaseController
 *
 * For security be sure to declare any new methods as protected or private.
 */
abstract class BaseController extends Controller
{
    protected $session; 
    protected $db; 
    /**
     * Instance of the main Request object.
     *
     * @var CLIRequest|IncomingRequest
     */
    protected $request;

    /**
     * An array of helpers to be loaded automatically upon
     * class instantiation. These helpers will be available
     * to all other controllers that extend BaseController.
     *
     * @var array
     */
    protected $helpers = [ "template", "form", "tools", "inversion" ];

    /**
     * Be sure to declare properties for any property fetch you initialized.
     * The creation of dynamic property is deprecated in PHP 8.2.
     */
    // protected $session;


    protected $data = [
        "usuario"   => null,
        "navbar"    => false,
        "menu"      => null,
        "fondo"     => "light",
        "header_x"  => null
    ];

    /**
     * @return void
     */
 
    public function initController(RequestInterface $request, ResponseInterface $response, LoggerInterface $logger)
    {
        // Do Not Edit This Line
        parent::initController($request, $response, $logger);

        $router = \Config\Services::router();
        $_controller = explode("\\", $router->controllerName()); 
        if( $_controller[ 3 ] != "sesion" && !$request->isAJAX() && rand( 0, 3 ) == 0 ) update_estatus_random( 1 );

        // Preload any models, libraries, etc, here.
        $this->session = session();


        // protección temporal para evitar objeto en cookie
/*         if( is_object( session( "usuario" ) ) ){
            $u = session( "usuario" );
            $this->session->set( "usuario", null );
            return redirect()->to( "login" );
        } */

        $meses = [];
        $dy    = date( "Y" );
        $dm    = date( "m" );
        for( $a = 0; $a < 12; $a++ ){
            $meses[ $a ] = ( $dy * 100 ) + $dm;
            if( --$dm < 1 ){
                 $dm = 12;
                 $dy--;
            }
        }
        

        $router = \Config\Services::router();

        $this->data[ "_method" ] = $router->methodName();
        $this->data[ "_controller" ] = explode("\\", $router->controllerName()); 

        if( !defined( "MESES" ) ) define( "MESES", $meses );

        load_catalogo( "modelos", !in_array( $this->data[ "_controller" ][ 3 ], [ "Geodata", "Pedidos", "Admin", "Bancos", "Eventos", "Reportes" ] ) && $this->data[ "_method" ] != "promociones" ? "estatus_codigo = '201-ACTIVO'" : "" );
        load_catalogo( "estatus" );
        load_catalogo( "rangos" );
        load_catalogo( "variables" );

        $this->data[ "usuario" ] = session( "usuario" ) > 0 ? model( "UsuarioModel" )->find( session( "usuario" ) ) : new \App\Entities\E_usuario();

        if( $this->data[ "usuario" ]->id > 0 && !isset( $this->data[ "usuario" ]->data->verificaciones->{"PASSWORD"} ) ){
            $this->data[ "usuario" ]->update_verificacion();
        }

        /* echo "x{$this->data[ "usuario" ]->data->verificaciones->{"PASSWORD"}}x";
        if( ( $this->data[ "usuario" ]->data->verificaciones->{"PASSWORD"} ?? false ) != 1 ){
            return redirect()->to( "login/restablecer" );
        } */
    }
    
}

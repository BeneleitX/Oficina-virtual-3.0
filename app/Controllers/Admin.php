<?php

namespace App\Controllers;

class Admin extends BaseController
{
    function __construct() {
        $this->data[ "menu" ] = "admin";
    }

    /**
     * Muestra el dashboard de administraci n
     *
     * @return void
     */
    public function dashboard(){

        $this->data[ "usuario" ]->valida_modelo();

        if( !(
            $this->data[ "usuario" ]->es_admin() 
        ) ){
            return redirect()->to( "no_permiso" ); 
        }
        
        /**********************************/

        $db = db_connect();

        $this->data[ "navbar" ] = true;
        $this->data[ "titulo" ] = "Administración de sistema";

        $sql = "( 
                    ( 
                        data->>'$.credencial.frente' != 'null' and data->>'$.credencial.reverso' != 'null' 
                    ) 
                    OR  
                    data->>'$.credencial.acta' != 'null' 
                ) 
                and data->>'$.credencial.estatus' = 1";
                
        $this->data[ "credenciales" ] = model( "UsuarioModel" )->where( $sql , null, false )->findAll();

        $sql = "substring(estatus_codigo , 1, 3 ) > 200";
        
        $this->data[ "saldos"   ]     = $db->query( "select count(id) as uss from t_usuarios where DATA->>'$.saldo.\"10-NUTRICION\".cantidad' > 0 or DATA->>'$.saldo.\"20-TELEFONIA\".cantidad' > 0 or DATA->>'$.saldo.\"30-ALIMENTOS\".cantidad' > 0" )->getRow()->uss;

        $this->data[ "promociones" ]  = model( "PromocionModel" )->where( $sql , null, false )->findAll();
        $this->data[ "pasarelas" ]    = model( "MetodopagoModel" )->where( $sql , null, false )->findAll();
        $this->data[ "paqueterias" ]  = model( "MetodoentregaModel" )->where( $sql , null, false )->findAll();
        $this->data[ "almacenes" ]    = $db->query( "select count(*) as conteo from t_almacenes where estatus_codigo = '201-ACTIVO'" )->getRow()->conteo;
        $this->data[ "rangos" ]       = model( "RangoModel" )->findAll();
        $this->data[ "productos" ]    = model( "ProductoModel" )->where( $sql , null, false )->findAll();
        $this->data[ "eventos" ]      = $db->query( "select count(*) as uss from t_promociones where substring(estatus_codigo , 1, 3 ) > 200 and settings->>'$.evento' = 'true'" )->getRow()->uss;
        $this->data[ "roles" ]        = model( "RolModel" )->findAll();
        $this->data[ "periodos" ]     = model( "PeriodoModel" )->where( "substring(estatus_codigo , 1, 3 ) > 200 and inicia > '2024-08-06' " , null, false )->findAll();
        $this->data[ "esquemas" ]     = model( "EsquemaModel" )->where( $sql , null, false )->findAll();
        $this->data[ "recompensas" ]  = model( "RecompensaModel" )->where( $sql , null, false )->findAll(); 
        $this->data[ "banners" ]      = model( "BannerModel" )->where( $sql, null, false )->findAll();

        $sql = "SELECT count(*) as total from t_pedidos
                where data->>'$.sat.factura' = '144-FACTURA-PENDIENTE'
                and substring( estatus_codigo,1,3 ) > 400";
        $this->data[ "facturas" ] = $db->query( $sql )->getRow()->total;

        $this->data[ "tarjetas" ] = $db->query( "select count(id) as uss from t_usuarios where historial->>'$.modelos.\"40-GASOLINAS\".primercompra.\"412-TARJETA\"' IS NOT null" )->getRow()->uss;
        $this->data[ "hash" ]     = $db->query( "select count(pedido_id) as uss from t_inversiones where estatus_codigo = '420-PAGADO'" )->getRow()->uss;

        echo template( "admin/dashboard", $this->data );
    }

    /**
     * Displays the page for validating INE credentials of socios.
     *
     * This function checks if the user has the appropriate permissions
     * to access the credential validation page. If the user lacks the
     * required permissions, they are redirected to a "no permission" page.
     * It sets up the navbar and title for the page, queries the database
     * to retrieve socios with valid INE credentials that need validation,
     * and renders the credential validation template with the retrieved data.
     */
    public function credenciales()
    {

        if( !(
            $this->data[ "usuario" ]->permiso( "34-VALIDACION") || 
            $this->data[ "usuario" ]->permiso( "40-ADMIN")
        ) ){
            return redirect()->to( "no_permiso" ); 
        }
        
        /**********************************/


        $this->data[ "navbar" ] = true;
        $this->data[ "titulo" ] = "Validar credenciales INE";

        $sql = "( ( data->>'$.credencial.frente' != 'null' and data->>'$.credencial.reverso' != 'null' ) OR data->>'$.credencial.acta' != 'null' ) and data->>'$.credencial.estatus' = 1";
        $this->data[ "socios" ] = model( "UsuarioModel" )->where( $sql , null, false )->findAll();

        echo template( "admin/credenciales", $this->data );
    }    


    /**
     * Resuelve la petición de validar o rechazar la credencial INE de un socio.
     * 
     * @param string $accion accion a realizar. Puede ser "acepta" o "rechaza".
     * @param int $socio id del socio a procesar.
     * @param string $motivo motivo de rechazo, solo se envia si $accion es "rechaza".
     * 
     * @return redirect a la vista de validación de credenciales.
     */
    public function resolucion_ine()
    {

        if( !(
            $this->data[ "usuario" ]->permiso( "34-VALIDACION") || 
            $this->data[ "usuario" ]->permiso( "40-ADMIN")
        ) ){
            return redirect()->to( "no_permiso" ); 
        }
        
        /**********************************/

        extract( $this->request->getPost() );

        $socio = model( "UsuarioModel" )->find( $socio );

        $json = $socio->data;
        $json->credencial->estatus = $accion == "acepta" ? "2" : "-1";
        $json->credencial->motivo  = $motivo;
        
        $json->verificacion->credencial = ( $accion == "acepta" );
        
        if( $accion == "acepta" ){
            $historial = $socio->historial;    
            $historial->validacion = date( "Y-m-d" ); 
            $socio->historial = $historial; 
        }
        else{
            $json->credencial->frente   = null;
            $json->credencial->reverso  = null;
        }

        $socio->data = $json; 

        model( "UsuarioModel" )->save( $socio ); 
        $socio->update_verificacion();

        // BITACORA Creación de cuenta de usuario
        bitacora( $accion == "acepta" ? ( $socio->es_menor() ? 18 : 8 ) : ( $socio->es_menor() ? 19 : 9 ), $socio->id, [ 
            "usuario" => $this->data[ "usuario" ]->id,
            "motivo"  => $motivo
        ] );

        return redirect()->to( "valida_credenciales" );        
    }


    /**
     * Muestra la lista de promociones de un modelo
     * @param string $modelo Código del modelo
     * @return void
     */
    public function promociones( $modelo ){
        
        if( !(
            $this->data[ "usuario" ]->permiso( "40-ADMIN")
        ) ){
            return redirect()->to( "no_permiso" ); 
        }
        
        /**********************************/


        $this->data[ "navbar" ] = true;
        $this->data[ "titulo" ] = "Promociones";
        $this->data[ "modelo" ] = $modelo;

        $sql = "modelo_codigo = '{$modelo}'";
        $this->data[ "promociones" ] = model( "PromocionModel" )->where( $sql , null, false )->findAll();

        echo template( "admin/promociones", $this->data );
    }    


    /**
     * Muestra el detalle de una promoción
     * @param string $promocion Código de la promoción
     * @return void
     */
    public function promo_detalle( $promocion ){
        if( !(
            $this->data[ "usuario" ]->permiso( "40-ADMIN")
        ) ){
            return redirect()->to( "no_permiso" ); 
        }
        
        /**********************************/

        $sql = "codigo = '{$promocion}'";
        $this->data[ "promocion" ] = model( "PromocionModel" )->where( $sql , null, false )->first();

        $this->data[ "navbar" ] = true;
        $this->data[ "titulo" ] = "Promoción ";

        $sql = "estatus_codigo = '201-ACTIVO' ".( ( MODELOS[ $this->data[ "promocion" ][ "modelo_codigo" ] ][ "settings" ][ "global" ] ?? false ) ? "" : " AND modelo_codigo = '{$this->data[ "promocion" ][ "modelo_codigo" ]}'" );
        $this->data[ "productos" ] = model( "ProductoModel" )->where( $sql , null, false )->findAll();

        echo template( "admin/promo_detalle", $this->data );
    }


    /**
     * Guarda la configuración de una promoción
     * 
     * @return void
     */
    public function save_promo()
    {
        if( !(
            $this->data[ "usuario" ]->permiso( "40-ADMIN")
        ) ){
            return redirect()->to( "no_permiso" ); 
        }
        
        /**********************************/

        $data = $this->request->getPost( "data" );

        model( "PromocionModel" )->save( $data );
    }


    /**
     * Muestra la lista de métodos de pago para el modelo 
     * especificado en la url.
     * 
     * @param string $modelo Código del modelo.
     * 
     * @return void
     */
    public function pasarelas( $modelo ){
        if( !(
            $this->data[ "usuario" ]->permiso( "40-ADMIN")
        ) ){
            return redirect()->to( "no_permiso" ); 
        }
        
        /**********************************/

        $this->data[ "navbar" ] = true;
        $this->data[ "titulo" ] = "Métodos de pago";
        $this->data[ "modelo" ] = $modelo;

        $sql = "modelo_codigo = '{$modelo}'";
        $this->data[ "pasarelas" ] = model( "MetodopagoModel" )->where( $sql , null, false )->findAll();

        echo template( "admin/pasarelas", $this->data );
    }   

    
    
    /**
     * Displays a list of products for a given model.
     *
     * This function checks if the user has the necessary permissions
     * (either "20-ALMACEN" or "40-ADMIN") before proceeding. It retrieves 
     * a list of products from the database that are associated with the 
     * provided model code and then renders the "admin/productos" template 
     * with the retrieved data.
     *
     * @param string $modelo The model code used to filter the products.
     * @return void Redirects to "no_permiso" if the user lacks permissions.
     */
    public function productos( $modelo ){

        if( !(
            $this->data[ "usuario" ]->permiso( "20-ALMACEN") || 
            $this->data[ "usuario" ]->permiso( "40-ADMIN")
        ) ){
            return redirect()->to( "no_permiso" ); 
        }
        
        /**********************************/

        $this->data[ "navbar" ] = true;
        $this->data[ "titulo" ] = "Productos";
        $this->data[ "modelo" ] = $modelo;

        $sql = "modelo_codigo = '{$modelo}'";
        $this->data[ "productos" ] = model( "ProductoModel" )->where( $sql , null, false )->findAll();

        echo template( "admin/productos", $this->data );
    }   



    /**
     * Displays the list of statuses for a socio.
     * 
     * This function checks if the user has the "40-ADMIN" permission.
     * If the user lacks the required permissions, they are redirected to
     * a "no permission" page. It sets up the navbar and page title,
     * retrieves all statuses from the `t_estatus` table, and renders the
     * "admin/estatus" template with the retrieved data.
     *
     * @return void Redirects to "no_permiso" if the user lacks permissions.
     */
    public function estatus()
    {
        if( !(
            $this->data[ "usuario" ]->permiso( "40-ADMIN")
        ) ){
            return redirect()->to( "no_permiso" ); 
        }
        
        /**********************************/

        $this->data[ "navbar" ] = true;
        $this->data[ "titulo" ] = "Estatus de socio";

        $db = db_connect();
        $this->data[ "estatuses" ] = $db->query( "SELECT * from t_estatus" )->getResultArray();

        echo template( "admin/estatus", $this->data );
    } 


    /**
     * Muestra la lista de socios con saldo a favor.
     * 
     * Este m  todo busca en la tabla t_usuarios el saldo de cada socio
     * y utiliza la funci n JSON_TABLE para obtener la suma de los saldos
     * de cada socio. Luego, busca en la tabla t_usuarios los socios
     * que tengan saldo a favor y los muestra en una tabla.
     * 
     * @return void
     */
    public function saldos()
    {
        if( !(
            $this->data[ "usuario" ]->permiso( "40-ADMIN")
        ) ){
            return redirect()->to( "no_permiso" ); 
        }
        
        /**********************************/

        $this->data[ "navbar" ] = true;
        $this->data[ "titulo" ] = "Socios con saldo a favor";

        $db  = db_connect();
        $sql = "SELECT json_arrayagg( id ) as socios from(
                    select u.id as id, sum(t.saldos) as have
                    from t_usuarios u, JSON_TABLE( 
                        u.data, '$.saldo**.cantidad' columns( saldos decimal(8,2) path '$')
                    ) t 
                    group by u.id having have > 0
                ) x ;";

        $socios = json_decode( $db->query( $sql )->getRow()->socios );

        $this->data[ "saldos" ] = model( "UsuarioModel" )->find( $socios ); 

        echo template( "admin/saldos", $this->data );
    } 
    


    /**
     * Muestra la lista de tablas de ISR SEMANAL.
     * 
     * Este m  todo busca en la tabla t_isr todas las tablas de ISR
     * y las muestra en una tabla.
     * 
     * @return void
     */ 
    public function isr()
    {
        if( !(
            $this->data[ "usuario" ]->permiso( "38-CONTABILIDAD" )
        ) ){
            return redirect()->to( "no_permiso" ); 
        }
        
        /**********************************/

        $this->data[ "navbar" ] = true;
        $this->data[ "titulo" ] = "Tablas de ISR SEMANAL";

        $db = db_connect();
        $this->data[ "isr" ] = $db->query( "SELECT * from t_isr" )->getResultArray();

        echo template( "admin/isr", $this->data );
    } 
    
    

    /**
     * Página de variables de sistema
     *
     * Muestra una interfaz para modificar las variables de sistema
     *
     * @return void
     */
    public function variables()
    {
        
        if( !(
            $this->data[ "usuario" ]->permiso( "40-ADMIN")
        ) ){
            return redirect()->to( "no_permiso" ); 
        }
        
        /**********************************/

        $this->data[ "navbar" ] = true;
        $this->data[ "titulo" ] = "Variables de sistema";

        echo template( "admin/variables", $this->data );
    } 
    

    /**
     * Updates the value of a system variable in the database.
     *
     * This function checks if the user has admin permissions before proceeding.
     * It retrieves the variable code and its new value from the POST request,
     * updates the corresponding record in the `t_variables` table, and logs the
     * operation in the system bitacora.
     *
     * @return void
     */
    public function save_variable()
    {
        if( !(
            $this->data[ "usuario" ]->permiso( "40-ADMIN")
        ) ){
            return redirect()->to( "no_permiso" ); 
        }
        
        /**********************************/

        $variable = $this->request->getPost( "variable" );
        $valor    = $this->request->getPost( "valor" );

        $db = db_connect();
        $db->query( "update t_variables set valor = '{$valor}' where codigo = '{$variable}'" );

        // BITACORA Creación de cuenta de usuario
        bitacora( 26, $this->data[ "usuario" ]->id, [ 
            "variable" => $variable,
            "valor" => $valor
        ] );        
    }


    /**
     * Agrega saldos a favor de un socio
     *
     * @author Rafael Gutiérrez Latorre
     * @since 2022-11-28
     * @param int $socio_saldo ID del socio
     * @param array $saldo Arreglo con los saldos a agregar, por modelo
     * @return void
     */
    public function agrega_saldos()
    {
        if( !(
            $this->data[ "usuario" ]->permiso( "40-ADMIN")
        ) ){
            return redirect()->to( "no_permiso" ); 
        }
        
        /**********************************/

        extract( $this->request->getPost() );

        $socio = model( "UsuarioModel" )->find( $socio_saldo );
        $data  = $socio->data;

        if( $socio ){
            foreach( $saldo as $m => $s ){

                if( !isset( $data->saldo->{$m} ) ){
                    $data->saldo->{$m} = (object)[
                        "cantidad" => 0,
                        "estatus"  => 0
                    ];
                }

                if( $data->saldo->{$m}->cantidad != $s ){

                    // BITACORA Edita saldo a favor
                    bitacora( 57, $this->data[ "usuario" ]->id, [ 
                        "socio"    => $socio->id,
                        "anterior" => $data->saldo->{$m}->cantidad,
                        "nuevo"    => $s,
                        "modelo"   => $m
                    ] );        

                    $data->saldo->{$m}->cantidad = $s ?? 0;
                    $data->saldo->{$m}->estatus  = 0;
                }
            }

            $socio->data = $data;
            model( "UsuarioModel" )->save( $socio );
        }

        return redirect()->to( "saldos" );    
    }


    /**
     * Edita saldos a favor de un socio
     *
     * @author Rafael Gutiérrez Latorre
     * @since 2022-11-28
     * @param int $socio_saldo ID del socio
     * @param array $saldo Arreglo con los saldos a cambiar, por modelo
     * @param array $estatus Arreglo con los estatus a cambiar, por modelo
     * @return void
     */
    public function edita_saldos()
    {
        if( !(
            $this->data[ "usuario" ]->permiso( "40-ADMIN")
        ) ){
            return redirect()->to( "no_permiso" ); 
        }
        
        /**********************************/

        extract( $this->request->getPost() );

        $socio = model( "UsuarioModel" )->find( $socio_saldo );
        $data  = $socio->data;

        foreach( $this->request->getPost( "saldo" ) as $m => $s ){
            if( $data->saldo->{$m}->cantidad != $s ){

                // BITACORA Edita saldo a favor
                bitacora( 57, $this->data[ "usuario" ]->id, [ 
                    "socio"    => $socio->id,
                    "anterior" => $data->saldo->{$m}->cantidad,
                    "nuevo"    => $s,
                    "modelo"   => $m
                ] );        

                $data->saldo->{$m}->cantidad = $s;
            }
        }

        foreach( $this->request->getPost( "saldo" ) as $m => $s ){
            if( $data->saldo->{$m}->estatus != ( $estatus[ $m ] ?? 0 ) ){

                // BITACORA Edita saldo a favor
                bitacora( 67, $this->data[ "usuario" ]->id, [ 
                    "socio"    => $socio->id,
                    "anterior" => $data->saldo->{$m}->estatus,
                    "nuevo"    => ( $estatus[ $m ] ?? 0 ),
                    "modelo"   => $m
                ] );        

                $data->saldo->{$m}->estatus = ( $estatus[ $m ] ?? 0 );
            }
        }
        
        $socio->data = $data;
        model( "UsuarioModel" )->save( $socio );
        
        return redirect()->to( "saldos" );    
    }


    /**
     * Displays the API keys management page.
     *
     * This function checks if the user has the "40-ADMIN" permission and, if so, 
     * sets up necessary data for displaying the API keys management interface. 
     * If the user does not have the required permission, they are redirected 
     * to a "no permission" page.
     */
    public function apikeys()
    {
        if( !(
            $this->data[ "usuario" ]->permiso( "40-ADMIN")
        ) ){
            return redirect()->to( "no_permiso" ); 
        }
        
        /**********************************/

        $this->data[ "navbar" ] = true;
        $this->data[ "titulo" ] = "API keys";

        echo template( "admin/apikeys", $this->data );
    } 


    public function verificaciones()
    {
        if( !(
            $this->data[ "usuario" ]->permiso( "40-ADMIN")
        ) ){
            return redirect()->to( "no_permiso" ); 
        }
        
        /**********************************/

        $this->data[ "navbar" ] = true;
        $this->data[ "titulo" ] = "Puntos para verificación de cuenta";

        echo template( "admin/verificaciones", $this->data );
    }

    
    public function guarda_verificaciones()
    {
        if( !(
            $this->data[ "usuario" ]->permiso( "40-ADMIN")
        ) ){
            return redirect()->to( "no_permiso" ); 
        }
        
        /**********************************/
                
        $check = $this->request->getPost( "check" );

        foreach( MODELOS as $modelo ){
            $previo = $modelo[ "settings" ][ "verificaciones" ] ?? [];
            $guarda = false;

            $verificado = $previo;

            foreach( VARIABLES[ "tipos_de_cuenta" ][ "valor" ] as $tipo => $data ){
                foreach( VARIABLES[ "verificaciones" ][ "valor" ] as $punto ){
                    $verificado[ $tipo ][ $punto[ "codigo" ] ] = ( $check[ $modelo[ "codigo" ] ][ $tipo ][ $punto[ "codigo" ] ] ?? 0 ) ? true : false;

                    if( $verificado[ $tipo ][ $punto[ "codigo" ] ] != ( $previo[ $tipo ][ $punto[ "codigo" ] ] ?? false ) ){
                        $guarda = true;
                    }
                }
            }

            if( $guarda ){
                $m = model( "ModeloModel" )->find( $modelo[ "codigo" ] );
                $m[ "settings" ][ "verificaciones" ] = $verificado;
                model( "ModeloModel" )->save( $m );

                bitacora( 102, $this->data[ "usuario" ]->id, [ 
                    "modelo"   => $modelo[ "codigo" ],
                    "anterior" => $previo,
                    "nuevo"    => $verificado
                ] );  
            }
        }

        return redirect()->to( "verificaciones" )->with( "msg", [ 
            "clase" => "success", 
            "icono" => "check", 
            "texto" => "Puntos de verificación actualizados"] );  
    }


    public function estatus_test(){
        echo template( "admin/estatus_test", $this->data );
    }
}

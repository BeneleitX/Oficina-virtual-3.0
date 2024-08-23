<?php

namespace App\Controllers;

class Admin extends BaseController
{
    function __construct() {
        $this->data[ "menu" ] = "admin";
    }

    public function dashboard(){

        if( !(
            $this->data[ "usuario" ]->permiso( "18-STOCK" ) ||
            $this->data[ "usuario" ]->permiso( "20-ALMACEN" ) ||
            $this->data[ "usuario" ]->permiso( "30-SOPORTE" ) || 
            $this->data[ "usuario" ]->permiso( "32-EDICION" ) ||
            $this->data[ "usuario" ]->permiso( "38-CONTABILIDAD" ) ||
            $this->data[ "usuario" ]->permiso( "34-VALIDACION" ) ||
            $this->data[ "usuario" ]->permiso( "40-ADMIN" )
        ) ){
            return redirect()->to( "inicio" ); 
        }
        
        /**********************************/

        $db = db_connect();

        $this->data[ "navbar" ] = true;
        $this->data[ "titulo" ] = "Administración de sistema";

        $sql = "( ( data->>'$.credencial.frente' != 'null' and data->>'$.credencial.reverso' != 'null' ) OR data->>'$.credencial.acta' != 'null' ) and data->>'$.credencial.estatus' = 1";
        $this->data[ "credenciales" ] = model( "UsuarioModel" )->where( $sql , null, false )->findAll();

        $sql = "substring(estatus_codigo , 1, 3 ) > 200";
        
        $this->data[ "promociones" ]  = model( "PromocionModel" )->where( $sql , null, false )->findAll();
        $this->data[ "pasarelas" ]    = model( "MetodopagoModel" )->where( $sql , null, false )->findAll();
        $this->data[ "paqueterias" ]  = model( "MetodoentregaModel" )->where( $sql , null, false )->findAll();
        $this->data[ "almacenes" ]    = $db->query( "select count(*) as conteo from t_almacenes where estatus_codigo = '201-ACTIVO'" )->getRow()->conteo;
        $this->data[ "rangos" ]       = model( "RangoModel" )->findAll();
        $this->data[ "productos" ]    = model( "ProductoModel" )->where( $sql , null, false )->findAll();
        $this->data[ "usuarios" ]     = $db->query( "select count(id) as uss from t_usuarios" )->getRow()->uss;
        $this->data[ "roles" ]        = model( "RolModel" )->findAll();
        $this->data[ "periodos" ]     = model( "PeriodoModel" )->where( $sql , null, false )->findAll();
        $this->data[ "esquemas" ]     = model( "EsquemaModel" )->where( $sql , null, false )->findAll();
         $this->data[ "recompensas" ]  = model( "RecompensaModel" )->where( $sql , null, false )->findAll(); 
       
        echo template( "admin/dashboard", $this->data );
    }

    public function credenciales(){

        if( !(
            $this->data[ "usuario" ]->permiso( "34-VALIDACION") || 
            $this->data[ "usuario" ]->permiso( "40-ADMIN")
        ) ){
            return redirect()->to( "inicio" ); 
        }
        
        /**********************************/


        $this->data[ "navbar" ] = true;
        $this->data[ "titulo" ] = "Validar credenciales INE";

        $sql = "( ( data->>'$.credencial.frente' != 'null' and data->>'$.credencial.reverso' != 'null' ) OR data->>'$.credencial.acta' != 'null' ) and data->>'$.credencial.estatus' = 1";
        $this->data[ "socios" ] = model( "UsuarioModel" )->where( $sql , null, false )->findAll();

        echo template( "admin/credenciales", $this->data );
    }    


    public function resolucion_ine(){

        if( !(
            $this->data[ "usuario" ]->permiso( "34-VALIDACION") || 
            $this->data[ "usuario" ]->permiso( "40-ADMIN")
        ) ){
            return redirect()->to( "inicio" ); 
        }
        
        /**********************************/

        extract( $this->request->getPost() );

        $socio = model( "UsuarioModel" )->find( $socio );

        $json = $socio->data;
        $json->credencial->estatus = $accion == "acepta" ? 2 : -1;
        $json->credencial->motivo  = $motivo;
        $json->verificacion->credencial = $accion == "acepta";
        

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

        // BITACORA Creación de cuenta de usuario
        bitacora( $accion == "acepta" ? ( $socio->es_menor() ? 18 : 8 ) : ( $socio->es_menor() ? 19 : 9 ), $socio->id, [ 
            "usuario" => $this->data[ "usuario" ]->id,
            "motivo"  => $motivo
        ] );

        return redirect()->to( "valida_credenciales" );        
    }

    public function promociones( $modelo ){
        
        if( !(
            $this->data[ "usuario" ]->permiso( "40-ADMIN")
        ) ){
            return redirect()->to( "inicio" ); 
        }
        
        /**********************************/


        $this->data[ "navbar" ] = true;
        $this->data[ "titulo" ] = "Promociones";
        $this->data[ "modelo" ] = $modelo;

        $sql = "modelo_codigo = '{$modelo}'";
        $this->data[ "promociones" ] = model( "PromocionModel" )->where( $sql , null, false )->findAll();

        echo template( "admin/promociones", $this->data );
    }    


    public function promo_detalle( $promocion ){
        if( !(
            $this->data[ "usuario" ]->permiso( "40-ADMIN")
        ) ){
            return redirect()->to( "inicio" ); 
        }
        
        /**********************************/

        $sql = "codigo = '{$promocion}'";
        $this->data[ "promocion" ] = model( "PromocionModel" )->where( $sql , null, false )->first();

        $this->data[ "navbar" ] = true;
        $this->data[ "titulo" ] = "Promoción ";

        $sql = "estatus_codigo = '201-ACTIVO' AND modelo_codigo = '{$this->data[ "promocion" ][ "modelo_codigo" ]}'";
        $this->data[ "productos" ] = model( "ProductoModel" )->where( $sql , null, false )->findAll();

        echo template( "admin/promo_detalle", $this->data );
    }


    public function save_promo(){
        if( !(
            $this->data[ "usuario" ]->permiso( "40-ADMIN")
        ) ){
            return redirect()->to( "inicio" ); 
        }
        
        /**********************************/

        $data = $this->request->getPost( "data" );

        model( "PromocionModel" )->save( $data );
    }


    public function pasarelas( $modelo ){
        if( !(
            $this->data[ "usuario" ]->permiso( "40-ADMIN")
        ) ){
            return redirect()->to( "inicio" ); 
        }
        
        /**********************************/

        $this->data[ "navbar" ] = true;
        $this->data[ "titulo" ] = "Métodos de pago";
        $this->data[ "modelo" ] = $modelo;

        $sql = "modelo_codigo = '{$modelo}'";
        $this->data[ "pasarelas" ] = model( "MetodopagoModel" )->where( $sql , null, false )->findAll();

        echo template( "admin/pasarelas", $this->data );
    }   

    
    
    public function productos( $modelo ){

        if( !(
            $this->data[ "usuario" ]->permiso( "20-ALMACEN") || 
            $this->data[ "usuario" ]->permiso( "40-ADMIN")
        ) ){
            return redirect()->to( "inicio" ); 
        }
        
        /**********************************/

        $this->data[ "navbar" ] = true;
        $this->data[ "titulo" ] = "Productos";
        $this->data[ "modelo" ] = $modelo;

        $sql = "modelo_codigo = '{$modelo}'";
        $this->data[ "productos" ] = model( "ProductoModel" )->where( $sql , null, false )->findAll();

        echo template( "admin/productos", $this->data );
    }   



    public function estatus(){
        if( !(
            $this->data[ "usuario" ]->permiso( "40-ADMIN")
        ) ){
            return redirect()->to( "inicio" ); 
        }
        
        /**********************************/

        $this->data[ "navbar" ] = true;
        $this->data[ "titulo" ] = "Estatus de socio";

        $db = db_connect();
        $this->data[ "estatuses" ] = $db->query( "SELECT * from t_estatus" )->getResultArray();

        echo template( "admin/estatus", $this->data );
    } 
    


    public function isr(){
        if( !(
            $this->data[ "usuario" ]->permiso( "38-CONTABILIDAD" )
        ) ){
            return redirect()->to( "inicio" ); 
        }
        
        /**********************************/

        $this->data[ "navbar" ] = true;
        $this->data[ "titulo" ] = "Tablas de ISR SEMANAL";

        $db = db_connect();
        $this->data[ "isr" ] = $db->query( "SELECT * from t_isr" )->getResultArray();

        echo template( "admin/isr", $this->data );
    } 
    
    

    public function variables(){
        
        if( !(
            $this->data[ "usuario" ]->permiso( "40-ADMIN")
        ) ){
            return redirect()->to( "inicio" ); 
        }
        
        /**********************************/

        $this->data[ "navbar" ] = true;
        $this->data[ "titulo" ] = "Variables de sistema";

        echo template( "admin/variables", $this->data );
    } 
    
    public function save_variable(){
        if( !(
            $this->data[ "usuario" ]->permiso( "40-ADMIN")
        ) ){
            return redirect()->to( "inicio" ); 
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

    public function apikeys(){
        if( !(
            $this->data[ "usuario" ]->permiso( "40-ADMIN")
        ) ){
            return redirect()->to( "inicio" ); 
        }
        
        /**********************************/

        $this->data[ "navbar" ] = true;
        $this->data[ "titulo" ] = "API keys";

        echo template( "admin/apikeys", $this->data );
    } 


}

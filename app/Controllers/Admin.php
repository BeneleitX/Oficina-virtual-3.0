<?php

namespace App\Controllers;

class Admin extends BaseController
{
    function __construct() {
        $this->data[ "menu" ] = "admin";
    }

    public function dashboard(){

        if( !$this->data[ "usuario" ]->permiso( "20-ALMACEN") ){
            return redirect()->to( "inicio" ); 
        }



        $this->data[ "navbar" ] = true;
        $this->data[ "titulo" ] = "Administración de sistema";

        $sql = "( ( data->>'$.credencial.frente' != 'null' and data->>'$.credencial.reverso' != 'null' ) OR data->>'$.credencial.acta' != 'null' ) and data->>'$.credencial.estatus' = 1";
        $this->data[ "credenciales" ] = model( "UsuarioModel" )->where( $sql , null, false )->findAll();

        $sql = "substring(estatus_codigo , 1, 3 ) > 200";

        $this->data[ "promociones" ]  = model( "PromocionModel" )->where( $sql , null, false )->findAll();
        $this->data[ "pasarelas" ]    = model( "MetodopagoModel" )->where( $sql , null, false )->findAll();
        $this->data[ "paqueterias" ]  = model( "MetodoentregaModel" )->where( $sql , null, false )->findAll();
        $this->data[ "almacenes" ]    = model( "AlmacenModel" )->where( $sql , null, false )->findAll();
        $this->data[ "rangos" ]       = model( "RangoModel" )->findAll();
        $this->data[ "productos" ]    = model( "ProductoModel" )->where( $sql , null, false )->findAll();
        $this->data[ "usuarios" ]     = model( "UsuarioModel" )->where( $sql , null, false )->findAll();
        $this->data[ "roles" ]        = model( "RolModel" )->findAll();
        $this->data[ "periodos" ]     = model( "PeriodoModel" )->where( $sql , null, false )->findAll();
        $this->data[ "esquemas" ]     = model( "EsquemaModel" )->where( $sql , null, false )->findAll();
        $this->data[ "recompensas" ]  = model( "RecompensaModel" )->where( $sql , null, false )->findAll();

        echo template( "admin/dashboard", $this->data );
    }

    public function credenciales(){
        $this->data[ "navbar" ] = true;
        $this->data[ "titulo" ] = "Validar credenciales INE";

        $sql = "( ( data->>'$.credencial.frente' != 'null' and data->>'$.credencial.reverso' != 'null' ) OR data->>'$.credencial.acta' != 'null' ) and data->>'$.credencial.estatus' = 1";
        $this->data[ "socios" ] = model( "UsuarioModel" )->where( $sql , null, false )->findAll();

        echo template( "admin/credenciales", $this->data );
    }    


    public function resolucion_ine(){
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
        $this->data[ "navbar" ] = true;
        $this->data[ "titulo" ] = "Promociones";
        $this->data[ "modelo" ] = $modelo;

        $sql = "modelo_codigo = '{$modelo}'";
        $this->data[ "promociones" ] = model( "PromocionModel" )->where( $sql , null, false )->findAll();

        echo template( "admin/promociones", $this->data );
    }    


    public function promo_detalle( $promocion ){
        $sql = "codigo = '{$promocion}'";
        $this->data[ "promocion" ] = model( "PromocionModel" )->where( $sql , null, false )->first();

        $this->data[ "navbar" ] = true;
        $this->data[ "titulo" ] = "Promoción ";

        $sql = "estatus_codigo = '201-ACTIVO' AND modelo_codigo = '{$this->data[ "promocion" ][ "modelo_codigo" ]}'";
        $this->data[ "productos" ] = model( "ProductoModel" )->where( $sql , null, false )->findAll();

        echo template( "admin/promo_detalle", $this->data );
    }


    public function save_promo(){
        $data = $this->request->getPost( "data" );

        model( "PromocionModel" )->save( $data );
    }


    public function pasarelas( $modelo ){
        $this->data[ "navbar" ] = true;
        $this->data[ "titulo" ] = "Métodos de pago";
        $this->data[ "modelo" ] = $modelo;

        $sql = "modelo_codigo = '{$modelo}'";
        $this->data[ "pasarelas" ] = model( "MetodopagoModel" )->where( $sql , null, false )->findAll();

        echo template( "admin/pasarelas", $this->data );
    }   

    
    
    public function productos( $modelo ){
        $this->data[ "navbar" ] = true;
        $this->data[ "titulo" ] = "Productos";
        $this->data[ "modelo" ] = $modelo;

        $sql = "modelo_codigo = '{$modelo}'";
        $this->data[ "productos" ] = model( "ProductoModel" )->where( $sql , null, false )->findAll();

        echo template( "admin/productos", $this->data );
    }   
    
  
    public function rangos( $modelo ){
        $this->data[ "navbar" ] = true;
        $this->data[ "titulo" ] = "Rangos de socio";
        $this->data[ "modelo" ] = $modelo;

        $sql = "modelo_codigo = '{$modelo}'";
        $this->data[ "rangos" ] = model( "rangoModel" )->where( $sql , null, false )->findAll();

        $db = db_connect();
        $socios = $db->query( "SELECT if( SUBSTRING( u.estatus_codigo, 1,3 ) > 200, \"activos\", \"inactivos\") AS estatus, u.data->>\"$.rango\" AS rango_codigo, COUNT(*) AS cantidad FROM t_usuarios u GROUP BY if( SUBSTRING( u.estatus_codigo, 1,3 ) > 200, \"ACTIVO\", \"INACTIVO\"), u.data->>\"$.rango\"" );

        foreach( $socios->getResult() as $s ){
            $this->data[ "socios" ][ $s->rango_codigo ][ $s->estatus ] = $s->cantidad;
        }

        echo template( "admin/rangos", $this->data );
    } 



    public function estatus(){
        $this->data[ "navbar" ] = true;
        $this->data[ "titulo" ] = "Estatus de socio";

        $db = db_connect();
        $this->data[ "estatuses" ] = $db->query( "SELECT * from t_estatus" )->getResultArray();

        echo template( "admin/estatus", $this->data );
    } 
    
    

    public function variables(){
        $this->data[ "navbar" ] = true;
        $this->data[ "titulo" ] = "Variables de sistema";

        echo template( "admin/variables", $this->data );
    } 
    
    public function save_variable(){
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
}

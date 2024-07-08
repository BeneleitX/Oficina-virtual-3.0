<?php

namespace App\Controllers;

class Rangos extends BaseController
{
    function __construct() {
        $this->data[ "menu" ] = "admin";
    }
    

    
    public function catalogo( $modelo ){
        $this->data[ "navbar" ] = true;
        $this->data[ "titulo" ] = "Entrega de pines de rango";
        $this->data[ "modelo" ] = $modelo;

        $sql = "modelo_codigo = '{$modelo}'";
        $this->data[ "rangos" ] = model( "rangoModel" )->where( $sql , null, false )->findAll();

        $db = db_connect();
        $socios = $db->query( "SELECT if( SUBSTRING( u.estatus_codigo, 1,3 ) > 200, \"activos\", \"inactivos\") AS estatus, u.data->>\"$.rango\" AS rango_codigo, COUNT(*) AS cantidad FROM t_usuarios u GROUP BY if( SUBSTRING( u.estatus_codigo, 1,3 ) > 200, \"ACTIVO\", \"INACTIVO\"), u.data->>\"$.rango\"" );

        foreach( $socios->getResult() as $s ){
            $this->data[ "socios" ][ $s->rango_codigo ][ $s->estatus ] = $s->cantidad;
        }

        echo template( "rangos/pines", $this->data );
    } 

}

<?php

namespace App\Controllers;

class Geodata extends BaseController
{
    function __construct() {
        $this->data[ "menu" ] = "admin";
    }

    /**
     * Función que muestra una gráfica con la distribución geográfica de las ventas de Paqueterías
     * en el mes de Diciembre de 2025
     * 
     * Requiere permiso "40-ADMIN"
     */

    public function mapa(){
        if( !(
            $this->data[ "usuario" ]->permiso( "22-MAPA" ) ||
            $this->data[ "usuario" ]->permiso( "40-ADMIN" )
        ) ){
            return redirect()->to( "no_permiso" ); 
        }

        $this->data[ "navbar" ]  = true;
        $this->data[ "titulo" ]  = "Datos de distribución geográfica";

        $this->data[ "chart_data" ] = [];
        
        extract( $this->request->getPost() );

        if( !isset( $tipo_datos ) ){
            $tipo_datos = "venta_paqueteria";
        }

        if( !isset( $f_inicia ) ){
            $f_inicia = date( "Y-m-01" );
        }

        if( !isset( $f_termina ) ){
            $f_termina = date( "Y-m-d" );
        }

        if( !isset( $empresa ) ){
            $empresa = "10-NUTRICION";
        }

        $db = db_connect();

        switch( $tipo_datos ){
            case "venta_paqueteria":
                
                $sql = "SELECT 
                    concat( 'mx-', e.chart_code ) as code,
                    sum( p.data->>'$.total' ) as total
                    from t_pedidos p
                    join t_colonias c on c.id = p.data->>'$.domicilio.colonia_id'
                    join t_entidades e on e.id = c.entidad_id

                    where p.modelo_codigo = '{$empresa}'
                    and substring( p.estatus_codigo, 1, 3 ) > 400
                    and cast( p.fechas->>'$.pagado' as date ) between '{$f_inicia}' and '{$f_termina}'
                    and substring( p.metodoentrega_codigo, 4 ) = 'PAQUETERIA'

                    group by c.entidad_id";
                
                $result = $db->query( $sql );

                foreach( $result->getResultObject() as $row ){
                    $this->data[ "chart_data" ][] = [
                        $row->code,
                        (float)$row->total
                    ];
                }

                break;

            case "venta_almacen":
                $sql = "SELECT 
                        substring( a.codigo, 5 ) as codigo,
                        any_value( a.nombre ) as nombre,
                        any_value( a.settings->>'$.coordenadas.lat' ) as lat,
                        any_value( a.settings->>'$.coordenadas.lon' ) as lon,
                        IFNULL( sum( p.data->>'$.total' ), 0 ) as venta,
                        count(*) as pedidos
                        from t_almacenes a 
                        left join t_pedidos p 
                            on  a.codigo = p.data->>'$.entrega' 
                            and p.modelo_codigo = '{$empresa}'
                            and substring( p.metodoentrega_codigo, 4 ) = 'ALMACEN'
                            and substring( p.estatus_codigo, 1, 3 ) > 400  
                            and cast( p.fechas->>'$.pagado' as date ) between '{$f_inicia}' and '{$f_termina}'

                        where a.modelo_codigo = '{$empresa}'
                        and a.settings->>'$.tipo' not in ( 'LIDER', 'ALMACEN' )
                        and substring( a.estatus_codigo, 1, 3 ) > 200  
                        group by substring( a.codigo, 5 ) ";
                
                $this->data[ "chart_data" ] = $db->query( $sql )->getResultArray();

                foreach( $this->data[ "chart_data" ] as $k => $row ){
                    $this->data[ "chart_data" ][ $k ][ "venta" ] = (integer)$row[ "venta" ];

                    if( $row[ "venta" ] == 0 ){
                        $row[ "pedidos" ] = 0;
                    }

                    $this->data[ "chart_data" ][ $k ][ "pedidos" ] = (integer)$row[ "pedidos" ];

                    $this->data[ "chart_data" ][ $k ][ "lat" ] = (float)$row[ "lat" ];
                    $this->data[ "chart_data" ][ $k ][ "lon" ] = (float)$row[ "lon" ];
                }                

                break;

            case "nuevos_socios":


                break;

            case "socios_activos":
                $sql = "SELECT 
                    concat( 'mx-', e.chart_code ) as code,
                    sum( p.data->>'$.total' ) as total
                    from t_pedidos p
                    join t_colonias c on c.id = p.data->>'$.domicilio.colonia_id'
                    join t_entidades e on e.id = c.entidad_id

                    where p.modelo_codigo = '{$empresa}'
                    and substring( p.estatus_codigo, 1, 3 ) > 400
                    and cast( p.fechas->>'$.pagado' as date ) between '{$f_inicia}' and '{$f_termina}'
                    and substring( p.metodoentrega_codigo, 4 ) = 'PAQUETERIA'

                    group by c.entidad_id";
                
                $result = $db->query( $sql );

                foreach( $result->getResultObject() as $row ){
                    $this->data[ "chart_data" ][] = [
                        $row->code,
                        (float)$row->total
                    ];
                }
                break;
        }

        $this->data[ "empresa" ]    = $empresa;
        $this->data[ "tipo_datos" ] = $tipo_datos;
        $this->data[ "f_inicia" ]   = $f_inicia;
        $this->data[ "f_termina" ]  = $f_termina;
     
        echo template( "geodata/mapa", $this->data );
    }

}

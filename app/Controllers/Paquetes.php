<?php

namespace App\Controllers;

class Paquetes extends BaseController
{
    function __construct() {
        $this->data[ "menu" ] = "admin";
    }
    

    /**
     *
     * Requiere el par metro $mes, que es el mes en formato "YYYYMM".
     *
     * Verifica que el usuario logueado tenga permiso de administraci n.
     *
     * Redirecciona a la p gina de no permiso si no se cumple la condici n
     * anterior.
     *
     * @param string $mes
     *
     * @return void
     */
    public function admin( $mes = null )
    {
        if( !(
            $this->data[ "usuario" ]->permiso( "40-ADMIN")
        ) ){
            return redirect()->to( "no_permiso" ); 
        }
        
        if( !$mes ){
            $mes = date( "Ym", strtotime( date("Y-m-d")." - 1 month" ) );
        }

        /**********************************/
                
        $this->data[ "navbar" ] = true;
        $this->data[ "titulo" ] = "Solicitudes de retiro de producto";
        $this->data[ "mes" ]    = $mes;

        $this->data[ "solicitudes" ] = model( "RetiroModel" )
            ->select('t_retiros.*')
            ->join( "t_inversiones", "t_inversiones.id = t_retiros.inversion_id" )
            
            ->where( "SUBSTRING( t_retiros.estatus_codigo,1,3) > 200" )
            ->where( "JSON_EXTRACT( t_retiros.fechas, '$.mes' ) = '{$mes}'" )
            ->where( "cast( json_unquote( json_extract( t_inversiones.fechas, '$.pagado' ) ) as date ) > '".FECHA_BASE."' " )

            ->findAll();

        echo template( "paquetes/admin", $this->data );
    }


    /**
     *
     * Mostrar&#225; informaci&#243;n de los retiros de cada mes.
     *
     * Requiere el permiso de administraci&#243;n.
     *
     * @return void
     */
    public function dashboard( $socio = null )
    {
        if( $socio ){
            $request = base64_decode( urldecode( $socio ) );
            $this->data[ "socio" ] = model( "UsuarioModel" )->where( "password = '{$request}'" )->first();
        }
        else{
            $this->data[ "socio" ] = null;
        }

        $this->data[ "navbar" ] = true;
        $this->data[ "titulo" ] = "Mis inversiones";

        $db  = db_connect();
        $sql = "UPDATE t_retiros 
                set estatus_codigo = '124-VENCIDO' 
                where estatus_codigo = '165-ESPERANDO-CODIGO' 
                and fechas->>'$.mes' < date_format( now(), '%Y%m' )";

        $db->query( $sql );

        echo template( "paquetes/dashboard", $this->data );
    }


    /**
     *
     * Requiere el par metro $mes, que es el mes en formato "YYYYMM".
     *
     * Verifica que el usuario logueado tenga permiso de administraci n.
     *
     * Redirecciona a la p gina de no permiso si no se cumple la condici n
     * anterior.
     *
     * @param string $mes
     *
     * @return void
     */
    public function bono_liderazgo( $mes = null )
    {
        if( !(
            $this->data[ "usuario" ]->permiso( "31-GASOLINA" ) ||
            $this->data[ "usuario" ]->permiso( "40-ADMIN")
        ) ){
            return redirect()->to( "no_permiso" ); 
        }
        
        if( !$mes ){
            $mes = date( "Ym", strtotime( date("Y-m-d")." - 1 month" ) );
        }

        /**********************************/

        $db = db_connect();

        // revisamos que no haya socios pendientes de calcular su bono

        if( $mes < date( "Ym" ) ){

            $mes1 = date( "Ym", strtotime( substr( $mes, 0, 4 )."-".substr( $mes, 4, 2 )."-01 - 1 month" ) );
            $mes0 = $mes;

            $sql = "SELECT u.id as socio
                    from t_usuarios u 
                    where substring( u.data->>'$.estatus.modelos.\"50-INVERSION\"',1,3 ) > 400 
                    and u.historial->'$.modelos.\"50-INVERSION\".corte_mensual.\"{$mes1}\".directos' > 1 
                    and u.historial->'$.modelos.\"50-INVERSION\".corte_mensual.\"{$mes0}\".directos' is null   ";

            $pendientes = $db->query( $sql );

            // evitar que haya socios sin calculo de bono por falta de login
        
            foreach( $pendientes->getResult() as $socio ){
                $u = model( "UsuarioModel" )->find( $socio->socio );

                $sql     = "call p_get_inversiones( {$u->id}, {$mes} )";
                $ps      = $db->query( $sql )->getResult();

                $u->revisa_bono_liderazgo( $ps, substr( $mes0, 0, 4 )."-".substr( $mes0, 4, 2 )."-01" );
            }
        }

        $this->data[ "mes" ]    = $mes;
        $this->data[ "navbar" ] = true;
        $this->data[ "titulo" ] = "Bono de liderazgo";

        $mesb = date( "Ym", strtotime( substr( $mes, 0, 4 )."-".substr( $mes, 4, 2 )."-01" ) ); //  + 1 month

        $sql = "SELECT 
                    u.id,u.historial->>'$.modelos.\"50-INVERSION\".corte_mensual.\"{$mesb}\"' as cortes
                from t_usuarios u
                where 
                    u.data->>'$.estatus.modelos.\"50-INVERSION\"' = '520-CALIFICADO-ACTUAL'
                and u.historial->>'$.modelos.\"50-INVERSION\".corte_mensual.\"{$mesb}\".directos' > 3";

        $historial = $db->query( $sql )->getResultArray();

        $this->data[ "socios" ] = [];

        foreach( $historial as $socio ){
            $corte = json_decode($socio[ "cortes" ], true );
            
            if( $corte[ "directos" ] > 3 ){

                if( $corte[ "directos" ] > 11 ){
                    $rango = "530-LEYENDA";
                }
                elseif( $corte[ "directos" ] > 7 ){
                    $rango = "520-CONQUISTADOR";
                }
                elseif( $corte[ "directos" ] > 3 ){
                    $rango = "510-PIONERO";
                }

                $this->data[ "socios" ][] = [ 
                    "id"    => $socio[ "id" ],
                    "rango" => $rango,
                    "mes"   => $corte
                ];
            }
        }

        echo template( "paquetes/bono_liderazgo", $this->data );
    }


    /**
     *
     * This function checks for administrative permissions before proceeding.
     * It gathers various investment-related data such as active investments,
     * ranks, seeds, monthly returns, purchase periods, commissions, and withdrawals.
     * The processed data is stored in the class's data array for use in rendering
     * the "paquetes/inversiones" template.
     *
     * The function performs multiple database queries to fetch information
     * about investments, ranks, purchase periods, and more. It also calculates
     * totals for various metrics and formats data for display.
     *
     * @return void
     */
    public function inversiones()
    {
        if( !(
            $this->data[ "usuario" ]->permiso( "31-GASOLINA" ) ||
            $this->data[ "usuario" ]->permiso( "40-ADMIN")
        ) ){
            return redirect()->to( "no_permiso" ); 
        }
        
        $db  = db_connect();

        $this->data[ "navbar" ] = true;
        $this->data[ "titulo" ] = "Paquetes de producto";
        load_catalogo( "productos", "modelo_codigo = '50-INVERSION' and substring( codigo, 1 ,3 ) > 500 and estatus_codigo = '201-ACTIVO'" );

        $sql = "SELECT count(*) as total from (
                    SELECT any_value(i.id), i.usuario_id, count(*)
                    from t_inversiones i
                    join t_pedidos p on p.id = i.pedido_id and substring( p.estatus_codigo, 1, 3 ) > 400
                    where substring( i.estatus_codigo, 1, 3 ) > 300
                    and cast( now() as date ) between cast( i.fechas->>'$.pagado' as date ) and cast( i.fechas->>'$.cierre' as date )
                    group by i.usuario_id
                ) as t";

        $this->data[ "total_activos" ] = $db->query( $sql )->getRow()->total;

        $sql = "SELECT
                    r.codigo, r.nombre, r.color,
                    count(*) as cantidad
                from t_rangos r 
                join t_usuarios u on u.data->>'$.rango_inversion' = r.codigo
                where r.modelo_codigo = '50-INVERSION' 
                and substring( r.codigo, 1, 3 ) > 500
                and substring( u.data->>'$.estatus.modelos.\"50-INVERSION\"', 1, 3 ) > 300 
                group by r.codigo
                order by r.codigo";

        $this->data[ "rangos" ] = $db->query( $sql )->getResultArray();

        $sql = "SELECT 
                    u.historial->>'$.modelos.\"50-INVERSION\".corte_mensual' as cortes
                from t_usuarios u
                where 
                    u.data->>'$.estatus.modelos.\"50-INVERSION\"' = '520-CALIFICADO-ACTUAL'";

        $historial = $db->query( $sql )->getResultArray();
        $drangos   = [
            "530-LEYENDA"      => [],
            "520-CONQUISTADOR" => [],
            "510-PIONERO"      => []
        ];

        $date = new \DateTime( date( "Y-m"."-01" ) );

        for( $a = 0; $a < 13; $a++){
            $mes = $date->format( "Ym" );

            $drangos[ "530-LEYENDA" ][ $mes ] = 0;
            $drangos[ "520-CONQUISTADOR" ][ $mes ] = 0;
            $drangos[ "510-PIONERO" ][ $mes ] = 0;

            $date->modify( "- 1 month" );
        } 

        $errores = [];
        foreach( $historial as $socio ){
            $cortes = json_decode($socio[ "cortes" ], true );

            if( is_array( $cortes ) ){
                foreach( $cortes as $k => $v ){

                    $k = date( "Ym", strtotime( substr( $k, 0, 4 )."-".substr( $k, 4, 2 )."-01 - 1 month" ) );

                    if( $v[ "directos" ] > 11 ){
                        if( !isset($drangos[ "530-LEYENDA" ][ $k ])){
                            $drangos[ "530-LEYENDA" ][ $k ] = 0;
                        }

                        $drangos[ "530-LEYENDA" ][ $k ]++;
                    }
                    elseif( $v[ "directos" ] > 7 ){
                        if( !isset($drangos[ "520-CONQUISTADOR" ][ $k ])){
                            $drangos[ "520-CONQUISTADOR" ][ $k ] = 0;
                        }

                        $drangos[ "520-CONQUISTADOR" ][ $k ]++;
                    }
                    elseif( $v[ "directos" ] > 3 ){ 
                        if( !isset($drangos[ "510-PIONERO" ][ $k ])){
                            $drangos[ "510-PIONERO" ][ $k ] = 0;
                        }

                        $drangos[ "510-PIONERO" ][ $k ]++;
                    }
                }
            }
            else{
                $errores[] = $socio;
            }
        }
        if( sizeof( $errores ) ){
            echo "<pre class=\"alert alert-danger\">".print_r( $errores, true )."</pre>";
        }

        $this->data[ "drangos" ] = [];

        $rtemp = [];
        $date = new \DateTime( date( "Y-m"."-01" ) );

        for( $a = 0; $a < 13; $a++){
            $mes = $date->format( "Ym" );

            $rtemp[ "530-LEYENDA" ][ $a ] = $drangos[ "530-LEYENDA" ][ $mes ];
            $rtemp[ "520-CONQUISTADOR" ][ $a ] = $drangos[ "520-CONQUISTADOR" ][ $mes ];
            $rtemp[ "510-PIONERO" ][ $a ] = $drangos[ "510-PIONERO" ][ $mes ];

            $date->modify( "- 1 month" );
        }

        $this->data[ "drangos_total" ] = $rtemp;
        $this->data[ "drangos" ] = [];

        foreach( array_reverse( $rtemp ) as $k => $v ){
            $this->data[ "drangos" ][] = [
                "name" => $k, "data" => array_reverse( $v ) 
            ];
        }


        $sql = "SELECT 
                    substring( i.producto_codigo, 1, 13 ) as codigo, 
                    any_value( o.data->>'$.porcentaje' ) as porcentaje, 
                    any_value( o.data->>'$.color' ) as color, 
                    count(*) as cantidad, 
                    date_format( i.fechas->>'$.pagado', '%Y%m' ) as fecha
                from t_inversiones i
                join t_pedidos p on p.id = i.pedido_id and substring( p.estatus_codigo, 1, 3 ) > 400
                join t_productos o on o.codigo = substring( i.producto_codigo, 1, 13 )
                where substring( i.estatus_codigo, 1, 3 ) > 300
                and cast( now() as date ) between cast( i.fechas->>'$.pagado' as date ) and cast( i.fechas->>'$.cierre' as date )
                group by substring( i.producto_codigo, 1, 13 ), fecha
                order by fecha";


        $inversiones = $db->query( $sql )->getResultArray();

        $sql = "SELECT e.Ym as fecha, sum( e.semilla ) as semilla
                FROM t_inversiones i
                join t_pedidos p on p.id = i.pedido_id and substring( p.estatus_codigo, 1, 3 ) > 400,
                JSON_TABLE( i.extras, '$.meses[*]'
                    COLUMNS (
                        Ym VARCHAR(6) PATH '$.Ym',
                        semilla DECIMAL(10,2) PATH '$.semilla'
                    )
                ) AS e
                Where  substring( i.estatus_codigo, 1, 3 ) > 300
                and e.Ym <= date_format( now(), '%Y%m')
                and cast( now() as date ) between cast( i.fechas->>'$.pagado' as date ) and cast( i.fechas->>'$.cierre' as date )
                group by e.Ym
                order by e.Ym";

        $semilla = $db->query( $sql )->getResultArray();

        
        $sql = "SELECT e.Ym as fecha, sum( e.rendimiento_mes ) as rendimiento
                FROM t_inversiones i
                join t_pedidos p on p.id = i.pedido_id and substring( p.estatus_codigo, 1, 3 ) > 400,
                JSON_TABLE( i.extras, '$.meses[*]'
                    COLUMNS (
                        Ym VARCHAR(6) PATH '$.Ym',
                        rendimiento_mes DECIMAL(10,2) PATH '$.rendimiento_mes'
                    )
                ) AS e
                Where  substring( i.estatus_codigo, 1, 3 ) > 300
                and e.Ym <= date_format( now(), '%Y%m')
                and cast( now() as date ) between cast( i.fechas->>'$.pagado' as date ) and cast( i.fechas->>'$.cierre' as date )
                group by e.Ym
                order by e.Ym";

        $rendimiento = $db->query( $sql )->getResultArray();

        $sql = "SELECT e.codigo as codigo, sum( p.data->>'$.total' *.2 ) as total, count(*) as pedidos
                from t_pedidos p
                join t_periodos e on cast( p.fechas->>'$.reparte' as date ) between e.inicia and e.termina
                where e.modelo_codigo = '50-INVERSION'
                and p.modelo_codigo = '50-INVERSION'
                and substring( p.estatus_codigo, 1, 3) > 400
                -- and e.estatus_codigo = '422-PERIODO-PAGADO'
                and e.inicia > '2025-03-01'
                group by e.codigo";

        $periodos = $db->query( $sql )->getResultArray();

        $sql = "SELECT e.codigo as periodo, substring( i.producto_codigo, 1, 13 ) as tipo, count(*) as total
                from
                t_inversiones i 
                join t_pedidos p on p.id = i.pedido_id
                join t_productos o on o.codigo = substring( i.producto_codigo, 1, 13 )
                join t_periodos e on cast( p.fechas->>'$.reparte' as date ) between e.inicia and e.termina and e.modelo_codigo = '50-INVERSION'
                where 
                substring( p.estatus_codigo,1, 3) > 400
                and p.modelo_codigo = '50-INVERSION'
                and substring(i.estatus_codigo,1,3) > 200
                group by e.codigo, substring( i.producto_codigo, 1, 13 )";

        $compras = $db->query( $sql )->getResultArray();
      
        $this->data[ "compras" ]       = [];
        $this->data[ "total_compras" ] = 0;

        $sql = "SELECT sum(r.cantidad) as total, r.fechas->>'$.mes' as mes
                from
                t_retiros r
                join t_inversiones i on i.id = r.inversion_id and substring(i.estatus_codigo,1,3) > 200
                join t_pedidos p on p.id = i.pedido_id and substring( p.estatus_codigo,1, 3) > 400 
                where substring( r.estatus_codigo,1, 3) > 200
                group by r.fechas->>'$.mes'
                order by mes asc";

        $retiros = $db->query( $sql )->getResultArray();
        

        $sql = "SELECT 
                    u.id as socio, 
                    f_get_semilla( u.id, date_format( now(), '%Y%m' ), u.id ) as semilla,
                    json_unquote( json_extract( u.historial, concat( '$.modelos.\"50-INVERSION\".corte_mensual.\"', date_format( now(), '%Y%m'), '\".bolsa' ) ) ) as bolsa,
                    json_unquote( json_extract( u.historial, concat( '$.modelos.\"50-INVERSION\".corte_mensual.\"', date_format( now(), '%Y%m'), '\".directos' ) ) ) as directos	
                from t_usuarios u
                where u.data->>'$.estatus.modelos.\"50-INVERSION\"' = '520-CALIFICADO-ACTUAL'
                having bolsa > 6500";

        $this->data[ "ranking" ] = $ranking = $db->query( $sql )->getResultArray();

        $this->data[ "bono" ]       = [];
        $this->data[ "total_bono" ] = 0;

        $sql = "SELECT sum(c.cantidad) as total, date_format( c.fecha, '%Y%m') as mes
                from t_comisiones c
                join t_usuarios u on u.id = c.usuario_id
                where c.esquema_codigo = '530-LIDERAZGO'
                and u.data->>'$.estatus.modelos.\"50-INVERSION\"' = '520-CALIFICADO-ACTUAL'
                group by mes
                order by mes asc";

        $bonos = $db->query( $sql )->getResultArray();

        $rtemp = [];
        $date = new \DateTime( date( "Y-m"."-01" ) );

        foreach( $bonos as $r ){
            $rtemp[ $r[ "mes" ] ] = $r[ "total" ];
            $this->data[ "total_bono" ] += $r[ "total" ];
        }

        for( $a = 0; $a < 13; $a++){
            $mes = $date->format( "Ym" );
            $this->data[ "bono" ][ $mes ] = $rtemp[ $mes ] ?? 0;

            $date->modify( "- 1 month" );
        }


        $this->data[ "retiros" ]       = [];
        $this->data[ "total_retiros" ] = 0;
        
        $rtemp = [];
        $date = new \DateTime( date( "Y-m"."-01" ) );

        foreach( $retiros as $r ){
            $rtemp[ $r[ "mes" ] ] = $r[ "total" ];
            $this->data[ "total_retiros" ] += $r[ "total" ];
        }

        for( $a = 0; $a < 13; $a++){
            $mes = $date->format( "Ym" );
            $this->data[ "retiros" ][ $mes ] = $rtemp[ $mes ] ?? 0;

            $date->modify( "- 1 month" );
        }


        foreach( $compras as $c ){
            if( !isset( $this->data[ "compras" ][ $c[ "tipo" ] ] ) ){
                $this->data[ "compras" ][ $c[ "tipo" ] ] = [];
            }

            $this->data[ "compras" ][ $c[ "tipo" ] ][] = $c[ "total" ];
            $this->data[ "total_compras" ] += $c[ "total" ];
        }

        $this->data[ "semanas" ] = [];
        $this->data[ "total_comisiones" ] = 0;
        $this->data[ "data_comisiones" ]  = [];

        foreach( $periodos as $p ){
            $this->data[ "semanas" ][] = periodo( $p[ "codigo" ] );
            $this->data[ "total_comisiones" ] += $p[ "total" ];
            $this->data[ "data_comisiones" ][ "pedidos" ][] = intval( $p[ "pedidos" ] );
            $this->data[ "data_comisiones" ][ "total" ][]   = $p[ "total" ];
        }

        // dd($this->data[ "semanas" ], $this->data[ "total_comisiones" ], $this->data[ "data_comisiones" ]);   

        $this->data[ "total_inversiones" ] = 0;

        $data = [];

        foreach( $inversiones as $i ){   
            $this->data[ "total_inversiones" ] += $i[ "cantidad" ];
            $data[ $i[ "codigo" ] ][ $i[ "fecha" ] ] = intval( $i[ "cantidad" ] );
        }

        $this->data[ "meses" ] = [];
        $temp_semilla = [];
        $temp_rendimiento = [];
       
        $this->data[ "data_inversiones" ]  = [];
        $this->data[ "data_semilla" ]      = [];
        $this->data[ "data_rendimiento" ]  = [];

        foreach( $semilla as $s ){
            $temp_semilla[ $s[ "fecha" ] ] = intval( $s[ "semilla" ] );
        }

        foreach( $rendimiento as $s ){
            $temp_rendimiento[ $s[ "fecha" ] ] = intval( $s[ "rendimiento" ] );
        }

        $meses = 12;

        $mes      = date( "Ym", strtotime( date( "Y-m")."-01 - {$meses} month" ) );
        $anterior = date( "Ym", strtotime( date( "Y-m")."-01 - ".( $meses + 1)." month" ) );
     
        for( $a = 0; $a <= $meses; $a++ ){
            $this->data[ "meses" ][] = strtoupper( mes( substr( $mes, 4, 2 ), 3) ); //." ".substr( $mes, 0, 4 );

            $this->data[ "data_semilla" ][ $mes ] = ( $temp_semilla[ $mes ] ?? 0 );
            $this->data[ "data_rendimiento" ][ $mes ] = ( $temp_rendimiento[ $mes ] ?? 0 );

            foreach( PRODUCTOS as $codigo => $tipo ){    
                if( !isset( $this->data[ "data_inversiones" ][ $codigo ] ) ){
                    $this->data[ "data_inversiones" ][ $codigo ] = 0;
                }

                if( isset( $data[ $codigo ][ $mes ] ) ){
                    $this->data[ "data_inversiones" ][ $codigo ] += $data[ $codigo ][ $mes ];
                }
                else{
                    $data[ $codigo ][ $mes ] =0;
                }

                $data[ $codigo ][ $mes ] += ( $data[ $codigo ][ $anterior ] ?? 0 );
            }

            $anterior = $mes;
            $mes = date( "Ym", strtotime( substr( $mes, 0, 4 )."-".substr( $mes, 4, 2 )."-01 + 1 month" ) );
        }

        ksort($this->data[ "data_semilla" ]);

        $this->data[ "data_compras" ] = [];

        foreach( PRODUCTOS as $codigo => $tipo ){

            ksort($data[ $codigo ]);

            $this->data[ "data" ][] = [ 
                "name" => PRODUCTOS[ $codigo ][ "data" ][ "nombre" ],
                "data" => array_reverse( array_reverse( $data[ $codigo ] ) )
            ];

            $this->data[ "data_compras" ][] = [ 
                "name" => PRODUCTOS[ $codigo ][ "data" ][ "nombre" ],
                "data" => array_reverse( array_reverse( $this->data[ "compras" ][ $codigo ] ) )
            ];
        }

        echo template( "paquetes/inversiones", $this->data );
    }


    /**
     * Processes a POST request to verify a transaction hash on the blockchain.
     *
     * This function receives a transaction hash via POST, validates its length,
     * and uses an external API to retrieve transaction details. If the transaction
     * is confirmed and directed to an active wallet, it registers the transaction
     * in the database, updates the investment information, and returns a success
     * response. If any validation fails, an appropriate error message is returned.
     *
     * @return void The response is echoed as a JSON object containing either
     *              error messages or success data based on the processing outcome.
     */
    public function quick_data()
    {
        $respuesta = [
            "ok" => false,
            "error" => "error"
        ];

        $hash = $this->request->getPost( "hash" );

        if( strlen( $hash ) == 64 ){
            // endpoint GET de tronscan para verificar la transaccion
            // la URL se obtiene de la tabla t_variables
            // devuelve un JSON con la informacion de la transaccion del que tomaremos los datos más importantes

            $vars = ( VARIABLES[ "inversiones" ]["valor"] );

            $curl = curl_init();
            curl_setopt($curl, CURLOPT_URL, str_replace( "%hash%", $hash,  $vars[ "url" ] ) );
            curl_setopt($curl, CURLOPT_RETURNTRANSFER, true); 
            $d = json_decode( curl_exec( $curl ), true );
            curl_close($curl);

            if( sizeof( $d ) ){
                $tx  = [
                    "block"                 => $d[ "block" ], // 68750547,
                    "contractRet"           => $d[ "contractRet" ], // "SUCCESS",
                    "confirmed"             => $d[ "confirmed" ], // true,
                    "icon_url"              => $d[ "tokenTransferInfo" ][ "icon_url" ], // "https://static.tronscan.org/production/logo/usdtlogo.png",  
                    "symbol"                => $d[ "tokenTransferInfo" ][ "symbol" ], // "USDT",
                    "to_address"            => $d[ "tokenTransferInfo" ][ "to_address" ], // "TAr7YFFgxkRs2zEHGm34dcj8M4TqAv2eGP",
                    "name"                  => $d[ "tokenTransferInfo" ][ "name" ], // "Tether USD",
                    "decimals"              => $d[ "tokenTransferInfo" ][ "decimals" ], // 6,
                    "from_address"          => $d[ "tokenTransferInfo" ][ "from_address" ], // "TJy2LR9FFrP7ZQw99CRfHeiFCG2RZUasGF"
                    "amount_str"            => $d[ "tokenTransferInfo" ][ "amount_str" ], // "500000000",
                    "cost" => [
                        "date_created"          => date( "Y-m-d", $d[ "cost" ][ "date_created" ] ), // 1736902989,
                        "net_fee_cost"          => $d[ "cost" ][ "net_fee_cost" ], // 1000,
                        "fee"                   => $d[ "cost" ][ "fee" ], // 0,
                        "energy_fee_cost"       => $d[ "cost" ][ "energy_fee_cost" ], // 210,
                        "net_usage"             => $d[ "cost" ][ "net_usage" ], // 0,
                        "multi_sign_fee"        => $d[ "cost" ][ "multi_sign_fee" ], // 0,
                        "net_fee"               => $d[ "cost" ][ "net_fee" ], // 345000,
                        "energy_penalty_total"  => $d[ "cost" ][ "energy_penalty_total" ], // 49635,
                        "energy_usage"          => $d[ "cost" ][ "energy_usage" ], // 0,
                        "energy_fee"            => $d[ "cost" ][ "energy_fee" ], // 13499850,
                        "energy_usage_total"    => $d[ "cost" ][ "energy_usage_total" ], // 64285,
                        "memoFee"               => $d[ "cost" ][ "memoFee" ], // 0,
                        "origin_energy_usage"   => $d[ "cost" ][ "origin_energy_usage" ] // 0
                    ]
                ];

                $respuesta[ "data" ]    = json_encode( $tx );

                // validamos que sea una transacción confirmada

                if( $tx[ "contractRet" ] == "SUCCESS" && $tx[ "confirmed" ] == true ){
                    
                    // validamos que sea al wallet destino correcto

                    $address = false;
                    foreach( $vars[ "wallets" ] as $wallet ){
                        if( $tx[ "to_address" ] == $wallet[ "token"] && $wallet[ "estatus" ] == "201-ACTIVO" ){
                            $address = true;
                        }
                    }

                    // Si se encontró wallet

                    if( $address ){
                        
                        $inversion = model( "InversionModel" )->find( $this->request->getPost( "inversion" ) );
                        $pedido    = model( "PedidoModel" )->find( $inversion[ "pedido_id" ] );
                        $producto  = model( "ProductoModel" )->find( array_keys( $pedido[ "promociones"][ "510-SEMILLA" ][ "productos" ] ) )[ 0 ];

                        $fecha = $pedido[ "fechas" ][ "pagado" ];
                        $saldo = 0;

                        // nos aseguramos de que la transacción no haya sido registrada antes

                        $db  = db_connect();
                        $sql = "select count(*) as existe from t_fondeos where operacion = '{$hash}'";
                        $f_i = get_fecha_inversion( $pedido[ "fechas" ][ "pagado" ] );

                        if( !$db->query( $sql )->getrow()->existe ){
                            
                            // Al no existir antes, la registramos en la base de datos de fondeos
                            $cantidad = $tx[ "amount_str" ] / pow( 10, $tx[ "decimals" ] );
                            $total    = $pedido[ "data" ][ "total" ] - $saldo;

                            model( "FondeoModel" )->ignore( true )->save( [
                                "operacion"         => $hash, 
                                "fecha"             => $fecha,
                                "estatus_codigo"    => "420-PAGADO",
                                "metodopago_codigo" => $pedido[ "metodopago_codigo" ],
                                "usuario_id"        => $pedido[ "usuario_id" ],
                                "referencia"        => $pedido[ "referencia" ],
                                "cantidad"          => $cantidad,
                                "extras"            => $tx
                            ] );                           

                            $inversion[ "extras" ][ "TxHash" ] = $hash;
                            $inversion[ "extras" ][ "wallets" ][ "from" ] = $tx[ "from_address" ];
                            $inversion[ "extras" ][ "wallets" ][ "to" ] = $tx[ "to_address" ];
            
                            model( "InversionModel" )->save( $inversion );

                            $respuesta[ "error" ]   = false;
                            $respuesta[ "success" ] = [];
                        }
                        else{
                            $respuesta[ "error" ] = "<h5 class=\"mb-0 text-red text-center\">TxHash ya registrado</h5>Esta transacción ya ha sido registrada anteriormente en la base de datos";
                        }
                    }
                    else{
                        $respuesta[ "error" ] = "<h5 class=\"mb-0 text-red text-center\">Wallet destino incorrecta</h5>La transacción ingresada no tiene como destino alguna wallet de Beneleit";
                    }
                }
                else{
                    $respuesta[ "error" ] = "<h5 class=\"mb-0 text-red text-center\">TxHash no confirmado</h5>La transacción no ha sido confirmada en la blockchain";
                }
            }
            else{
                $respuesta[ "error" ] = "<h5 class=\"mb-0 text-red text-center\">TxHash no encontrado</h5>No existe información en la red para el hash ingresado";
            }
        }
        else{
            $respuesta[ "error" ] = "<h5 class=\"mb-0 text-red text-center\">Hash incorrecto</h5>Ingresaste ".strlen( $hash )." caracteres";
        } 

        echo json_encode( $respuesta );
    }


    /**
     * Crea una solicitud de retiro para una inversión.
     * 
     * Verifica que el usuario logueado sea el mismo que el usuario de la inversión.
     * Genera un registro en la tabla `retiros` con el estatus "255-PENDIENTE" y lo
     * relaciona con la inversión y el usuario.
     * Actualiza la cantidad de meses de la inversión en la tabla `inversiones`.
     * Registra una bitácora de la acción.
     * Redirecciona a la página de capital con un mensaje de éxito.
     *  
     * @return void
     */
    public function crea_retiro()
    {

        $i    = model( "InversionModel" )->find( $this->request->getPost( "inversion_id" ) );
        $p    = model( "ProductoModel" )->find( $i[ "producto_codigo" ] );
        $t    = $this->request->getPost( "tipo" );
        $tipo = intval( $this->request->getPost( "opciones_".( $t == "semilla" ? "semilla" : "retiro" ) ) );
        $bt   = balance_inversion( $i );

        $retiro = [
            $bt[ "rendimiento_mes_total"], 
            $i[ "extras" ][ "meses" ][ 24 ][ "Ym" ] < date( "Ym" ) ? $bt[ "total" ] : $bt[ "finmes" ], 
            floatval( $this->request->getPost( "custom" ) ), 
            $bt[ "semilla" ],
            floatval( $this->request->getPost( "custom" ) ), 
        ];

        if( $this->data[ "usuario" ]->id == $i[ "usuario_id" ] || $this->data[ "usuario" ]->permiso( "40-ADMIN" ) ){

            // generar retiro

            $descuento = 0;

            if( $t == "semilla" && aviso_semilla( $i, $p, $this->request->getPost( "mes_apply" ) ) ){
                $descuento = floor( 100 * ( $retiro[ $tipo -1 ] * 25 / 100 ) ) / 100;
            }

            $retiro_add = [
                "id" => NULL,
                "estatus_codigo" => "165-ESPERANDO-CODIGO",
                "usuario_id"     => $i[ "usuario_id" ], 
                "inversion_id"   => $i[ "id" ],
                "cantidad"       => $retiro[ $tipo -1 ],
                "deposito"       => $retiro[ $tipo -1 ] - $descuento,
                "tipo"           => $tipo,
                "fechas"         => [
                    "creacion"       => date( "Y-m-d" ),
                    "mes"            => $this->request->getPost( "mes_apply" ),
                    "deposito"       => null,
                    "descuento"      => $descuento
                ]
            ];

            $where  = "inversion_id = {$i[ "id" ]} and substring( estatus_codigo, 1, 3 ) > 200 and json_unquote( json_extract( fechas, '$.mes' ) ) = '{$this->request->getPost( "mes_apply" )}' and tipo in ( ".( $t == "semilla" ? "'STOTAL', 'SPARCIAL'" : "'TOTAL', 'PARCIAL', 'MENSUAL'" )." )";

            $existe = model( "RetiroModel" )->where( $where )->find();

            if( !$existe ){

                model( "RetiroModel" )->save( $retiro_add );
                $r = model( "RetiroModel" )->find( model( "RetiroModel" )->insertID() );

                $pedido = model( "PedidoModel" )->find( $i[ "pedido_id" ] );
        
                $ms = genera_meses( $pedido, $i[ "id" ], $p );
                $i[ "extras" ][ "meses" ] = $ms[ 0 ];
                $i[ "extras" ][ "semilla_retirada" ] = $ms[ 1 ];

                model( "InversionModel" )->save( $i );

                // BITACORA Crea solicitud de retiro
                bitacora( 86, $this->data[ "usuario" ]->id, [ 
                    "socio"     => $i[ "usuario_id" ],
                    "inversion" => $i[ "id" ],
                    "retiro"    => $r[ "id" ],
                    "mes"       => $this->request->getPost( "mes_apply" ),
                    "cantidad"  => $retiro[ $tipo -1 ],
                    "requested" => $this->request->getPost()
                ] );

                if( $this->request->getPost( "mes_apply" ) == date( "Ym" ) ){

                    // ENVIAR CORREO

                    $imagenes = [
                    ];

                    $u = model( "UsuarioModel" )->find( $i[ "usuario_id" ] );
                    $a = [ $u->password_original().$i[ "extras" ][ "TxHash" ], $r[ "id" ] ];
                    $url = base_url()."confirma_retiro/".urlencode( base64_encode( json_encode( $a ) ) );

                    $subject = "Solicitud de retiro ".strip_tags( id( $r[ "id" ], 5 ) );
                    $message = "
                        
                        <p>¡Hola ".$u->nombre()."! </p>
                        <p>Hemos recibido tu solicitud de retiro de tu paquete de producto. Por favor verifica que todos los datos sean correctos. Para confirmar la solicitud haz click en el botón o el enlace. Una vez confirmada se enviará para ser procesada durante <strong>los primeros tres días hábiles del próximo mes</strong>.</p>

                        <table align=\"center\" border=\"1\" cellpadding=\"10\" style=\"border-collapse:collapse\">
                            <tr><td>Socio</td><td>".id( $u->id, 7 )."</td></tr>
                            <tr><td>Inversión</td><td>".id( $i[ "id" ], 6 )."</td></tr>
                            <tr><td>TxHash</td><td>{$i[ "extras" ][ "TxHash" ]}</td></tr>
                            <tr><td>Fecha de solicitud</td><td>".fecha( $r[ "fechas" ][ "creacion" ] )."</td></tr>
                            <tr><td>Folio</td><td>".id( $r[ "id" ], 5 )."</td></tr>
                            <tr><td>Tipo de retiro</td><td>".( substr( $r[ "tipo" ], 0, 1 ) == "S" ?  substr( $r[ "tipo" ], 1 )." PAQUETE" : $r[ "tipo" ]." PRODUCTOS" )."</td></tr>
                            <tr><td>Cantidad solicitada</td><td>$".number_format( $r[ "cantidad" ], 2 )."</td></tr>
                            <tr><td>Penalización</td><td>".( substr( $r[ "tipo" ], 0, 1 ) == "S" ? "$".number_format( $r[ "cantidad" ] - $r[ "deposito" ], 2 )." (25%)" : "$0.00" )."</td></tr>
                            <tr><td>Cantidad a depositar</td><td>$".number_format( $r[ "deposito" ], 2 )."</td></tr>
                            <tr><td>Wallet TRON USDT destino</td><td>{$u->data->wallet}</td></tr>
                        </table>

                        <p style=\"word-wrap: break-word; text-align:center\">
                            <a href=\"{$url}\"><span style=\"background:#1a2542; text-align:center; padding:15px 0; width:400px; display:inline-block; color:#fff; border-radius:5px; font-size:30px;font-weight:bold\">Confirmar solicitud</span></a>
                            <br><br>
                            <p class=\"small text-center\"><a href=\"{$url}\">{$url}</a></p>
                        </p>
                
                    ";
                    if( ENVIRONMENT != 'development' ){
                        $respuesta = envia_correo( $u, $subject, $message, $imagenes );
                    }

                    // redirect para refresh

                    return redirect()->to( "paquete" )->with( "msg", [ 
                        "clase" => "success", 
                        "icono" => "check", 
                        "texto" => "Se generó solicitud de retiro" ] );   
                    }

                // si es por admin a meses anteriores
                else{
                    // BITACORA Confirma solicitud de retiro
                    bitacora( 100, $this->data[ "usuario" ]->id, [ 
                        "socio"  => $i[ "usuario_id" ],
                        "retiro" => $r[ "id" ]
                    ] );

                    $ms = genera_meses( $pedido, $i[ "id" ], $p );
                    $i[ 0 ][ "extras" ][ "meses" ] = $ms[ 0 ];
                    $i[ 0 ][ "extras" ][ "semilla_retirada" ] = $ms[ 1 ];

                    model( "InversionModel" )->save( $i );   

                    $r[ "estatus_codigo" ] = "255-PENDIENTE";
                    model( "RetiroModel" )->save( $r );

                    return redirect()->to( "paquete" )->with( "msg", [ 
                        "clase" => "success", 
                        "icono" => "check", 
                        "texto" => "Se procesó el retiro retroactivo" ] );                         
                }
            }
            else{
                return redirect()->to( "paquete" )->with( "msg", [ 
                    "clase" => "warning", 
                    "icono" => "warning", 
                    "texto" => "Ya existe un retiro programado, no es posible crear solicitud" ] );   
            }
        }
        else{
            return redirect()->to( "paquete" );
        }
    }


    /**
     * Cancela una solicitud de retiro, marcando como cancelada la solicitud y actualizando los meses de la inversión.
     * 
     * @return Response
     */
    public function cancela_retiro()
    {
        
        $retiro = model( "RetiroModel" )->find( $this->request->getPost( "solicitud_id" ) );

        if ($retiro && substr( $retiro[ "estatus_codigo" ], 0, 3 ) < 400 ) {

            $retiro["estatus_codigo"] = "150-CANCELADO";
            $retiro[ "fechas" ][ "cancelado" ] = date( "Y-m-d H:i:s" );

            model("RetiroModel")->save($retiro);

            // actualizar meses de inversión

            $i = model( "InversionModel" )->find( $retiro[ "inversion_id" ] );
            $pedido = model( "PedidoModel" )->find( $i[ "pedido_id" ] );

            $ms = genera_meses( $pedido, $i[ "id" ] );
            $i[ "extras" ][ "meses" ] = $ms[ 0 ];
            $i[ "extras" ][ "semilla_retirada" ] = $ms[ 1 ];

            model( "InversionModel" )->save( $i );
            
            // BITACORA Cancela retiro
            bitacora( 87, $this->data[ "usuario"]->id, [
                "retiro_id" => $retiro[ "id" ]
            ]);

            return redirect()->to( "paquete" )->with( "msg", [
                "clase" => "success",
                "icono" => "check",
                "texto" => "Se canceló la solicitud de retiro"
            ]);
        } else {
            return redirect()->to( "paquete" );
        }
    }


    /**
     * actual y los movimientos realizados en ella.
     *
     * Requiere el parámetro $hash, que es el hash de la transacción
     * correspondiente a la inversión.
     *
     * Verifica que el usuario logueado sea el mismo que el usuario de la
     * inversión, o que tenga permiso de ingreso, almacén, contabilidad o
     * administración.
     *
     * Redirecciona a la página de capital si no se cumple la condición
     * anterior.
     *
     * @param string $hash
     *
     * @return void
     */
    public function estadodecuenta( $hash )
    {
        $hash = base64_decode( urldecode( $hash ) );

        $where = "JSON_UNQUOTE( JSON_EXTRACT( t_inversiones.extras, '$.TxHash' ) ) = '{$hash}'";

        $i = model( "InversionModel" )->where( $where )->first();
        
        if( !$i ){
            return redirect()->to( "paquete" );
        }

        $p      = model( "ProductoModel" )->find( $i[ "producto_codigo" ] );
        $pedido = model( "PedidoModel" )->find( $i[ "pedido_id" ] );

        if( 1 || !isset($i[ "extras" ][ "meses" ] ) || ( $i[ "extras" ][ "v" ] ?? 0 ) != 2 ){

            $ms = genera_meses( $pedido, $i[ "id" ], $p );

            $i[ "extras" ][ "meses" ] = $ms[ 0 ];
            $i[ "extras" ][ "v" ] = 2;
            $i[ "extras" ][ "semilla_retirada" ] = $ms[ 1 ];
            $i[ "extras" ][ "refresh" ] = date( "Y-m-d" );

            model( "InversionModel" )->save( $i );
        }

        $this->data[ "i" ] = $i;

        if( $this->data[ "usuario" ]->id != intval( $this->data[ "i" ][ "usuario_id" ] ) && !(
            $this->data[ "usuario" ]->permiso( "28-INGRESA" ) ||
            $this->data[ "usuario" ]->permiso( "38-CONTABILIDAD" ) ||
            $this->data[ "usuario" ]->permiso( "40-ADMIN" )
        ) ){
            return template( "paquetes", $this->data );
        }

        $this->data[ "navbar" ] = true;
        $this->data[ "titulo" ] = "Detalle de tu paquete de inversión";

        echo template( "paquetes/detalle", $this->data );
    }


    /**
     *
     * Requiere el parámetro $inversion, que es el id de la inversión.
     *
     * Verifica que el usuario logueado tenga permiso de administración.
     *
     * Redirecciona a la página de no permiso si no se cumple la condición
     * anterior.
     *
     * @param string $inversion
     *
     * @return void
     */
    public function get_retiros()
    {
        if( !(
            $this->data[ "usuario" ]->permiso( "40-ADMIN")
        ) ){
            return redirect()->to( "no_permiso" ); 
        }
         
        extract( $this->request->getPost() );

        $html = "";
        $socio = model( "UsuarioModel" )->find( $socio );

        $db = db_connect();

        $sql = "SELECT 
                    r.id as folio,
                    u.id as socio,
                    p.referencia,
                    u.data->>'$.wallet' as wallet,
                    r.estatus_codigo as estatus,
                    r.cantidad,
                    r.deposito,
                    r.fechas->>'$.deposito' as pagado,
                    r.fechas->>'$.mes' as mes,
                    e.data->>'$.nombre' as producto,
                    e.data->>'$.color' as color
                    FROM t_retiros r
                LEFT JOIN t_inversiones i on r.inversion_id = i.id
                left join t_pedidos p ON i.pedido_id = p.id 
                left join t_usuarios u on u.id = r.usuario_id
                left join t_productos e on e.codigo = i.producto_codigo
                WHERE p.usuario_id = {$socio->id}
                    AND p.modelo_codigo = '50-INVERSION'
                    AND SUBSTRING( p.estatus_codigo, 1, 3) > 400
                    AND SUBSTRING( r.estatus_codigo, 1, 3) > 200    
                    AND p.PTS->>'$.\"510-SEMILLA\"' > 0
                    AND r.inversion_id = {$inversion}";

        $retiros = $db->query( $sql )->getResult();

        $html .= "\n<table class=\"w-100 m-0 table table-striped\" id=\"tabla_retiros\">
                    <thead><tr>
                        <th>Folio</th>
                        <th class=\"text-start\">Tipo</th>
                        <th>Wallet</th>
                        <th>Cantidad</th>
                        <th>Retiro</th>
                        <th>Estatus</th>
                        <th>&nbsp;</th>
                    </tr></thead><tbody>";

        foreach( $retiros as $k => $r ){

            // Si no hay fecha de retiro, extraerla del mes

            if( substr( $r->estatus, 0, 3 ) > 300 && strlen( $r->pagado) != 10 ){
                $ret = model( "RetiroModel" )->find( $r->folio );

                $nueva = date( "Y-m-d", strtotime( substr( $r->mes, 0, 4 )."-".substr( $r->mes, 5, 2 )."-01 + 1 month" ) );
                
                $ret[ "fechas" ][ "deposito" ] = $nueva;
                model( "RetiroModel" )->save( $ret );

                $r->pagado = $nueva;
            }

            if( $r->mes > $mes ){
                $r->estatus = "152-FUTURO";
            }
            $url = urlencode( base64_encode( $r->folio ) );
            $cantidad = $r->deposito > 0 ? $r->deposito : $r->cantidad;
            $html .= "\n<tr>
                        <td><span class=\"badge bg-gray-600\">{$inversion}-{$r->folio}</span></td>
                        <td class=\"text-start\"><span class=\"badge bg-{$r->color}\">{$r->producto}</span></td>
                        <td><a href=\"javascript:navigator.clipboard.writeText( '{$r->wallet}' );\"><i class=\"fa fa-wallet text-teal\"></i></a> {$r->wallet}</td>
                        <td class=\"text-end\"><span class=\"d-none\">{$cantidad}</span><strong>$".number_format( $cantidad,2 )."</strong> <button type=\"button\" class=\"btn btn-light btn-sm px-1 py-0\" onclick=\"navigator.clipboard.writeText( '{$cantidad}' )\"><i class=\"fa fa-copy\"></i></button></td>
                        <td nowrap><span class=\"d-none\">{$r->mes}</span>".strtoupper( mes( substr( $r->mes, 4, 2 ), 3 ) )." ".substr( $r->mes , 0, 4 )."</td>
                        <td>".estatus( $r->estatus )."</td>
            <td class=\"text-end\">".( $r->mes < date( "Ym" ) && substr( $r->estatus, 0, 3 ) < 300 && $this->data[ "usuario" ]->permiso( "40-ADMIN") ? "<a href=\"".base_url()."entrega_retiro/{$url}\" class=\"btn btn-sm btn-warning\"><i class=\"fa fa-check\"></i> Marcar como tranferida</a>" : "" )."</td>
                    </tr>";
        }

        $html .= "</tbody></table></form></div>"; 

        echo $html;        
    }


    /**
     * Marks a specific withdrawal as transferred.
     *
     * Validates if the current user has admin permissions before proceeding. 
     * Decodes the provided withdrawal identifier to find the corresponding 
     * record in the database. Updates the status of the withdrawal to "applied" 
     * and logs the action in the system's records. Finally, redirects to the 
     * capital page with a success message.
     *
     * @param string $retiro Encoded withdrawal identifier.
     * @return \CodeIgniter\HTTP\RedirectResponse Redirects to the capital page.
     */

    public function entrega_retiro( $retiro )
    {
        if( !(
            $this->data[ "usuario" ]->permiso( "40-ADMIN")
        ) ){
            return redirect()->to( "no_permiso" ); 
        }

        $db = db_connect();

        $r     = model( "RetiroModel" )->find( base64_decode( urldecode( $retiro ) ) );
        $socio = model( "UsuarioModel" )->find( $r[ "usuario_id" ] );

        $r[ "fechas" ][ "deposito" ] = date( "Y-m-d" );
        $r[ "estatus_codigo" ] = "421-APLICADO";
        model( "RetiroModel" )->save( $r );

        // BITACORA Marca recompensa entregada
        bitacora( 90, $this->data[ "usuario" ]->id, [ 
            "socio"    => $socio->id,
            "retiro"   => $r[ "id" ],
            "wallet"   => $socio->data->wallet,
            "cantidad" => $r[ "cantidad" ]
        ] );

        return redirect()->to( "paquetes/".$r[ "fechas" ][ "mes"] )->with( "msg", [ 
            "clase" => "success", 
            "icono" => "check", 
            "texto" => "El retiro se ha marcado como transferido" ] );   
    }


    /**
     * Marca como entregados todos los retiros del mes. 
     * @param string $mes Mes en formato "YYYYMM".
     * @return redirect a paquetes/$mes
     */
    public function entrega_retiros( $mes )
    {
        if( !(
            $this->data[ "usuario" ]->permiso( "40-ADMIN")
        ) ){
            return redirect()->to( "no_permiso" ); 
        }

        $retiros = model( "RetiroModel" )->where( "SUBSTRING( estatus_codigo,1,3) between 200 AND 300 AND JSON_EXTRACT( fechas, '$.mes' ) = '{$mes}' " )->findAll();

        $db = db_connect();

        foreach( $retiros as $r ){
            $socio = model( "UsuarioModel" )->find( $r[ "usuario_id" ] );

            $r[ "fechas" ][ "deposito" ] = date( "Y-m-d" );
            $r[ "estatus_codigo" ] = "421-APLICADO";

            model( "RetiroModel" )->save( $r );
    
            // BITACORA Marca recompensa entregada
            bitacora( 90, $this->data[ "usuario" ]->id, [ 
                "socio"    => $socio->id,
                "retiro"   => $r[ "id" ],
                "wallet"   => $socio->data->wallet,
                "cantidad" => $r[ "cantidad" ]
            ] );   
        }

        return redirect()->to( "paquetes/".$r[ "fechas" ][ "mes"] )->with( "msg", [ 
            "clase" => "success", 
            "icono" => "check", 
            "texto" => "Todos los retiros del mes han sido marcados como entregados" ] );   
    }    


    /**
     * Descarga un Excel con los retiros de un mes.
     * @param string $mes Mes en formato "YYYYMM".
     * @return void
     */
    public function excel_retiros()
    {
        if( !(
            $this->data[ "usuario" ]->permiso( "31-GASOLINA") ||
            $this->data[ "usuario" ]->permiso( "40-ADMIN" )
        ) ){
            return redirect()->to( "no_permiso" ); 
        }

        $mes = $this->request->getPost( "mes" );
        $db  = db_connect();

        $sql = "SELECT 
                u.id as socio, 
                r.estatus_codigo as estatus,
                CONCAT( i.pedido_id, '-', i.id) as inversion,
                u.data->>'$.wallet' as wallet,
                r.cantidad,
                r.deposito
            from t_retiros r
            join t_usuarios u on u.id = r.usuario_id
            join t_inversiones i on i.id = r.inversion_id
            where r.fechas->>'$.mes' = '{$mes}'
            and substring( r.estatus_codigo, 1, 3) > 200
            order by u.id asc";

        $retiros  = $db->query( $sql );
        $db       = db_connect();
        $data     = [];

        $mySpreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
        $mySpreadsheet->removeSheetByIndex(0);
        $worksheet = new \PhpOffice\PhpSpreadsheet\Worksheet\Worksheet($mySpreadsheet, "INGRESO MENSUAL");
        $mySpreadsheet->addSheet( $worksheet, 0 );

        $col = 0;
        $e = [];
        $worksheet->setCellValue( chr(65 + $col++)."1", "SOCIO" );
        $worksheet->setCellValue( chr(65 + $col++)."1", "NOMBRE" );
        $worksheet->setCellValue( chr(65 + $col++)."1", "TELEFONO" );
        $worksheet->setCellValue( chr(65 + $col++)."1", "INVERSION" );
        $worksheet->setCellValue( chr(65 + $col++)."1", "WALLET" ); 
        $worksheet->setCellValue( chr(65 + $col++)."1", "CANTIDAD" );
        $worksheet->setCellValue( chr(65 + $col++)."1", "ESTATUS" );

        $row = 1;

        foreach( $retiros->getResult() as $r ){
            $u = model( "UsuarioModel" )->find( $r->socio );

            $row++;
            $col  = 0;
            
            $worksheet->setCellValue( chr(65 + $col++).$row, $u->id );
            $worksheet->setCellValue( chr(65 + $col++).$row, $u->nombre( 2, false, true ) );
            $worksheet->setCellValue( chr(65 + $col++).$row, $u->telefono );
            $worksheet->setCellValue( chr(65 + $col++).$row, $r->inversion );
            $worksheet->setCellValue( chr(65 + $col++).$row, $u->data->wallet ?? "NO-WALLET" );
            $worksheet->setCellValue( chr(65 + $col++).$row, $r->deposito > 0 ? $r->deposito : $r->cantidad );
            $worksheet->setCellValue( chr(65 + $col++).$row, $r->estatus );
        }

        $col--;

        $worksheet->getStyle( "A1:".chr(65 + $col)."1" )->getFont()->getColor()->setARGB('ffffff');
        $worksheet->getStyle( "F2:F".$row )->getNumberFormat()->setFormatCode( "$#,##0.00" );
        $worksheet->getStyle( "A1:".chr(65 + $col)."1" )->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setARGB('192b5a');

        $col--;
        $worksheet->getStyle( chr(65 + $col)."1" )->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setARGB('009779');
        $worksheet->getStyle( chr(65 + $col)."2:".chr(65 + $col).$row )->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setARGB('c1ebd7');

        foreach( $worksheet->getColumnIterator() as $column ){
            $worksheet->getColumnDimension( $column->getColumnIndex() )->setAutoSize( true );
        }

        // BITACORA descarga excel de retiros
        bitacora( 91, $this->data[ "usuario" ]->id, [
            "mes" => $mes
        ] );

        $path = "data/excel/retiros";
        if( !is_dir( $path ) ) mkdir( $path, 0755, true );

        echo $file = $path."/Retiros_".strtoupper( mes( substr( $mes, 4, 2 ) ) )."-".substr( $mes, 0, 4 )."_".time().".xlsx";

        $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($mySpreadsheet);
        $writer->save( $file );
    }    


    /**
     * incluyendo el corte de socios directos activos y el volumen
     * de capital semilla de la red.
     *
     * @return void
     */
    public function rangos_paquetes()
    {
        $this->data[ "navbar" ] = true;
        $this->data[ "titulo" ] = "Bonos de liderazgo";

        echo template( "paquetes/rangos_paquetes", $this->data );
    }

    /**
     * @return void
     */
    public function excel_rangos()
    {
        if( !(
            $this->data[ "usuario" ]->permiso( "31-GASOLINA") ||
            $this->data[ "usuario" ]->permiso( "40-ADMIN" )
        ) ){
            return redirect()->to( "no_permiso" ); 
        }

        $m1 = $this->request->getPost( "mes" );
        $m2 = substr( $m1, 0, 4 )."-".substr( $m1, 4, 2 )."-01";
        $mesa = date( "Ym", strtotime( $m2 ) );
        $mesb = date( "Ym", strtotime( $m2."" ) ); //  + 1 month
        $db  = db_connect();

        $sql = "SELECT 
                    u.id,u.historial->>'$.modelos.\"50-INVERSION\".corte_mensual.\"{$mesb}\"' as cortes
                from t_usuarios u
                where 
                    u.data->>'$.estatus.modelos.\"50-INVERSION\"' = '520-CALIFICADO-ACTUAL'
                and u.historial->>'$.modelos.\"50-INVERSION\".corte_mensual.\"{$mesb}\".directos' > 3";

        $historial = $db->query( $sql )->getResultArray();

        $socios = [];
        
        foreach( $historial as $socio ){
            $corte = json_decode($socio[ "cortes" ], true );
            
            if( $corte[ "directos" ] > 3 ){

                if( $corte[ "directos" ] > 11 ){
                    $rango = "530-LEYENDA";
                }
                elseif( $corte[ "directos" ] > 7 ){
                    $rango = "520-CONQUISTADOR";
                }
                elseif( $corte[ "directos" ] > 3 ){
                    $rango = "510-PIONERO";
                }

                $socios[] = [ 
                    "id"    => $socio[ "id" ],
                    "rango" => $rango,
                    "mes"   => $corte
                ];
            }
        } 
        $data     = [];

        $mySpreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
        $mySpreadsheet->removeSheetByIndex(0);
        $worksheet = new \PhpOffice\PhpSpreadsheet\Worksheet\Worksheet($mySpreadsheet, "BONO LIDERAZGO");
        $mySpreadsheet->addSheet( $worksheet, 0 );

        $col = 0;
        $e = [];
        $worksheet->setCellValue( chr(65 + $col++)."1", "SOCIO" );
        $worksheet->setCellValue( chr(65 + $col++)."1", "NOMBRE" );
        $worksheet->setCellValue( chr(65 + $col++)."1", "TELEFONO" );
        $worksheet->setCellValue( chr(65 + $col++)."1", "WALLET" );
        $worksheet->setCellValue( chr(65 + $col++)."1", "DIRECTOS" );
        $worksheet->setCellValue( chr(65 + $col++)."1", "BOLSA" );
        $worksheet->setCellValue( chr(65 + $col++)."1", "BONO" );
        $worksheet->setCellValue( chr(65 + $col++)."1", "CANTIDAD" );


        $row = 1;

        foreach( $socios as $s ){
            $u = model( "UsuarioModel" )->find( $s[ "id" ] );

            $row++;
            $col  = 0;
            
            $cantidad = $s[ "mes" ][ "bolsa" ] * $s[ "mes" ][ "bono" ] / 100;

            $worksheet->setCellValue( chr(65 + $col++).$row, $u->id );
            $worksheet->setCellValue( chr(65 + $col++).$row, $u->nombre( 2, false, true ) );
            $worksheet->setCellValue( chr(65 + $col++).$row, $u->telefono );
            $worksheet->setCellValue( chr(65 + $col++).$row, $u->data->wallet ?? "NO WALLET" );
            $worksheet->setCellValue( chr(65 + $col++).$row, $s[ "mes" ][ "directos" ] );
            $worksheet->setCellValue( chr(65 + $col++).$row, $s[ "mes" ][ "bolsa" ] );
            $worksheet->setCellValue( chr(65 + $col++).$row, $s[ "mes" ][ "bono" ] );
            $worksheet->setCellValue( chr(65 + $col++).$row, $cantidad );
        }

        $col--;

        $worksheet->getStyle( "A1:".chr(65 + $col)."1" )->getFont()->getColor()->setARGB('ffffff');
        $worksheet->getStyle( "F2:F".$row )->getNumberFormat()->setFormatCode( "$#,##0.00" );
        $worksheet->getStyle( "H2:H".$row )->getNumberFormat()->setFormatCode( "$#,##0.00" );
        $worksheet->getStyle( "A1:".chr(65 + $col)."1" )->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setARGB('192b5a');

        
        $worksheet->getStyle( chr(65 + $col)."1" )->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setARGB('009779');
        $worksheet->getStyle( chr(65 + $col)."2:".chr(65 + $col).$row )->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setARGB('c1ebd7');

        foreach( $worksheet->getColumnIterator() as $column ){
            $worksheet->getColumnDimension( $column->getColumnIndex() )->setAutoSize( true );
        }

        // BITACORA descarga excel de retiros
        bitacora( 91, $this->data[ "usuario" ]->id, [
            "mes" => $mesa
        ] );

        $path = "data/excel/liderazgo";
        if( !is_dir( $path ) ) mkdir( $path, 0755, true );

        echo $file = $path."/BonoLiderazgo_".strtoupper( mes( substr( $mesa, 4, 2 ) ) )."-".substr( $mesa, 0, 4 )."_".time().".xlsx";

        $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($mySpreadsheet);
        $writer->save( $file );
    }


    /**
     * Genera el excel de bono de liderazgo
     * 
     * @return string
     */
    public function get_bono_liderazgo()
    {
        $mes   = $this->request->getPost( "mes" );
        $fecha = date( "Y-m-d", strtotime( substr( $mes, 0, 4 )."-".substr( $mes, 4, 2 )."-01 - 1 month" ) );
        $cant  = intval( $this->request->getPost( "cantidad" ) );

        $db  = db_connect();
        $sql = "call p_get_inversiones( {$this->data[ "usuario" ]->id}, {$mes} )";
                // ".date( "Ym", strtotime( $fecha ) )."

        $ps  = $db->query( $sql )->getResult();
        
        $directos = 0;
        $bolsa    = 0;

        $html  = "\n<table class=\"table table-striped w-100\"><thead><tr><th></th><th>Nombre</th><th>Nivel</th><th>Inversión</th></tr></thead><tbody>";

        foreach( $ps as $s ){
            
            if( 
                $s->nivel > 0 &&
                $s->semilla > 0 && 
                substr( $s->estatus, 0, 3 ) > 300 && 
                substr( $fecha, 0, 7 ) >= substr( $s->activacion, 0, 7 )
            ){
                if( $s->nivel == 1 ){
                    $directos++;
                }

                $data   = model( "UsuarioModel" )->find( $s->id );
                $bolsa += $s->semilla;
                $html  .= "\n<tr><td>".$data->id( "50-INVERSION" )."</td><td>".$data->avatar(24)." ".$data->nombre( 2 )."</td><td>{$s->nivel}</td><td class=\"text-end\">$".number_format( $s->semilla, 2 )."</td></tr>";
            }
        }

        $html .= "\n</tbody></table>";

        $html = "\n<div class=\"row\"><div class=\"col-lg-6 text-center\"><span class=\"badge fs-1 w-100 bg-mustard py-1\">".strtoupper( mes( substr( $fecha, 5, 2 ) ) )." ".substr( $fecha, 0, 4 )."</span></div><div class=\"col-lg-6 text-center\"><div class=\"card\"><h1 class=\"m-0\">$".number_format( $bolsa, 2 )."</h1></div></div></div>".$html;

        // validar cantidad

        if( $cant != $bolsa && $mes == date( "Ym" ) ){
            $this->data[ "usuario" ]->revisa_bono_liderazgo( $ps, substr( $mes, 0, 4 )."-".substr( $mes, 4, 2 )."-01");     
        }

        return $html;
    }


    public function reporte_inversiones()
    {
        if( !(
            $this->data[ "usuario" ]->permiso( "40-ADMIN" )
        ) ){
            return redirect()->to( "no_permiso" ); 
        }

        $this->data[ "navbar" ]    = true;
        $this->data[ "titulo" ]    = "Listado de Inversiones";
        load_catalogo( "productos", "modelo_codigo = '50-INVERSION' and substring( codigo, 1 ,3 ) > 500 and estatus_codigo = '201-ACTIVO'" );

        echo template( "paquetes/reporte_inversiones", $this->data );
    }

    

    public function excel_reporte_inversiones()
    {
        if( !(
            $this->data[ "usuario" ]->permiso( "40-ADMIN" )
        ) ){
            return redirect()->to( "no_permiso" ); 
        }

        extract( $this->request->getPost() );
        $db  = db_connect();

        $tipo = "";

        if( $d_tipoinversion  != "TODOS" ){
            $tipo = "AND r.data->>'$.porcentaje' = '{$d_tipoinversion}' ";
        }

        $sql = "SELECT 
            i.id as ID_INVERSION,
            u.id as SOCIO,
            concat_ws( ' ', u.data->>'$.nombre', u.data->>'$.apellidos[0]', u.data->>'$.apellidos[1]') as NOMBRE,
            u.data->>'$.ubicacion.origen' as PAIS,
            u.telefono as CELULAR,
            IFNULL( u.data->>'$.wallet', '--' ) as WALLET_COMISIONES,
            p.referencia as PEDIDO,
            i.cantidad as USDT,
            r.data->>'$.porcentaje' as PORCENTAJE,
            cast( i.fechas->>'$.pagado' as date ) as FECHA_PAGO,
            cast( i.fechas->>'$.inversion' as date ) as INICIO_RENDIMIENTOS,
            i.extras->>'$.TxHash' as TXHASH_TRON,
            i.extras->>'$.wallets.from' as TXHASH_ORIGEN,
            i.extras->>'$.wallets.to' as TXHASH_DESTINO,
            p.data->>'$.primercompra' as PRIMERA_INVERSION

        from t_inversiones i
        join t_pedidos p on p.id = i.pedido_id
        join t_usuarios u on u.id = i.usuario_id
        join t_productos r on r.codigo = i.producto_codigo

        where cast( i.fechas->>'$.pagado' as date ) between '{$f_inicio}' and '{$f_final}'
        and substring( p.estatus_codigo,1,3) > 400
        {$tipo}
        and substring( i.estatus_codigo,1,3) > 600";

        $inversiones  = $db->query( $sql );
        $db   = db_connect();
        $data = [];

        $mySpreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
        $mySpreadsheet->removeSheetByIndex(0);
        $worksheet = new \PhpOffice\PhpSpreadsheet\Worksheet\Worksheet($mySpreadsheet, "INVERSIONES");
        $mySpreadsheet->addSheet( $worksheet, 0 );

        $col = 0;
        $e = [];
        $worksheet->setCellValue( chr(65 + $col++)."1", "ID_INVERSION" );
        $worksheet->setCellValue( chr(65 + $col++)."1", "SOCIO" );
        $worksheet->setCellValue( chr(65 + $col++)."1", "NOMBRE" );
        $worksheet->setCellValue( chr(65 + $col++)."1", "PAIS" );
        $worksheet->setCellValue( chr(65 + $col++)."1", "CELULAR" );
        $worksheet->setCellValue( chr(65 + $col++)."1", "WALLET_COMISIONES" ); 
        $worksheet->setCellValue( chr(65 + $col++)."1", "PEDIDO" );
        $worksheet->setCellValue( chr(65 + $col++)."1", "USDT" );
        $worksheet->setCellValue( chr(65 + $col++)."1", "PORCENTAJE" );
        $worksheet->setCellValue( chr(65 + $col++)."1", "FECHA_PAGO" );
        $worksheet->setCellValue( chr(65 + $col++)."1", "INICIO_RENDIMIENTOS" );
        $worksheet->setCellValue( chr(65 + $col++)."1", "TXHASH_TRON" );
        $worksheet->setCellValue( chr(65 + $col++)."1", "TXHASH_ORIGEN" );
        $worksheet->setCellValue( chr(65 + $col++)."1", "TXHASH_DESTINO" );
        $worksheet->setCellValue( chr(65 + $col++)."1", "PRIMERA_INVERSION" );

        $row = 1;

        foreach( $inversiones->getResult() as $r ){

            $row++;
            $col  = 0;
          

            $worksheet->setCellValue( chr(65 + $col++).$row, $r->ID_INVERSION );
            $worksheet->setCellValue( chr(65 + $col++).$row, $r->SOCIO );
            $worksheet->setCellValue( chr(65 + $col++).$row, $r->NOMBRE );
            $worksheet->setCellValue( chr(65 + $col++).$row, $r->PAIS );
            $worksheet->setCellValue( chr(65 + $col++).$row, $r->CELULAR );
            $worksheet->setCellValue( chr(65 + $col++).$row, $r->WALLET_COMISIONES );
            $worksheet->setCellValue( chr(65 + $col++).$row, $r->PEDIDO );
            $worksheet->setCellValue( chr(65 + $col++).$row, $r->USDT );
            $worksheet->setCellValue( chr(65 + $col++).$row, $r->PORCENTAJE );
            $worksheet->setCellValue( chr(65 + $col++).$row, $r->FECHA_PAGO );
            $worksheet->setCellValue( chr(65 + $col++).$row, $r->INICIO_RENDIMIENTOS );
            $worksheet->setCellValue( chr(65 + $col++).$row, $r->TXHASH_TRON );
            $worksheet->setCellValue( chr(65 + $col++).$row, $r->TXHASH_ORIGEN );
            $worksheet->setCellValue( chr(65 + $col++).$row, $r->TXHASH_DESTINO );
            $worksheet->setCellValue( chr(65 + $col++).$row, $r->PRIMERA_INVERSION );
        }

        $col--;

        $worksheet->getStyle( "A1:".chr(65 + $col)."1" )->getFont()->getColor()->setARGB('ffffff');
        $worksheet->getStyle( "H2:H".$row )->getNumberFormat()->setFormatCode( "$#,##0.00" );
        $worksheet->getStyle( "A1:".chr(65 + $col)."1" )->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setARGB('192b5a');

        $col--;
        $worksheet->getStyle( "H1" )->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setARGB('009779');
        $worksheet->getStyle( "H2:H".$row )->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setARGB('c1ebd7');

        foreach( $worksheet->getColumnIterator() as $column ){
            $worksheet->getColumnDimension( $column->getColumnIndex() )->setAutoSize( true );
        }

        $path = "data/excel/inversiones";
        if( !is_dir( $path ) ) mkdir( $path, 0755, true );

        echo $file = $path."/Inversiones_del_{$f_inicio}_al_{$f_final}_".time().".xlsx";

        $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($mySpreadsheet);
        $writer->save( $file );
    }    
}

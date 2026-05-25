<?php

namespace App\Controllers;

class Almacenes extends BaseController 
{
    /**
     * Displays the list of warehouses and delivery points for a given model.
     *
     * Checks if the user has the necessary permissions to view stock, warehouse, 
     * or admin information. If not, redirects to a "no permission" page.
     *
     * Sets up navbar visibility and title for the page. Queries the database 
     * to retrieve warehouse information such as code, status, name, settings,
     * number of orders, and transfer counts. Filters the warehouses based on 
     * the model code and user permissions. Passes the retrieved data to the 
     * "almacenes/listado" template for rendering.
     *
     * @param string $modelo The code of the warehouse model.
     *
     * @return void
     */
    public function listado( $modelo )
    {
 
        if( !(
            $this->data[ "usuario" ]->permiso( "18-STOCK" )   ||
            $this->data[ "usuario" ]->permiso( "20-ALMACEN" ) ||
            $this->data[ "usuario" ]->permiso( "40-ADMIN" )
        ) ){
            return redirect()->to( "no_permiso" ); 
        }
 
        $this->data[ "navbar" ] = true;
        $this->data[ "titulo" ] = "Almacenes y puntos de entrega";
        $this->data[ "modelo" ] = $modelo;

        $db = db_connect();
        $sql = "SELECT a.codigo, a.estatus_codigo, a.nombre, a.settings, COUNT(p.id) AS pedidos, (
                    SELECT COUNT(DISTINCT t.fecha) FROM t_transferencias t where t.destino = a.codigo AND estatus_codigo = '530-ENVIADO'
                ) AS transferencias
                FROM t_almacenes a 
                LEFT JOIN t_pedidos p ON p.data->>'$.entrega' = a.codigo and SUBSTRING( p.estatus_codigo, 1, 3 ) between 400 and 600 AND (json_extract(p.data, '$.salida') = 0 OR json_extract(p.data, '$.salida') IS NULL)
                WHERE a.modelo_codigo = '{$modelo}'
                
                ".( $this->data[ "usuario" ]->permiso( "18-STOCK", true ) ? "AND json_contains( a.settings->>'$.staff', '{$this->data[ "usuario" ]->id}' )" : "" )."
                GROUP BY a.codigo";

        $this->data[ "almacenes" ] = $db->query( $sql )->getResultArray();

        echo template( "almacenes/listado", $this->data );
    } 
    

    public function entregas( $modelo )
    {
 
        if( !(
            $this->data[ "usuario" ]->permiso( "18-STOCK" )   ||
            $this->data[ "usuario" ]->permiso( "20-ALMACEN" ) ||
            $this->data[ "usuario" ]->permiso( "40-ADMIN" )
        ) ){
            return redirect()->to( "no_permiso" ); 
        }
 
        $this->data[ "navbar" ] = true;
        $this->data[ "titulo" ] = "Almacenes y puntos de entrega";
        $this->data[ "modelo" ] = $modelo;

        $db = db_connect();
        $sql = "SELECT a.codigo, a.estatus_codigo, a.nombre, a.settings, COUNT(p.id) AS pedidos, (
                    SELECT COUNT(DISTINCT t.fecha) FROM t_transferencias t where t.destino = a.codigo AND estatus_codigo = '530-ENVIADO'
                ) AS transferencias
                FROM t_almacenes a 
                LEFT JOIN t_pedidos p ON p.data->>'$.entrega' = a.codigo and SUBSTRING( p.estatus_codigo, 1, 3 ) between 400 and 600
                WHERE a.modelo_codigo = '{$modelo}'

                and ( json_extract( p.data, '$.salida' ) = 0 or json_extract( p.data, '$.salida' ) is null )
                
                ".( $this->data[ "usuario" ]->permiso( "18-STOCK", true ) ? "AND json_contains( a.settings->>'$.staff', '{$this->data[ "usuario" ]->id}' )" : "" )."
                GROUP BY a.codigo";

        $this->data[ "almacenes" ] = $db->query( $sql )->getResultArray();

        echo template( "almacenes/entregas", $this->data );
    } 
    
    


    /**
     * Displays detailed information about a specific warehouse.
     *
     * Checks if the user has the necessary permissions (stock, warehouse, or admin)
     * to view warehouse details. If not, redirects to a "no permission" page.
     *
     * Retrieves and prepares data for rendering warehouse details, including
     * the warehouse information and a list of orders linked to the warehouse.
     * Sets up navbar visibility and customizes the page title.
     *
     * @param string $almacen The code of the warehouse to display details for.
     *
     * @return void
     */
    public function detalle( $almacen )
    {

        if( !(
            $this->data[ "usuario" ]->permiso( "18-STOCK" )   ||
            $this->data[ "usuario" ]->permiso( "20-ALMACEN" ) ||
            $this->data[ "usuario" ]->permiso( "40-ADMIN" )
        ) ){
            return redirect()->to( "no_permiso" ); 
        }


        $this->data[ "navbar"  ] = true;
        $this->data[ "socio"   ] = $this->data[ "usuario" ];
        $this->data[ "almacen" ] = model( "AlmacenModel" )->find( $almacen );

        if( $this->data[ "usuario" ]->permiso( "18-STOCK", true ) && !in_array( $this->data[ "usuario" ]->id, $this->data[ "almacen" ][ "settings" ][ "staff" ] ) ){
            return redirect()->to( "no_permiso" ); 
        }


        $this->data[ "titulo"  ] = "Administración de almacen <span class=\"badge bg-teal\">".$this->data[ "almacen"  ][ "nombre" ]."</span> <span class=\"badge bg-marine\">".( MODELOS[ $this->data[ "almacen"  ][ "modelo_codigo" ] ][ "nombre" ])."</span>";

        load_catalogo( "productos", "substring(estatus_codigo,1,3) >= 140 AND modelo_codigo = '{$this->data[ "almacen" ][ "modelo_codigo" ]}'");

        $db = db_connect();
        $sql = "SELECT p.*, u.data AS socio from t_pedidos p
            LEFT JOIN t_usuarios u ON u.id = p.usuario_id
            WHERE p.data->>'$.entrega' = '{$almacen}' 
            and ( json_extract( p.data, '$.salida' ) = 0 or json_extract( p.data, '$.salida' ) is null )
            AND SUBSTRING( p.estatus_codigo, 1, 3 ) between 400 and 600";

        $this->data[ "pedidos" ] = $db->query( $sql )->getResultArray();

        echo template( "almacenes/detalle", $this->data );
    }
    

    /**
     * Muestra el listado de productos de un pedido en el almacen y permite entregarlos
     *
     * @return void
     */
    public function entrega()
    {
        if( !(
            $this->data[ "usuario" ]->permiso( "18-STOCK" ) ||
            $this->data[ "usuario" ]->permiso( "20-ALMACEN" ) ||
            $this->data[ "usuario" ]->permiso( "40-ADMIN" )
        ) ){
            return redirect()->to( "no_permiso" ); 
        }
 
        $productos = [];

        $this->data[ "navbar"  ] = true;
        $this->data[ "socio"   ] = $this->data[ "usuario" ];
        $this->data[ "titulo"  ] = "Entrega de productos en almacen";
        $this->data[ "pedido"  ] = model( "PedidoModel" )->find( $this->request->getPost( "pedido" ) );
        $this->data[ "almacen" ] = model( "AlmacenModel" )->find( $this->data[ "pedido" ][ "data" ][ "entrega" ] );
        $this->data[ "cliente" ] = model( "UsuarioModel" )->find( $this->data[ "pedido"  ][ "usuario_id" ] );

        if( $this->data[ "usuario" ]->permiso( "18-STOCK", true ) && !in_array( $this->data[ "usuario" ]->id, $this->data[ "almacen" ][ "settings" ][ "staff" ] ) ){
            return redirect()->to( "no_permiso" ); 
        }

        $this->data[ "pedido" ][ "productos" ] = [];
        foreach( $this->data[ "pedido" ][ "promociones" ] as $promo ){
            if( isset( $promo[ "productos" ] ) )
            foreach( $promo[ "productos" ] as $codigo => $producto ){
                $productos[] = $codigo;
                $this->data[ "pedido" ][ "productos" ][ $codigo ] = $producto[ "cantidad" ] + ( $this->data[ "pedido" ][ "productos" ][ $codigo ] ?? 0 );
            }
        }

        $sql = "codigo in ('".implode( "','", $productos )."')";
        // load_catalogo( "productos", $sql );
        $productos = model( "ProductoModel" )->where( $sql , null, false )->findAll();

        $this->data[ "productos" ] = [];
        foreach( $productos as $p ){
            $this->data[ "productos" ][ $p->codigo ] = $p;
        } 

        echo template( "almacenes/entrega", $this->data );
    }


    /**
     * Marca un pedido como entregado
     *
     * @return redirect a la vista de almacen con mensaje de confirmacion
     */
    public function marca_entregado()
    {
        if( !(
            $this->data[ "usuario" ]->permiso( "18-STOCK" ) ||
            $this->data[ "usuario" ]->permiso( "20-ALMACEN" ) ||
            $this->data[ "usuario" ]->permiso( "40-ADMIN" )
        ) ){
            return redirect()->to( "no_permiso" ); 
        }
         
        // aqui se marca como entregado el pedido

        extract( $this->request->getPost() );
        $pedido   = model( "PedidoModel"  )->find( $pedido );
        $entrega  = model( "UsuarioModel" )->find( $entrega );
        $almacen  = model( "AlmacenModel" )->find( $pedido[ "data" ][ "entrega" ] );

        if( $this->data[ "usuario" ]->permiso( "18-STOCK", true ) && !in_array( $this->data[ "usuario" ]->id, $almacen[ "settings" ][ "staff" ] ) ){
            return redirect()->to( "no_permiso" ); 
        }

        $path     = "assets/img/evidencias/";
        $filename = $pedido[ "id" ]."_".time().".jpg";
        $tmpName  = $_FILES[ "evidencia" ][ "tmp_name" ];
        move_uploaded_file( $tmpName, $path.$filename );

        $pedido[ "estatus_codigo" ] = "622-ENTREGADO";
        $pedido[ "fechas" ][ "entregado" ] = date( "Y-m-d H:i:s" );
        model( "PedidoModel" )->save( $pedido );

        if( $tarjeta == "1" ){
            $u = model( "UsuarioModel" )->find( $pedido[ "usuario_id" ] );

            $data = $u->data;
            $data->tarjeta->estatus = "623-ENTREGA";
            $u->data = $data;

            model( "UsuarioModel" )->save( $u );

            // BITACORA Marca recompensa entregada
            bitacora( 77, $this->data[ "usuario" ]->id, [ 
                "socio"   => $u->id,
                "pedido"  => $pedido[ "id" ]
            ] );      
        }

        foreach( $pedido[ "promociones" ] as $promo ){
            foreach( $promo[ "productos" ] as $c => $p ){
                $almacen[ "productos" ][ $c ] = ( $almacen[ "productos" ][ $c ] ?? 0 ) - $p[ "cantidad" ];
            }
        }
        model( "AlmacenModel" )->save( $almacen );

        // BITACORA Entrega pedido en almacen
        bitacora( 27, $entrega->id, [ 
            "pedido"  => $pedido,
            "recibe"  => $recibe,
            "celular" => $celular,
            "usuario" => $this->data[ "usuario" ]->id
        ] );

        return redirect()->to( "almacen/".$almacen[ "codigo" ] )->with( "msg", [ 
            "clase" => "success", 
            "icono" => "check", 
            "texto" => "El pedido {$pedido[ "referencia" ]} fue marcado como entregado"] );        
    }


    /**
     * Retrieves and outputs the inventory balance for a specified warehouse.
     *
     * This function fetches the inventory balance of a warehouse using the 
     * 'almacen' identifier provided in the POST request. It retrieves the 
     * warehouse data from the AlmacenModel and outputs the inventory balance 
     * in JSON format.
     *
     * @return void
     */
    public function get_inventario()
    {
        $almacen = model( "AlmacenModel" )->find( $this->request->getPost( "almacen" ) );
        echo json_encode( $almacen[ "inventario" ][ "balance" ] );
    }



    /**
     * Retrieves and displays detailed product data for a specific warehouse.
     *
     * This function checks if the current user has the necessary permissions 
     * to access stock, warehouse, or admin information. If the user lacks 
     * permissions, they are redirected to a "no permission" page.
     *
     * The function extracts posted data, connects to the database, and queries 
     * transfer information for a specific product in a warehouse. It generates 
     * an HTML report displaying received, sent, and in-transit transfers, 
     * as well as product sales and inventory status.
     *
     * The HTML report includes sections for received transfers, sent transfers, 
     * in-transit transfers, products sold, pending deliveries, products available 
     * for sale, and physical inventory.
     *
     * @return void
     */
    public function get_data_producto()
    {
        if( !(
            $this->data[ "usuario" ]->permiso( "18-STOCK" ) ||
            $this->data[ "usuario" ]->permiso( "20-ALMACEN" ) ||
            $this->data[ "usuario" ]->permiso( "40-ADMIN" )
        ) ){
            return redirect()->to( "no_permiso" ); 
        }
         
        extract( $this->request->getPost() );

        $html = "";
        $almacen = model( "AlmacenModel" )->find( $almacen );

        $db = db_connect();

        $sql = "SELECT id, notas, fecha, cantidad from t_transferencias where estatus_codigo = '620-RECIBIDO' and producto_codigo = '{$producto}' and destino = '{$almacen[ "codigo" ]}'";
        $transfers = $db->query( $sql );



        $html .= "<div class=\"card mb-3\" style=\"overflow:hidden\"><div class=\"card-header bg-marine\"><span class=\"text-white m-0\"><i class=\"fa fa-right-to-bracket\"></i> Tranferencias recibidas</span></div><table class=\"w-100 m-0 table table-striped\">";

        if( $transfers->getNumRows() ){
            foreach( $transfers->getResult() as $t ){
                $html .= "<tr><td nowrap>".date( "d-m-Y", strtotime( $t->fecha ) )."</td><td class=\"w-100\"><strong>{$t->notas}</strong></td><td class=\"text-end pe-3\" nowrap>".number_format( $t->cantidad )."</td></tr>";
            }
        }
        else{
            $html .= "<tr><td colspan=\"3\" class=\"text-end pe-3 text-gray-600\">0</td></tr>";
        }
        $html .= "</table></div>";

        $html .= "<div class=\"card mb-3\" style=\"overflow:hidden\"><div class=\"card-header bg-marine\"><div class=\"row\"><div class=\"text-white col-8\"><i class=\"fa fa-right-from-bracket\"></i> Tranferencias enviadas</div><div class=\"text-end text-white col-4\">".number_format($almacen[ "inventario" ][ "transfers_origen"][ $producto ] ?? 0 )."</div></div></div></div>";

        $sql = "SELECT id, notas, fecha, cantidad from t_transferencias where estatus_codigo = '530-ENVIADO' and producto_codigo = '{$producto}' and destino = '{$almacen[ "codigo" ]}'";
        $transfers = $db->query( $sql );

        $html .= "<div class=\"card mb-3\" style=\"overflow:hidden\"><div class=\"card-header bg-mustard\"><span class=\"text-white m-0\"><i class=\"fa fa-truck-arrow-right\"></i> Tranferencias por recibir (en tránsito)</span></div><form method=\"post\" action=\"".base_url( "recibe_transfer" )."\"><table class=\"w-100 m-0 table table-striped\">";
        $html .= csrf_field(); 

        if( $transfers->getNumRows() ){
            foreach( $transfers->getResult() as $t ){
                $html .= "<tr><td nowrap>".date( "d-m-Y", strtotime( $t->fecha ) )."</td><td class=\"w-100\"><strong>{$t->notas}</strong></td><td><button type=\"submit\" class=\"btn btn-sm btn-warning\" name=\"recibe\" value=\"{$t->id}\">RECIBE</button></td><td class=\"text-end pe-3\" nowrap>".number_format( $t->cantidad )."</td></tr>";
            }
        }
        else{
            $html .= "<tr><td colspan=\"3\" class=\"text-end pe-3 text-gray-600\">0</td></tr>";
        }       
        $html .= "</table></form></div>"; 
        
        $html .= "<div class=\"card mb-3\" style=\"overflow:hidden\"><div class=\"card-header bg-teal\"><div class=\"row\"><div class=\"text-white col-8\"><i class=\"fa fa-shopping-cart\"></i> Productos vendidos</div><div class=\"text-end text-white col-4\">".number_format( ( $almacen[ "inventario" ][ "transfers_destino"][ "620" ][ $producto ] ?? 0 ) - ( $almacen[ "inventario" ][ "transfers_origen"][ $producto ] ?? 0 ) - ( $almacen[ "inventario" ][ "balance" ][ $producto ] ?? 0 ) )."</div></div></div><table class=\"w-100 m-0 table table-striped\">";

        $html .= "<tr><td nowrap class=\"w-100\">Productos entregados</td><td class=\"text-end pe-3\" nowrap>".number_format( $almacen[ "inventario" ][ "venta"][ "622" ][ $producto ] ?? 0 )."</td></tr>";
        $html .= "<tr><td nowrap class=\"w-100\">Productos pendientes de entrega</td><td class=\"text-end pe-3\" nowrap>".number_format( $almacen[ "inventario" ][ "venta"][ "420" ][ $producto ] ?? 0 )."</td></tr>";
        $html .= "</table></div>";

        $html .= "<div class=\"card mb-3\" style=\"overflow:hidden\"><div class=\"card-header bg-marine\"><div class=\"row\"><div class=\"text-white col-8\"><i class=\"fa fa-cash-register\"></i> Productos disponibles para venta</div><div class=\"text-end text-white col-4\">".number_format( $almacen[ "inventario" ][ "balance" ][ $producto ] ?? 0 )."</div></div></div></div>";        

        $html .= "<div class=\"card mb-0\" style=\"overflow:hidden\"><div class=\"card-header bg-red\"><div class=\"row\"><div class=\"text-white col-8\"><i class=\"fa fa-boxes-stacked\"></i> Inventario físico</div><div class=\"text-end text-white col-4\">".number_format( ( $almacen[ "inventario" ][ "venta"][ "420" ][ $producto ] ?? 0 ) + ( $almacen[ "inventario" ][ "balance" ][ $producto ] ?? 0 ) )."</div></div></div></div>";

        echo $html;
    }



    /**
     * Agrega productos a stock de almacenes
     *
     * El usuario debe tener permiso de ALMACEN o ADMIN
     *
     * @param string $almacen Codigo del almacen
     * @param string $producto Codigo del producto
     * @param int $cantidad Cantidad a agregar
     *
     * @return void
     */
    public function addstock()
    {
        if( !(
            $this->data[ "usuario" ]->permiso( "20-ALMACEN" ) ||
            $this->data[ "usuario" ]->permiso( "40-ADMIN" )
        ) ){
            return redirect()->to( "no_permiso" ); 
        }
         
        // agregar productos a stock de almacenes

        extract( $this->request->getPost() );
        $almacen  = model( "AlmacenModel" )->find( $almacen );
        $producto = model( "ProductoModel" )->find( $producto );

        $almacen[ "productos" ][ $producto->codigo ] = ( $almacen[ "productos" ][ $producto->codigo ] ?? 0 ) + $cantidad;
        model( "AlmacenModel" )->save( $almacen );

        // BITACORA Entrega pedido en almacen
        bitacora( 28, $this->data[ "usuario" ]->id, [ 
            "almacen"  => $almacen[ "codigo" ],
            "producto" => $producto->codigo,
            "cantidad" => $cantidad
        ] );

        return redirect()->to( "almacen/".$almacen[ "codigo" ] )->with( "msg", [ 
            "clase" => "success", 
            "icono" => "check", 
            "texto" => "El producto se ha agregado al almacen"] );  
    }


    /**
     * Vista de transferencias entre almacenes
     *
     * @param string $modelo Código del modelo de almacén
     *
     * @return void
     */
    public function transferencias( $modelo )
    {
        if( !(
            $this->data[ "usuario" ]->permiso( "20-ALMACEN" ) ||
            $this->data[ "usuario" ]->permiso( "40-ADMIN" )
        ) ){
            return redirect()->to( "no_permiso" ); 
        }
         
        $this->data[ "navbar"  ] = true;
        $this->data[ "titulo"  ] = "Transferencias entre almacenes";
        $this->data[ "modelo"  ] = $modelo;

        load_catalogo( "productos", "substring(estatus_codigo,1,3) >= 140 AND modelo_codigo = '{$modelo}'");
        load_catalogo( "almacenes", "estatus_codigo = '201-ACTIVO' AND modelo_codigo = '{$modelo}'");

        echo template( "almacenes/transferencias", $this->data );
    }


    /**
     * Marks a product transfer as received.
     * 
     * This function checks if the current user has the necessary permissions 
     * to mark a transfer as received. If permissions are valid, it updates 
     * the transfer status to "620-RECIBIDO" and assigns the current user as 
     * the receiver. It also logs this action in the bitacora.
     * 
     * Redirects to the destination warehouse with a success message upon completion.
     */
    public function recibe_transfer()
    {

        if( !(
            $this->data[ "usuario" ]->permiso( "18-STOCK" )   ||
            $this->data[ "usuario" ]->permiso( "20-ALMACEN" ) ||
            $this->data[ "usuario" ]->permiso( "40-ADMIN" )
        ) ){
            return redirect()->to( "no_permiso" ); 
        }

        $transfer = model( "TransferenciaModel" )->find( $this->request->getPost( "recibe" ) );
        $transfer[ "estatus_codigo" ] = "620-RECIBIDO";
        $transfer[ "recibe" ] = $this->data[ "usuario" ]->id;
        model( "TransferenciaModel" )->save( $transfer );

        // BITACORA recibe transferencia de productos
        bitacora( 53, $this->data[ "usuario" ]->id, [ 
            "id"    => $transfer[ "id" ]
        ] );

        return redirect()->to( "almacen/".$transfer[ "destino" ] )->with( "msg", [ 
            "clase" => "success", 
            "icono" => "check", 
            "texto" => "Los productos se han marcado como recibidos"] );  
    } 


    /**
     * Handles the transfer of products between warehouses.
     *
     * This function first checks if the current user has the necessary permissions 
     * to perform a transfer. If permissions are valid, it processes the transfer 
     * by extracting data from the POST request, validates the origin and destination 
     * warehouses, and iterates over the list of products to be transferred. Each 
     * product with a quantity greater than zero is inserted into the `t_transferencias` 
     * table with an 'ENVIADO' status. A log entry is also created in the bitacora 
     * for tracking the transfer.
     *
     * @return \CodeIgniter\HTTP\RedirectResponse Redirects to the warehouse page 
     * indicating that the products have been marked as sent.
     */
    public function aplica_transfer()
    {
        if( !(
            $this->data[ "usuario" ]->permiso( "20-ALMACEN" ) ||
            $this->data[ "usuario" ]->permiso( "40-ADMIN" )
        ) ){
            return redirect()->to( "no_permiso" );  
        }
          
        $db = db_connect();
        extract( $this->request->getPost() );

        if( $origen == $destino ){
            $origen = null;
        }

        foreach( $productos as $k => $p ){
            if ( intval( $p ) == 0 ){
                unset( $productos[ $k ] );
            }
            else{
                $sql = "INSERT into t_transferencias values(
                    NULL,
                    '530-ENVIADO',
                    '{$k}',
                    ".intval( $p ).",
                    ".( $origen ? "'{$origen}'" : "NULL" ).",
                    '{$destino}',
                    '{$fecha}',
                    {$this->data[ "usuario" ]->id},
                    NULL,
                    '{$notas}'
                )";
        
                $db->query( $sql );
            }
        }

        // BITACORA Envía transferencia de productos
        bitacora( 52, $this->data[ "usuario" ]->id, [ 
            "origen"    => $origen,
            "destino"   => $destino,
            "fecha"     => $fecha,
            "notas"     => $notas, 
            "productos" => $productos
        ] );

        return redirect()->to( "almacenes/".$modelo )->with( "msg", [ 
            "clase" => "success", 
            "icono" => "check", 
            "texto" => "Los productos se han marcado como enviados"] );  
    }

}

<style>
    body{ zoom: 60%; margin-right:50% }
    
@page {
    size: letter;
    margin: 12mm 12mm 12mm 12mm;
    
}
</style>
<pre style="overflow:inherit"><div class="row"><div class="col-6"><h4 class="mt-1">Pedido # <span style="border:1px solid black" class="badge bg-white text-black"><?php echo $pedido[ "referencia" ]; ?></span></h4></div><div class="col-6 text-end">
    <img src="<?php echo base_url(); ?>assets/img/logo_color.png" style="width:80px;"></div></div>
<div class="card mb-3" style="overflow:hidden"><table class="table rounded-3 m-0">

        <tr><td valign="middle" class="">Socio</td><td valign="middle"><h5 class="m-0"><?php echo $socio->nombre( 2 )." <span class=\"badge bg-black text-white\">".$socio->id( false )."</span>"; ?></h5></td></tr>   
    
        <tr><td valign="middle" class="">Fecha de pago</td><td valign="middle"><h5 class="m-0"><?php echo substr( $pedido[ "fechas" ][ "pagado" ], 0, 10 ); ?></h5></td></tr>           
        
        <tr><td valign="middle">Calificación</td><td valign="middle"><h5 class="m-0"><?php echo strtoupper( mes(substr( $pedido[ "fechas" ][ "califica" ], 5, 2 ) ) )." ".substr( $pedido[ "fechas" ][ "califica" ], 0, 4 ); ?></h5></td></tr></table></div><?php
        $total_prods  = 0;
        $total_precio = 0;

        foreach( PROMOCIONES as $p ){
            if( isset( $pedido[ "promociones" ][ $p[ "codigo" ] ][ "productos"] ) ){
                $cant_productos = sizeof( $pedido[ "promociones" ][ $p[ "codigo" ] ][ "productos"] );

                if( $cant_productos ){
                    echo "<div class=\"card\"><div class=\"card-header bg-gray-300\"><div class=\"row\"><div class=\"col-md-4\"><h5 class=\"m-0\">{$p[ "settings" ][ "nombre" ]}</h5></div><div class=\"col-md-8\">{$cant_productos} productos</div></div></div><table productos class=\"w-100\">";

                    foreach( $pedido[ "promociones" ][ $p[ "codigo" ] ][ "productos"] as $k ){

                        echo "<tr><td class=\"w-100\"><div class=\"row\"><div class=\"col-1\"><h5>{$k[ "cantidad" ]}</h5></div><div class=\"col-md-9\"><h5 class=\"m-0\">{$k[ "nombre" ]}</h5><p class=\"small mb-3\">{$k[ "descripcion" ]}</p></div></div></td><td valign=\"top\" class=\"text-end\" nowrap><small>P. unitario</small><h5>$".number_format( $k[ "precio" ], 2 )."</h5></td><td valign=\"top\" class=\"text-end\" nowrap><small>Subtotal</small><h5 subtotal>$".number_format( $k[ "precio" ] * $k[ "cantidad" ], 2 )."</h5></td></tr>";
                    }
                    
                    echo "\n</table><div class=\"card-footer text-end\"><table align=\"right\"><tr><td>{$p[ "settings" ][ "nombre" ]} &nbsp; </td><td><h5 class=\"m-0\" style=\"width:120px\">$".number_format( $subtotal = ( isset( $pedido[ "promociones" ][ $p[ "codigo" ] ][ "precio"] ) ? $pedido[ "promociones" ][ $p[ "codigo" ] ][ "precio"] : 0 ), 2 )."</h5></td></tr>
                    </table></div></div>";

                    $total_precio += $subtotal;
                    $total_prods  += $cant_productos;
                }
            }
        }

        echo "\n<div class=\"card\" style=\"overflow:hidden\"><div class=\"card-footer text-end bg-black text-white\"><table class=\"w-100\"><tr><td style=\"width:47%\">{$total_prods} productos</td><td>Sub total de productos &nbsp; </td><td style=\"width:120px\"><h5 class=\"m-0 text-white\" style=\"width:120px\">$".number_format( $total_precio, 2 )."</h5></td></tr></table></div></div>";

        $me = METODOSENTREGA[ $pedido[ "metodoentrega_codigo" ] ];
        $mp = METODOSPAGO[ $pedido[ "metodopago_codigo" ] ];

        if( substr( $pedido[ "metodoentrega_codigo" ], 0, 2 ) == "00" ){
            $entrega = ALMACENES[ $pedido[ "data" ][ "entrega" ] ];
        }
        else{
            $domicilios = $socio->getDomicilios();
            $entrega = $domicilios[ $pedido[ "data" ][ "entrega" ] ];

            /* echo "\n<div domicilio_id=\"{$d[ "id" ]}\" class=\"card border-teal text-teal text-start mb-3 p-2\"><p><strong>{$d[ "nombre" ]}</strong></p>
            {$d[ "calleynumero" ]}<br>
            Colonia {$d[ "colonia" ]}<br>
            {$d[ "localidad" ]}, {$d[ "entidad" ]}<br>
            C.P. {$d[ "codigopostal" ]}
            </div>"; */
        }
    
    ?>

<div class="card mb-3">
    <div class="card-header bg-gray-300"><h5 class="m-0">Método de entrega</h5></div>
    <div class="card-footer"><table class="w-100"><tr><td><?php echo $me[ "nombre" ]; ?></td><td><?php echo $entrega[ "nombre" ]; ?></td><td class="text-end" style="width:120px"><h5 class="m-0">$<?php echo number_format( $pedido[ "data" ][ "comisionentrega" ], 2 ); ?></h5></td></tr></table></div>
</div><div class="card mb-3">
    <div class="card-header bg-gray-300"><h5 class="m-0">Método de pago</h5></div>
    <div class="card-footer"><table class="w-100"><tr><td><?php echo $mp[ "nombre" ]; ?></td><td class="text-end">Comision</td><td class="text-end" style="width:120px"><h5 class="m-0">$<?php echo number_format( $pedido[ "data" ][ "comisionbanco" ], 2 ); ?></h5></td></tr></table></div>
</div><div class="card mb-3">
    <div class="card-footer"><table class="w-100"><tr><td>Saldo a favor</td><td></td><td class="text-end" style="width:120px"><h5 class="m-0">-$<?php echo number_format( $pedido[ "data" ][ "saldo" ] ?? 0, 2 ); ?></h5></td></tr></table></div>
</div>
<div class="card mb-3" style="overflow:hidden">
    <table class="table rounded-3 m-0">
        <tr><td valign="middle" class="text-white text-end" style="background:var(--bs-black) !important">Total de pedido</td><td valign="middle" class="text-end" style="width:110px; background:var(--bs-black) !important"><h5 class="text-white my-0">$<?php echo number_format( $pedido[ "data" ][ "total" ] + $pedido[ "data" ][ "comisionentrega" ] - ( $pedido[ "data" ][ "saldo" ] ?? 0 ) + $pedido[ "data" ][ "comisionbanco" ], 2 ); ?></h5></td></tr>
    </table>
</div></pre>    
<script>
        $( 'body' ).css( 'background', 'white');

        window.onload = function() { window.print(); }
</script>
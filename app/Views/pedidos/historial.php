<link href="<?php echo base_url(); ?>assets/css/datatables.css" rel="stylesheet"/>
<script src="<?php echo base_url(); ?>assets/js/datatables.js" type="text/javascript"></script>
<script src="<?php echo base_url(); ?>assets/js/datatables_bs5.js" type="text/javascript"></script>

<h4 class="mt-1 mb-0"><?php echo $titulo; ?></h4>

<div class="row">
    
        <?php 
        if( $modelo == '20-TELEFONIA' ){
            echo "<div class=\"col-lg-6\">".pills( "historial", $modelo )."</div><div class=\"col-lg-3\"><a class=\"btn btn-lg mt-4 col-12 btn-success\" href=\"".base_url( "beneleit_movil" )."\"><i class=\"fa fa-shopping-cart\"></i> Paquetes y activaciones</a></div><div class=\"col-lg-3\"><a class=\"btn btn-lg mt-4 col-12 btn-secondary\" href=\"".base_url( "tienda/".$modelo )."\"><i class=\"fa fa-sim-card\"></i> Comprar SIM cards</a></div>";
        }else{
            echo "<div class=\"col-lg-8\">".pills( "historial", $modelo )."</div><div class=\"col-lg-4\"><a class=\"btn btn-lg mt-4 col-12 btn-secondary\" href=\"".base_url( "tienda/".$modelo )."\"><i class=\"fa fa-shopping-cart\"></i> Nuevos pedidos</a></div>";
        }
        ?>
</div>

<table class="table table-striped bg-white" id="tabla_pedidos">
    <thead>
        <tr>
            <th>Referencia</th>
            <th>Estatus</th>
            <th>promociones</th>
            <th>productos</th>
            <th>Precio</th>
            <th>Fecha pago</th>
            <th>Califica</th>
            <th>pago</th>
            <th>entrega</th>
            <th></th>
        </tr>
    </thead>

    <tbody>
        <?php 
            foreach( $pedidos as $p ){
                $PTS = "";

                foreach( $p[ "PTS" ] as $tipo => $cantidad ){
                    if( $cantidad ){
                        $PTS .= "<span class=\"badge bg-".(PROMOCIONES[ $tipo ][ "settings" ][ "clase" ])."\">".(PROMOCIONES[ $tipo ][ "settings" ][ "siglas" ])."</span> ";
                    }
                }

                echo "\n<tr pedido=\"{$p[ "id" ]}\">
                    <td class=\"text-center\"><span class=\"badge bg-marine\">{$p[ "referencia" ]}</span></td>
                    <td>".estatus( $p[ "estatus_codigo" ] )."</td>
                    <td>{$PTS} &nbsp; ";
                    
                for( $b = 0; $b < $p[ "estrellas" ]; $b++ ){ 
                    echo "<i class=\"fa fa-star text-amber\"></i>";
                }
             
                echo "</td>
                    <td class=\"text-center\">".( isset( $p[ "promociones" ][ "310-TELEFONIA" ] )  ?  "<span class=\"badge bg-light-blue\">".substr( array_keys( $p[ "promociones" ][ "310-TELEFONIA" ][ "productos" ] ?? [""] )[ 0 ] , 4 )."</span>" : $p[ "data" ][ "productos" ] )."</td>
                    <td class=\"text-end\">".( intval( substr( $p[ "estatus_codigo" ], 0, 3 ) ) < 255 ? "<span class=\"badge bg-gray-300 text-red\">Pendiente</span>" : "$".number_format( $p[ "data" ][ "total" ] + $p[ "data" ][ "comisionbanco" ] + $p[ "data" ][ "comisionentrega" ], 2 ) )."</td>
                    <td class=\"text-center\">".( intval( substr( $p[ "estatus_codigo" ], 0, 3 ) ) > 400 ? "<span class=\"d-none\">".substr( $p[ "fechas" ][ "pagado" ], 0, 10 )."</span> ".date( "d-m-Y", strtotime( substr( $p[ "fechas" ][ "pagado" ], 0, 10 ) ) ) : "<span class=\"badge bg-gray-300 text-red\">Pendiente</span>" )."</td>
                    
                    <td class=\"text-center\">".( isset( $p[ "fechas" ][ "califica" ] ) ? "<span class=\"d-none\">{$p[ "fechas" ][ "califica" ]}</span><span class=\"badge bg-".( $p[ "data" ][ "mesanterior" ] ? "red" : "indigo" )."\">".date( "m-Y", strtotime( $p[ "fechas" ][ "califica" ] ) )."</span>" : "<span class=\"d-none\">".date( "Y-m-d H:i:s" )."</span>" )."</td>

                    <td>".( intval( substr( $p[ "estatus_codigo" ], 0, 3 ) ) > 250 && $p[ "metodopago_codigo" ] ? METODOSPAGO[ $p[ "metodopago_codigo" ] ][ "nombre" ] : "<span clasS=\"badge bg-gray-300 text-red\">Pendiente</span>" )."</td>

                    <td nowrap>".( intval( substr( $p[ "estatus_codigo" ], 0, 3 ) ) > 250 ? ( $p[ "metodoentrega_codigo" ] ? METODOSENTREGA[ $p[ "metodoentrega_codigo" ] ][ "nombre" ] : ( isset( $p[ "promociones" ][ "310-TELEFONIA" ] ) ? "<i class=\"fa fa-circle-info text-marine\"></i> No aplica" : "<i class=\"fa fa-warning text-red\"></i> Sin detalles" ) ) : "<span clasS=\"badge bg-gray-300 text-red\">Pendiente</span>" ).( isset( $p[ "promociones" ][ "310-TELEFONIA" ] ) ? " <strong>".( $p[ "data" ][ "entrega" ] ?? "<span class=\"text-red\"><i class=\"fa fa-warning\"></i> dato pendiente</span>" )."</strong>" : "" )."</td>

                    <td class=\"text-end\"><a href=\"".base_url( "pedido/".$p[ "referencia" ] )."\" class=\"btn btn-xs btn-primary\">DETALLES</a></td>
                </tr>";
            }
        ?>
     
    </tbody>
</table>

<script>
    var modelo = '<?php echo $modelo ?>', 
        socio = <?php echo $socio->id ?>;
</script>

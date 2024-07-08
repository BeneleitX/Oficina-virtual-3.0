<link href="<?php echo base_url(); ?>assets/css/datatables.css" rel="stylesheet"/>
<script src="<?php echo base_url(); ?>assets/js/datatables.js" type="text/javascript"></script>
<script src="<?php echo base_url(); ?>assets/js/datatables_bs5.js" type="text/javascript"></script>

<h4 class="mt-1 mb-0"><?php echo $titulo; ?></h4>

<div class="row">
    <div class="col-lg-8">
        <?php echo pills( "historial", $modelo ); ?>
    </div>
    <div class="col-lg-4">
        <a class="btn btn-lg mt-4 col-12 btn-secondary" href="<?php echo base_url( "tienda/".$modelo ); ?>"><i class="fa fa-shopping-cart"></i> Nuevos pedidos</a>
    </div>
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
                    <td class=\"text-center\">{$p[ "data" ][ "productos" ]}</td>
                    <td class=\"text-end\">".( intval( substr( $p[ "estatus_codigo" ], 0, 3 ) ) < 400 ? "<span clasS=\"badge bg-gray-300 text-red\">Pendiente</span>" : "$".number_format( $p[ "data" ][ "total" ] + $p[ "data" ][ "comisionbanco" ] + $p[ "data" ][ "comisionentrega" ], 2 ) )."</td>
                    <td class=\"text-center\">".( intval( substr( $p[ "estatus_codigo" ], 0, 3 ) ) > 400 ? substr( $p[ "fechas" ][ "pagado" ], 0, 10) : "<span class=\"badge bg-gray-300 text-red\">Pendiente</span>" )."</td>
                    <td class=\"text-center\">".( isset( $p[ "fechas" ][ "califica" ] ) ? "<span class=\"badge bg-".( $p[ "data" ][ "mesanterior" ] ? "red" : "marine" )."\">".substr( $p[ "fechas" ][ "califica" ], 0, 7 )."</span>" : "" )."</td>
                    <td>".( intval( substr( $p[ "estatus_codigo" ], 0, 3 ) ) > 400 ? METODOSPAGO[ $p[ "metodopago_codigo" ] ][ "nombre" ] : "<span clasS=\"badge bg-gray-300 text-red\">Pendiente</span>" )."</td>

                    <td class=\"text-end\"><a href=\"".base_url( "pedido/".$p[ "referencia" ] )."\" class=\"btn btn-xs btn-primary\">DETALLES</a></td>
                </tr>";
            }
        ?>
     
    </tbody>
</table>

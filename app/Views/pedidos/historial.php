<link href="<?php echo base_url(); ?>assets/css/datatables.css" rel="stylesheet"/>
<script src="<?php echo base_url(); ?>assets/js/datatables.js" type="text/javascript"></script>
<script src="<?php echo base_url(); ?>assets/js/datatables_bs5.js" type="text/javascript"></script>

<h4 class="mt-1 mb-0"><?php echo $titulo; ?></h4>

<div class="row">
    
        <?php 
        if( $modelo == '20-TELEFONIA' ){
            echo "<div class=\"col-lg-6\">".pills( "historial", $modelo )."</div><div class=\"col-lg-3\"><a class=\"btn btn-lg mt-4 col-12 btn-success\" href=\"".base_url( "beneleit_movil" )."\"><i class=\"fa fa-shopping-cart\"></i> Paquetes y activaciones</a></div><div class=\"col-lg-3\"><a class=\"btn btn-lg mt-4 col-12 btn-secondary\" href=\"".base_url( "tienda/".$modelo )."\"><i class=\"fa fa-sim-card\"></i> SIM cards y Promocionales</a></div>";
        }elseif( $modelo == '40-GASOLINAS'  ){
            echo "<div class=\"col-lg-6\">".pills( "historial", $modelo )."</div><div class=\"col-lg-3\">".( $usuario->data->tarjeta->numero ?? null ? "<div class=\"alert alert-info text-center py-2 mt-4 h4\">{$usuario->data->tarjeta->numero}</div>" : "<button class=\"btn btn-lg mt-4 col-12 btn-info2\" onclick=\"$( '#activa_tarjeta' ).modal( 'show' )\"><i class=\"fa fa-credit-card\"></i> Activar tarjeta</button>" )."</div><div class=\"col-lg-3\"><a class=\"btn btn-lg mt-4 col-12 btn-secondary\" href=\"".base_url( "tienda/".$modelo )."\"><i class=\"fa fa-credit-card\"></i> &nbsp;Nuevos pedidos</a></div>";
        }elseif( $modelo == '50-INVERSION' ){
            echo "<div class=\"col-lg-8\">".pills( "historial", $modelo )."</div><div class=\"col-lg-4\"><a class=\"btn btn-lg mt-4 col-12 btn-secondary\" href=\"".base_url( "tienda/".$modelo )."\"><i class=\"fa fa-hand-holding-dollar\"></i> Nueva inversión</a></div>";
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
            <th><?php echo $modelo == "50-INVERSION"  ? "Paquete" : "Precio"; ?></th>
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
                        if( $tipo == "010-DISTRIBUIDOR" ){
                            $PTS .= "<span class=\"badge bg-".(PROMOCIONES[ $tipo ][ "settings" ][ "clase" ])."\" style=\"border-radius: 0.375rem 0 0 0.375rem;  padding-right: 0.2rem\">".(PROMOCIONES[ $tipo ][ "settings" ][ "siglas" ])."</span><span style=\"border-radius: 0 0.375rem 0.375rem 0; padding: 0.13rem 0.35rem 0.13rem 0.2rem\" class=\"badge bg-white border border-teal text-teal\">{$cantidad}</span> ";
                        }
                        else{
                            $PTS .= "<span class=\"badge bg-".(PROMOCIONES[ $tipo ][ "settings" ][ "clase" ])."\">".(PROMOCIONES[ $tipo ][ "settings" ][ "siglas" ])."</span> ";
                        }
                    }
                }

                if( !isset( $p[ "data" ][ "primercompra" ] ) ){
                    $p[ "data" ][ "primercompra" ] = get_primercompra( $p[ "usuario_id" ], $p[ "modelo_codigo" ] );
                }
                echo "\n<tr pedido=\"{$p[ "id" ]}\">
                    <td class=\"text-center\">".referencia( $p ).( $p[ "data" ][ "primercompra" ] == 1 ? " <span class=\"badge bg-white small text-purple border border-purple\">1°</span>" : "" )."</td>
                    <td>".estatus( $p[ "estatus_codigo" ] )."</td>
                    <td>{$PTS} &nbsp; ";
                    
                for( $b = 0; $b < $p[ "estrellas" ]; $b++ ){ 
                    echo "<i class=\"fa fa-star text-amber\"></i>";
                }
             
                echo "</td>
                    <td class=\"text-center\">";
                    
                    if( isset( $p[ "promociones" ][ "310-TELEFONIA" ] ) ){
                        echo "<span class=\"badge bg-light-blue\">".substr( array_keys( $p[ "promociones" ][ "310-TELEFONIA" ][ "productos" ] ?? [""] )[ 0 ] ?? "", 4 )."</span>";
                    }
                    elseif( isset( $p[ "promociones" ][ "510-SEMILLA" ] ) ){
                        echo "<span class=\"badge bg-light-green\">".substr( array_keys( $p[ "promociones" ][ "510-SEMILLA" ][ "productos" ] ?? [""] )[ 0 ] ?? "" , 4 )."</span>";
                    }
                    else{
                        echo $p[ "data" ][ "productos" ];
                    }

                    echo "</td><td class=\"text-end\">".( $p[ "data" ][ "sat" ][ "factura" ] ?? null ? " <small class=\"\"><span style=\"vertical-align: text-top;\" class=\"badge bg-".( $p[ "data" ][ "sat" ][ "factura" ] == "146-FACTURA-OK" ? ( $p[ "data" ][ "cfd" ] ?? null ? "teal" : "red" ) : "mustard" )."\">".( $p[ "data" ][ "sat" ][ "factura" ] == "146-FACTURA-OK" ? $p[ "data" ][ "cfd" ] ?? "CFD ERROR" : "<i class=\"fa fa-file-invoice-dollar\"></i>" )."</span></small> " : "" ).( intval( substr( $p[ "estatus_codigo" ], 0, 3 ) ) < 255 ? "<span class=\"badge bg-gray-300 text-red\">Pendiente</span>" : "$".number_format( $p[ "data" ][ "total" ] + $p[ "data" ][ "comisionbanco" ] + $p[ "data" ][ "comisionentrega" ], 2 ) )."</td>
                    
                    <td class=\"text-center\">".( intval( substr( $p[ "estatus_codigo" ], 0, 3 ) ) > 400 ? "<span class=\"d-none\">".substr( $p[ "fechas" ][ "pagado" ], 0, 10 )."</span> ".date( "d-m-Y", strtotime( substr( $p[ "fechas" ][ "pagado" ], 0, 10 ) ) ) : "<span class=\"badge bg-gray-300 text-red\">Pendiente</span>" )."</td>
                    
                    <td class=\"text-center\">".( isset( $p[ "fechas" ][ "califica" ] ) ? "<span class=\"d-none\">{$p[ "fechas" ][ "califica" ]}</span><span class=\"badge bg-".( $p[ "data" ][ "mesanterior" ] ? "red" : "indigo" )."\">".date( "m-Y", strtotime( $p[ "fechas" ][ "califica" ] ) )."</span>" : "<span class=\"d-none\">".date( "Y-m-d H:i:s" )."</span>" )."</td>

                    <td>".( intval( substr( $p[ "estatus_codigo" ], 0, 3 ) ) > 250 && $p[ "metodopago_codigo" ] ? METODOSPAGO[ $p[ "metodopago_codigo" ] ][ "nombre" ] : "<span clasS=\"badge bg-gray-300 text-red\">Pendiente</span>" )."</td>

                    <td nowrap>".( $p[ "data" ][ "enviogratis" ] == 1 ? "<span class=\"badge bg-white border border-teal text-teal\">GRATIS</span> " : "" )."".( intval( substr( $p[ "estatus_codigo" ], 0, 3 ) ) > 250 ? ( $p[ "metodoentrega_codigo" ] ? METODOSENTREGA[ $p[ "metodoentrega_codigo" ] ][ "nombre" ] : ( isset( $p[ "promociones" ][ "310-TELEFONIA" ] ) ? "<i class=\"fa fa-circle-info text-marine\"></i> No aplica" : "<i class=\"fa fa-warning text-red\"></i> Sin detalles" ) ) : "<span clasS=\"badge bg-gray-300 text-red\">Pendiente</span>" ).( isset( $p[ "promociones" ][ "310-TELEFONIA" ] ) ? " <strong>".( $p[ "data" ][ "entrega" ] ?? "<span class=\"text-red\"><i class=\"fa fa-warning\"></i> dato pendiente</span>" )."</strong>" : "" )."</td>

                    <td class=\"text-end\"><a href=\"".base_url( "pedido/".$p[ "referencia" ] )."\" class=\"btn btn-xs btn-primary\">DETALLES</a></td>
                </tr>";
            }
        ?>
     
    </tbody>
</table>


<div class="modal" tabindex="-1" id="activa_tarjeta">
	<div class="modal-dialog modal-lg">
		<div class="modal-content">
            <div class="modal-header bg-blue">
                <h5 class="modal-title text-white"><i class="fa fa-credit-card"></i> Activar tarjeta</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-lg-4 text-center">
                        <img src="<?php echo base_url(); ?>assets/img/productos/915-TARJETA.png" class="img-fluid px-3">
                    </div>
                    <div class="col-lg-8 pt-4">
                        <p class="mb-1">Escriba los 16 dígitos de la tarjeta</p>
                        <div class="row"><div class="col-lg-6"><input type="text" class="form-control mb-3" name="v_tarjeta1"></input></div></div>

                        <p class="mb-1">Repita con cuidado los 16 dígitos de la tarjeta</p>
                        <div class="row"><div class="col-lg-6"><input type="text" class="form-control" name="v_tarjeta2" disabled></input></div></div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button class="btn btn-light" id="submit_tarjeta" disabled><i class="fa fa-check"></i> Activar</button>
            </div>
		</div>
	</div>
</div>


<script>
    var modelo = '<?php echo $modelo ?>', 
        socio = <?php echo $socio->id ?>;
</script>

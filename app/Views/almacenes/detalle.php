<link href="<?php echo base_url(); ?>assets/css/datatables.css" rel="stylesheet"/>
<script src="<?php echo base_url(); ?>assets/js/datatables.js" type="text/javascript"></script>
<script src="<?php echo base_url(); ?>assets/js/datatables_bs5.js" type="text/javascript"></script>

<h4 class="mt-1 mb-0"><?php echo $titulo; ?></h4>
<p><a href="<?php echo base_url( "almacenes/".$almacen[ "modelo_codigo" ] ); ?>"><i class="fa fa-undo"></i> Regresar a almacenes</a></p>


        <div class="row mb-4" id="inventario">
            <?php  

                foreach($almacen[ "inventario" ][ "balance" ] as $k => $c ){

                    $p = PRODUCTOS[ $k ];
                    $avatar = file_exists( "assets/img/productos/{$p[ "codigo" ]}.png" );

                    if($c > 50 ){
                        $class = "teal";
                    }
                    elseif($c > 0 ){
                        $class = "mustard";
                    }
                    else{
                        $class = "red";
                    }

                    echo "
                        <div class=\"col-6 col-lg-3 mb-2 detalle_producto\" producto=\"{$p[ "codigo" ]}\" style=\"cursor:pointer\">
                            <div class=\"card border-{$class}\" style=\"overflow:hidden\">
                                <div class=\"card-body p-0\">
                                    <table class=\"w-100 m-0 p-0\"><tr class=\"text-{$class}\">
                                        <td class=\"\"><img class=\"m-1\" style=\"width:30px !important\" src=\"".base_url()."assets/img/productos/".( $avatar ? $p[ "codigo" ] : "NO-IMAGEN" ).".png\"></td>
                                        <td width=\"100%\">".mb_strtoupper( $p[ "data" ][ "nombre" ] )."</td>
                                        <td class=\"pe-2 pb-1\">".( ( $tf = $almacen[ "inventario" ][ "transfers_destino" ][ "530" ][ $k ] ?? 0 ) > 0 ? "<span class=\"badge bg-marine\"><i class=\"fa fa-truck-arrow-right\"></i> {$tf}</span>" : "" )."</td>
                                        <td nowrap class=\"pe-3 text-end\"><strong class=\"fs-5\">".number_format( $c )."</strong></td>
                                    </tr></table>
                                </div>
                            </div>
                        </div>";
                }
            ?>
        </div>

<form action="<?php echo base_url( "entrega" ); ?>" method="post">
    <?php echo csrf_field(); ?>

    <table class="table table-striped bg-white" id="tabla_pedidos">
        <thead>
            <tr>
                <th>Pedido</th>
                <th>Socio</th>
                <th>Estatus</th>
                <th>Productos</th>
                <th>Fecha de pago</th>
                <th></th>
            </tr>
        </thead>

        <tbody>
            <?php 
                foreach( $pedidos as $p ){
                    $p[ "PTS"         ] = json_decode( $p[ "PTS"  ], true );
                    $p[ "data"        ] = json_decode( $p[ "data" ], true );
                    $p[ "promociones" ] = json_decode( $p[ "promociones"  ], true );
                    $p[ "fechas"      ] = json_decode( $p[ "fechas" ], true );

                    //$p[ "socio" ] = new \App\Entities\E_usuario( $p[ "usuario_id" ], $p[ "socio" ] );
                    $p[ "socio" ] = model( "UsuarioModel" )->find( $p[ "usuario_id" ] );

                    echo "\n<tr almacen=\"{$p[ "id" ]}\">
                        <td><span class=\"badge bg-marine\">{$p[ "referencia" ]}</span></td>
                        <td>".$p[ "socio" ]->avatar(24)." ".$p[ "socio" ]->id( $p[ "modelo_codigo" ] )." ".$p[ "socio" ]->nombre(2)."</td>
                        <td>".estatus( "330-EN-ESPERA" )."</td>

                        <td class=\"text-center\">{$p[ "data" ][ "productos" ]}</td>

                        <td class=\"text-center\"><span class=\"d-none\">{$p[ "fechas" ][ "pagado" ]}</span> ".date( "d-m-Y", strtotime( substr( $p[ "fechas" ][ "pagado" ], 0, 10) ) )."</td>

                        <td class=\"text-end\">
                            <a href=\"".base_url( "pedido/".$p[ "referencia" ] )."\" class=\"btn btn-xs btn-secondary\">VER PEDIDO</a>
                            <button type=\"submit\" name=\"pedido\" value=\"{$p[ "id" ]}\" class=\"btn btn-xs btn-primary\">ENTREGAR</button>
                        </td>
                    </tr>"; 
                }
            ?>
        
        </tbody>
    </table>
</form>

<div class="modal" tabindex="-1" id="detalle_producto">
	<div class="modal-dialog">
		<div class="modal-content">
            <div class="modal-header bg-teal">
                <h5 class="modal-title text-white"></h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body"></div>
		</div>
	</div>
</div>



<div class="modal" tabindex="-1" id="stock_modal">
	<div class="modal-dialog">
		<div class="modal-content">
			<div class="modal-header">
				<div class="modal-title me-3">
                    <h5>Agregar <span id="producto_head"></span></h5>
				</div>
				<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
			</div>

            <form action="<?php echo base_url( "addstock" ); ?>" method="post">
                <?php echo csrf_field(); ?>
                <input type="hidden" id="stock_producto" name="producto" value="">
                <input type="hidden" name="almacen" value="<?php echo $almacen[ "codigo" ]; ?>">
                <div class="modal-body">
                    <div class="row">
                    <div class="col-4">
                            <img id="stock_avatar" class="img-fluid p-2" src="">
                        </div>
                        <div class="col-8">
                            <p>Ingresa la cantidad exacta de productos a agregar al stock</p>
                            <input class="form-control w-50" name="cantidad">
                        </div>                        

                    </div>
                    <div class="nombre"></div>
                </div>
                <div class="modal-footer">
                    <button class="btn btn-primary my-2" id="confirma_agregar">Agregar productos</button>
                </div>
            </form>
		</div>
	</div>
</div>



<script>
    var cat_productos   = <?php echo json_encode( PRODUCTOS ); ?>,
        almacen = '<?php echo $almacen[ "codigo" ]; ?>';
</script>

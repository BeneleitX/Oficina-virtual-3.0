<link href="<?php echo base_url(); ?>assets/css/datatables.css" rel="stylesheet"/>
<script src="<?php echo base_url(); ?>assets/js/datatables.js" type="text/javascript"></script>
<script src="<?php echo base_url(); ?>assets/js/datatables_bs5.js" type="text/javascript"></script>

<h4 class="mt-1 mb-0"><?php echo $titulo; ?></h4>
<p><a href="<?php echo base_url( "almacenes/".$almacen[ "modelo_codigo" ] ); ?>"><i class="fa fa-undo"></i> Regresar a almacenes</a></p>


<div class="card mb-4">
    <div class="card-header"><h5 class="m-0">Existencias</h5></div>
    <div class="card-body">
        <div class="row" id="inventario">
            <?php  
                foreach( PRODUCTOS as $p ){
                    if( !isset( $almacen[ "productos" ][ $p[ "codigo" ] ] ) ){
                        $almacen[ "productos" ][ $p[ "codigo" ] ]  = 0;
                    } 
                    
                    $e = intval( $almacen[ "productos" ][ $p[ "codigo" ] ]);
                    $d = intval( $almacen[ "productos" ][ $p[ "codigo" ] ]);

                    $avatar = file_exists( "assets/img/productos/{$p[ "codigo" ]}.png" );

                    $tabla = "<img class='img150 img-fluid p-2' src='".base_url()."assets/img/productos/".( $avatar ? $p[ "codigo" ] : "NO-IMAGEN" ).".png'><p class='m-0'><span class='d-inline-block w-60'>Existencia</span><span class='d-inline-block w-40 text-end'>".number_format( $e )."</span></p><div class='d-none'><p class='m-0'><span class='d-inline-block w-60'>Disponible</span><span class='d-inline-block w-40 text-end'>".number_format( $d )."</span></p><p class='m-0'><span class='d-inline-block w-60'>Por entregar</span><span class='d-inline-block w-40 text-end'>".number_format( $e- $d )."</span></p></div><a class='btn btn-info mt-3 col-12'>Agregar producto</a>";

                    echo "
                    <div class=\"col-6 text-center col-lg-2 small mb-2\">
                        <div tabindex=\"0\" role=\"button\" avatar=\"{$avatar}\" producto=\"{$p[ "codigo" ]}\" data-bs-placement=\"bottom\" data-bs-sanitize=\"false\" data-bs-html=\"true\" data-bs-toggle=\"popover\" data-bs-trigger=\"focus\" data-bs-title=\"".mb_strtoupper( $p[ "data" ][ "nombre" ] )."\" data-bs-content=\"{$tabla}\" class=\"btn-group btn-group-sm pover col-12\" role=\"group\">
                            <label style=\"font-size:10px\" class=\"btn col-12 btn-outline-".( $e ? "success" : "danger")."\">".mb_strtoupper( $p[ "data" ][ "nombre" ] )."</label>
                            <label class=\"btn btn-".( $e ? "success" : "danger")."\" style=\"width:60px\">".number_format( $e )."</label>
                        </div>
                    </div>";
                }
            ?>
        </div>
    </div>
</div>

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

                $p[ "socio" ] = new \App\Entities\E_usuario( $p[ "usuario_id" ], $p[ "socio" ] );

                echo "\n<tr almacen=\"{$p[ "id" ]}\">
                    <td><span class=\"badge bg-marine\">{$p[ "referencia" ]}</span></td>
                    <td>".$p[ "socio" ]->avatar(24)." ".$p[ "socio" ]->id()." ".$p[ "socio" ]->nombre(2)."</td>
                    <td>".estatus( "330-EN-ESPERA" )."</td>

                    <td class=\"text-center\">{$p[ "data" ][ "productos" ]}</td>
                    <td class=\"text-center\">".( intval( substr( $p[ "estatus_codigo" ], 0, 3 ) ) > 400 ? substr( $p[ "fechas" ][ "pagado" ], 0, 10) : "<span class=\"badge bg-gray-300 text-red\">Pendiente</span>" )."</td>

                    <td class=\"text-end\"><a href=\"".base_url( "pedido/".$p[ "referencia" ] )."\" class=\"btn btn-xs btn-primary\">DETALLES</a></td>
                </tr>"; 
            }
        ?>
     
    </tbody>
</table>


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

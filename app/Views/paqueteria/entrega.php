<link href="<?php echo base_url(); ?>assets/css/datatables.css" rel="stylesheet"/>
<script src="<?php echo base_url(); ?>assets/js/datatables.js" type="text/javascript"></script>
<script src="<?php echo base_url(); ?>assets/js/datatables_bs5.js" type="text/javascript"></script>

<h4 class="mt-1 mb-0"><?php echo $titulo." <span class=\"badge bg-marine\">".$paqueteria[ "nombre" ]; ?></span></h4>
<p class="mb-3"><a href="<?php echo base_url( "pedido/".$pedido[ "referencia" ] ); ?>"><i class="fa fa-undo"></i> Regresar a detalles del pedido</a></p>

<h5>Pedido <span class="badge bg-marine"><?php echo $pedido[ "referencia" ]; ?></span>
                <?php echo  estatus( $pedido[ "estatus_codigo" ] ); ?></h5>

<div class="row">
    <div class="col-lg-6">
        <form enctype="multipart/form-data" action="<?php echo base_url( "marca_enviado" ); ?>" method="post">

        <div class="card">
            <div class="card-body">
                <h5>Socio <?php echo $cliente->id()." ".$cliente->avatar()." ".$cliente->nombre( 2 ); ?></h5>
                <p>Fecha de pago: <?php echo $pedido[ "fechas" ][ "pagado" ]; ?></p>

                <div class="alert alert-info">Completa la información del formulario y coloca los productos en el pedido marcando uno por uno en los cuadros de la derecha. Una vez colocados todos los productos, podrás marcar el pedido como en proceso de envío.</div>

                <table class="w-100">
                    <tr>
                        <td valign="top">Recibe:</td>
                        <td><input class="form-control mb-3" value="<?php echo $cliente->nombre( 2 ); ?>"></td>
                    </tr>
                    <tr>
                        <td valign="top">Domicilio</td>
                        <td><div class="card mb-3"><div class="card-body"><?php
                        
                        if( is_array( $d ) ){
                            echo "\n
                            {$d[ "calleynumero" ]}<br>
                            Colonia {$d[ "colonia" ]}<br>
                            {$d[ "localidad" ]}, {$d[ "entidad" ]}<br>
                            C.P. {$d[ "codigopostal" ]}<br><br>".( strlen( trim( $d[ "referencias" ] ) ) ? $d[ "referencias" ] : "Sin referencias" )
                            ."<br><br><i class=\"fa fa-mobile-retro\"></i> {$cliente->telefono}";
                        }
                        else{
                            echo "<span class=\"text-red\"><i class=\"fa fa-warning\"></i> No se han podido recuperar datos del domicilio destino</span>";
                        }
                        ?></div></div></td>
                    </tr>

                    <tr>
                        <td valign="top">Fotografía de evidencia</td>
                        <td><input  accept="image/jpeg" type="file" name="evidencia" id="" class="form-control mb-3"></td>
                    </tr>
                    <tr>
                        <td valign="top">Guia de rastreo</td>
                        <td><input name="guia" id="" class="form-control mb-3"></td>
                    </tr>
                </table>
                
            </div>
        </div>

        <h1 class="text-center">
            <button disabled class="btn btn-lg my-5 btn-danger" id="boton_entregado_no"><span id="productos_conteo">0</span> productos de <?php echo $pedido[ "data" ][ "productos" ]; ?></button>
            
            <div id="boton_entregado_si" style="display:none">
                
                <?php echo csrf_field(); ?>
                <input type="hidden" name="pedido" value="<?php echo $pedido[ "id" ]; ?>">
                <button class="btn btn-lg my-5 btn-primary">Marcar pedido como enviado</button>
                
            </div>
        </h1>

        </form>
    </div>

    <div class="col-lg-6">

        <?php $cp = 0; foreach( $pedido[ "productos" ] as $p => $c ){ $cp+= $c; ?>
        <div class="card mb-3">
            <div class="card-header">
                <h5 class="float-end text-teal m-0"><?php echo $c; ?></h5>
                <h5 class="m-0"><?php echo $productos[$p]->data->nombre; ?></h5>
            </div>
            <div class="card-body">
                <?php for( $a = 1; $a <= $c; $a++ ){ ?>
                    <input type="checkbox" class="btn-check" id="check_<?php echo $p."_".$a; ?>">
                    <label producto="<?php echo $p; ?>" numero="<?php echo $a; ?>" class="btn btn-outline-yesno fs-1 px-3 mb-1" for="check_<?php echo $p."_".$a; ?>"><i class="far fa-circle-down"></i></label>
                <?php } ?>
            </div>
        </div>
        <?php } ?>

    </div>

</div>

<div class="modal" tabindex="-1" id="modal_confirma" producto="">
	<div class="modal-dialog">
		<div class="modal-content">
			<div class="modal-header">
				<div class="modal-title me-3">
                    <h5>Agregar producto</h5>
				</div>
				<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
			</div>

			<div class="modal-body text-center">
             <img style="width:200px">
             <div class="nombre"></div>
             <button class="btn btn-primary my-2" id="confirma_agregar">AGREGAR</button>
            </div>
		</div>
	</div>
</div>


<script>
var cat_productos   = <?php echo json_encode( $productos ); ?>,
    total_productos = <?php echo $pedido[ "data" ][ "productos" ]; ?>,
    problema = <?php echo $pedido[ "data" ][ "productos" ] != $cp ? "true" : "false" ?>;
</script>
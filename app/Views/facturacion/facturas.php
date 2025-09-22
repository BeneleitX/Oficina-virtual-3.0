<link href="<?php echo base_url(); ?>assets/css/datatables.css" rel="stylesheet"/>
<script src="<?php echo base_url(); ?>assets/js/datatables.js" type="text/javascript"></script>
<script src="<?php echo base_url(); ?>assets/js/datatables_bs5.js" type="text/javascript"></script>


<div class="row">
    <div class="col-6">
        <h4 class="mt-1 mb-0"><?php echo $titulo; ?></h4>
        <p><a class="btn btn-light btn-sm" href="<?php echo base_url( "facturacion" ); ?>"><i class="fa fa-undo"></i> Regresar a Facturación</a></p>
    </div>

    <div class="col-6 text-end text-red pt-4">
        
        <a href="<?php echo base_url( "facturas_historial" ); ?>" class="btn btn-secondary"><i class="fa fa-file-invoice-dollar"></i> Historial de pedidos facturados</a>
    </div>
</div>



<table class="table table-striped bg-white" id="tabla_pedidos">
    <thead>
        <tr>
            <th>Referencia</th>
            <th>Socio</th>
            <th>Nombre</th>
            <th>Modelo de negocio</th>
            <th>Cantidad</th>
            <th>Fecha de pago</th>
            <th></th>
        </tr>
    </thead>

    <tbody>
        <?php 
            foreach( $pedidos as $p ){
                $modelo = MODELOS[ $p[ "modelo_codigo" ] ];
                $u  = model( "UsuarioModel" )->find( $p[ "usuario_id" ] );

                echo "\n<tr pedido=\"{$p[ "id" ]}\" referencia=\"{$p[ "referencia" ]}\" correo=\"".( $p[ "data" ][ "sat" ][ "correo" ] ?? "" )."\" mp=\"".( $p[ "data" ][ "sat" ][ "mp" ] ?? "" )."\" uso=\"".( $p[ "data" ][ "sat" ][ "uso" ] ?? "" )."\" rfc=\"{$u->data->sat->rfc}\" link=\"".base_url()."data/{$u->id}/csf/{$u->data->sat->csf}\">
                    <td class=\"text-center\">".referencia( $p )."</td>
                    <td>".$u->id( $p[ "modelo_codigo" ] )."</td>
                    <td>".$u->avatar( 24 )." ".$u->nombre( 2 )."</td>

                    <td><span class=\"text-{$modelo[ "settings" ][ "color" ]}\"><i class=\"fa fa-{$modelo[ "settings" ][ "icono" ]}\"></i> {$modelo[ "nombre" ]}</span></td>
                    
                    <td class=\"text-end\"><strong>$".number_format( $p[ "data" ][ "total" ] + $p[ "data" ][ "comisionbanco" ] + $p[ "data" ][ "comisionentrega" ] - $p[ "data" ][ "saldo" ], 2 )."</strong></td>
                    <td class=\"text-center\"><span class=\"d-none\">".substr( $p[ "fechas" ][ "pagado" ], 0, 10 )."</span> ".date( "d-m-Y", strtotime( substr( $p[ "fechas" ][ "pagado" ], 0, 10 ) ) )."</td>
                    
                    <td class=\"text-end\"><a href=\"".base_url( "pedido/".$p[ "referencia" ] )."\" class=\"btn btn-xs btn-secondary\">VER PEDIDO</a> <button class=\"btn btn-xs btn-primary\" onclick=\"registra_folio( {$p[ "id" ]} )\">REGISTRA FOLIO</button></td>
                </tr>";
            }
        ?>
     
    </tbody>
</table>



<div class="modal" tabindex="-1" id="modal_factura">
	<div class="modal-dialog">
		<div class="modal-content">
			<form method="post" action="<?php echo base_url( "do_factura" ); ?>">
                <?php echo csrf_field() ?>
                <input type="hidden" name="r_pedido"  value="">

				<div class="modal-header bg-teal">
                    <h5 class="modal-title text-white m-0">
                        <table><tr><td><i class="fa fa-file-invoice-dollar"></i> Registrar folio de factura</td><td class="ps-3 text-white"><span class="badge bg-red" id="m_titulo"></span></td></tr></table>
                    </h5>
					<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
				</div>
				<div class="modal-body">
                    <div class="row">
                        <div class="col-5 pt-1 text-end"><label>Referencia de pedido</label></div>
                        <div class="col-4"><input type="text" class="mb-3 form-control" name="r_referencia" disabled></div>

                        <div class="col-5 pt-1 text-end"><label>RFC</label></div>
                        <div class="col-5"><input type="text" class="mb-3 form-control" name="r_rfc" disabled></div>
                        <div class="col-1"><a class="btn btn-light" id="r_link" href="" target="_blank"><i class="fa fa-download"></i></a></div>

                        <div class="col-5 pt-1 text-end"><label>Correo electrónico</label></div>
                        <div class="col-7"><input type="text" class="mb-3 form-control" name="r_correo" disabled></div>

                        <div class="col-5 pt-1 text-end"><label>Método de pago</label></div>
                        <div class="col-7"><input type="text" class="mb-3 form-control" name="r_mp" disabled></div>

                        <div class="col-5 pt-1 text-end"><label>Uso de CFDI</label></div>
                        <div class="col-7"><input type="text" class="mb-3 form-control" name="r_uso" disabled></div>

                        <div class="col-12">
                            <p class="text-marine m-0 mb-1">Para registro en sistema:</p>
                            <div class="alert alert-info m-0">
                                <div class="row">

                                    <div class="col-5 pt-1 text-end"><label>Folio de factura</label></div>
                                    <div class="col-4"><input type="text" class="mb-3 form-control" name="r_folio" required></div>

                                    <div class="col-5 pt-1 text-end"><label>Fecha de factura</label></div>
                                    <div class="col-6"><input type="date" class="mb-3 form-control" name="r_fecha" required></div>

                                </div>
                            </div>
                        </div>
                    </div>

                </div>
                <div class="modal-footer">
                    <button class="btn btn-secondary" type="submit"><i class="fa fa-check"></i> Registrar datos</button>
                </div>                
			</form>
		</div>
	</div>
</div>

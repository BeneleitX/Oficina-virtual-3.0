<link href="<?php echo base_url(); ?>assets/css/datatables.css" rel="stylesheet"/>
<script src="<?php echo base_url(); ?>assets/js/datatables.js" type="text/javascript"></script>
<script src="<?php echo base_url(); ?>assets/js/datatables_bs5.js" type="text/javascript"></script>

<h4 class="mt-1 mb-0"><?php echo $titulo; ?></h4>
<p><a href="<?php echo base_url( "admin" ); ?>"><i class="fa fa-undo"></i> Regresar a administración</a></p>

<div class="row">
    <div class="col-lg-6">
    
    </div>
    <div class="col-6 col-lg-3 text-end">
        <a class="btn col-12 btn-lg mb-4 btn-danger" href="<?php echo base_url( "pagos_pendientes" ); ?>"><i class="fa fa-hourglass-start"></i> Pagos sin destino</a>
    </div>
    <div class="col-6 col-lg-3 text-end">
        <button class="btn btn-lg mb-4 col-12 btn-primary" onclick="modal_analiza()"><i class="fa fa-arrow-up-from-bracket"></i> Cargar layout</button>
    </div>
</div>

<input type="file" class="d-none upload">

<table class="table table-striped bg-white" id="tabla_pedidos">
    <thead>
        <tr>
            <th>No.</th>
            <th>Banco</th>
            <th>Referencia</th>
            <th>Socio</th>
            <th>Fecha pago</th>
            <th>Precio</th>
            <th>Comisión</th>
            <th>Depositado</th>
            <th>Folio</th>
            <th>Acción</th>
        </tr>
    </thead>

    <tbody>
        <?php 

        ?>    
    </tbody>
</table>


<div class="modal" tabindex="-1" id="modal_analiza" promocion="">
	<div class="modal-dialog">
		<div class="modal-content">

			<div class="modal-body text-center">
                
			</div>
		</div>
	</div>
</div>
<link href="<?php echo base_url(); ?>assets/css/datatables.css" rel="stylesheet"/>
<script src="<?php echo base_url(); ?>assets/js/datatables.js" type="text/javascript"></script>
<script src="<?php echo base_url(); ?>assets/js/datatables_bs5.js" type="text/javascript"></script>

<h4 class="mt-1 mb-0"><?php echo $titulo; ?></h4>

<div class="row">
    <div class="col-6">
    <button class="btn btn-lg my-4 btn-primary" onclick="modal_analiza()"><i class="fa fa-arrow-up-from-bracket"></i> Cargar layout</button>
    </div>
    <div class="col-6 text-end">
    <a class="d-none btn btn-lg my-4 btn-warning" href="#"><i class="fa fa-hourglass-start"></i> Pagos perdidos</a>
    </div>
</div>

<input type="file" class="d-none upload">

<table class="table table-striped bg-white" id="tabla_pedidos">
    <thead>
        <tr>
            <th>No.</th>
            <th>Banco</th>
            <th>Pedido</th>
            <th>Socio</th>
            <th>Fecha pago</th>
            <th>Cantidad</th>
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
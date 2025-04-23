<link href="<?php echo base_url(); ?>assets/css/datatables.css" rel="stylesheet"/>
<script src="<?php echo base_url(); ?>assets/js/datatables.js" type="text/javascript"></script>
<script src="<?php echo base_url(); ?>assets/js/datatables_bs5.js" type="text/javascript"></script>

<h4 class="mt-1 mb-0"><?php echo $titulo; ?></h4>
<p><a class="btn btn-sm btn-light" href="<?php echo base_url( "reportes" ); ?>"><i class="fa fa-undo"></i> Regresar a reportes</a></p>

<div class="row mt-4">
    <div class="col-lg-6">

    <div class="row mb-3">
    <label class="col-sm-3 col-form-label">Fecha inicio</label>
        <div class="col-sm-3">
            <input class="form-control" type="date" value="<?php echo date( "Y-m-d" ); ?>" name="d_inicia">
        </div>
    </div>
    <div class="row mb-3">
        <label class="col-sm-3 col-form-label">Fecha final</label>
        <div class="col-sm-3">
            <input class="form-control" type="date" value="<?php echo date( "Y-m-d" ); ?>" name="d_termina">
        </div>
    </div>



    <button type="button" id="submit_button" class="btn btn-secondary"><i class="fa fa-redo"></i> Actualizar datos</button>

</div></div>

<table class="table display striped mt-5 bg-white">
    <thead>
        <tr>
            <th>Empresa</th>
            <th>Moneda</th>
            <th class="text-center">Pedidos</th>
            <th class="text-end">Producto</th>
            <th class="text-end">Comision bancaria</th>
            <th class="text-end">Paquetería</th>
            <th class="text-end">Total</th>
        </tr>
    </thead>
    <tbody id="tabla_datos">
        
    </tbody>
</table>
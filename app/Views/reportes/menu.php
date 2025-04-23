<link href="<?php echo base_url(); ?>assets/css/datatables.css" rel="stylesheet"/>
<script src="<?php echo base_url(); ?>assets/js/datatables.js" type="text/javascript"></script>
<script src="<?php echo base_url(); ?>assets/js/datatables_bs5.js" type="text/javascript"></script>

<h4 class="mt-1 mb-0"><?php echo $titulo; ?></h4>
<p><a class="btn btn-sm btn-light" href="<?php echo base_url( "admin" ); ?>"><i class="fa fa-undo"></i> Regresar a administración</a></p>

<table class="table table-striped" id="tabla_reportes">
<thead><tr><th>Reportes disponibles:</th></tr></thead>

<tbody>
    <tr><td><a class="d-block" href="<?php echo base_url( "reportes/socios_por_estatus" ); ?>"><i class="fa fa-file-excel"></i> Listado de socios por estatus</a></td></tr></tbody>
    <tr><td><a class="d-block" href="<?php echo base_url( "reportes/ingresos_por_empresa" ); ?>"><i class="fa fa-file-excel"></i> Ingresos por empresa</a></td></tr></tbody>
</table>
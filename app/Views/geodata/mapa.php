<style>
    #container_mexico {
    height: 100px;
    width: 100%;
    margin: 0 auto;
}
</style>


<h4 class="mt-1 mb-0"><?php echo $titulo; ?></h4>
<p><a class="btn btn-sm btn-light" href="<?php echo base_url( "admin" ); ?>"><i class="fa fa-undo"></i> Regresar a administración</a></p>


<script src="https://code.highcharts.com/maps/highmaps.js"></script>
<script src="https://code.highcharts.com/maps/modules/exporting.js"></script>

<form method="post" action="<?php echo base_url( "geodata" ); ?>">
	<?php echo csrf_field(); ?>

	<div class="alert alert-info">
		<div class="row">
			<div class="col-6 col-md-4 col-lg-3 col-xl-2">
				<label>Empresa</label>
				<select class="form-select" name="empresa">
				<?php 
				foreach( MODELOS as $m ){
				echo "\n<option value=\"{$m[ "codigo" ]}\" ".( $m[ "codigo" ] == $empresa ? "selected" : "" ).">".$m[ "nombre" ]."</option>";
				}
				?>
				</select>
			</div>

			<div class="col-6 col-md-4 col-lg-3 col-xl-2">	
				<label>Tipo de datos</label>
				<select class="form-select" name="tipo_datos">
					<option value="venta_paqueteria" <?php echo "venta_paqueteria" == $tipo_datos ? "selected" : ""; ?>>Venta paquetería</option></li>
					<option value="venta_almacen" <?php echo "venta_almacen" == $tipo_datos ? "selected" : ""; ?>>Venta almacen</option></li>
				<!--	<option value="nuevos_socios" <?php echo "nuevos_socios" == $tipo_datos ? "selected" : ""; ?>>Nuevos socios</option></li>
					<option value="socios_activos" <?php echo "socios_activos" == $tipo_datos ? "selected" : ""; ?>>Socios activos</option></li> -->
				</select>
			</div>

			<div class="col-6 col-md-4 col-lg-3 col-xl-2">
				<label>Fecha inicial</label>
				<input type="date" class="form-control" value="<?php echo $f_inicia; ?>" name="f_inicia">
			</div>

			<div class="col-6 col-md-4 col-lg-3 col-xl-2">
				<label>Fecha final</label>
				<input type="date" class="form-control" value="<?php echo $f_termina; ?>" name="f_termina">
			</div>

			<div class="col-6 col-md-4 col-lg-3 col-xl-2 offset-xl-2">
				<label>&nbsp;</label>
				<button class="btn btn-primary w-100" id="submit_btn" type="submit"><i class="fa fa-refresh"></i> Actualizar</button>
			</div>

		</div>
	</div>
</form>

<div id="container_mexico"></div>

<script>
var chart_data = <?php echo json_encode( $chart_data ); ?>;

</script>

<script src="<?php echo base_url()."assets/js/geodata/_{$tipo_datos}.js"; ?>"></script>
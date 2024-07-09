    
<h4 class="mt-1 mb-3"><?php echo $titulo; ?></h4>

<div class="row">
	<div class="col-md-9">
	<?php echo pills( "red", $modelo ); ?>
	</div>
	<div class="col-md-3 mb-3 text-end">

		
		<div class="btn-group " role="group" aria-label="Button group with nested dropdown">

		<div class="btn-group dropdown" role="group">
			<a class="btn btn-outline-danger  col-12 dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">
				Filtros
		</a>
			
			<div class="dropdown-menu">

				<div class="dropdown dropend">
                    <a class="dropdown-item mini dropdown-toggle" href="#" id="dropdown-layouts" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">Por Estatus</a>
                    <div class="dropdown-menu" aria-labelledby="dropdown-layouts">
						<a class="dropdown-item mini" href="javascript:filtrar( 'estatus', '220-NUEVO-VERIFICADO' )">NUEVO-VERIFICADO</a>
						<a class="dropdown-item mini" href="javascript:filtrar( 'estatus', '310-NO-CALIFICADO' )">NO-CALIFICADO</a>
						<a class="dropdown-item mini" href="javascript:filtrar( 'estatus', '410-CALIFICADO' )">CALIFICADO</a>
						<a class="dropdown-item mini" href="javascript:filtrar( 'estatus', '520-CALIFICADO-ACTUAL' )">CALIFICADO-ACTUAL</a>
                    </div>
                </div>

				<div class="dropdown dropend">
                    <a class="dropdown-item mini dropdown-toggle" href="#" id="dropdown-layouts" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">Por Patrocinador directo</a>
                    <div class="dropdown-menu" aria-labelledby="dropdown-layouts">
					  	<a class="dropdown-item mini" href="javascript:filtrar( 'patrocinador', 1 )">DIRECTOS</a>
						<a class="dropdown-item mini" href="javascript:filtrar( 'patrocinador', 2 )">NO DIRECTOS</a>
                    </div>
                </div>

				<div class="dropdown dropend">
                      <a class="dropdown-item mini dropdown-toggle" href="#" id="dropdown-layouts" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">Por calificación de mes anterior</a>
                      <div class="dropdown-menu" aria-labelledby="dropdown-layouts">
						<a class="dropdown-item mini" href="javascript:filtrar( 'califica_0', '00---' )">SIN CALIFICAR</a>
						<a class="dropdown-item mini" href="javascript:filtrar( 'califica_0', '10-B1' )">BASICO 1</a>
						<a class="dropdown-item mini" href="javascript:filtrar( 'califica_0', '15-B2' )">BASICO 2</a>
						<a class="dropdown-item mini" href="javascript:filtrar( 'califica_0', '20-BX' )">BIEX</a>
						<a class="dropdown-item mini" href="javascript:filtrar( 'califica_0', '30-EE' )">EJECUTIVO</a>
						<a class="dropdown-item mini" href="javascript:filtrar( 'califica_0', '40-PR' )">PREMIERE</a>
                      </div>
                </div>

				<div class="dropdown dropend">
					<a class="dropdown-item mini dropdown-toggle" href="#" id="dropdown-layouts" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">Por calificación de mes actual</a>
					<div class="dropdown-menu" aria-labelledby="dropdown-layouts">
						<a class="dropdown-item mini" href="javascript:filtrar( 'califica_1', '00---' )">SIN CALIFICAR</a>
						<a class="dropdown-item mini" href="javascript:filtrar( 'califica_1', '10-B1' )">BASICO 1</a>
						<a class="dropdown-item mini" href="javascript:filtrar( 'califica_1', '15-B2' )">BASICO 2</a>
						<a class="dropdown-item mini" href="javascript:filtrar( 'califica_1', '20-BX' )">BIEX</a>
						<a class="dropdown-item mini" href="javascript:filtrar( 'califica_1', '30-EE' )">EJECUTIVO</a>
						<a class="dropdown-item mini" href="javascript:filtrar( 'califica_1', '40-PR' )">PREMIERE</a>
					</div>
                </div>

				<div class="dropdown dropend">
					<a class="dropdown-item mini dropdown-toggle" href="#" id="dropdown-layouts" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">Por rango</a>
					<div class="dropdown-menu" aria-labelledby="dropdown-layouts">
						<a class="dropdown-item mini" href="javascript:filtrar( 'rango', '00-SOCIO' )">SOCIO</a>
						<a class="dropdown-item mini" href="javascript:filtrar( 'rango', '10-3K' )">3K</a>
						<a class="dropdown-item mini" href="javascript:filtrar( 'rango', '20-5K' )">5K</a>
						<a class="dropdown-item mini" href="javascript:filtrar( 'rango', '30-10K' )">10K</a>
						<a class="dropdown-item mini" href="javascript:filtrar( 'rango', '40-BRONCE' )">BRONCE</a>
						<a class="dropdown-item mini" href="javascript:filtrar( 'rango', '50-PLATA' )">PLATA</a>
						<a class="dropdown-item mini" href="javascript:filtrar( 'rango', '60-ORO' )">ORO</a>
						<a class="dropdown-item mini" href="javascript:filtrar( 'rango', '70-RUBI' )">RUBI</a>
						<a class="dropdown-item mini" href="javascript:filtrar( 'rango', '80-ESMERALDA' )">ESMERALDA</a>
						<a class="dropdown-item mini" href="javascript:filtrar( 'rango', '90-DIAMANTE' )">DIAMANTE</a>
					</div>
                </div>

				<div class="dropdown dropend">
					<a class="dropdown-item mini dropdown-toggle" href="#" id="dropdown-layouts" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">Por profundidad</a>
					<div class="dropdown-menu" aria-labelledby="dropdown-layouts">
						<a class="dropdown-item mini" href="javascript:filtrar( 'profundidad', 0 )">PRIMER NIVEL</a>
						<a class="dropdown-item mini" href="javascript:filtrar( 'profundidad', 1 )">SEGUNDO NIVEL</a>
						<a class="dropdown-item mini" href="javascript:filtrar( 'profundidad', 2 )">TERCER NIVEL</a>
						<a class="dropdown-item mini" href="javascript:filtrar( 'profundidad', 3 )">BONO DE PROFUNDIDAD</a>
					</div>
                </div>				
			</div>
		</div>
			<a type="button" onclick="filtrar( 'ninguno', '' )" class="btn btn-danger"><i class="fa fa-xmark"></i></a>
		</div>

		<button class="btn btn-success"><i class="fa fa-file-excel"></i> Descargar Excel</button>

	</div>
</div>


<div id="downline"></div>

<script src= "<?php echo base_url() ?>/assets/js/d3.js"></script>

<script>
	var estatus_minimo = 200,
		agregar_nuevos = false,
		modelo         = "<?php echo $modelo; ?>",
		socio          = <?php echo $socio->id; ?>,
		canvas 	       = '#downline',
		estatus        = <?php echo json_encode( ESTATUS )?>,
		rangos         = <?php echo json_encode( RANGOS )?>,
		m_0 = <?php echo date('Ym'); ?>,
		m_1 = <?php echo date('Ym', strtotime( date('Y-m').'-01'. ' -1 month' ) ); ?>,
		m_2 = <?php echo date('Ym', strtotime( date('Y-m').'-01'. ' -2 month' ) ); ?>;
</script>

<script src="<?php echo base_url(); ?>assets/js/redes/modelos/<?php echo $modelo; ?>.js" type="text/javascript"></script>

<div class="modal" tabindex="-1" id="modal_userdata">
	<div class="modal-dialog">
		<div class="modal-content">
			<div class="modal-body">
			</div>
		</div>
	</div>
</div>


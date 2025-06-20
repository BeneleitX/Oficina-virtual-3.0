<?php
if( defined( "VARIABLES" ) && VARIABLES[ "aviso_inicio" ][ "valor" ] ){
	echo "<div class=\"alert alert-danger small py-2\"><i class=\"fa fa-warning\"></i> ".VARIABLES[ "aviso_inicio" ][ "valor" ]."</div>";
}
?>
<div id="login-form">

	<?php
	/*
	if( sizeof( $banners ) ){
		?>

		<div id="carrusel" class="carousel slide mt-3" data-bs-ride="carousel">
			<div class="carousel-indicators">
				<?php
				$active = 0;
				foreach( $banners as $k => $b ){
					echo "\n<button type=\"button\" data-bs-target=\"#carouselExampleIndicators\" data-bs-slide-to=\"{$k}\" ".( $active++ ? "" : "  class=\"active\" aria-current=\"true\"" )."></button>";
				}
				?>
			</div>	
			<div class="carousel-inner rounded">
				<?php
				$active = 0;
				foreach( $banners as $b ){
					echo "\n<div class=\"carousel-item ".( $active++ ? "" : "active")."\"><img src=\"".base_url()."assets/img/banners/{$b[ "archivo" ]}\" class=\"d-block w-100\" alt=\"\" style=\"aspect-ratio:1.411\"></div>";
				}
				?>
			</div>

			<button class="carousel-control-prev" type="button" data-bs-target="#carrusel" data-bs-slide="prev">
				<span class="carousel-control-prev-icon" aria-hidden="true"></span>
				<span class="visually-hidden">Anterior</span>
			</button>
			<button class="carousel-control-next" type="button" data-bs-target="#carrusel" data-bs-slide="next">
				<span class="carousel-control-next-icon" aria-hidden="true"></span>
				<span class="visually-hidden">Siguiente</span>
			</button>
		</div>

		<?php
	}
	*/	
	?>
		
	<div class="xfixed-top p-4">
		<div class="row">
			<div class="col-xl-8 offset-xl-2 col-md-8 offset-md-2">
				<div class="row">
					<div class="col-lg-6 offset-lg-3">
						<p class="mx-5 px-5 py-3 text-center"><img src="<?php echo base_url(); ?>assets/img/logo_blanco.png" class="img-fluid"></p>

						<div class="xcard mb-3 bg-marine xtext-white ">
							<div class="card-body">

								<form id="login_bsumbit" method="post" action="<?php echo base_url( "oauth" ); ?>">
									<?php echo csrf_field() ?>
									<input type="text" class="form-control ps-4 xrounded-pill <?php echo session( "errors.socio_id" ) ? "is-invalid" : ""; ?>" name="socio_id" value="<?php echo $id ?? old( "socio_id" ); ?>" placeholder="Socio">
									<p class="small text-danger"><?php echo session( "errors.socio_id" ); ?></p>
									<input type="password" class="form-control ps-4 mt-3 xrounded-pill <?php echo session( "errors.socio_password" ) ? "is-invalid" : ""; ?>" name="socio_password" value="<?php echo old( "socio_password" ); ?>" placeholder="Password">
									<p class="small text-danger"><?php echo session( "errors.socio_password" ); ?></p>
									<p class="mt-3 mb-1 text-end"><button type="submit" id="submit_login" class="submit btn btn-primary xrounded-pill col-12">Ingresar <i class="fa fa-right-to-bracket"></i></button></p>
									<p class="text-center mt-3 mb-0"><a class="text-mustard" href="<?php echo base_url( "recover" ); ?>"><i class="far fa-circle-question"></i> Solicitar un nuevo password</a></p>
								</form>
							</div>
							
						</div>
						<p class="text-center mt-3 text-white"><a class="mt-2 btn btn-danger xpy-3 col-12" href="<?php echo base_url( "formulario" ); ?>" xstyle="border-radius:15px"><i class="fa fa-wand-magic-sparkles"></i> ¿Aun no eres socio? Registrate aquí</a></p>


						<p class="text-center mb-1 mt-4 text-white"><a class="mt-2 btn btn-info2 xpy-2 col-12" href="https://core.beneleit.talentonet.com/activar/" xstyle="font-size:1.3rem"><img src="assets/img/logo_beneleit_movil.png" style="width:70px"> &nbsp; Activa tu línea</a></p>

						<p class="text-center mb-1 text-white"><a class="mt-2 btn btn-info2 xpy-2 col-12" href="https://recarga.beneleit.talentonet.com/" xstyle="font-size:1.3rem"><img src="assets/img/logo_beneleit_movil.png" style="width:70px"> &nbsp; Recarga Express</a></p>
					</div>
				</div>
			</div>
		</div>
	</div>
		
</div>

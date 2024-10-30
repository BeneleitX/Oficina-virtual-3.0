<?php
if( defined( "VARIABLES" ) && VARIABLES[ "aviso_inicio" ][ "valor" ] ){
	echo "<div class=\"alert alert-danger small py-2\"><i class=\"fa fa-warning\"></i> ".VARIABLES[ "aviso_inicio" ][ "valor" ]."</div>";
}
?>
<p class="text-center d-block d-lg-none"><img src="<?php echo base_url(); ?>assets/img/icon_beneleit.png" class="opacity-25 w-25 my-0"></p>

<div id="login-form">
	<div class="row">
		<div class="col-lg-7 px-5">

		<?php
		if( sizeof( $banners ) ){
			?>

			<div id="carrusel" class="carousel slide mb-3" data-bs-ride="carousel">
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
						echo "\n<div class=\"carousel-item ".( $active++ ? "" : "active")."\"><img src=\"".base_url()."assets/img/banners/{$b[ "archivo" ]}\" class=\"d-block w-100\" alt=\"\"></div>";
					}
					?>
				</div>

				<button class="carousel-control-prev" type="button" data-bs-target="#carrusel" data-bs-slide="prev">
					<span class="carousel-control-prev-icon" aria-hidden="true"></span>
					<span class="visually-hidden">Previous</span>
				</button>
				<button class="carousel-control-next" type="button" data-bs-target="#carrusel" data-bs-slide="next">
					<span class="carousel-control-next-icon" aria-hidden="true"></span>
					<span class="visually-hidden">Next</span>
				</button>
			</div>

			<?php
		}
		?>
			
		</div>
		<div class="col-lg-1">

		</div>
		<div class="col-lg-4 px-5">
			<p class="text-center d-none d-lg-block"><img src="<?php echo base_url(); ?>assets/img/logo_blanco.png" class="opacity-25 w-25 mt-3"></p>

					<div class="card mb-3 bg-gray-200 xtext-white ">
						
						<div class="card-body">
							<h5 class="text-center py-2 text-marine">Oficina virtual</h5>

							<form id="login_bsumbit" method="post" action="<?php echo base_url( "oauth" ); ?>">
								<?php echo csrf_field() ?>
								<input type="text" class="form-control ps-4 rounded-pill <?php echo session( "errors.socio_id" ) ? "is-invalid" : ""; ?>" name="socio_id" value="<?php echo $id ?? old( "socio_id" ); ?>" placeholder="Socio">
								<p class="small text-danger"><?php echo session( "errors.socio_id" ); ?></p>
								<input type="password" class="form-control ps-4 mt-3 rounded-pill <?php echo session( "errors.socio_password" ) ? "is-invalid" : ""; ?>" name="socio_password" value="<?php echo old( "socio_password" ); ?>" placeholder="Password">
								<p class="small text-danger"><?php echo session( "errors.socio_password" ); ?></p>
								<p class="mt-3 mb-1 text-end"><button type="submit" id="submit_login" class="submit btn btn-primary rounded-pill col-12">Ingresar <i class="fa fa-right-to-bracket"></i></button></p>
								<hr class="b-primary">
								<p class="text-center mt-3 mb-0"><a href="<?php echo base_url( "recover" ); ?>"><i class="far fa-circle-question"></i> Solicitar un nuevo password</a></p>

							</form>
						</div>
						
					</div>
					<p class="text-center my-3 text-white">¿Aun no eres socio?<br><a class="mt-2 btn btn-warning py-3 col-12" href="<?php echo base_url( "formulario" ); ?>" style="border-radius:15px"><i class="fa fa-wand-magic-sparkles"></i> Registrate aquí</a></p>
					<p class="text-center my-3 text-white"><a class="mt-2 btn btn-info2 py-2 col-12" href="https://recarga.beneleit.talentonet.com/" style="border-radius:15px"><img src="assets/img/logo_beneleit_movil.png" style="width:70px"> Recarga Express</a></p>
		</div>
	</div>	
</div>
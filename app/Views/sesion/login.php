<style>
	.loader {
		position: relative;
		float:left;
		user-select: none;
		box-sizing: border-box;
		width: 150px;
		height: 150px;
	}
	.loader-bg {
		width: 100%;
		height: 100%;
		border-radius: 50%;
		box-sizing: border-box;
		border: 5px solid transparent;
		display: flex;
		align-items: center;
		justify-content: center;
	}
	.spinner-holder-one {
		position: absolute;
		top:0;
		left:0;
		overflow: hidden;
		width: 50%;
		height: 50%;
		background: transparent;
		box-sizing: border-box;
	}
	.spinner-holder-two {
		position: absolute;
		top:0;
		left:0;
		overflow: hidden;
		width: 100%;
		height: 100%;
		background: transparent;
		box-sizing: border-box;
	}
	.loader-spinner {
		width: 200%;
		height: 200%;
		border-radius: 50%;
		border: 5px solid #0b5ac1;
		opacity: 0.6;
		box-sizing: border-box;
	}  
	.animate-0-25-a {
		transform: rotate(90deg);
		transform-origin: 100% 100%;
	}
	.animate-0-25-b {
		transform: rotate(-90deg);
		transform-origin: 100% 100%;
	}
	.animate-25-50-a {
		transform: rotate(180deg);
		transform-origin: 100% 100%;
	}
	.animate-25-50-b {
		transform: rotate(-90deg);
		transform-origin: 100% 100%;
	}
	.animate-50-75-a {
		transform: rotate(270deg);
		transform-origin: 100% 100%;
	}
	.animate-50-75-b {
		transform: rotate(-90deg);
		transform-origin:100% 100%;
	}
	.animate-75-100-a {
		transform: rotate(0deg);
		transform-origin: 100% 100%;
	}
	.animate-75-100-b {
		transform: rotate(-90deg);
		transform-origin: 100% 100%;
	}
	.text {
		text-align: center;
		font-size: 20px;
		color: #6c757d;
		font-weight: bold;  
	}
</style>

<script type="text/javascript" src="<?php echo base_url(); ?>assets/js/circle.js"></script>

<?php
if( !$white && defined( "VARIABLES" ) && VARIABLES[ "aviso_inicio" ][ "valor" ] ){
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
		
	<div class="xfixed-top p-2">
		<div class="row">
			<div class="col-xl-8 offset-xl-2 col-md-8 offset-md-2">
				<div class="row">
					<div class="col-lg-6 offset-lg-3">
						<p class="<?php echo $white ? "d-none" : ""; ?> mx-5 px-5 py-3 text-center"><img src="<?php echo base_url(); ?>assets/img/logo_blanco.png" class="img-fluid"></p>

						<div class="xcard mb-3 bg-<?php echo $white ? "white" : "marine"; ?> xtext-white ">
							<div class="card-body">

								<form id="login_bsumbit" method="post" action="<?php echo base_url( "oauth" ); ?>">
									<?php echo csrf_field() ?>
									<input type="hidden" name="white" value="<?php echo $white ?? ""; ?>">

									<input type="text" class="form-control ps-4 xrounded-pill <?php echo session( "errors.socio_id" ) ? "is-invalid" : ""; ?>" name="socio_id" value="<?php echo $id ?? old( "socio_id" ); ?>" placeholder="Socio">
									<p class="text-red"><?php echo session( "errors.socio_id" ); ?></p>
									<input type="password" class="form-control ps-4 mt-3 xrounded-pill <?php echo session( "errors.socio_password" ) ? "is-invalid" : ""; ?>" name="socio_password" value="<?php echo old( "socio_password" ); ?>" placeholder="Password">
									<p class="text-red"><?php echo session( "errors.socio_password" ); ?></p>
									
									<table xclass="w-100" align="center"><tr>
										
											<td class="pe-2" style="position:relative">
												<div style="position:absolute; top:9px; left:12px;z-index:20"><a data-bs-toggle="tooltip" title="Click para solicitar un nuevo número" href="javascript:reload_captcha()"><i class="fa fa-refresh text-gray-600"></i></a></div>
											    <div class="circlebar"></div>	
											
											</td>
											<td class="text-center"><img width="110" height="40" class="me-1" src="" id="captcha" title="Para validar que eres humano, ingresa estos números en el cuadro de texto a la derecha." data-bs-toggle="tooltip"></td>
											<td class="text-end"><input type="text" class="form-control text-center <?php echo session( "errors.captcha" ) ? "is-invalid" : ""; ?>" name="captcha" value="" style="width:7rem"></td>
										</tr></table>

										<p class="text-red text-center m-0"><?php echo session( "errors.captcha" ); ?></p>

									<p class="mt-3 mb-1 text-end"><button type="submit" id="submit_login" class="submit btn btn-primary xrounded-pill col-12">Ingresar <i class="fa fa-right-to-bracket"></i></button></p>
									<p class="<?php echo $white ? "d-none" : ""; ?> text-center mt-3 mb-0"><a class="text-mustard" href="<?php echo base_url( "recover" ); ?>"><i class="far fa-circle-question"></i> Solicitar un nuevo password</a></p>
								</form>
							</div>
							
						</div>
						<p class="<?php echo $white ? "d-none" : ""; ?> text-center mt-3 text-white"><a class="mt-2 btn btn-danger xpy-3 col-12" href="<?php echo base_url( "registro" ); // formulario ?>" xstyle="border-radius:15px"><i class="fa fa-wand-magic-sparkles"></i> ¿Aún no eres socio? Regístrate aquí</a></p>

						<p class="<?php echo $white ? "d-none" : ""; ?> text-center mb-1 mt-4 text-white"><a class="mt-2 btn btn-warning xpy-2 col-12" xhref="<?php echo base_url( "vincular" ); ?>" xstyle="font-size:1.3rem"><img src="assets/img/logo_beneleit_movil.png" style="width:70px"> &nbsp; Vincula tu línea</a></p>

						<p class="<?php echo $white ? "d-none" : ""; ?> text-center mb-1 text-white"><a class="mt-2 btn btn-info2 xpy-2 col-12" href="https://core.beneleit.talentonet.com/activar/" xstyle="font-size:1.3rem"><img src="assets/img/logo_beneleit_movil.png" style="width:70px"> &nbsp; Activa tu línea</a></p>

						<p class="<?php echo $white ? "d-none" : ""; ?> text-center mb-1 text-white"><a class="mt-2 btn btn-info2 xpy-2 col-12" href="https://recarga.beneleit.talentonet.com/" xstyle="font-size:1.3rem"><img src="assets/img/logo_beneleit_movil.png" style="width:70px"> &nbsp; Recarga Express</a></p>
					</div>
				</div>		
			</div>
		</div>
	</div>
</div> 
<div class="<?php echo $white ? "d-none" : ""; ?> text-center mt-5" style="zoom:0.5">
<span id="siteseal"><script async type="text/javascript" src="https://seal.godaddy.com/getSealPremium?sealID=SXHRTCqFBcGyLhgSVbyD1lpfJBgTbR4brHwH6uLYrTyiD4N79vq8UKlLcGPk"></script></span>
</div>

<script>
	var white = "<?php echo $white; ?>";
</script>


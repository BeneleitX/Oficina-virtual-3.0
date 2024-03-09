<div class="p-3">
    <?php if( session( "message" ) !== null ) echo session('message'); ?>
	<div class="row">
		<div class="col-8 offset-2 col-md-4 offset-md-4 col-lg-3 offset-lg-8 col-xl-2 offset-xl-9" style="padding-top:50px">
			<p class="text-center"><img src="<?php echo base_url(); ?>assets/img/logo_color.png" class="w-50 mb-4"></p>

			<div class="card mb-3 xbg-secondary xtext-white ">
				
				<div class="card-body">
					<form method="post" action="<?php echo base_url( "oauth" ); ?>">
						<?php echo csrf_field() ?>
						<input type="text" class="form-control rounded-pill <?php echo session( "errors.socio_id" ) ? "is-invalid" : ""; ?>" name="socio_id" value="<?php echo old( "socio_id" ); ?>" placeholder="Socio">
						<p class="small text-danger"><?php echo session( "errors.socio_id" ); ?></p>
						<input type="password" class="form-control mt-3 rounded-pill <?php echo session( "errors.socio_password" ) ? "is-invalid" : ""; ?>" name="socio_password" value="<?php echo old( "socio_password" ); ?>" placeholder="Password">
						<p class="small text-danger"><?php echo session( "errors.socio_password" ); ?></p>
						<p class="text-center mt-3"><a href="#">¿Olvidaste tu password?</a></p>
						<hr class="b-primary">
						<p class="mt-3 mb-1 text-end"><button id="submit_login" class="btn btn-primary rounded-pill col-12">Ingresar <i class="fa fa-right-to-bracket"></i></button></p>
					</form>
				</div>
				
			</div>
			<p class="text-center">¿Aun no eres socio?<br><a href="#"><i class="fa fa-wand-magic-sparkles"></i> Registrate aquí</a></p>
		</div>
	</div>
</div>


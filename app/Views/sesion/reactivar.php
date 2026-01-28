<div class="row">
    <div class="col-md-4 offset-md-4" style="padding-top:50px">
        <p class="text-center"><img src="<?php echo base_url(); ?>assets/img/logo_color.png" class="w-25"></p>
        
        <div class="card mb-3">
            <div class="card-body text-start">

				<?php
					if(! $usuario->data->verificacion->password ){
						echo "<div class=\"alert alert-danger\"><i class=\"fa fa-warning\"></i> IMPORTANTE: Debes reemplazar el password temporal, por uno propio que te sea fácil de recordar</div>";
					}
				?>
				<form method="post" action="<?php echo base_url( "nuevo_password_reactivar" ); ?>">
					<?php echo csrf_field() ?>

					<label>Password temporal actual</label>
					<input type="password" name="actual" class="form-control mb-0 <?php echo session( "errors.actual" ) ? "is-invalid" : ""; ?>" value="<?php echo old( "actual" ); ?>">
					<p class="small text-red"><?php echo session( "errors.actual" ); ?></p>

					<ul class="small">
						<li>Debe tener un mínimo de 6 caracteres</li>
						<li>Debe tener al menos un número</li>
						<lI>No uses nombres propios ni fechas</li>
						<li>Guarda una copia en un lugar seguro</li>
					</ul>

					<label>Escribe tu nuevo password</label>
					<input type="password" name="nuevo" class="form-control mb-0 <?php echo session( "errors.nuevo" ) ? "is-invalid" : ""; ?>" value="<?php echo old( "nuevo" ); ?>">
					<p class="small text-red"><?php echo session( "errors.nuevo" ); ?></p>

					<label>Confirma el password</label>
					<input type="password" name="nuevo_bis" class="form-control mb-0 <?php echo session( "errors.nuevo_bis" ) ? "is-invalid" : ""; ?>" value="<?php echo old( "nuevo_bis" ); ?>">
					<p class="small text-red"><?php echo session( "errors.nuevo_bis" ); ?></p>

					<button type="submit" class="btn btn-primary">Asignar nuevo password</button>
				</form>
			</div>
        </div>
	</div>
</div>

                    
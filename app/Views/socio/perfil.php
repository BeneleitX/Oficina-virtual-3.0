<div class="alert alert-warning"><i class="fa fa-warning"></i> Tu dirección de correo <strong><?php echo $socio->correo; ?></strong> no ha sido validada. Haz <button class="btn btn-warning">Click aquí</button> para enviarte un mensaje, abre tu correo y sigue las instrucciones.</div>

<h4 class="mt-1 mb-3"><?php echo $titulo; ?></h4>


		<a href="javascript:$( '#verificacion').modal( 'show' )" data-bs-toggle="tooltip" title="Click para ver detalles de verificación" class="col-12">
		
				<div class="progress bg-white mb-3" role="progressbar" aria-label="Animated striped example" aria-valuenow="<?php echo $avance; ?>" aria-valuemin="0" aria-valuemax="100">
					<div class="progress-bar progress-bar-striped progress-bar-animated bg-teal" style="width: <?php echo $avance; ?>%"><?php echo $avance; ?>%</div>
				</div>
			
		</a>



<div class="card mb-4">
	<div class="card-body">
		<div class="row">
		<div class="col-md-4 p-4 position-relative text-center">
			<div id="imagencontenedor">
				<div id="imagen_avatar"><?php echo $socio->avatar( 200 ); ?></div>
			</div>
			<div id="cambia_avatar" style="display:none" class="position-absolute top-50 start-50 text-center translate-middle"><a class="btn btn-sm btn-primary" href="<?php echo base_url()."fotografia"; ?>"><i class="fa fa-edit"></i> Cambiar foto</a></div>
		</div>
		<div class="col-md-4">
			<label>Nombre</label>
			<input type="text" class="form-control mb-3" value="<?php echo $socio->data->nombre; ?>">

			<label>Primer apellido</label>
			<input type="text" class="form-control mb-3" value="<?php echo $socio->data->apellidos[0]; ?>">

			<label>Segundo apellido</label>
			<input type="text" class="form-control mb-3" value="<?php echo $socio->data->apellidos[1]; ?>">

		</div>
		<div class="col-md-4">
			<label>Correo electrónico</label>
			<input type="text" class="form-control mb-3" value="<?php echo $socio->correo; ?>">

			<label>Teléfono</label>
			<input type="text" class="form-control mb-3" value="<?php echo $socio->telefono; ?>">

			<label>CURP</label>
			<input type="text" class="form-control" value="<?php echo $socio->curp; ?>">			
		</div>
		</div>
	</div>
</div>


<div class="row">
	<div class="col-md-6">
		<div class="card mb-4">
            <div class="card-header p-0">
				<div class="row">
				<div class="col-6 py-2 px-4">
						Identificación oficial
					</div>
					<div class="col-6 text-end" style="padding: 0.2rem 1.8rem 0 4.8rem">
						<img src="<?php echo base_url(); ?>assets/img/logo_ine.png" style="width:105px">
					</div>
				</div>
            </div>
            <div class="card-body">
				
				<?php 
				switch( $socio->data->credencial->estatus ){
					case -1 : 
						echo "<div class=\"alert alert-danger\"><i class=\"fa fa-address-card\"></i> Tu credencial fue rechazada por el siguiente motivo: <strong>{$socio->data->credencial->motivo}</strong>. Por favor carga nuevas imagenes de tu credencial.</div>";
						break; 
					case 0 : 
						echo "<div class=\"alert alert-warning\"><i class=\"fa fa-address-card\"></i> Verifica tu cuenta cargando fotografías de tu credencial de elector (INE) por ambos lados.</div>";
						break; 
					case 1 : 
						echo "<div class=\"alert alert-info\"><i class=\"fa fa-address-card\"></i> Tu identificación oficial se encuentra en proceso de validación.</div>";
						break; 
					case 2 : 
						echo "<div class=\"alert alert-success\"><i class=\"fa fa-address-card\"></i> Tu identificación oficial ha sido validada.</div>";
						break; 
					}												
				 ?>

				<div class="row">
						<div class="col-6 text-center ct_frente">
							<?php if( $socio->data->credencial->frente ){ ?>
								<img src="<?php echo base_url()."data/{$socio->id}/ine/{$socio->data->credencial->frente}"; ?>" alt="" class="img-fluid rounded-3">
							<?php 
								if( $socio->data->credencial->estatus <= 0 ) echo "<a href=\"".base_url("cancela_ine/frente")."\" class=\"small\"><i class=\"fa fa-trash\"></i> Cancelar esta foto</a>";
							} else { ?>
								<button tipo="frente" onclick="$( 'input[tipo=frente]' ).click()" class="btn btn-outline-success col-12 py-4">Cargar foto del <h5 class="text-green m-0">frente</h5>de credencial</button>
							<?php } ?>
						</div>
						<div class="col-6 text-center ct_reverso">
							<?php if( $socio->data->credencial->reverso ){ ?>
								<img src="<?php echo base_url()."data/{$socio->id}/ine/{$socio->data->credencial->reverso}"; ?>" alt="" class="img-fluid rounded-3">
							<?php 
								if( $socio->data->credencial->estatus <= 0 ) echo "<a href=\"".base_url("cancela_ine/reverso")."\" class=\"small\"><i class=\"fa fa-trash\"></i> Cancelar esta foto</a>";
							} else { ?>							
								<button tipo="reverso" onclick="$( 'input[tipo=reverso]' ).click()" class="btn btn-outline-success col-12 py-4">Cargar foto del <h5 class="text-green m-0">reverso</h5>de credencial</button>
							<?php } ?>
						</div>
					</div>
					
					<?php if( $socio->data->credencial->estatus <= 0 ){ ?>
						<input type="file" class="d-none upload" tipo="frente" accept="image/jpeg">
						<input type="file" class="d-none upload" tipo="reverso" accept="image/jpeg">

						<h5 class="mt-4">1. Carga de fotografías</h5>
						<p>Click en los botones para carga fotografías de tu credencial del INE vigente por ambos lados. Cancelala si deseas repetir el proceso con una nueva foto.</p>

						<h5>2. Envíalas a revisión</h5>
						<p>Click en el botón para enviarlas. Personal de la empresa validará los datos y se te notificará cuando hayas terminado el proceso.</p>
						<a class="btn btn-success <?php echo $socio->data->credencial->frente && $socio->data->credencial->reverso > 0 ? "" : "disabled" ?>" id="valida_credencial" href="<?php echo base_url( "valida_credencial" ); ?>"><i class="fa fa-paper-plane"></i> Enviar para validación</a>
					<?php } ?>
			</div>
        </div>

		<div class="card mb-4">
            <div class="card-header">Password</div>
            <div class="card-body">	
				x
			</div>
		</div>

	</div>

	<div class="col-md-6">
	<div class="card mb-4">
            <div class="card-header">CLABE Interbancaria</div>
            <div class="card-body" style="position:relative">	
				<div class="alert m-0 p-0 text-end">
				<img src="<?php echo base_url(); ?>assets/img/bancos/1.png" style="height:30px; position:absolute; top:0px; left: 0">
				<h4 class="m-0">646209401776584352 <button class="btn btn-outline-success"><i class="fa fa-edit"></i></button></h4>
				</div>
			</div>
		</div>

		<div class="card mb-4">
            <div class="card-header">Domicilios</div>
            <div class="card-body">	
				<div class="row">
					<div class="col-md-6">
						<div class="alert alert-info mb-0">
							<h5>Mi casa</h5>
							<p class="mb-0">
								Av. Lázaro Cárdenas 345 int. A<br>
								Colonia Nuevo progreso<br>
								Villa de Alvarez, Colima<br>
								C.P. 23380
							</p>
						</div>
					</div>
				</div>
			</div>
		</div>	
		
		<div class="card mb-4">
            <div class="card-header">Beneficiarios de la cuenta </div>
			<div class="card-body">	

			<div class="card mb-3 mb-0">	
			<table class="table align-middle mb-0">
				<?php 

					foreach( $socio->data->beneficiarios as $k => $b ){
						echo "\n<tr>
							<td><strong>{$b->nombre}</strong></td>
							<td>{$b->porcentaje}%</td>
							<td class=\"text-end\"><a href=\"javascript:borra_beneficiario( {$k} )\" class=\"text-red\"><i class=\"fa fa-trash\"></i></a></td>
						</tr>
						";
					}
				?>
			</table>
			<div class="card-body <?php if( $porc == 100 ) echo "d-none"; ?>">	
			<button class="btn btn-success" onclick="$( '#beneficiario' ).modal( 'show' )"><i class="fa fa-plus"></i> Agregar beneficiario</button>
			</div></div>
			
			<small><i class="fa fa-circle-info"></i> Un beneficiario es una persona designada por el socio titular, que heredaría los derechos (o un porcentaje de ellos) sobre su cuenta, su red y sus ingresos pasivos, en dado caso de que el titular llegue a fallecer.</small>
			</div>
		</div>			
	</div>

</div>


<div class="modal" tabindex="-1" id="verificacion">
	<div class="modal-dialog">
		<div class="modal-content">
			<div class="modal-header">
				<h5 class="modal-title">Verificación de cuenta de socio</h5>
				<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
			</div>
			<div class="modal-body">
				<div class="progress mb-3" role="progressbar" aria-label="Animated striped example" aria-valuenow="<?php echo $avance; ?>" aria-valuemin="0" aria-valuemax="100">
					<div class="progress-bar progress-bar-striped progress-bar-animated bg-teal" style="width: <?php echo $avance; ?>%"><?php echo $avance; ?>%</div>
				</div>


				<?php
					$puntos_verificacion = admin( "puntos_verificacion" );
	 				
					foreach( $puntos_verificacion as $codigo => $punto){
						if( $socio->data->verificacion->{$codigo} ){
							echo "<p class=\"\"><i class=\"fas fa-square-check text-teal\"></i> {$punto}</p>";
						}
						else{
							echo "<p class=\"text-gray-500\"><i class=\"far fa-square\"></i> {$punto}</p>"; // $socio->data->verificacion
						}
					} 
				?>
			</div>
		</div>
	</div>
</div>


<div class="modal" tabindex="-1" id="beneficiario">
	<div class="modal-dialog">
		<div class="modal-content">
			<form method="post" action="<?php echo base_url( "add_beneficiario" ); ?>">
                <?php echo csrf_field() ?>
                <input type="hidden" name="socio"  value="<?php echo $socio->id; ?>">

				<div class="modal-header">
					<h5 class="modal-title">Agregar beneficiario</h5>
					<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
				</div>
				<div class="modal-body">

					<div class="alert alert-info"><i class="fa fa-circle-info"></i> Asegurate que el nombre del beneficiario coincida fielmente a su documentación oficial</div>
					<label for="beneficiario_nuevo">Nombre completo del nuevo beneficiario</label><br>
					<input type="text" class="form-control mb-3" name="beneficiario_nuevo">

					<div class="w-50">
						<label for="beneficiario_porcentaje">Porcentaje</label><br>
						<select class="form-select" name="beneficiario_porcentaje">
							<?php 
							for($a = 100; $a > 0; $a -= 10){
								if( $porc + $a <= 100 )
									echo "\n<option value=\"{$a}\">{$a}%</option>";
							}
							?>
						</select>
					</div>
				</div>
				<div class="modal-footer">
					<button type="submit" class="btn btn-success">Agregar</button>
				</div>
			</form>
		</div>
	</div>
</div>


<div class="modal" tabindex="-1" id="borra_beneficiario">
	<div class="modal-dialog">
		<div class="modal-content">
			<form method="post" action="<?php echo base_url( "cancela_beneficiario" ); ?>">
                <?php echo csrf_field() ?>
                <input type="hidden" name="socio"  value="<?php echo $socio->id; ?>">
				<input type="hidden" name="old_beneficiario"  value="">

				<div class="modal-header">
					<h5 class="modal-title">Eliminar beneficiario</h5>
					<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
				</div>
				<div class="modal-body">
					Al eliminar el beneficiario, su porcentaje asignado se liberará, por lo que deberás asignar uno nuevo para cumplir con el 100% de asignación de tu cuenta.
				</div>
				<div class="modal-footer">
					<button type="submit" class="btn btn-danger">Eliminar</button>
				</div>
			</form>
		</div>
	</div>
</div>


<script>
	var porcentaje = <?php echo $porc; ?>;
</script>
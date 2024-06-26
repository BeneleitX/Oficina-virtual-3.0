<?php 


if( !$socio->data->verificacion->correo ){ ?>
<div class="alert alert-warning"><i class="fa fa-warning"></i> Tu dirección de correo <strong><?php echo $socio->correo; ?></strong> no ha sido validada. Haz <a class="btn btn-warning btn-sm" href="<?php echo base_url( "valida_correo" ) ?>">Click aquí</a> para enviarte un mensaje, abre tu correo y sigue las instrucciones.</div>
<?php } ?>

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
			<div class="col-md-8">
				<div class="row mb-3">
					<div class="col-md-4">
						<label>Nombre</label>
						<input disabled  type="text" class="form-control mb-3" value="<?php echo $socio->data->nombre; ?>">
					</div>
					<div class="col-md-4">
						<label>Primer apellido</label>
						<input disabled  type="text" class="form-control mb-3" value="<?php echo $socio->data->apellidos[0]; ?>">
					</div>
					<div class="col-md-4">
						<label>Segundo apellido</label>
						<input disabled  type="text" class="form-control mb-3" value="<?php echo $socio->data->apellidos[1]; ?>">					
					</div>
					<div class="col-md-4">
						<label>Fecha de nacimiento</label>
						<input disabled  type="text" class="form-control mb-3" value="<?php echo $socio->fechanac; ?>">
					</div>
					<div class="col-md-4">
						<label>CURP</label>
						<input disabled  type="text" class="form-control" value="<?php echo $socio->curp; ?>">			
					</div>
				</div>

				<div class="row">
					<div class="col-md-6">
						<label>Correo electrónico</label>

<div class="input-group mb-3">
  <input type="text" disabled class="form-control" aria-describedby="button-addon2" value="<?php echo $socio->correo; ?>">
  <a data-bs-toggle="tooltip" title="Correo electrónico <?php echo $socio->data->verificacion->correo ? "verificado" : "sin verificar. Click aquí para enviar mensaje de verificación ahora"; ?>" class="btn btn-<?php echo $socio->data->verificacion->correo ? "success" : "danger"; ?>" href="<?php echo base_url( "valida_correo" ); ?>" id="button-addon2"><i class="fa fa-<?php echo $socio->data->verificacion->correo ? "check" : "xmark"; ?>"></i></a>
</div>
					</div>
					<div class="col-md-6">
						<label>Teléfono</label>

<div class="input-group mb-3">
  <input type="text" disabled class="form-control" aria-describedby="button-addon2" value="<?php echo $socio->telefono; ?>">
  <button data-bs-toggle="tooltip" title="Teléfono <?php echo $socio->data->verificacion->telefono ? "verificado" : "sin verificar"; ?>" class="btn btn-<?php echo $socio->data->verificacion->telefono ? "success" : "danger"; ?>" type="button" id="button-addon2"><i class="fa fa-<?php echo $socio->data->verificacion->telefono ? "check" : "xmark"; ?>"></i></button>
</div>

					</div>
				</div>
			</div>
		</div>
	</div>
</div>


<div class="row">
	<div class="col-md-6">

		<?php if( $socio->es_menor() ){
		?>
		<div class="card mb-4">
            <div class="card-header"><h5 class="mb-0">Acta de nacimiento</h5></div>
            <div class="card-body">
				<div class="row">
						<div class="col-4 text-center ct_acta">
							<?php if( $socio->data->credencial->acta ?? false ){ ?>
								<img src="<?php echo base_url()."data/{$socio->id}/ine/{$socio->data->credencial->acta}"; ?>" alt="" class="img-fluid rounded-3">
							<?php 
								if( $socio->data->credencial->estatus <= 0 ) echo "<a href=\"".base_url("cancela_ine/acta")."\" class=\"small\"><i class=\"fa fa-trash\"></i> Cancelar esta foto</a>";
							} else { ?>
								<button tipo="acta" onclick="$( 'input[tipo=acta]' ).click()" class="btn btn-outline-success col-12" style="padding: 200px 0">Cargar foto de <h5 class="text-green m-0">Acta de Nacimiento</h5></button>
							<?php } ?>
						</div>
						<div class="col-8">
						<?php 
				switch( $socio->data->credencial->estatus ){
					case -1 : 
						echo "<div class=\"alert alert-danger\"><i class=\"fa fa-address-card\"></i> Tu Acta de nacimiento fue rechazada por el siguiente motivo: <strong>{$socio->data->credencial->motivo}</strong>. Por favor carga una nueva imagen.</div>";
						break; 
					case 0 : 
						echo "<div class=\"alert alert-danger\"><i class=\"fa fa-address-card\"></i> Verifica tu cuenta cargando una fotografía de tu acta de nacimiento.</div>";
						break; 
					case 1 : 
						echo "<div class=\"alert alert-info\"><i class=\"fa fa-address-card\"></i> Tu Acta de nacimiento se encuentra en proceso de validación.</div>";
						break; 
					case 2 : 
						echo "<div class=\"alert alert-success\"><i class=\"fa fa-address-card\"></i> Tu Acta de nacimiento ha sido validada.</div>";
						break; 
					}												
				 ?>
				 							
						<?php if( $socio->data->credencial->estatus <= 0 ){ ?>
							<input type="file" class="d-none upload" tipo="acta" accept="image/jpeg">

							<h5 class="mt-4">1. Carga de fotografía</h5>
							<p>Click en el botón para carga fotografía de tu Acta de nacimiento. Cancelala si deseas repetir el proceso con una nueva foto.</p>

							<h5>2. Envíala a revisión</h5>
							<p>Click en el botón para enviarla. Personal de la empresa validará los datos y se te notificará cuando hayas terminado el proceso.</p>
							<a class="btn btn-success <?php echo $socio->data->credencial->acta ? "" : "disabled" ?>" id="valida_credencial" href="<?php echo base_url( "valida_credencial" ); ?>"><i class="fa fa-paper-plane"></i> Enviar para validación</a>
						<?php } ?>


						<p class="mt-4"><small><i class="fa fa-circle-info"></i> El <strong>Acta de nacimiento</strong> es el documento que valida las cuentas de menores de edad. Al cumplir 18 años, deberás revalidar tu cuenta con tu credencial de elector.</small></p>

						</div>
					</div>
					

			</div>
        </div>
		<?php 
		} 
		else{
		?>
		<div class="card mb-4">
            <div class="card-header p-0">
				<div class="row">
				<div class="col-6 py-2 px-4"><h5 class="mb-0">Identificación oficial</h5></div>
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
						echo "<div class=\"alert alert-danger\"><i class=\"fa fa-address-card\"></i> Verifica tu cuenta cargando fotografías de tu credencial de elector (INE) por ambos lados.</div>";
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
						<input type="file" class="d-none upload" tipo="frente"  Xaccept="image/jpeg">
						<input type="file" class="d-none upload" tipo="reverso" xaccept="image/jpeg">

						<h5 class="mt-4">1. Carga de fotografías</h5>
						<p>Click en los botones para carga fotografías de tu credencial del INE vigente por ambos lados. Cancelala si deseas repetir el proceso con una nueva foto.</p>

						<h5>2. Envíalas a revisión</h5>
						<p>Click en el botón para enviarlas. Personal de la empresa validará los datos y se te notificará cuando hayas terminado el proceso.</p>
						<a class="btn btn-success <?php echo $socio->data->credencial->frente && $socio->data->credencial->reverso ? "" : "disabled" ?>" id="valida_credencial" href="<?php echo base_url( "valida_credencial" ); ?>"><i class="fa fa-paper-plane"></i> Enviar para validación</a>
					<?php } ?>
			</div>
        </div>
		<?php } ?>


		<div class="card mb-4">
            <div class="card-header"><h5 class="mb-0">Password</h5></div>
            <div class="card-body">	
				<?php
					if(! $socio->data->verificacion->password ){
						echo "<div class=\"alert alert-danger\"><i class=\"fa fa-warning\"></i> IMPORTANTE: Debes reemplazar el password generado al registrarte, por uno propio que te sea fácil de recordar</div>";
					}
				?>
				<form method="post" action="<?php echo base_url( "nuevo_password" ); ?>">
					<?php echo csrf_field() ?>

					<label>Password actual</label>
					<input type="password" name="actual" class="form-control mb-3" value="">

					<ul class="small">
						<li>Debe tener un mínimo de 6 caracteres</li>
						<li>Debe tener al menos un número</li>
						<lI>No uses nombres propios ni fechas</li>
						<li>GUarda una copia en un lugar seguro</li>
					</ul>

					<label>Escribe tu nuevo password</label>
					<input type="password" name="nuevo" class="form-control mb-3" value="">

					<label>Confirma el password</label>
					<input type="password" name="nuevo_bis" class="form-control mb-3" value="">

					<button type="submit" class="btn btn-success">Asignar nuevo password</button>
				</form>
			</div>
		</div>

	</div>

	<div class="col-md-6">
		<div class="card mb-4">
            <div class="card-header"><h5 class="mb-0">CLABE Interbancaria</h5></div>
            <div class="card-body">	
				<form method="post" action="<?php echo base_url( "guarda_clabe" ); ?>">
					<?php echo csrf_field() ?>
					<table class="mb-3">
						<tr>
							<td><img id="clabe_banco" src="<?php echo $socio->banco( true ); ?>" style="height:50px; width:100px"></td>
							<td style="width:100%; padding: 0 20px;"><input name="clabe" id="clabe" style="font-weight:bold" disabled class="form-control m-0 text-center" value="<?php echo $socio->data->clabe; ?>"></td>
							<td class="pt-1"><h5><a href="javascript:edita_clabe()" data-bs-toggle="tooltip" title="Click para editar tu CLABE interbancaria"><i class="fa fa-edit"></i></a></h5></td>
						</tr>
					</table>

					<small><i class="fa fa-circle-info"></i> La <strong>CLABE</strong> interbancaria se compone de 18 dígitos y es un requisito indispensable para la operación de tu oficina virtual.</small>	
					<div id="nota_clabe" style="display:none" class="mt-3">
						<h5>Actualizar CLABE interbancaria</h5>
						<p>Proporciona tu clabe interbancaria a 18 dígitos. Esta CLABE debe pertenecer a una cuenta bancaria de la que seas titular y se encuentre activa. Al terminar haz click en el botón.</p>
						<button type="submit" class="btn btn-success"><i class="fa fa-save"></i> Guardar cambios</button>
					</div>
				</form>
			</div>
		</div>


		<div class="card mb-4">
            <div class="card-header"><h5 class="mb-0">Declaración de impuestos</h5></div>
            <div class="card-body">	
				<table><tr><td>
					<input id="check_sat" type="checkbox" style="transform: scale(3); margin:0 15px" <?php if( $socio->data->sat->estatus == 0) echo "checked"; ?> <?php if( $socio->data->sat->csf ?? false ) echo "disabled"; ?>>
				</td><td style="padding-left:10px" <?php if( $socio->data->sat->csf ) echo "class=\"text-gray-500\""; ?>> <strong>SI,</strong> estoy de acuerdo en que BENELEIT se haga cargo de la declaración de impuestos generados por los ingresos residuales obtenidos a mi nombre. 
				</td></tr></table>
				<div id="sube_csf" class="alert alert-<?php echo !$socio->data->sat->csf ? "warning" : "success"; ?> mt-3 mb-0" <?php if( $socio->data->sat->estatus == 0) echo "style=\"display:none\""; ?>>
					<p><i class="fa fa-warning"></i> <strong>IMPORTANTE:</strong> Al desmarcar la casilla, estas aceptando la responsabilidad de tu propia declaración obligatoria de impuestos ante el SAT. Para completar la activación de esta opción, debes proporcionarnos tu Constancia de Situación Fiscal reciente. Para cancelar la opción y aceptar que BENELEIT se haga cargo, simplemente cancela tu constancia y marca de nuevo la casilla.</p>

					<?php if( $socio->data->sat->csf ){ ?>
						<table><tr>
							<td class="text-end pe-3">
								<a href="<?php echo base_url()."data/{$socio->id}/csf/{$socio->data->sat->csf}"; ?>" target="_blank"><img src="<?php echo base_url(); ?>assets/img/csf.png" style="width:50%" class="rounded-3 border border-5 border-white"></a>
							</td>
							<td>
								<p>La Constancia de Situación Fiscal ha sido recibida. La opción de declaración de impuestos por parte de la empresa ha quedado deshabilitada.</p>
								<a href="<?php echo base_url("cancela_csf"); ?>" class="small"><i class="fa fa-trash"></i> Cancelar constancia</a></td>
					</tr></table>
					<?php } else { ?>
						<button tipo="frente" onclick="$( '#carga_csf' ).click()" class="btn bg-white btn-outline-success col-6 xoffset-3 py-4 mt-3">Cargar <h5 class="text-green m-0">Constancia de Situación Fiscal</h5>reciente</button>
					<?php } ?>

					<input type="file" class="d-none" id="carga_csf" accept="application/pdf">
				</div>
			</div>
		</div>	


		<div class="card mb-4">
            <div class="card-header"><h5 class="mb-0">Domicilios</h5></div>
            <div class="card-body">	
				<div class="row">
					<?php
						$domicilios = $socio->getDomicilios();

						if( sizeof($domicilios) ){
							foreach( $domicilios as $d ){
								echo "
									<div class=\"col-xl-12  mb-3\">
										<div class=\"alert alert-info mb-0\">
											<h5>{$d[ "nombre" ]} <a style=\"float:right\" class=\"text-teal\" href=\"#\"><i class=\"fa fa-edit\"></i></a></h5>
											
											<p class=\"mb-0\">
												{$d[ "calleynumero" ]}<br>
												Colonia {$d[ "colonia" ]}<br>
												{$d[ "localidad" ]}, {$d[ "entidad" ]}<br>
												C.P. {$d[ "codigopostal" ]}
											</p>
										</div>
									</div>
								";
							}
						}
						else{
							// echo "<div class=\"col-xl-12  mb-3\">x</div>";
						}
					?>
				</div>

				<button class="btn btn-success" id="nuevo_domicilio"><i class="fa fa-plus"></i> Agregar domicilio</button>
			</div>
		</div>	
		
		<div class="card mb-4">
            <div class="card-header"><h5 class="mb-0">Beneficiarios de la cuenta</h5></div>
			<div class="card-body">	

			<div class="card mb-3 mb-0">	
			<table class="table align-middle mb-0">
				<?php 

					foreach( $socio->data->beneficiarios as $k => $b ){
						echo "\n<tr>
							<td>{$b->nombre}</td>
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
			
			<small><i class="fa fa-circle-info"></i> Un <strong>beneficiario</strong> es una persona designada por el socio titular, que heredaría los derechos (o un porcentaje de ellos) sobre su cuenta, su red y sus ingresos pasivos, en dado caso de que el titular llegue a fallecer.</small>
			</div>
		</div>			
	</div>
</div>



<div class="card border-red mt-3">
	<div class="card-header">
		<h5 class="text-red mb-0">Administración de socio</h5>
	</div>
	<div class="card-body">
		<a class="btn btn-danger" href="<?php echo base_url( "bitacora/".$socio->id ); ?>"><i class="fa fa-magnifying-glass"></i> Ver bitácora de movimientos</a>
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

						if( $codigo == "csf" && $socio->data->sat->estatus == 0){
							echo "<p class=\"text-gray-500\"><i class=\"fas fa-square-xmark text-gray\"></i> {$punto} <span class=\"badge bg-red\">no aplica</span></p> ";
						}
						else{
							if( $socio->data->verificacion->{$codigo} ){
								echo "<p class=\"\"><i class=\"fas fa-square-check text-teal\"></i> {$punto}</p>";
							}
							else{
								echo "<p class=\"text-gray-500\"><i class=\"far fa-square\"></i> {$punto}</p>"; // $socio->data->verificacion
							}
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
					<button type="submit" class="btn btn-danger"><i class="fa fa-trash"></i> Eliminar</button>
				</div>
			</form>
		</div>
	</div>
</div>



<div class="modal" tabindex="-1" id="modal_domicilio">
	<div class="modal-dialog modal-lg">
		<div class="modal-content">

			<div class="modal-header">
				<h5 class="modal-title">Domicilio</h5>
				<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
			</div>
			<div class="modal-body">
				<form id="form_domicilio">

					<?php echo csrf_field() ?>
					<input type="hidden" name="dom_socio"  value="<?php echo $socio->id; ?>">
					<input type="hidden" name="dom_id"  value="">
					<input type="hidden" name="n_localidad_id"  value="">
					<input type="hidden" name="tipo_colonia"  value="select">
					<input type="hidden" name="n_entidad_id"  value="">
					<div class="row">
						<div class="col-md-4">
							<label>Nombre</label>
							<input required type="text" name="n_nombre" class="form-control mb-3" value="">
							</div>

							<div class="col-md-8 pt-3">
							<small><i class="fa fa-circle-info"></i> Cada domicio que agregues debe estar identificado con un nombre.<br>Por ejemplo: "Mi casa", "oficina", "casa de luis", etc.</small>

						</div>
					</div>
					<div class="row">
						<div class="col-md-8">
							<label>Calle y número exterior/interior</label>
							<input required type="text"  name="n_calle"  class="form-control mb-3" value="">
						</div>

						<div class="col-md-4">
							<label>Código postal</label>
							<input required type="text"  name="n_cp" id="getCP" class="form-control mb-3" value="">
						</div>

						<div class="col-md-4" id="colonia_select">
							<label>Colonia</label>
							<select required disabled id="n_colonia" name="n_colonia" class="form-select mb-3">

							</select>
						</div>

						<div class="col-md-4" id="colonia_nueva" style="display:none">
							<label>Colonia</label>
							<input id="n_colonia_nueva" name="n_colonia_nueva" class="form-control mb-3">
						</div>
						
						<div class="col-md-4">
							<label>Localidad</label>
							<input disabled type="text" id="n_localidad" class="form-control mb-3" value="">
						</div>

						<div class="col-md-4">
							<label>Entidad</label>
							<input disabled type="text" id="n_entidad" class="form-control mb-3" value="">
						</div>

						<div class="col-md-12 small mb-3" id="aviso_colonia_select"><i class="fa fa-circle-info"></i> Si tu colonia no aparece en el listado después de escribir tu código postal, <a href="javascript:agrega_colonia()">haz click aquí</a> para agregarla.</div>

						<div class="col-md-12 small mb-3 text-red" id="aviso_colonia_nueva" style="display:none"><i class="fa fa-warning"></i> Escribe el nombre de tu colonia exactamente como viene en los recibos de tus servicios o estados de cuenta bancarios. Para regresar al listado de colonias existentes en tu código postal <a href="javascript:regresar_colonia()">haz click aquí</a>.</div>

						<div class="col-md-12">
							<label>Referencias adicionales</label>
							<input type="text"  name="n_referencias" class="form-control mb-3" value="">
						</div>
					</div>
				</form>
				
			</div>
			<div class="modal-footer">
				<button id="submit_domicilio" class="btn btn-success"><i class="fa fa-check"></i> Guardar</button>
			</div>
			
		</div>
	</div>
</div>


<script>
	var porcentaje = <?php echo $porc; ?>;
</script>

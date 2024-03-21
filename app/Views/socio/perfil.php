<h4 class="mt-1 mb-3"><?php echo $titulo; ?></h4>

<div class="row mb-3">
	<div class="col-md-8">
		<ul class="nav nav-pills">
			<li class="nav-item"><a class="nav-link active" aria-current="page" href="#">Datos generales</a></li>
			<li class="nav-item"><a class="nav-link position-relative" href="#">Domicilios <span class="badge text-white bg-gray-400">0</span></a></li>
			<li class="nav-item"><a class="nav-link" href="#">Ajustes</a></li>
		</ul>
	</div>

	<div class="col-md-4 mt-2">
  		<table width="100%"><tr><td width="50%">
		  	<div class="progress bg-white" role="progressbar" aria-label="Animated striped example" aria-valuenow="20" aria-valuemin="0" aria-valuemax="100">
				<div class="progress-bar progress-bar-striped progress-bar-animated bg-mustard" style="width: 20%"></div>
			</div>
		</td><td width="50%" class="text-end">
			<small><i class="fas fa-triangle-exclamation text-mustard"></i> Verificación de la cuenta al 20%</small>
		</td></tr></table>
	</div>
</div>


<div class="card mb-4">
	<div class="card-body">
		<div class="row">
		<div class="col-md-4 p-4 position-relative text-center">
			<div id="imagencontenedor">
				<div id="imagen_avatar"><?php echo $usuario->avatar( 200 ); ?></div>
			</div>
			<div id="cambia_avatar" style="display:none" class="position-absolute top-50 start-50 text-center translate-middle"><a class="btn btn-sm btn-primary" href="<?php echo base_url()."fotografia"; ?>"><i class="fa fa-edit"></i> Cambiar foto</a></div>
		</div>
		<div class="col-md-4"></div>
		<div class="col-md-4"></div>
		</div>
	</div>
</div>


<div class="row">
	<div class="col-6">
	<div class="card mb-4">
            <div class="card-header">
                Identificación oficial
            </div>
            <div class="card-body">
				<div class="alert alert-warning"><i class="fa fa-address-card"></i> Verifica tu cuenta cargando fotografías de tu credencial de elector (INE) por ambos lados.</div>
				<label>Frente de la credencial</label>
				<input type="file" class="form-control mb-3">
				<label>Reverso de la credencial</label>
				<input type="file" class="form-control">
			</div>
        </div>
	</div>
</div>

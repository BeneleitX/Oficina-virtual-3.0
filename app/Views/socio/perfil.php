    <h4 class="mt-1 mb-3"><?php echo $titulo; ?></h4>

    <ul class="nav nav-pills mb-3">
  <li class="nav-item">
    <a class="nav-link active" aria-current="page" href="#">Datos generales</a>
  </li>
  <li class="nav-item">
    <a class="nav-link position-relative" href="#">Domicilios
	<span class="badge text-white bg-red">1</span>
	</a>
  </li>
  <li class="nav-item">
    <a class="nav-link" href="#">Ajustes</a>
  </li>
</ul>


<div class="card mb-4">
            <div class="card-body">
				<div class="row">
				<div class="col-md-4">
					<?php echo $usuario->avatar(); ?>
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
                Cuenta sin verificar
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

<h4 class="mt-1 mb-3"><?php echo $titulo; ?></h4>

<ul class="nav nav-pills">
	<li class="nav-item">
    	<a class="nav-link active" aria-current="page" href="#">Nutrición</a>
  	</li>
  	<li class="nav-item">
    	<a class="nav-link" href="#">Telefonía</a>
  	</li>
  	<li class="nav-item">
    	<a class="nav-link" href="#">Alimentos</a>
  	</li>
</ul>


<div  class="text-center">
	<a href="#" data-bs-toggle="offcanvas" data-bs-target="#offcanvasWithBothOptions">
		<p style="margin-top:100px"><i class="fa fa-cart-plus" style="font-size:200px; color: var(--bs-gray-300)"></i></p>
		<h3 style="color: var(--bs-gray-300)">Tu carrito de compras de Nutrición está vacío</h3>
		Agrega productos ahora
	</a>
</div>

<div class="offcanvas offcanvas-bottom bg-light" style="height:70%" data-bs-scroll="true" tabindex="-1" id="offcanvasWithBothOptions">
 	<div class="offcanvas-header">
    	<div class="offcanvas-title flex-grow-1">
			<div class="row">
				<div class="col-6 col-md-3">
					<h5>Productos disponibles</h5>
				</div>
				<div class="col-6 col-md-3">
					<div class="input-group mb-3">
						<span class="input-group-text"><i class="fa fa-magnifying-glass"></i></span>
						<input type="text" class="form-control" placeholder="Buscar productos" id="busca_producto">
					</div>
				</div>
			</div>
		</div>
    	<button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="Close"></button>
  	</div>

  	<div class="offcanvas-body ">
  		<div class="container-fluid p-0">
			<div class="row">

				<?php 
				foreach( $productos as $p ){

					echo "<div class=\"col-sm-6 col-md-4\" producto=\"{$p->codigo}\">
					<div class=\"card mb-3\" style=\"max-width: 540px;\">
						<div class=\"row g-0\">
							<div class=\"col-md-4\">
							<img src=\"".base_url()."assets/img/productos/{$p->codigo}.png\" class=\"img-fluid rounded-start\">
							</div>
							<div class=\"col-md-8\">
							<div class=\"card-body\">
								<h5 class=\"card-title\">".strtoupper( $p->data->nombre )."</h5>
								<p class=\"card-text\">$ ".number_format( $p->precio->total, 2)."</p>
								<p class=\"card-text text-end\"><button class=\"btn btn-outline-success\"><i class=\"fa fa-plus\"></i></button</p>
							</div>
							</div>
						</div>
						</div>
					</div>";
				}
				?>

			</div>
    	</div>
    </div>
	</div>


</div>
<div id="contenedor-menu" class="<?php echo session( "admin" ) && session( "admin" ) != urlencode( base64_encode( $usuario->password_original() ) ) ? "contenedor-menu-admin" : ""; ?>">
<div class="dropdown dropup" style="display: inline;">
  <a class="menu-opcion avatar" type="button" data-bs-toggle="dropdown" aria-expanded="false">
  <!-- <div class="pie-chart" style="	background:
		radial-gradient(
			circle closest-side,
			transparent 100%,
			#2e2e2e 0
		),
		conic-gradient(
			#2e2e2e 0,
			#2e2e2e 38%,
			#57595e 0, 
			#57595e 100%
	); "></div>   -->
  <?php echo $usuario->avatar(); ?>
  
    </a>
  <ul class="dropdown-menu">
    <li>
        <a class="dropdown-item text-center" href="<?php echo base_url( "perfil"); ?>" style="background:white !important">

			<table><tr>
				<td><?php echo $usuario->avatar( 120 ); ?></td>
				<td><?php echo $usuario->rango( 150 ); ?></td>
				</tr></table>
			

			<p class="m-0 mt-3 text-center"><?php echo $usuario->nombre(2); ?></p>
			<h1 class="text-center"><?php echo $usuario->id( null, "marine" ); ?></h1>
        </a>
    </li>
	<li><hr class="dropdown-divider"></li>
    <li class="d-none"><a class="dropdown-item" href="<?php echo base_url( "inicio"); ?>"><i class="fa fa-house"></i> Inicio</a></li>
    <li><a class="dropdown-item" href="<?php echo base_url( "perfil"); ?>"><i class="fa fa-user"></i> Perfil de socio</a></li>
	<li><hr class="dropdown-divider"></li>
    
	<?php 
	if( 0 && session( "admin" ) && session( "admin" ) != urlencode( base64_encode( $usuario->password_original() ) ) ){
		echo "<li><a class=\"dropdown-item\" href=\"".base_url( "oauth/".session( "admin" ) )."\"><button class=\"btn btn-danger\"><i class=\"fa fa-undo\"></i> Regresar a sesión de ADMIN</button></a></li><li><hr class=\"dropdown-divider\"></li>";
	}
	?>
    <li><a class="dropdown-item" href="<?php echo base_url( "logout" ); ?>"><i class="fa fa-right-from-bracket"></i> Cerrar sesión</a></li>
  </ul>
</div>

	<a data-bs-toggle="tooltip" 
		title="Inicio" class="menu-opcion <?php echo $menu == "inicio" ? "selected" : ""; ?>" 
		href="<?php echo base_url( "inicio" ); ?>">
		<i class="fa fa-house-chimney-user"></i>
	</a>

    <a data-bs-toggle="tooltip" 
		title="Balance" class="menu-opcion <?php echo $menu == "ingresos" ? "selected" : ""; ?>" 
		href="<?php echo base_url( "balance/".getModeloPrincipal()."/".codigo_periodo( getModeloPrincipal(), date("Y-m-d") ) ); ?>">
		<i class="fa fa-sack-dollar"></i>
	</a>

    <a data-bs-toggle="tooltip" 
		title="Compras" class="menu-opcion <?php echo $menu == "tienda" ? "selected" : ""; ?>" 
		href="<?php echo base_url( "historial/".getModeloPrincipal() ); ?>">
		<i class="fa fa-cart-shopping"></i>
	</a>

    <a data-bs-toggle="tooltip" 
		title="Redes" class="menu-opcion <?php echo $menu == "redes" ? "selected" : ""; ?>" 
		href="<?php echo base_url( "red/".getModeloPrincipal() ); ?>">
		<i class="fa fa-sitemap"></i>
	</a>

	<a data-bs-toggle="tooltip" 
		title="Ayuda y soporte" class="menu-opcion <?php echo $menu == "soporte" ? "selected" : ""; ?>" 
		href="<?php echo base_url( "soporte" ); ?>">
		<i class="fa fa-circle-question"></i>
	</a>

	<?php 
	if( $usuario->es_admin() ){ ?>
    <a data-bs-toggle="tooltip" 
		title="Administración" class="menu-opcion <?php echo $menu == "admin" ? "selected" : ""; ?>" 
		href="<?php echo base_url( "admin" ); ?>">
		<i class="fa fa-gear"></i>
	</a>
	<?php } 
	
	if( session( "admin" ) && session( "admin" ) != urlencode( base64_encode( $usuario->password_original() ) ) ){
		?>	
		<a data-bs-toggle="tooltip" 
	title="Regresar a sesión de Admin" class="menu-opcion" href="<?php echo base_url( "oauth/".session( "admin" ) ); ?>">
	<i class="fa fa-shuffle xtext-marine"></i>
	</a>
	<?php
	}

	?>


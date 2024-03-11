</div>
<div id="contenedor-menu" class="">
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
  <img src="<?php echo base_url(); ?>data/usuarios/666/img/avatar/iCezEnwD_400x400.jpg">
    </a>
  <ul class="dropdown-menu">
    <li>
        <a class="dropdown-item d-none" href="#">
            <div style="width:200px; height:200px; border:1px solid gray; border-radius:8px;">
            </div>
        </a>
    </li>
    <li><a class="dropdown-item" href="<?php echo base_url( "perfil" ); ?>"><i class="fa fa-user"></i> Perfil de socio</a></li>
    <li><a class="dropdown-item" href="<?php echo base_url( "logout" ); ?>"><i class="fa fa-right-from-bracket"></i> Cerrar sesión</a></li>
  </ul>
</div>

    <a data-bs-toggle="tooltip" title="Inicio" class="menu-opcion selected" href="<?php echo base_url( "inicio" ); ?>"><i class="fa fa-house"></i></a>
    <a data-bs-toggle="tooltip" title="Recompensas" class="menu-opcion" href="#"><i class="fa fa-award"></i></a>
    <a data-bs-toggle="tooltip" title="Balance" class="menu-opcion" href="#"><i class="fa fa-sack-dollar"></i></a>
    <a data-bs-toggle="tooltip" title="Tienda" class="menu-opcion" href="<?php echo base_url( "tienda" ); ?>"><i class="fa fa-cart-shopping"></i></a>
    <a data-bs-toggle="tooltip" title="Redes" class="menu-opcion" href="#"><i class="fa fa-diagram-project"></i></a>
    <a data-bs-toggle="tooltip" title="Comunicación" class="menu-opcion" href="#"><i class="fa fa-comment-dots"></i></a>            

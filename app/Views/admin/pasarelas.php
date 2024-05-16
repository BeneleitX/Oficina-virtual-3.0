<link href="<?php echo base_url(); ?>assets/css/datatables.css" rel="stylesheet"/>
<script src="<?php echo base_url(); ?>assets/js/datatables.js" type="text/javascript"></script>
<script src="<?php echo base_url(); ?>assets/js/datatables_bs5.js" type="text/javascript"></script>

<h4 class="mt-1 mb-0"><?php echo $titulo; ?></h4>
<p><a href="<?php echo base_url( "admin" ); ?>"><i class="fa fa-undo"></i> Regresar a configuración</a></p>


		<ul class="nav nav-pills mb-3">
			<?php 
			foreach( MODELOS as $m ){
				if( $m[ "settings" ][ "efectivo" ] ){
					echo "\n<li class=\"nav-item\"><a class=\"nav-link ".( $modelo == $m[ "codigo" ] ? "active" : "")."\" aria-current=\"page\" href=\"".base_url( "pasarelas/".$m[ "codigo" ] )."\"><i class=\"fa fa-{$m[ "settings" ][ "icono" ]}\"></i> {$m[ "nombre" ]}</a></li>";
				}
			}
			?>
		</ul>

<table class="table table-striped bg-white" id="tabla_pasarelas">
    <thead>
        <tr>
            <th>Código</th>
            <th>Nombre</th>
            <th>Estatus</th>
            <th>Comisión</th>
            <th></th>
        </tr>
    </thead>

    <tbody>
        <?php 
            foreach( $pasarelas as $metodopago ){
                echo "\n<tr pasarela=\"{$metodopago[ "codigo" ]}\">
                    <td>{$metodopago[ "codigo" ]}</td>
                    <td>{$metodopago[ "nombre" ]}</td>
                    <td>".estatus( $metodopago[ "estatus_codigo" ] )."</td>
                    <td class=\"text-end\">$".number_format( $metodopago[ "settings" ][ "comision" ], 2 )."</td>
                    <td class=\"text-end\"><a href=\"".base_url( "promo_detalle/".$metodopago[ "codigo" ] )."\" class=\"btn btn-xs btn-primary\">DETALLES</a></td>
                </tr>";
            }
        ?>
     
    </tbody>
</table>

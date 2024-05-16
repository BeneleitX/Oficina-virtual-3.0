<link href="<?php echo base_url(); ?>assets/css/datatables.css" rel="stylesheet"/>
<script src="<?php echo base_url(); ?>assets/js/datatables.js" type="text/javascript"></script>
<script src="<?php echo base_url(); ?>assets/js/datatables_bs5.js" type="text/javascript"></script>

<h4 class="mt-1 mb-0"><?php echo $titulo; ?></h4>
<p><a href="<?php echo base_url( "admin" ); ?>"><i class="fa fa-undo"></i> Regresar a configuración</a></p>


		<ul class="nav nav-pills mb-3">
			<?php 
			foreach( MODELOS as $m ){
				if( $m[ "settings" ][ "efectivo" ] ){
					echo "\n<li class=\"nav-item\"><a class=\"nav-link ".( $modelo == $m[ "codigo" ] ? "active" : "")."\" aria-current=\"page\" href=\"".base_url( "rangos/".$m[ "codigo" ] )."\"><i class=\"fa fa-{$m[ "settings" ][ "icono" ]}\"></i> {$m[ "nombre" ]}</a></li>";
				}
			}
			?>
		</ul>

<table class="table table-striped bg-white" id="tabla_rangos">
    <thead>
        <tr>
            <th>Código</th>
            <th>Nombre</th>
            <th>Activos</th>
            <th>Inactivos</th>
            <th>Desde</th>
            <th>Hasta</th>
            <th></th>
        </tr>
    </thead>

    <tbody>
        <?php 
            foreach( $rangos as $rango ){
                $activos = isset( $socios[ $rango[ "codigo" ] ][ "activos"] ) ? $socios[ $rango[ "codigo" ] ][ "activos"] : 0;
                $inactivos = isset( $socios[ $rango[ "codigo" ] ][ "inactivos"] ) ? $socios[ $rango[ "codigo" ] ][ "inactivos"] : 0;

                echo "\n<tr rango=\"{$rango[ "codigo" ]}\">
                    <td><span class=\"badge bg-{$rango[ "color" ]}\">{$rango[ "codigo" ]}</span></td>
                    <td>{$rango[ "nombre" ]}</td>
                    <td>{$activos}</td>
                    <td>{$inactivos}</td>
                    <td class=\"text-end\">".number_format( $rango[ "cantidades" ][0], 2 )."</td>
                    <td class=\"text-end\">".number_format( $rango[ "cantidades" ][1], 2 )."</td>
                    <td class=\"text-end\"><a href=\"".base_url( "promo_detalle/".$rango[ "codigo" ] )."\" class=\"btn btn-xs btn-primary\">DETALLES</a></td>
                </tr>";
            }
        ?>
     
    </tbody>
</table>

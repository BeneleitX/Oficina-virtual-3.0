<link href="<?php echo base_url(); ?>assets/css/datatables.css" rel="stylesheet"/>
<script src="<?php echo base_url(); ?>assets/js/datatables.js" type="text/javascript"></script>
<script src="<?php echo base_url(); ?>assets/js/datatables_bs5.js" type="text/javascript"></script>

<h4 class="mt-1 mb-0"><?php echo $titulo; ?></h4>
<p><a href="<?php echo base_url( "admin" ); ?>"><i class="fa fa-undo"></i> Regresar a configuración</a></p>


		<ul class="nav nav-pills mb-3">
			<?php 
			foreach( MODELOS as $m ){
				if( $m[ "settings" ][ "efectivo" ] ){
					echo "\n<li class=\"nav-item\"><a class=\"nav-link ".( $modelo == $m[ "codigo" ] ? "active" : "")."\" aria-current=\"page\" href=\"".base_url( "productos/".$m[ "codigo" ] )."\"><i class=\"fa fa-{$m[ "settings" ][ "icono" ]}\"></i> {$m[ "nombre" ]}</a></li>";
				}
			}
			?>
		</ul>

<table class="table table-striped bg-white" id="tabla_productos">
    <thead>
        <tr>
            <th></th>
            <th>Nombre</th>
            <th>Descripcion</th>
            <th>Estatus</th>
            <th class="text-end">Precio</th>
            <th></th>
        </tr>
    </thead>

    <tbody>
        <?php 
            foreach( $productos as $producto ){

                echo "\n<tr producto=\"{$producto->codigo}\">
                    <td class=\"text-center\"><img style=\"width:30px; height:30px; \" src=\"".base_url()."assets/img/productos/".($producto->data->avatar ? $producto->codigo : "NO-IMAGEN").".png\"></td>
                    <td>".strtoupper( $producto->data->nombre )."</td>
                    <td>{$producto->data->descripcion}</td>
                    <td>".estatus( $producto->estatus_codigo )."</td>
                    <td class=\"text-end\">$".number_format( $producto->precio->total, 2 )."</td>
                    <td class=\"text-end\"><a href=\"".base_url( "promo_detalle/".$producto->codigo )."\" class=\"btn btn-xs btn-primary\">DETALLES</a></td>
                </tr>";
            }
        ?>
     
    </tbody>
</table>

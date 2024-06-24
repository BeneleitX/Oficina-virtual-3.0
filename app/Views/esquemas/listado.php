<link href="<?php echo base_url(); ?>assets/css/datatables.css" rel="stylesheet"/>
<script src="<?php echo base_url(); ?>assets/js/datatables.js" type="text/javascript"></script>
<script src="<?php echo base_url(); ?>assets/js/datatables_bs5.js" type="text/javascript"></script>

<h4 class="mt-1 mb-0"><?php echo $titulo; ?></h4>
<p><a href="<?php echo base_url( "admin" ); ?>"><i class="fa fa-undo"></i> Regresar a configuración</a></p>


		<ul class="nav nav-pills mb-3">
			<?php 
			foreach( MODELOS as $m ){
                echo "\n<li class=\"nav-item\"><a class=\"nav-link ".( $modelo == $m[ "codigo" ] ? "active" : "")."\" aria-current=\"page\" href=\"".base_url( "esquemas/".$m[ "codigo" ] )."\"><i class=\"fa fa-{$m[ "settings" ][ "icono" ]}\"></i> {$m[ "nombre" ]}</a></li>";
			}
			?>
		</ul>

<table class="table table-striped bg-white" id="tabla_paqueterias">
    <thead>
        <tr>
            <th>Código</th>
            <th>Estatus</th>
            <th>Nombre</th>
            <th>Inicia</th>
            <th>Termina</th>
            <th></th>
        </tr>
    </thead>

    <tbody>
        <?php 
            foreach( ESQUEMAS as $e ){

                echo "\n<tr paqueteria=\"{$e[ "codigo" ]}\">
                    <td><span class=\"badge bg-marine\">{$e[ "codigo" ]}</span></td>
                    <td>".estatus( $e[ "estatus_codigo" ] )."</td>
                    <td>{$e[ "settings" ][ "titulo" ]}</td>
                    <td class=\"text-center\">{$e[ "inicia" ]}</td>
                    <td class=\"text-center\">{$e[ "termina" ]}</td>
                    <td class=\"text-end\"><a href=\"".base_url( "paqueteria/".$e[ "codigo" ] )."\" class=\"btn btn-xs btn-primary\">DETALLES</a></td>
                </tr>";
            }
        ?>
     
    </tbody>
</table>

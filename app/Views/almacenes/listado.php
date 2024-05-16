<link href="<?php echo base_url(); ?>assets/css/datatables.css" rel="stylesheet"/>
<script src="<?php echo base_url(); ?>assets/js/datatables.js" type="text/javascript"></script>
<script src="<?php echo base_url(); ?>assets/js/datatables_bs5.js" type="text/javascript"></script>

<h4 class="mt-1 mb-0"><?php echo $titulo; ?></h4>
<p><a href="<?php echo base_url( "admin" ); ?>"><i class="fa fa-undo"></i> Regresar a configuración</a></p>


		<ul class="nav nav-pills mb-3">
			<?php 
			foreach( MODELOS as $m ){
				if( $m[ "settings" ][ "efectivo" ] ){
					echo "\n<li class=\"nav-item\"><a class=\"nav-link ".( $modelo == $m[ "codigo" ] ? "active" : "")."\" aria-current=\"page\" href=\"".base_url( "almacenes/".$m[ "codigo" ] )."\"><i class=\"fa fa-{$m[ "settings" ][ "icono" ]}\"></i> {$m[ "nombre" ]}</a></li>";
				}
			}
			?>
		</ul>

<table class="table table-striped bg-white" id="tabla_almacenes">
    <thead>
        <tr>
            <th>Código</th>
            <th>Nombre</th>
            <th>Estatus</th>
            <th>Socio</th>
            <th>Pedidos</th>
            <th>Tipo</th>
            <th></th>
        </tr>
    </thead>

    <tbody>
        <?php 
            foreach( $almacenes as $a ){
                $a[ "settings"  ] = json_decode( $a[ "settings"  ], true );
                $a[ "productos" ] = json_decode( $a[ "productos" ], true );

                $a[ "socio" ] = new \App\Entities\E_usuario( $a[ "settings" ][ "socio" ], $a[ "socio" ] );

                echo "\n<tr almacen=\"{$a[ "codigo" ]}\">
                    <td>{$a[ "codigo" ]}</td>
                    <td>{$a[ "nombre" ]}</td>
                    <td>".estatus( $a[ "estatus_codigo" ] )."</td>
                    <td>".$a[ "socio" ]->avatar(24)." ".$a[ "socio" ]->nombre(2)."</td>
                    <td class=\"text-center\">".( $a[ "pedidos" ] > 0 ? "<strong>{$a[ "pedidos" ]}</strong>" : "-")."</td>
                    <td class=\"text-center\">{$a[ "settings" ][ "tipo" ]}</td>
                    <td class=\"text-end\"><a href=\"".base_url( "almacen/".$a[ "codigo" ] )."\" class=\"btn btn-xs btn-primary\">DETALLES</a></td>
                </tr>";
            }
        ?>
     
    </tbody>
</table>

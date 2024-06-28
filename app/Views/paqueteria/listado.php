<link href="<?php echo base_url(); ?>assets/css/datatables.css" rel="stylesheet"/>
<script src="<?php echo base_url(); ?>assets/js/datatables.js" type="text/javascript"></script>
<script src="<?php echo base_url(); ?>assets/js/datatables_bs5.js" type="text/javascript"></script>

<h4 class="mt-1 mb-0"><?php echo $titulo; ?></h4>
<p><a href="<?php echo base_url( "admin" ); ?>"><i class="fa fa-undo"></i> Regresar a configuración</a></p>

<?php echo pills( "paqueterias", $modelo ); ?>

<table class="table table-striped bg-white" id="tabla_paqueterias">
    <thead>
        <tr>
            <th>Código</th>
            <th>Nombre</th>
            <th>Costo por bulto</th>
            <th>Gramos por bulto</th>
            <th>Estatus</th>
            <th>Pedidos</th>
            <th></th>
        </tr>
    </thead>

    <tbody>
        <?php 
            foreach( $paqueterias as $a ){
                $a[ "settings"  ] = json_decode( $a[ "settings"  ], true );

                echo "\n<tr paqueteria=\"{$a[ "codigo" ]}\">
                    <td><span class=\"badge bg-marine\">{$a[ "codigo" ]}</span></td>
                    <td>{$a[ "nombre" ]}</td>
                    <td class=\"text-center\">$".number_format( $a[ "settings" ][ "costo" ], 2 )."</td>
                    <td class=\"text-center\">".number_format( $a[ "settings" ][ "gramaje" ] )."</td>
                    <td>".estatus( $a[ "estatus_codigo" ] )."</td>
                    <td class=\"text-center\">".( $a[ "pedidos" ] > 0 ? "<strong>{$a[ "pedidos" ]}</strong>" : "-")."</td>
                    <td class=\"text-end\"><a href=\"".base_url( "paqueteria/".$a[ "codigo" ] )."\" class=\"btn btn-xs btn-primary\">DETALLES</a></td>
                </tr>";
            }
        ?>
     
    </tbody>
</table>

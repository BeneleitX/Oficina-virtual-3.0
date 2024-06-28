<link href="<?php echo base_url(); ?>assets/css/datatables.css" rel="stylesheet"/>
<script src="<?php echo base_url(); ?>assets/js/datatables.js" type="text/javascript"></script>
<script src="<?php echo base_url(); ?>assets/js/datatables_bs5.js" type="text/javascript"></script>

<h4 class="mt-1 mb-0"><?php echo $titulo; ?></h4>
<p><a href="<?php echo base_url( "admin" ); ?>"><i class="fa fa-undo"></i> Regresar a configuración</a></p>

<?php echo pills( "paqueteria", $modelo ); ?>

<table class="table table-striped bg-white" id="tabla_paqueteria">
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
            foreach( $paqueterias as $metodoentrega ){
                echo "\n<tr paqueteria=\"{$metodoentrega[ "codigo" ]}\">
                    <td>{$metodoentrega[ "codigo" ]}</td>
                    <td>{$metodoentrega[ "nombre" ]}</td>
                    <td>".estatus( $metodoentrega[ "estatus_codigo" ] )."</td>
                    <td class=\"text-end\">$".number_format( $metodoentrega[ "settings" ][ "costo" ], 2 )."</td>
                    <td class=\"text-end\"><a href=\"".base_url( "promo_detalle/".$metodoentrega[ "codigo" ] )."\" class=\"btn btn-xs btn-primary\">DETALLES</a></td>
                </tr>";
            }
        ?>
     
    </tbody>
</table>

<link href="<?php echo base_url(); ?>assets/css/datatables.css" rel="stylesheet"/>
<script src="<?php echo base_url(); ?>assets/js/datatables.js" type="text/javascript"></script>
<script src="<?php echo base_url(); ?>assets/js/datatables_bs5.js" type="text/javascript"></script>

<h4 class="mt-1 mb-0"><?php echo $titulo; ?></h4>
<p><a href="<?php echo base_url( "admin" ); ?>"><i class="fa fa-undo"></i> Regresar a configuración</a></p>

<?php echo pills( "pasarelas", $modelo ); ?>

<table class="table table-striped bg-white" id="tabla_pasarelas">
    <thead>
        <tr>
            <th>Código</th>
            <th>Nombre</th>
            <th>Descripcion</th>
            <th>Estatus</th>
            <th>Comisión</th>
            <th></th>
        </tr>
    </thead>

    <tbody>
        <?php 
            foreach( $pasarelas as $metodopago ){

                if( !isset( $metodopago[ "settings" ][ "tipocomision" ] ) ){
                    $metodopago[ "settings" ][ "tipocomision" ] = "";
                }

                echo "\n<tr pasarela=\"{$metodopago[ "codigo" ]}\">
                    <td><span class=\"badge bg-marine\">{$metodopago[ "codigo" ]}</span></td>
                    <td>{$metodopago[ "nombre" ]}</td>
                    <td>{$metodopago[ "settings" ][ "descripcion" ]}</td>
                    <td>".estatus( $metodopago[ "estatus_codigo" ] )."</td>
                    <td class=\"text-end\">".( $metodopago[ "settings" ][ "tipocomision" ] == "porcentaje" ? "" : "$" ).number_format( $metodopago[ "settings" ][ "comision" ], 2 ).( $metodopago[ "settings" ][ "tipocomision" ] == "porcentaje" ? "%" : "" )."</td>
                    <td class=\"text-end\"><a href=\"".base_url( "promo_detalle/".$metodopago[ "codigo" ] )."\" class=\"btn btn-xs btn-primary\">DETALLES</a></td>
                </tr>";
            }
        ?>
     
    </tbody>
</table>

<link href="<?php echo base_url(); ?>assets/css/datatables.css" rel="stylesheet"/>
<script src="<?php echo base_url(); ?>assets/js/datatables.js" type="text/javascript"></script>
<script src="<?php echo base_url(); ?>assets/js/datatables_bs5.js" type="text/javascript"></script>

<h4 class="mt-1 mb-0"><?php echo $titulo; ?></h4>
<p><a href="<?php echo base_url( "admin" ); ?>"><i class="fa fa-undo"></i> Regresar al perfil de socio</a></p>

<?php echo pills( "promociones", $modelo ); ?>

<table class="table table-striped bg-white" id="tabla_promociones">
    <thead>
        <tr>
            <th>Código</th>
            <th>Nombre</th>
            <th>Descripción</th>
            <th>Estatus</th>
            <th>Fecha de inicio</th>
            <th>Fecha de término</th>
            <th>Vigencia</th>
            <th></th>
        </tr>
    </thead>

    <tbody>
        <?php 
            foreach( $promociones as $promocion ){
                echo "\n<tr socio=\"{$promocion[ "codigo" ]}\">
                    <td nowrap><span class=\"badge bg-{$promocion[ "settings" ][ "clase" ]}\">{$promocion[ "settings" ][ "siglas" ]}</span> {$promocion[ "codigo" ]}</td>
                    <td nowrap>{$promocion[ "settings" ][ "nombre" ]}</td>
                    <td>{$promocion[ "settings" ][ "descripcion" ]}</td>
                    <td>".estatus( $promocion[ "estatus_codigo" ] )."</td>
                    <td nowrap>".substr( $promocion[ "inicia" ], 0, 10 )."</td>
                    <td nowrap>".substr( $promocion[ "termina" ], 0, 10 )."</td>
                    <td>".estatus( $promocion[ "vigencia" ] )."</td>
                    <td class=\"text-end\"><a href=\"".base_url( "promo_detalle/".$promocion[ "codigo" ] )."\" class=\"btn btn-xs btn-primary\">DETALLES</a></td>
                </tr>";
            }
        ?>
     
    </tbody>
</table>

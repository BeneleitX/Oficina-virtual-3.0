<link href="<?php echo base_url(); ?>assets/css/datatables.css" rel="stylesheet"/>
<script src="<?php echo base_url(); ?>assets/js/datatables.js" type="text/javascript"></script>
<script src="<?php echo base_url(); ?>assets/js/datatables_bs5.js" type="text/javascript"></script>

<h4 class="mt-1 mb-0"><?php echo $titulo; ?></h4>
<p><a href="<?php echo base_url( "admin" ); ?>"><i class="fa fa-undo"></i> Regresar a configuración</a></p>


<table class="table table-striped bg-white" id="tabla_estatus">
    <thead>
        <tr>
            <th>Código</th>
            <th>Nombre</th>
        </tr>
    </thead>

    <tbody>
        <?php 
            foreach( $estatuses as $estatus ){

                echo "\n<tr estatus=\"{$estatus[ "codigo" ]}\">
                    <td><span class=\"badge bg-{$estatus[ "color" ]}\">{$estatus[ "codigo" ]}</span></td>
                    <td>{$estatus[ "descripcion" ]}</td>
                </tr>";
            }
        ?>
     
    </tbody>
</table>

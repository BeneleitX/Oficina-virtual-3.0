<link href="<?php echo base_url(); ?>assets/css/datatables.css" rel="stylesheet"/>
<script src="<?php echo base_url(); ?>assets/js/datatables.js" type="text/javascript"></script>
<script src="<?php echo base_url(); ?>assets/js/datatables_bs5.js" type="text/javascript"></script>

<h4 class="mt-1 mb-0"><?php echo $titulo; ?></h4>
<p><a href="<?php echo base_url( "admin" ); ?>"><i class="fa fa-undo"></i> Regresar a configuración</a></p>

<table class="table table-striped bg-white" id="tabla_roles">
    <thead>
        <tr>
            <th>Código</th>
            <th>Descripción</th>
        </tr>
    </thead>

    <tbody>
        <?php 
            foreach( $roles as $rol ){
                echo "\n<tr pasarela=\"{$rol[ "codigo" ]}\">
                    <td>{$rol[ "codigo" ]}</td>
                    <td>{$rol[ "descripcion" ]}</td>
                </tr>";
            }
        ?>
     
    </tbody>
</table>

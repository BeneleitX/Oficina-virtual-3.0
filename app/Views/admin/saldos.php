<link href="<?php echo base_url(); ?>assets/css/datatables.css" rel="stylesheet"/>
<script src="<?php echo base_url(); ?>assets/js/datatables.js" type="text/javascript"></script>
<script src="<?php echo base_url(); ?>assets/js/datatables_bs5.js" type="text/javascript"></script>

<h4 class="mt-1 mb-0"><?php echo $titulo; ?></h4>
<p><a href="<?php echo base_url( "admin" ); ?>"><i class="fa fa-undo"></i> Regresar a configuración</a></p>


<table class="table table-striped bg-white" id="tabla_saldos">
    <thead>
        <tr>
            <th>Socio</th>
            <th>Nombre</th>
            <?php
                foreach( MODELOS as $m ){
                    echo "<th><span class=\"text-{$m[ "settings" ][ "color" ]}\"><i class=\"fa fa-{$m[ "settings" ][ "icono" ]}\"></i> {$m[ "nombre" ]}</span></th>";
                }
            ?>
            <th></th>
        </tr>
    </thead>

    <tbody>
        <?php 
            foreach( $saldos as $s ){

                echo "\n<tr estatus=\"{$s->id}\">
                    <td>".$s->id( null, "marine", false )."</td>
                    <td>".$s->avatar( 24 )." ".$s->nombre( 2 )."</td>";

                foreach( MODELOS as $m ){
                    echo "<td>".( $s->data->saldo->{$m[ "codigo" ]} > 0 ? "$".number_format( $s->data->saldo->{$m[ "codigo" ]}, 2) : "" )."</td>";
                }

                echo "\n<td class=\"text-end\">
                            <button type=\"submit\" class=\"btn btn-secondary btn-sm\"><i class=\"fa fa-hand-holding-dollar\"></i> Editar saldo</button>
                        </td></tr>";
            }
        ?>
     
    </tbody>
</table>

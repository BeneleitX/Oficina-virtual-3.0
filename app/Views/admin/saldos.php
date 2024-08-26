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

                echo "\n<tr socio=\"{$s->id}\">
                    <td class=\"s_id\">".$s->id( null, "marine", false )."</td>
                    <td class=\"s_datos\">".$s->avatar( 24 )." ".$s->nombre( 2 )."</td>";

                foreach( MODELOS as $m ){
                    echo "<td modelo=\"{$m[ "codigo" ]}\" saldo=\"".$s->data->saldo->{$m[ "codigo" ]}."\">".( $s->data->saldo->{$m[ "codigo" ]} > 0 ? "$".number_format( $s->data->saldo->{$m[ "codigo" ]}, 2) : "" )."</td>";
                }

                echo "\n<td class=\"text-end\">
                            <button onclick=\"edita_saldo({$s->id})\" class=\"btn btn-secondary btn-sm\"><i class=\"fa fa-hand-holding-dollar\"></i> Editar saldo</button>
                        </td></tr>";
            }
        ?>
     
    </tbody>
</table>


<div class="modal" tabindex="-1" id="edita_saldo">
	<div class="modal-dialog">
		<div class="modal-content">
            <form method="post" action="<?php echo base_url( "edita_saldos" ); ?>">
                <?php echo csrf_field() ?>
                <input type="hidden" name="socio_saldo" value="">

                <div class="modal-header bg-marine">
                    <h5 class="modal-title text-white"><i class="fa fa-hand-holding-dollar"></i> Editar saldos a favor</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div id="contenido" class="mb-3"></div>

                    <table class="table table-striped w-100">
                        <?php
                        foreach( MODELOS as $m ){
                            echo "\n<tr><td><span class=\"text-{$m[ "settings" ][ "color" ]}\"><i class=\"fa fa-{$m[ "settings" ][ "icono" ]}\"></i> {$m[ "nombre" ]}</span></td><td><input type=\"number\" modelo=\"{$m[ "codigo" ]}\" name=\"saldo[{$m[ "codigo" ]}]\" class=\"form-control w-50 saldo text-end\"></td></tr>";
                        }
                        ?>
                    </table>

                    <div class="alert alert-info m-0">
                        <p>Colocar un saldo en ceros equivale a eliminarlo.</p>
                        <p class="m-0">Verificar bien el modelo de negocio al cual aplica antes de enviar los cambios.</p>
                    </div>

                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-secondary">Aplicar cambios</button>
                </div>
            </form>
		</div>
	</div>
</div>



<script>
var modelos = <?php echo json_encode( MODELOS ); ?>;

</script>
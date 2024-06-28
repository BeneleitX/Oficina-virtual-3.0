<link href="<?php echo base_url(); ?>assets/css/datatables.css" rel="stylesheet"/>
<script src="<?php echo base_url(); ?>assets/js/datatables.js" type="text/javascript"></script>
<script src="<?php echo base_url(); ?>assets/js/datatables_bs5.js" type="text/javascript"></script>

<h4 class="mt-1 mb-0"><?php echo $titulo; ?></h4>
<p><a href="<?php echo base_url( "perfil" ); ?>"><i class="fa fa-undo"></i> Regresar al perfil de socio</a></p>

<table class="table bg-white table-striped" id="tabla_bitacora">
    <thead>
        <tr>
            <th>Id movimiento</th>
            <th>Código</th>
            <th>IP Address</th>
            <th>Fecha</th>
            <th>Hora</th>
            <th>Movimiento</th>
        </tr>
    </thead>
    <tbody>
        <?php
        $movimientos = $socio->getBitacora();

        foreach( $movimientos as $m ){
            echo "\n<tr>
                <td>{$m->indice}</td>
                <td><span class=\"badge bg-marine\">{$m->codigo}</span></td>
                <td>{$m->ip}</td>
                <td>".substr( $m->fecha, 0, 10)."</td>
                <td>".substr( $m->fecha, 11, 8)."</td>
                <td>{$m->string}</td>
            </tr>";
        }
        
        ?>
    </tbody>
</table>

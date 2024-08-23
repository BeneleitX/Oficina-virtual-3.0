<link href="<?php echo base_url(); ?>assets/css/datatables.css" rel="stylesheet"/>
<script src="<?php echo base_url(); ?>assets/js/datatables.js" type="text/javascript"></script>
<script src="<?php echo base_url(); ?>assets/js/datatables_bs5.js" type="text/javascript"></script>

<h4 class="mt-1 mb-0"><?php echo $titulo; ?></h4>
<p class="mb-4"><a href="<?php echo base_url( "layout_bancos" ); ?>"><i class="fa fa-undo"></i> Regresar a ingreso de pagos</a></p>


<table class="table table-striped bg-white" id="tabla_pendientes">
    <thead>
        <tr>
            <th>Banco</th>
            <th>Fecha pago</th>
            <th>Operación</th>
            <th>Referencia</th>
            <th>Pedido</th>
            <th>Usuario</th>
            <th>Cantidad</th>
        </tr>
    </thead>

    <tbody>
        <?php 
        foreach( $pendientes->getResult() as $p ){
            $p->extras = json_decode( $p->extras );
            echo "<tr>
                <td><img style=\"width:50px; border-radius:5px\" src=\"".base_url()."assets/img/bancos/".( $p->extras->banco == "BBVA" ? "012" : "127" ).".png\"></td>
                <td><span class=\"d-none\">{$p->fecha}</span>".date( "d-m-Y", strtotime( $p->fecha ) )."</td>
                <td>{$p->operacion}</td>
                <td>{$p->extras->referencia}</td>
                <td><span class=\"badge border border-red text-red\">Desconocido</span></td>
                <td><span class=\"badge border border-red text-red\">Desconocido</span></td>
                <td class=\"text-end\">$".number_format( $p->cantidad, 2  )."</td>
            </tr>";
        }
        ?>    
    </tbody>
</table>
<link href="<?php echo base_url(); ?>assets/css/datatables.css" rel="stylesheet"/>
<script src="<?php echo base_url(); ?>assets/js/datatables.js" type="text/javascript"></script>
<script src="<?php echo base_url(); ?>assets/js/datatables_bs5.js" type="text/javascript"></script>


<div class="row">
    <div class="col-6">
        <h4 class="mt-1 mb-0"><?php echo $titulo; ?></h4>
        <p><a class="btn btn-light btn-sm" href="<?php echo base_url( "facturas" ); ?>"><i class="fa fa-undo"></i> Regresar a pedidos por facturar</a></p>
    </div>

    <div class="col-6 text-end text-red pt-4">
        
    </div>
</div>



<table class="table table-striped bg-white" id="tabla_pedidos">
    <thead>
        <tr>
            <th>Referencia</th>
            <th>Socio</th>
            <th>Nombre</th>
            <th>Modelo de negocio</th>
            <th>Cantidad</th>
            <th>Fecha de pago</th>
            <th>Método de pago</th>
            <th>Folio</th>
            <th></th>
        </tr>
    </thead>

    <tbody>
        <?php 
            foreach( $pedidos as $p ){
                $modelo = MODELOS[ $p[ "modelo_codigo" ] ];
                $u  = model( "UsuarioModel" )->find( $p[ "usuario_id" ] );

                echo "\n<tr pedido=\"{$p[ "id" ]}\" referencia=\"{$p[ "referencia" ]}\" rfc=\"{$u->data->sat->rfc}\" link=\"".base_url()."data/{$u->id}/csf/{$u->data->sat->csf}\">
                    <td class=\"text-center\"><span class=\"badge bg-marine\">{$p[ "referencia" ]}</span></td>
                    <td>".$u->id( $p[ "modelo_codigo" ] )."</td>
                    <td>".$u->avatar( 24 )." ".$u->nombre( 2 )."</td>

                    <td><span class=\"text-{$modelo[ "settings" ][ "color" ]}\"><i class=\"fa fa-{$modelo[ "settings" ][ "icono" ]}\"></i> {$modelo[ "nombre" ]}</span></td>
                    
                    <td class=\"text-end\"><strong>$".number_format( $p[ "data" ][ "total" ] + $p[ "data" ][ "comisionbanco" ] + $p[ "data" ][ "comisionentrega" ] - $p[ "data" ][ "saldo" ], 2 )."</strong></td>
                    <td class=\"text-center\"><span class=\"d-none\">".substr( $p[ "fechas" ][ "pagado" ], 0, 10 )."</span> ".date( "d-m-Y", strtotime( substr( $p[ "fechas" ][ "pagado" ], 0, 10 ) ) )."</td>
                    
                    <td>".METODOSPAGO[ $p[ "metodopago_codigo" ] ][ "nombre" ]."</td>

                    <td class=\"text-center\">".( $p[ "data" ][ "sat" ][ "cfd" ] ?? null ? "<h5 class=\"m-0 text-teal\">{$p[ "data" ][ "sat" ][ "cfd" ]}</h5>" : "<span class=\"badge bg-red\">PENDIENTE</span>" )."</td>

                    <td class=\"text-end\"><a href=\"".base_url( "pedido/".$p[ "referencia" ] )."\" class=\"btn btn-xs btn-secondary\">DETALLES</a> <button class=\"btn btn-xs btn-primary\" onclick=\"registra_folio( {$p[ "id" ]} )\">REGISTRA FOLIO</a></td>
                </tr>";
            }
        ?>
     
    </tbody>
</table>


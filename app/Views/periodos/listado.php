<link href="<?php echo base_url(); ?>assets/css/datatables.css" rel="stylesheet"/>
<script src="<?php echo base_url(); ?>assets/js/datatables.js" type="text/javascript"></script>
<script src="<?php echo base_url(); ?>assets/js/datatables_bs5.js" type="text/javascript"></script>

<h4 class="mt-1 mb-0"><?php echo $titulo; ?></h4>
<p><a href="<?php echo base_url( "admin" ); ?>"><i class="fa fa-undo"></i> Regresar a configuración</a></p>

<ul class="nav nav-pills mb-5">
    <?php 
    foreach( MODELOS as $m ){
        if( $m[ "settings" ][ "efectivo" ] ){
            echo "\n<li class=\"nav-item\"><a class=\"nav-link ".( $modelo == $m[ "codigo" ] ? "active" : "")."\" aria-current=\"page\" href=\"".base_url( "periodos/".$m[ "codigo" ] )."\"><i class=\"fa fa-{$m[ "settings" ][ "icono" ]}\"></i> {$m[ "nombre" ]}</a></li>";
        }
    }
    ?>
</ul>

<table class="table table-striped bg-white" id="tabla_cortes">
    <thead>
        <tr>
            <th>Periodo</th>
            <th>Inicia</th>
            <th>Termina</th>
            <th>Pedidos</th>
            <th>Socios</th>
            <th>Venta</th>
            <th>Comisiones</th>
            <th>Pagado</th>
            <th></th>
        </tr>
    </thead>

    <tbody>
    <?php 
        foreach( $periodos as $periodo ){
            echo "<tr>
                <td><span class=\"badge bg-marine\">".periodo( $periodo[ "codigo" ] )."</span></td>
                <td>{$periodo[ "inicia" ]}</td>
                <td>{$periodo[ "termina" ]}</td>
                <td>0</td>
                <td>0</td>
                <td class=\"text-end\">$0.00</td>
                <td class=\"text-end\">$0.00</td>
                <td>".estatus( $periodo[ "estatus_codigo" ] )."</td>
                <td class=\"text-end\"><a href=\"".base_url( "periodo/".$periodo[ "codigo" ] )."\" class=\"btn btn-xs btn-primary\">DETALLES</a></td>
            </tr>";
        }
        
    ?>
    </tbody>
</table>

    
<div class="modal fade" id="modal_periodo" tabindex="-1" aria-labelledby="add_rolLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header" style="display:none">
                <h5 class="modal-title" id="add_rolLabel"><i class="i-factura"></i> Resumen del periodo <span class="periodo_codigo"></span></h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body py-5" id="periodo_loader" style="display:none"></div>
            <div class="modal-body" id="periodo_detalle" style="display:none"></div>
            
            <div class="modal-footer" style="display:none">
            <button type="button" class="btn bg-secondary" data-bs-dismiss="modal" ><i class="i-cancelar"></i> Cerrar</button>
            <a class="btn bg-primary" id="p_detalles" href=""><i class="i-lupa"></i> Abrir detalles</a>
            </div>
        </div>
    </div>
</div> 


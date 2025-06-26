<link href="<?php echo base_url(); ?>assets/css/datatables.css" rel="stylesheet"/>
<script src="<?php echo base_url(); ?>assets/js/datatables.js" type="text/javascript"></script>
<script src="<?php echo base_url(); ?>assets/js/datatables_bs5.js" type="text/javascript"></script>

<h4 class="mt-1 mb-0"><?php echo $titulo; ?></h4>
<p><a href="<?php echo base_url( "admin" ); ?>"><i class="fa fa-undo"></i> Regresar a administración</a></p>

<?php echo pills( "periodos", $modelo ); 
?>

<table class="table table-striped bg-white" id="tabla_cortes">
    <thead>
        <tr>
            <th>Periodo</th>
            <th>Inicia</th>
            <th>Termina</th>
            <th>Pedidos</th>
            <th>Socios</th>
            <th>Comisiones</th>
            <th>I.S.R.</th>
            <th>Pagado</th>
            <th>Estatus</th>
            <th></th>
        </tr>
    </thead> 

    <tbody>
    <?php 
        foreach( $periodos as $periodo ){
 // if( $periodo[ "codigo" ] == "50S202526") dd( $periodo );
          // if( $periodo[ "codigo"] == "50S202514" )      dd($periodo); 
            echo "<tr>
                <td><span class=\"badge bg-marine\"><span class=\"d-none\">{$periodo[ "codigo"]}</span>".periodo( $periodo[ "codigo" ] )."</span></td>
                <td><span class=\"d-none\">{$periodo[ "inicia" ]}</span> ".date( "d-m-Y", strtotime( $periodo[ "inicia" ] ) )."</td>
                <td><span class=\"d-none\">{$periodo[ "termina" ]}</span> ".date( "d-m-Y", strtotime( $periodo[ "termina" ] ) )."</td>
                <td>".( $periodo[ "data" ][ "pedidos" ] ?? 0 )."</td>
                <td>".( $periodo[ "data" ][ "pagos" ] ?? 0 )."</td>
                <td class=\"text-end\">$".number_format( $periodo[ "data" ][ "comisiones" ] ?? 0 , 2 )."</td>
                <td class=\"text-end\">$".number_format( $periodo[ "data" ][ "isr" ] ?? 0 , 2 )."</td>
                <td class=\"text-end\">$".number_format( $periodo[ "data" ][ "total" ] ?? 0 , 2 )."</td>
                <td>".estatus( substr( codigo_periodo( $modelo ), 3 ) >= substr( $periodo[ "codigo" ], 3 ) ? $periodo[ "estatus_codigo" ] : "152-FUTURO" )."</td>
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

<link href="<?php echo base_url(); ?>assets/css/datatables.css" rel="stylesheet"/>
<script src="<?php echo base_url(); ?>assets/js/datatables.js" type="text/javascript"></script>
<script src="<?php echo base_url(); ?>assets/js/datatables_bs5.js" type="text/javascript"></script>

<h4 class="mt-1 mb-0"><?php echo $titulo; ?></h4>
<p class="mb-5"><a href="<?php echo base_url( "periodos/".$periodo[ "modelo_codigo" ] ); ?>"><i class="fa fa-undo"></i> Regresar a periodos</a></p>

<div class="alert alert-info">
    <div class="row">
        <div class="col-md-6">
        
        </div>
        <div class="col-md-3">
        
        </div>
        <div class="col-md-3">
            <button onclick="$( '#modal_corte' ).modal( 'show' )" class="btn btn-danger col-12"><i class="fa fa-repeat"></i> Generar pagos</button>
        </div>
    </div>
</div>

<table class="table table-striped bg-white" id="tabla_pagos">
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

        
    ?>
    </tbody>
</table>

<div class="modal fade" id="modal_corte" tabindex="-1" aria-labelledby="add_rolLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="add_rolLabel"><i class="i-factura"></i> Corte del periodo <span class="badge bg-marine"><?php echo $periodo[ "codigo" ]; ?></span> <span class="periodo_codigo"></span></h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body" id="periodo_detalle">
                <div class="text-center">
                    <p style="font-size:100px" class="m-0 p-0text-center"><i class=" text-red icon_gira fa fa-repeat"></i></p>
                    <p class="text-red text-center corte_aviso">El proceso puede durar varios segundos</p>

                    <p>
                        <div class="pe1 mt-4 mb-3"><button class="btn btn-success" id="corte_start">Click para comenzar</button></div>
                        <div class="mt-0 pe2 mb-3" style="display:none">
                            <p class="m-1">Calculando comisiones de pedidos <span id="cuentapedidos"></span></p>
                            <div class="progress" role="progressbar" aria-label="Animated striped example" aria-valuemin="0" aria-valuemax="100">
                                <div class="progress-bar progress-bar-striped progress-bar-animated bg-teal" style="width: 0%"></div>
                            </div>
                        </div>
                    </p>
                </div>
            </div>
            
            <div class="modal-footer d-none">
            <button type="button" class="btn bg-secondary d-none" data-bs-dismiss="modal" ><i class="i-cancelar"></i> Cerrar</button>
            
            </div>
        </div>
    </div>
</div> 


<script>
    var periodo = '<?php echo $periodo[ "codigo" ]; ?>';
</script>

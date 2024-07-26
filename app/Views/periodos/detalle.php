<link href="<?php echo base_url(); ?>assets/css/datatables.css" rel="stylesheet"/>
<script src="<?php echo base_url(); ?>assets/js/datatables.js" type="text/javascript"></script>
<script src="<?php echo base_url(); ?>assets/js/datatables_bs5.js" type="text/javascript"></script>

<h4 class="mt-1 mb-0"><?php echo $titulo; ?></h4>
<p class="mb-5"><a href="<?php echo base_url( "periodos/".$periodo[ "modelo_codigo" ] ); ?>"><i class="fa fa-undo"></i> Regresar a periodos</a></p>

<?php

if( sizeof( $t[ "extras" ] ) ){
    echo "<div class=\"alert alert-danger mb-3\"><i class=\"fa fa-warning\"></i> Este periodo contiene pagos con inconsistencias. Favor de notificar a sistemas antes de cerrar la semana</div>";
}

?>


<div class="alert alert-info mb-5">
    <div class="row">
        <div class="col-md-3">
            <button onclick="lanza_corte()" class="btn btn-danger col-12 <?php echo substr( $periodo[ "estatus_codigo" ], 0, 3 ) > 300 ? "d-none" : ""; ?>"><i class="fa fa-repeat"></i> Generar pagos</button>
        </div>        
        <div class="col-md-3 <?php echo $periodo[ "estatus_codigo" ] != '255-PENDIENTE' ? "d-none" : ""; ?>">
            <button onclick="$( '#modal_cierra' ).modal( 'show' )" class="btn btn-warning col-12"><i class="fa fa-lock"></i> Cerrar periodo</button>
        </div>
        <div class="col-md-3 <?php echo substr( $periodo[ "estatus_codigo" ], 0, 3 ) < 300 ? "d-none" : ""; ?>">
            <button onclick="$( '#modal_abre' ).modal( 'show' )" class="btn btn-secondary col-12"><i class="fa fa-lock"></i> Abrir periodo</button>
        </div>        
        <div class="col-md-3">
            <button onclick="excel_corte()" <?php echo substr( $periodo[ "estatus_codigo" ], 0, 3 ) > 250 ? "" : "disabled"; ?> class="btn btn-success col-12"><i class="fa fa-file-excel"></i> Descargar excel</button>
        </div>
    </div>
</div>

<h5 class="mt-5 text-teal">Pagos de periodos anteriores</h5>
<table class="table table-striped bg-white" id="tabla_anteriores">
    <thead>
        <tr>
            <th width="8%">Creado</th>
            <th width="8%" class="text-start">Pagado</th>
            <th width="12%">Estatus</th>
            <th width="28%">Socio</th>
            <th width="14%">CLABE Interbancaria</th>
            <th width="10%">Sub total</th>
            <th width="10%">I.S.R.</th>
            <th width="10%">Total</th>
        </tr>
    </thead>

    <tbody>
    <?php 

    foreach( $t[ "previos" ] as $g ){
        echo "<tr>
            <td width=\"8%\"><span class=\"badge bg-marine\">".periodo( $g[ "data" ][ "periodos" ][ "creacion" ] )."</span></td>
            <td width=\"10%\" class=\"text-start\"><span class=\"badge bg-".ESTATUS[ $g[ "estatus_codigo" ] ][ "color" ]."\">".periodo( $g[ "data" ][ "periodos" ][ "deposito" ] )."</span></td>
            <td width=\"12%\">".estatus( $g[ "estatus_codigo" ] )."</td>
            <td width=\"29%\">".$g[ "s" ]->avatar( 24 )." ".$g[ "s" ]->id( $g[ "modelo_codigo" ], null, 1 )." ".$g[ "s" ]->nombre( 2 )."</td>
            <td width=\"14%\">".( $g[ "data" ][ "retencion"] ? "" : "<i class=\"fa fa-filter-circle-dollar text-blue\"></i> " ).( $g[ "data" ][ "menor"] ? "<i class=\"fa fa-child-reaching text-pink\"></i> " : "" )."{$g[ "clabe" ]}</td>
            <td width=\"9%\">$".number_format($g[ "data" ][ "cantidades"][ "subtotal" ], 2)."</td>
            <td width=\"9%\">$".number_format($g[ "data" ][ "cantidades"][ "isr" ], 2)."</td>
            <td width=\"9%\">$".number_format($g[ "data" ][ "cantidades"][ "total" ], 2)."</td></tr>";
    }
    ?>
    </tbody>
</table>


<h5 class="mt-5 text-teal">Pagos de periodo</h5>
<table class="table table-striped bg-white" id="tabla_pagos">
    <thead>
        <tr>
            <th width="8%">Creado</th>
            <th width="8%" class="text-start">Pagado</th>
            <th width="12%">Estatus</th>
            <th width="28%">Socio</th>
            <th width="14%">CLABE Interbancaria</th>
            <th width="10%">Sub total</th>
            <th width="10%">I.S.R.</th>
            <th width="10%">Total</th>
        </tr>
    </thead>

    <tbody>
    <?php 

    foreach( $t[ "actual" ] as $g ){
        echo "<tr>
            <td width=\"8%\"><span class=\"badge bg-marine\">".periodo( $g[ "data" ][ "periodos" ][ "creacion" ] )."</span></td>
            <td width=\"10%\" class=\"text-start\"><span class=\"badge bg-".ESTATUS[ $g[ "estatus_codigo" ] ][ "color" ]."\">".periodo( $g[ "data" ][ "periodos" ][ "deposito" ] )."</span></td>
            <td width=\"12%\">".estatus( $g[ "estatus_codigo" ] )."</td>
            <td width=\"29%\">".$g[ "s" ]->avatar( 24 )." ".$g[ "s" ]->id( $g[ "modelo_codigo" ], null, 1 )." ".$g[ "s" ]->nombre( 2 )."</td>
            <td width=\"14%\">".( $g[ "data" ][ "retencion"] ? "" : "<i class=\"fa fa-filter-circle-dollar text-blue\"></i> " ).( $g[ "data" ][ "menor"] ? "<i class=\"fa fa-child-reaching text-pink\"></i> " : "" )."{$g[ "clabe" ]}</td>
            <td width=\"9%\">$".number_format($g[ "data" ][ "cantidades"][ "subtotal" ], 2)."</td>
            <td width=\"9%\">$".number_format($g[ "data" ][ "cantidades"][ "isr" ], 2)."</td>
            <td width=\"9%\">$".number_format($g[ "data" ][ "cantidades"][ "total" ], 2)."</td></tr>";
    }
        
    ?>
    </tbody>
</table>

<h5 class="mt-5 text-teal">Pagos de periodo a pagar en periodos posteriores</h5>
<table class="table table-striped bg-white" id="tabla_pagos">
    <thead>
        <tr>
        <th width="8%">Creado</th>
            <th width="8%" class="text-start">Pagado</th>
            <th width="12%">Estatus</th>
            <th width="28%">Socio</th>
            <th width="14%">CLABE Interbancaria</th>
            <th width="10%">Sub total</th>
            <th width="10%">I.S.R.</th>
            <th width="10%">Total</th>
        </tr>
    </thead>

    <tbody>
    <?php 

foreach( $t[ "siguiente" ] as $g ){
    echo "<tr>
        <td width=\"8%\"><span class=\"badge bg-marine\">".periodo( $g[ "data" ][ "periodos" ][ "creacion" ] )."</span></td>
        <td width=\"10%\" class=\"text-start\"><span class=\"badge bg-".ESTATUS[ $g[ "estatus_codigo" ] ][ "color" ]."\">".periodo( $g[ "data" ][ "periodos" ][ "deposito" ] )."</span></td>
        <td width=\"12%\">".estatus( $g[ "estatus_codigo" ] )."</td>
        <td width=\"29%\">".$g[ "s" ]->avatar( 24 )." ".$g[ "s" ]->id( $g[ "modelo_codigo" ], null, 1 )." ".$g[ "s" ]->nombre( 2 )."</td>
        <td width=\"14%\">".( $g[ "data" ][ "retencion"] ? "" : "<i class=\"fa fa-filter-circle-dollar text-blue\"></i> " ).( $g[ "data" ][ "menor"] ? "<i class=\"fa fa-child-reaching text-pink\"></i> " : "" )."{$g[ "clabe" ]}</td>
        <td width=\"9%\">$".number_format($g[ "data" ][ "cantidades"][ "subtotal" ], 2)."</td>
        <td width=\"9%\">$".number_format($g[ "data" ][ "cantidades"][ "isr" ], 2)."</td>
        <td width=\"9%\">$".number_format($g[ "data" ][ "cantidades"][ "total" ], 2)."</td></tr>";
}
        
    ?>
    </tbody>
</table>


<div class="modal" id="modal_corte" tabindex="-1" aria-labelledby="add_rolLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="add_rolLabel"><i class="i-factura"></i> Corte del periodo <span class="badge bg-marine"><?php echo periodo( $periodo[ "codigo" ] ); ?></span> <span class="periodo_codigo"></span></h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body" id="periodo_detalle">
                <div class="text-center">
                    <p style="font-size:100px" class="m-0 p-0 text-center"><i class="text-red icon_gira fa fa-repeat"></i></p>
                    <p class="text-red text-center corte_aviso">El proceso puede durar varios segundos</p>

                    <p>
                        <div class="pe1 mt-4 mb-3"><button class="btn btn-info" id="corte_start">Click para comenzar</button></div>
                        <div class="mt-0 pe2 mb-3" style="display:none">
                            <p class="mt-3 mb-0">Calculando comisiones de pedidos</p>
                            <div class="progress mb-3" role="progressbar" aria-label="Animated striped example" aria-valuemin="0" aria-valuemax="100">
                                <div class="progress-bar porcentaje_comisiones progress-bar-striped progress-bar-animated bg-teal" style="width: 0%"></div>
                            </div>
                            <p class="mt-3 mb-0">Generando pagos de socios</p>
                            <div class="progress mb-3" role="progressbar" aria-label="Animated striped example" aria-valuemin="0" aria-valuemax="100">
                                <div class="progress-bar porcentaje_pagos progress-bar-striped progress-bar-animated bg-teal" style="width: 0%"></div>
                            </div>

                            <table class="table table-striped">
                            <tr><td class="text-start">Pedidos pagados en el periodo</td><td class="text-end"><span id="dato_pedidos">0</span></td></tr>
                            <tr><td class="text-start">Socios involucrados</td><td class="text-end"><span id="dato_socios">0</span></td></tr>
                            <tr><td class="text-start">Pagos reales</td><td class="text-end"><span id="dato_pagos">0</span></td></tr>
                            <tr><td class="text-start">Comisiones generadas</td><td class="text-end"><span id="dato_comisiones">0</span></td></tr>
                            <tr><td class="text-start">Retención I.S.R.</td><td class="text-end"><span id="dato_isr">0</span></td></tr>
                            <tr><td class="text-start">Total a depositar</td><td class="text-end"><span id="dato_total">0</span></td></tr>
                            </table>
                        </div>
                    </p>
                </div>
            </div>
            
            <div class="modal-footer" style="display:none">
                <button type="button" class="btn btn-warning" data-bs-dismiss="modal" ><i class="i-cancelar"></i> Continuar</button>
            </div>
        </div>
    </div>
</div> 



<div class="modal" id="modal_cierra" tabindex="-1" aria-labelledby="add_rolLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="add_rolLabel"><i class="i-factura"></i> Corte del periodo <span class="badge bg-marine"><?php echo periodo( $periodo[ "codigo" ] ); ?></span> <span class="periodo_codigo"></span></h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body" id="periodo_detalle">
                <div class="text-center">
                    <p style="font-size:100px" class="m-0 p-0 text-center"><i class=" text-mustard fa fa-lock"></i></p>
                    <p>
                    <?php 
                    if( $periodo[ "estatus_codigo" ] != '255-PENDIENTE' ){
                        echo "<div class=\"alert alert-danger\">No se puede cerrar este periodo</div>";
                    }
                    else{
                        echo "<div class=\"mt-4 mb-3\"><button class=\"btn btn-danger\" id=\"cierra_start\">Click para cerrar el periodo</button></div>";
                    }
                    ?>
                    </p>
                </div>
            </div>
        </div>
    </div>
</div> 


<div class="modal" id="modal_abre" tabindex="-1" aria-labelledby="add_rolLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="add_rolLabel"><i class="i-factura"></i> Corte del periodo <span class="badge bg-marine"><?php echo periodo( $periodo[ "codigo" ] ); ?></span> <span class="periodo_codigo"></span></h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body" id="periodo_detalle">
                <div class="text-center">
                    <p style="font-size:100px" class="m-0 p-0 text-center"><i class=" text-marine fa fa-lock-open"></i></p>
                    <p>
                    <div class="mt-4 mb-3"><button class="btn btn-danger" id="abre_start">Click para abrir el pertiodo</button></div>
                    </p>
                </div>
            </div>
        </div>
    </div>
</div> 


<script>
    var periodo = '<?php echo $periodo[ "codigo" ]; ?>';
</script>

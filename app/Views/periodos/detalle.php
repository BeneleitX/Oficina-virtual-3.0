<link href="<?php echo base_url(); ?>assets/css/datatables.css" rel="stylesheet"/>
<script src="<?php echo base_url(); ?>assets/js/datatables.js" type="text/javascript"></script>
<script src="<?php echo base_url(); ?>assets/js/datatables_bs5.js" type="text/javascript"></script>

<h4 class="mt-1 mb-0"><?php echo $titulo; ?></h4>
<p class="mb-5"><a href="<?php echo base_url( "periodos/".$periodo[ "modelo_codigo" ] ); ?>"><i class="fa fa-undo"></i> Regresar a periodos</a></p>

<div class="alert alert-info mb-5">
    <div class="row">
        <div class="col-md-3">
        </div>
        <div class="col-md-3">
            <button onclick="$( '#modal_corte' ).modal( 'show' )" class="btn btn-danger col-12"><i class="fa fa-repeat"></i> Generar pagos</button>
        </div>        
        <div class="col-md-3">
            <button onclick="$( '#modal_cierra' ).modal( 'show' )" class="btn btn-warning col-12"><i class="fa fa-lock"></i> Cerrar periodo</button>
        </div>
        <div class="col-md-3">
            <button xonclick="$( '#modal_corte' ).modal( 'show' )" disabled class="btn btn-primary col-12"><i class="fa fa-file-excel"></i> Descargar excel</button>
        </div>
    </div>
</div>

<h5 class="mt-5 text-teal">Pagos de periodos anteriores</h5>
<table class="table table-striped bg-white" id="tabla_anteriores">
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


<h5 class="mt-5 text-teal">Pagos de periodo</h5>
<table class="table table-striped bg-white" id="tabla_pagos">
    <thead>
        <tr>
            <th>Creado</th>
            <th>Pagado</th>
            <th>Estatus</th>
            <th>Socio</th>
            <th>CLABE Interbancaria</th>
            <th>Sub total</th>
            <th>I.S.R.</th>
            <th>Total</th>
        </tr>
    </thead>

    <tbody>
    <?php 

    foreach( $pagos as $p ){
        if( $p[ "clabe" ] && strlen( $p[ "clabe" ] ) == 18 ){
            $s = model( "usuarioModel" )->find( $p[ "usuario_id" ] );
            echo "<tr>
                <td><span class=\"badge bg-marine\">".periodo( $p[ "data" ][ "periodos" ][ "creacion" ] )."</span></td>
                <td><span class=\"badge bg-teal\">".periodo( $p[ "data" ][ "periodos" ][ "deposito" ] )."</span></td>
                <td>".estatus( $p[ "estatus_codigo" ] )."</td>
                <td>".$s->avatar( 24 )." ".$s->id( $p[ "modelo_codigo" ] )." ".$s->nombre( 2 )."</td>
                <td>".( $p[ "data" ][ "retencion"] ? "" : "<i class=\"fa fa-filter-circle-dollar text-blue\"></i> " ).( $p[ "data" ][ "menor"] ? "<i class=\"fa fa-child-reaching text-pink\"></i> " : "" )."{$p[ "clabe" ]}</td>
                <td>$".number_format($p[ "data" ][ "cantidades"][ "subtotal" ], 2)."</td>
                <td>$".number_format($p[ "data" ][ "cantidades"][ "isr" ], 2)."</td>
                <td>$".number_format($p[ "data" ][ "cantidades"][ "total" ], 2)."</td></tr>";
        }
    }
        
    ?>
    </tbody>
</table>

<h5 class="mt-5 text-teal">Pagos de periodo pagados en periodos posteriores</h5>
<table class="table table-striped bg-white" id="tabla_pagos">
    <thead>
        <tr>
            <th>Creado</th>
            <th>Pagado</th>
            <th>Estatus</th>
            <th>Socio</th>
            <th>CLABE Interbancaria</th>
            <th>Sub total</th>
            <th>I.S.R.</th>
            <th>Total</th>
        </tr>
    </thead>

    <tbody>
    <?php 

    foreach( $pagos as $p ){
            if( !$p[ "clabe" ] || strlen( $p[ "clabe" ] ) != 18 ){
                $s = model( "usuarioModel" )->find( $p[ "usuario_id" ] );
                echo "<tr>
                <td><span class=\"badge bg-marine\">".periodo( $p[ "data" ][ "periodos" ][ "creacion" ] )."</span></td>
                <td><span class=\"badge bg-teal\">".periodo( $p[ "data" ][ "periodos" ][ "deposito" ] )."</span></td>
                    <td>".estatus( $p[ "estatus_codigo" ] )."</td>
                    <td>".$s->avatar( 24 )." ".$s->id( $p[ "modelo_codigo" ] )." ".$s->nombre( 2 )."</td>
                    <td>".( $p[ "data" ][ "menor"] ? "<i class=\"fa fa-child-reaching text-pink\"></i> " : "" )."{$p[ "clabe" ]}</td>
                    <td>$".number_format($p[ "data" ][ "cantidades"][ "subtotal" ], 2)."</td>
                    <td>$".number_format($p[ "data" ][ "cantidades"][ "isr" ], 2)."</td>
                    <td>$".number_format($p[ "data" ][ "cantidades"][ "total" ], 2)."</td></tr>";
            }
    }
        
    ?>
    </tbody>
</table>


<div class="modal" id="modal_corte" tabindex="-1" aria-labelledby="add_rolLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="add_rolLabel"><i class="i-factura"></i> Corte del periodo <span class="badge bg-teal"><?php echo $periodo[ "codigo" ]; ?></span> <span class="periodo_codigo"></span></h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body" id="periodo_detalle">
                <div class="text-center">
                    <p style="font-size:100px" class="m-0 p-0 text-center"><i class=" text-red icon_gira fa fa-repeat"></i></p>
                    <p class="text-red text-center corte_aviso">El proceso puede durar varios segundos</p>

                    <p>
                        <div class="pe1 mt-4 mb-3"><button class="btn btn-info" id="corte_start">Click para comenzar</button></div>
                        <div class="mt-0 pe2 mb-3" style="display:none">
                            <p class="m-3">Calculando comisiones de pedidos <span id="cuentapedidos"></span></p>
                            <div class="progress mb-3" role="progressbar" aria-label="Animated striped example" aria-valuemin="0" aria-valuemax="100">
                                <div class="progress-bar progress-bar-striped progress-bar-animated bg-teal" style="width: 0%"></div>
                            </div>

                            <table class="table table-striped">
                            <tr><td class="text-start">Pedidos pagados en el periodo</td><td class="text-end"><span id="dato_pedidos">0</span></td></tr>
                            <tr><td class="text-start">Socios beneficiados</td><td class="text-end"><span id="dato_socios">0</span></td></tr>
                            <tr><td class="text-start">Comisiones generadas</td><td class="text-end"><span id="dato_comisiones">0</span></td></tr>
                            <tr><td class="text-start">Bolsa</td><td class="text-end"><span id="dato_bolsa">0</span></td></tr>
                            <tr><td class="text-start">Retención I.S.R.</td><td class="text-end"><span id="dato_isr">0</span></td></tr>
                            <tr><td class="text-start">Total a depositar</td><td class="text-end"><span id="dato_deposito">0</span></td></tr>
                            </table>
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



<div class="modal" id="modal_cierra" tabindex="-1" aria-labelledby="add_rolLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="add_rolLabel"><i class="i-factura"></i> Corte del periodo <span class="badge bg-teal"><?php echo $periodo[ "codigo" ]; ?></span> <span class="periodo_codigo"></span></h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body" id="periodo_detalle">
                <div class="text-center">
                    <p style="font-size:100px" class="m-0 p-0 text-center"><i class=" text-mustard fa fa-lock"></i></p>
                    <p class="text-mustard text-center">El proceso puede durar varios segundos</p>

                    <p>
                        <div class="pe1 mt-4 mb-3"><button class="btn btn-info" id="cierra_periodo">Click para cerrar ahora</button></div>
                    </p>
                </div>
            </div>
        </div>
    </div>
</div> 


<script>
    var periodo = '<?php echo $periodo[ "codigo" ]; ?>';
</script>

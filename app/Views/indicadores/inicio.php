
<div class="row">
    <div class="col-lg-6">
        <h4 class="mt-1 mb-0"><?php echo $titulo; ?></h4>
        <p><a class="btn btn-light btn-sm" href="<?php echo base_url( "admin" ); ?>"><i class="fa fa-undo"></i> Regresar a administración</a></p>
    </div>
    <div class="col-lg-6 text-end">
        <select id="empresa_indicadores" class="mt-4 form-select form-select-sm" style="display: inline-block; width:auto">
            <option selected value="202507">NUTRICION</option>
        </select>

        <select id="mes_indicadores" class="mt-4 form-select form-select-sm" style="display: inline-block; width:auto">
            <option selected value="202507">JULIO 2025</option>
        </select>

        <button id="update_indicadores" class="btn btn-secondary btn-sm" style="display: inline-block; width:auto">
            <i class="fa fa-rotate-right"></i> Actualizar
        </button>
    </div>
</div>

<h5 class="mt-5 mb-0">Venta</h5>
<div class="row">
    <div class="col-lg-4">
        <div class="card mt-3"><div class="card-header bg-teal"><h5 class="m-0 text-white">Venta total</h5></div>
            <div class="card-body text-center"><img src="<?php echo base_url(); ?>assets/img/demo.png" class="img-fluid"><br><hr><h1>$<?php echo number_format( $historico[ "venta" ][ "total" ][ $mes ], 2 ); ?></h1></div>
        </div>
    </div>

    <div class="col-lg-4">
        <div class="card mt-3"><div class="card-header bg-teal"><h5 class="m-0 text-white">Venta socios nuevos</h5></div>
            <div class="card-body text-center"><img src="<?php echo base_url(); ?>assets/img/demo.png" class="img-fluid"><br><hr><h1>$<?php echo number_format( $historico[ "venta" ][ "nuevos" ][ $mes ], 2 ); ?></h1></div>
        </div>
    </div>

    <div class="col-lg-4">
        <div class="card mt-3"><div class="card-header bg-teal"><h5 class="m-0 text-white">Venta por recompra</h5></div>
            <div class="card-body text-center"><img src="<?php echo base_url(); ?>assets/img/demo.png" class="img-fluid"><br><hr><h1>$<?php echo number_format( $historico[ "venta" ][ "recompra" ][ $mes ], 2 ); ?></h1></div>
        </div>
    </div>
</div>


<h5 class="mt-5 mb-0">Comisiones</h5>
<div class="row">
    <div class="col-lg-4">
        <div class="card mt-3"><div class="card-header bg-deep-purple"><h5 class="m-0 text-white">Reparto total</h5></div>
            <div class="card-body text-center"><img src="<?php echo base_url(); ?>assets/img/demo.png" class="img-fluid"><br><hr><h1>$<?php echo number_format( $historico[ "venta" ][ "recompra" ][ $mes ], 2 ); ?></h1></div>
        </div>
    </div>

    <div class="col-lg-4">
        <div class="card mt-3"><div class="card-header bg-deep-purple"><h5 class="m-0 text-white">Reparto socios nuevos</h5></div>
            <div class="card-body text-center"><img src="<?php echo base_url(); ?>assets/img/demo.png" class="img-fluid"><br><hr><h1>$<?php echo number_format( $historico[ "venta" ][ "recompra" ][ $mes ], 2 ); ?></h1></div>
        </div>
    </div>

    <div class="col-lg-4">
        <div class="card mt-3"><div class="card-header bg-deep-purple"><h5 class="m-0 text-white">Reparto por recompra</h5></div>
            <div class="card-body text-center"><img src="<?php echo base_url(); ?>assets/img/demo.png" class="img-fluid"><br><hr><h1>$<?php echo number_format( $historico[ "venta" ][ "recompra" ][ $mes ], 2 ); ?></h1></div>
        </div>
    </div>
</div>

<h5 class="mt-5 mb-0">Ticket promedio</h5>
<div class="row">
    <div class="col-lg-4">
        <div class="card mt-3"><div class="card-header bg-orange"><h5 class="m-0 text-white">Ticket promedio total</h5></div>
            <div class="card-body text-center"><img src="<?php echo base_url(); ?>assets/img/demo.png" class="img-fluid"><br><hr><h1>$<?php echo number_format( $historico[ "venta" ][ "recompra" ][ $mes ], 2 ); ?></h1></div>
        </div>
    </div>

    <div class="col-lg-4">
        <div class="card mt-3"><div class="card-header bg-orange"><h5 class="m-0 text-white">Ticket promedio socios nuevos</h5></div>
            <div class="card-body text-center"><img src="<?php echo base_url(); ?>assets/img/demo.png" class="img-fluid"><br><hr><h1>$<?php echo number_format( $historico[ "venta" ][ "recompra" ][ $mes ], 2 ); ?></h1></div>
        </div>
    </div>

    <div class="col-lg-4">
        <div class="card mt-3"><div class="card-header bg-orange"><h5 class="m-0 text-white">Ticket promedio por recompra</h5></div>
            <div class="card-body text-center"><img src="<?php echo base_url(); ?>assets/img/demo.png" class="img-fluid"><br><hr><h1>$<?php echo number_format( $historico[ "venta" ][ "recompra" ][ $mes ], 2 ); ?></h1></div>
        </div>
    </div>
</div>

<h5 class="mt-5 mb-0">Socios</h5>
<div class="row">
    <div class="col-lg-4">
        <div class="card mt-3"><div class="card-header bg-blue"><h5 class="m-0 text-white">Total de socios Activos</h5></div>
            <div class="card-body text-center"><img src="<?php echo base_url(); ?>assets/img/demo.png" class="img-fluid"><br><hr><h1><?php echo number_format( $historico[ "venta" ][ "recompra" ][ $mes ], 2 ); ?></h1></div>
        </div>
    </div>

    <div class="col-lg-4">
        <div class="card mt-3"><div class="card-header bg-blue"><h5 class="m-0 text-white">Socios inscritos</h5></div>
            <div class="card-body text-center"><img src="<?php echo base_url(); ?>assets/img/demo.png" class="img-fluid"><br><hr><h1><?php echo number_format( $historico[ "venta" ][ "recompra" ][ $mes ], 2 ); ?></h1></div>
        </div>
    </div>

    <div class="col-lg-4">
        <div class="card mt-3"><div class="card-header bg-blue"><h5 class="m-0 text-white">Socios activos nuevos</h5></div>
            <div class="card-body text-center"><img src="<?php echo base_url(); ?>assets/img/demo.png" class="img-fluid"><br><hr><h1><?php echo number_format( $historico[ "venta" ][ "recompra" ][ $mes ], 2 ); ?></h1></div>
        </div>
    </div>

    <div class="col-lg-4">
        <div class="card mt-3"><div class="card-header bg-blue"><h5 class="m-0 text-white">Socios activos por recompra</h5></div>
            <div class="card-body text-center"><img src="<?php echo base_url(); ?>assets/img/demo.png" class="img-fluid"><br><hr><h1><?php echo number_format( $historico[ "venta" ][ "recompra" ][ $mes ], 2 ); ?></h1></div>
        </div>
    </div>

    <div class="col-lg-4">
        <div class="card mt-3"><div class="card-header bg-blue"><h5 class="m-0 text-white">Socios a inactividad</h5></div>
            <div class="card-body text-center"><img src="<?php echo base_url(); ?>assets/img/demo.png" class="img-fluid"><br><hr><h1><?php echo number_format( $historico[ "venta" ][ "recompra" ][ $mes ], 2 ); ?></h1></div>
        </div>
    </div>
</div>

<h5 class="mt-5 mb-0">Productos</h5>

        <div class="card mt-3"><div class="card-header bg-red"><h5 class="m-0 text-white">Ranking de productos por venta</h5></div>
            <table class="table table-striped">
                <tr><td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td></tr>
                <tr><td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td></tr>
                <tr><td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td></tr>
                <tr><td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td></tr>
                <tr><td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td></tr>
                <tr><td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td></tr>
                <tr><td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td></tr>
                <tr><td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td></tr>
            </table>
</div>








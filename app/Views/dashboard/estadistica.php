
<img style="position:absolute; right:20px; top:30px; width:120px" src="<?php echo base_url(); ?>assets/img/logo_color.png">
<h4 class="mt-1"><?php echo $titulo; ?></h4>
<p><a class="btn btn-sm btn-light" href="<?php echo base_url( "sociodata/".urlencode( base64_encode( $socio->password_original() ) ) ); ?>"><i class="fa fa-undo"></i> Regresar a detalles de socio</a></p>

 <p class="text-end">
            <?php echo $socio->avatar()." ".$socio->id( $modelo )." ".$socio->nombre( 2 ); ?>
        </p>

<ul class="nav nav-tabs">
    <?php
    foreach( MODELOS as $m ){
        echo "\n<li class=\"nav-item\"><a class=\"nav-link ".( $m[ "codigo" ] == $modelo ? "active fw-bold" : "" )."\" aria-current=\"page\" href=\"".base_url( "estadistica/".urlencode( base64_encode( $socio->password_original() ) )."/".$m[ "codigo" ] )."\"><span class=\"text-{$m[ "settings" ][ "color" ]}\"><i class=\"fa fa-{$m[ "settings" ][ "icono" ]}\"></i> {$m[ "nombre" ]}</span></a></li>";
    }
    ?>

</ul>

<div class="card mb-5" style="border-color: var(--bs-border-color); border-top:none; border-radius: 0 0 6px 6px">
    <div class="card-body">
       <div class="row">
            <div class="col-lg-3 text-center">
                <h1 class="display-5 m-0"><?php echo number_format( $stats[ "202508" ][ "socios_activos" ] ); ?></h1>
                Socios activos en la red
            </div>
            <div class="col-lg-3 text-center">
                <h1 class="display-5 m-0"><?php echo number_format( $stats[ "202508" ][ "niveles" ][ 1 ] ); ?></h1>
                Socios activos directos
            </div>
            <div class="col-lg-3 text-center">
                <h1 class="display-5 m-0"><?php echo number_format( $stats[ "202508" ][ "nuevos" ] ); ?></h1>
                Socios nuevos en la red
            </div>
            <div class="col-lg-3 text-center">
                <h1 class="display-5 m-0"><?php echo number_format( $stats[ "202508" ][ "rojos" ] ); ?></h1>
                Socios en rojo
            </div>
        </div>

        <div class="row mt-4 mb-3">
            <div class="col-lg-3 text-center">
                <h1 class="display-5 m-0">$<?php echo number_format( $stats[ "202508" ][ "consumo_red" ], 2 ); ?></h1>
                Consumo de red en el mes
            </div>
            <div class="col-lg-3 text-center">
                <h1 class="display-5 m-0">$<?php echo number_format( $stats[ "202508" ][ "ticket_promedio" ], 2 ); ?></h1>
                Ticket promedio
            </div>
            <div class="col-lg-3 text-center">
                <h1 class="display-5 m-0">$<?php echo number_format( $stats[ "202508" ][ "socio" ]->ingresos, 2 ); ?></h1>
                Comisiones propias en el mes
            </div>
            <div class="col-lg-3 text-center">
                <h1 class="display-5 m-0">$<?php echo number_format( $stats[ "202508" ][ "ingresos_red" ], 2 ); ?></h1>
                Comisiones de la red en el mes
            </div>
        </div>
    </div>
</div>

<div class="row mb-5">

    <div class="col-lg-6 mb-4">
        <div class="card">
            <div class="card-header bg-teal">
                <h5 class="m-0 text-white">Socios activos</h5>
            </div>
            <div class="card-body text-center">
                <img src="<?php echo base_url(); ?>assets/img/demo.png" class="img-fluid">
            </div>
        </div>
    </div>

    <div class="col-lg-6 mb-4">
        <div class="card">
            <div class="card-header bg-teal">
                <h5 class="m-0 text-white">Nuevos socios</h5>
            </div>
            <div class="card-body text-center">
                <img src="<?php echo base_url(); ?>assets/img/demo.png" class="img-fluid">
            </div>
        </div>
    </div>

    <div class="col-lg-6 mb-4">
        <div class="card">
            <div class="card-header bg-teal">
                <h5 class="m-0 text-white">Volumen de consumo</h5>
            </div>
            <div class="card-body text-center">
                <img src="<?php echo base_url(); ?>assets/img/demo.png" class="img-fluid">
            </div>
        </div>
    </div>

    <div class="col-lg-6 mb-4">
        <div class="card">
            <div class="card-header bg-teal">
                <h5 class="m-0 text-white">Comisiones generadas</h5>
            </div>
            <div class="card-body text-center">
                <img src="<?php echo base_url(); ?>assets/img/demo.png" class="img-fluid">
            </div>
        </div>
    </div>
</div>
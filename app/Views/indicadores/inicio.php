<script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>

<div class="row">
    <div class="col-lg-6">
        <h4 class="mt-1 mb-0"><?php echo $titulo; ?></h4>
        <p><a class="btn btn-light btn-sm" href="<?php echo base_url( "admin" ); ?>"><i class="fa fa-undo"></i> Regresar a administración</a></p>
    </div>
    <div class="col-lg-6 text-end">
        <button class="btn btn-success btn-sm" id="descarga_ingreso"><i class="fa fa-file-excel"></i><span class="d-none d-lg-inline"> Descargar Excel</span></button>

        <select id="empresa_indicadores" class="mt-4 form-select form-select-sm" style="display: inline-block; width:auto">
            <?php
                foreach( MODELOS as $m ){
                    echo "\n<option ".( $modelo == $m[ "codigo" ] ? "selected" : "" )." value=\"{$m[ "codigo" ]}\">".mb_strtoupper( $m[ "nombre" ] )."</option>";
                }
            ?>
        </select>

        <select id="mes_indicadores" class="mt-4 form-select form-select-sm" style="display: inline-block; width:auto">
            <?php 
                for( $a = 1; $a <= 12; $a++ ){
                    $em = str_pad( $a, 2, "0", STR_PAD_LEFT );
                    echo "\n<option ".( substr( $mes, 4, 2 ) == $em ? "selected" : "" )." value=\"{$em}\">".mb_strtoupper( mes( $a ) )."</option>";
                }
            ?>
        </select>
        <select id="year_indicadores" class="mt-4 form-select form-select-sm" style="display: inline-block; width:auto">
            <?php 
                for( $a = 2018; $a <= date( "Y" ); $a++ ){
                    echo "\n<option ".( substr( $mes, 0, 4 ) == $a ? "selected" : "" )." value=\"{$a}\">{$a}</option>";
                }
            ?>
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
            <div class="card-body text-center">
                <div id="chart_ventas_total"></div><hr>
                <h1>$<?php echo number_format( $historico[ "venta" ][ "total" ][ $mes ], 2 ); ?></h1>
            </div>
        </div>
    </div>

    <div class="col-lg-4">
        <div class="card mt-3"><div class="card-header bg-teal"><h5 class="m-0 text-white">Venta socios nuevos</h5></div>
            <div class="card-body text-center"><img src="<?php echo base_url(); ?>assets/img/demo.png" class="d-none img-fluid"><br><hr><h1>$<?php echo number_format( $historico[ "venta" ][ "nuevos" ][ $mes ], 2 ); ?></h1></div>
        </div>
    </div>

    <div class="col-lg-4">
        <div class="card mt-3"><div class="card-header bg-teal"><h5 class="m-0 text-white">Venta por recompra</h5></div>
            <div class="card-body text-center"><img src="<?php echo base_url(); ?>assets/img/demo.png" class="d-none img-fluid"><br><hr><h1>$<?php echo number_format( $historico[ "venta" ][ "recompra" ][ $mes ], 2 ); ?></h1></div>
        </div>
    </div>

    <div class="col-lg-4">
        <div class="card mt-3"><div class="card-header bg-teal"><h5 class="m-0 text-white">Pedidos totales</h5></div>
            <div class="card-body text-center"><img src="<?php echo base_url(); ?>assets/img/demo.png" class="d-none img-fluid"><br><hr><h1><?php echo number_format( $historico[ "pedidos" ][ "total" ][ $mes ] ); ?></h1></div>
        </div>
    </div>

    <div class="col-lg-4">
        <div class="card mt-3"><div class="card-header bg-teal"><h5 class="m-0 text-white">Pedidos primer compra</h5></div>
            <div class="card-body text-center"><img src="<?php echo base_url(); ?>assets/img/demo.png" class="d-none img-fluid"><br><hr><h1><?php echo number_format( $historico[ "pedidos" ][ "nuevos" ][ $mes ] ); ?></h1></div>
        </div>
    </div>

    <div class="col-lg-4">
        <div class="card mt-3"><div class="card-header bg-teal"><h5 class="m-0 text-white">Pedidos recompra</h5></div>
            <div class="card-body text-center"><img src="<?php echo base_url(); ?>assets/img/demo.png" class="d-none img-fluid"><br><hr><h1><?php echo number_format( $historico[ "pedidos" ][ "recompra" ][ $mes ] ); ?></h1></div>
        </div>
    </div>

</div>


<h5 class="mt-5 mb-0">Comisiones</h5>
<div class="row">
    <div class="col-lg-4">
        <div class="card mt-3"><div class="card-header bg-deep-purple"><h5 class="m-0 text-white">Reparto total</h5></div>
            <div class="card-body text-center"><img src="<?php echo base_url(); ?>assets/img/demo.png" class="d-none img-fluid"><br><hr><h1>$<?php echo number_format( $historico[ "reparto" ][ "total" ][ $mes ], 2 ); ?></h1></div>
        </div>
    </div>

    <div class="col-lg-4">
        <div class="card mt-3"><div class="card-header bg-deep-purple"><h5 class="m-0 text-white">Reparto socios nuevos</h5></div>
            <div class="card-body text-center"><img src="<?php echo base_url(); ?>assets/img/demo.png" class="d-none img-fluid"><br><hr><h1>$<?php echo number_format( $historico[ "reparto" ][ "nuevos" ][ $mes ], 2 ); ?></h1></div>
        </div>
    </div>

    <div class="col-lg-4">
        <div class="card mt-3"><div class="card-header bg-deep-purple"><h5 class="m-0 text-white">Reparto por recompra</h5></div>
            <div class="card-body text-center"><img src="<?php echo base_url(); ?>assets/img/demo.png" class="d-none img-fluid"><br><hr><h1>$<?php echo number_format( $historico[ "reparto" ][ "recompra" ][ $mes ], 2 ); ?></h1></div>
        </div>
    </div>
</div>

<h5 class="mt-5 mb-0">Ticket promedio</h5>
<div class="row">
    <div class="col-lg-4">
        <div class="card mt-3"><div class="card-header bg-orange"><h5 class="m-0 text-white">Ticket promedio total</h5></div>
            <div class="card-body text-center"><img src="<?php echo base_url(); ?>assets/img/demo.png" class="d-none img-fluid"><br><hr><h1>$<?php echo number_format( $historico[ "ticket" ][ "total" ][ $mes ], 2 ); ?></h1></div>
        </div>
    </div>

    <div class="col-lg-4">
        <div class="card mt-3"><div class="card-header bg-orange"><h5 class="m-0 text-white">Ticket promedio socios nuevos</h5></div>
            <div class="card-body text-center"><img src="<?php echo base_url(); ?>assets/img/demo.png" class="d-none img-fluid"><br><hr><h1>$<?php echo number_format( $historico[ "ticket" ][ "nuevos" ][ $mes ], 2 ); ?></h1></div>
        </div>
    </div>

    <div class="col-lg-4">
        <div class="card mt-3"><div class="card-header bg-orange"><h5 class="m-0 text-white">Ticket promedio por recompra</h5></div>
            <div class="card-body text-center"><img src="<?php echo base_url(); ?>assets/img/demo.png" class="d-none img-fluid"><br><hr><h1>$<?php echo number_format( $historico[ "ticket" ][ "recompra" ][ $mes ], 2 ); ?></h1></div>
        </div>
    </div>
</div>

<h5 class="mt-5 mb-0">Socios</h5>
<div class="row">
    <div class="col-lg-4">
        <div class="card mt-3"><div class="card-header bg-blue"><h5 class="m-0 text-white">Total de socios Activos</h5></div>
            <div class="card-body text-center"><img src="<?php echo base_url(); ?>assets/img/demo.png" class="d-none img-fluid"><br><hr><h1><?php echo number_format( $historico[ "socios" ][ "SOCIOS_ACTIVOS" ][ $mes ] ); ?></h1></div>
        </div>
    </div>

    <div class="col-lg-4">
        <div class="card mt-3"><div class="card-header bg-blue"><h5 class="m-0 text-white">Socios inscritos</h5></div>
            <div class="card-body text-center"><img src="<?php echo base_url(); ?>assets/img/demo.png" class="d-none img-fluid"><br><hr><h1><?php echo number_format( $historico[ "socios" ][ "SOCIOS_INSCRITOS" ][ $mes ] ); ?></h1></div>
        </div>
    </div>

    <div class="col-lg-4">
        <div class="card mt-3"><div class="card-header bg-blue"><h5 class="m-0 text-white">Socios activos con primer compra</h5></div>
            <div class="card-body text-center"><img src="<?php echo base_url(); ?>assets/img/demo.png" class="d-none img-fluid"><br><hr><h1><?php echo number_format( $historico[ "socios" ][ "SOCIOS_NUEVOS" ][ $mes ] ); ?></h1></div>
        </div>
    </div>

    <div class="col-lg-4">
        <div class="card mt-3"><div class="card-header bg-blue"><h5 class="m-0 text-white">Socios activos con recompra en el mes</h5></div>
            <div class="card-body text-center"><img src="<?php echo base_url(); ?>assets/img/demo.png" class="d-none img-fluid"><br><hr><h1><?php echo number_format( $historico[ "socios" ][ "SOCIOS_RECOMPRA" ][ $mes ] ); ?></h1></div>
        </div>
    </div>

    <div class="col-lg-4">
        <div class="card mt-3"><div class="card-header bg-blue"><h5 class="m-0 text-white">Socios a inactividad</h5></div>
            <div class="card-body text-center"><img src="<?php echo base_url(); ?>assets/img/demo.png" class="d-none img-fluid"><br><hr><h1><?php echo number_format( $historico[ "socios" ][ "SOCIOS_BAJA" ][ $mes ] ); ?></h1></div>
        </div>
    </div>
</div>

<h5 class="mt-5 mb-0">Productos ordenados por venta mensual</h5>

<div class="row">

                <?php
                    $historico[ "productos" ] = ordena_productos( $historico[ "productos" ], $mes );
                    

                     foreach( $historico[ "productos" ] as $p => $v ){
                        if( !isset( $v[ $mes ] ) ){
                            $v[ $mes ] = 0;
                        }
                        
                        echo "\n
                            <div class=\"col-lg-4\">
                                <div class=\"card mt-3\"><div class=\"card-header bg-red\"><h5 class=\"m-0 text-white\">".PRODUCTOS[ substr( $p, 10 ) ][ "data" ][ "nombre" ]."</h5></div>
                                    <div class=\"card-body text-center\"><img src=\"".base_url()."assets/img/demo.png\" class=\"d-none img-fluid\"><br><hr><h1>".number_format( $v[ $mes ] )."</h1></div>
                                </div>
                            </div>
                            ";
                    } 
                ?>


</div>




<script>

    var options = {
        colors: ['var(--bs-marine)'],
        series: [{ 'name': 'Ventas', 'data': [ <?php echo implode( ',', $historico[ "venta" ][ "total" ] ); ?> ]}],
        chart: {
            type: 'bar',
            height: 230,
            stacked: false,
            toolbar: {
                show: true
            },
            zoom: {
                enabled: true
            }
        },
        dataLabels: {
            enabled: false
        },        
        yaxis: {
        },      
        grid: {
            row: {
                colors: ['#e5e5e5', 'transparent'],
                opacity: 0.5
            }, 
        },
        stroke: {
            width: 2,
            curve: 'smooth'
        },
        xaxis: {
            categories: <?php echo json_encode( $meses ); ?>,
        },
        legend: {
            show: false
        },
        fill: {
            opacity: 1
        }
    };

    var chart = new ApexCharts(document.querySelector("#chart_ventas_total"), options);
    chart.render();   

</script>



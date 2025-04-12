<script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>

<h4 class="mt-1 mb-4"><?php echo $titulo; ?></h4>

<div class="row">
    <div class="col-lg-4 col-md-6 mb-3">

        <div class="row mb-3">
            <div class="col-6">
                <div class="card text-center py-3 bg-teal text-white">
                    <h1 class="m-0 text-white"><i class="far fa-user text-marine"></i> <?php echo $total_activos; ?></h1>
                    <p class="m-0">Socios</p>
                </div>
            </div>
            <div class="col-6">
                <div class="card text-center py-3 bg-teal text-white">
                    <h1 class="m-0 text-white"><i class="fa fa-arrow-trend-up text-marine"></i> <?php echo $total_inversiones; ?></h1>
                    <p class="m-0">Inversiones</p>
                </div>
            </div>
        </div>

        <div class="card text-center py-3">
            <div class="row">
                <?php
                    foreach( $rangos as $rango ){
                        echo "\n<div class=\"col-4\"><h1 class=\"m-0\">{$rango[ "cantidad" ]}</h1>{$rango[ "nombre" ]}<img src=\"".base_url()."assets/img/rangos/{$rango[ "codigo" ]}.png\" class=\"img-fluid px-3\"></div>";      
                    }
                ?>
            </div>
            <p class="m-0">Rangos liderazgo</p>
        </div>

    </div>


    <div class="col-lg-4 col-md-6 mb-3">
        <div class="card mb-1">
            <div id="chart_inversiones_tipo"></div>
        </div>

        <div class="card text-center py-3">
        <div class="row">
                <?php
                    foreach( PRODUCTOS as $codigo => $tipo ){
                        echo "\n<div class=\"col-4\"><table align=\"center\"><tr><td class=\"pe-1\"><img src=\"".base_url()."assets/img/productos/{$codigo}.png\" class=\"\" width=\"30\"></td><td><h1 class=\"m-0 text-{$tipo[ "data" ][ "color" ]}\">{$data_inversiones[ $codigo ]}</h1></td></tr></table></div>";      
                    }
                ?>
            </div> 
            <p class="m-0">Total de inversiones por tipo</p>
        </div>
    </div>


    <div class="col-lg-4 col-md-6 mb-3">
        <div class="card mb-1">
            <div id="chart_semilla"></div>
        </div>

        <div class="card text-center py-3 bg-marine text-white">
            <h1 class="m-0 text-white">$<?php echo number_format( array_sum( $semilla ), 2 ); ?></h1>
            <p class="m-0">Capital semilla</p>
        </div>
    </div>

    
    <div class="col-lg-4 col-md-6 mb-3">
        <div class="card mb-1">
            <div id="chart_rendimiento"></div>
        </div>

        <div class="card text-center py-3 bg-teal text-white">
            <h1 class="m-0 text-white">$<?php echo number_format( array_sum( $semilla ), 2 ); ?></h1>
            <p class="m-0">Capital semilla</p>
        </div>
    </div>


    <div class="col-lg-4 col-md-6 mb-3">
        <div class="card text-center">
            &nbsp;
        </div>
    </div>

    <div class="col-lg-4 col-md-6 mb-3">
        <div class="card text-center">
            &nbsp;
        </div>
    </div>

    <div class="col-lg-4 col-md-6 mb-3">
        <div class="card text-center">
            &nbsp;
        </div>
    </div>
</div>




<script>
    var options = {
        colors: ['var(--bs-green)', 'var(--bs-mustard)', 'var(--bs-red)'],
        series: <?php echo json_encode( $data ); ?>,
        chart: {
            type: 'line',
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

    var chart = new ApexCharts(document.querySelector("#chart_inversiones_tipo"), options);
    chart.render();


    var options = {
        colors: ['var(--bs-teal)'],
        series: [{
            name: 'Capital semilla',
            data: <?php echo json_encode( array_values( $data_semilla ) ); ?>
        }],
        chart: {
            type: 'line',
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
            labels: {
                formatter: function (value) {
                    return "$" + value ;
                }
            }
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

    var chart = new ApexCharts(document.querySelector("#chart_semilla"), options);
    chart.render();
</script>
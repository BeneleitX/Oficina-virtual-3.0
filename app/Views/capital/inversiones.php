<script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>

<h4 class="mt-1 mb-4"><?php echo $titulo; ?></h4>

<div class="row">


    <div class="col-lg-4 col-md-6 mb-3">
        <div class="card mb-1">
        <div class="row">
                <?php
                    foreach( $rangos as $rango ){
                        echo "\n<div class=\"col-4 mb-3\"><table align=\"center\"><tr><td class=\"text-center pt-3\"><img src=\"".base_url()."assets/img/rangos/{$rango[ "codigo" ]}.png\" class=\"p-3\" width=\"140\"></td</tr><tr><td class=\"text-center\"><h1 class=\"m-0\">{$rango[ "cantidad" ]}</h1>{$rango[ "nombre" ]}</td></tr></table></div>";      
                    }
                ?>
            </div>
        </div>

        <div class="card text-center bg-teal py-3  text-white">
        <h1 class="m-0 text-white"><?php echo number_format( $total_activos ); ?></h1>
            <p class="m-0">Socios activos</p>
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
            <div id="chart_compras"></div>
        </div>

        <div class="card text-center py-3">
            <h1 class="m-0"><?php echo number_format( $total_compras ); ?></h1>
            <p class="m-0">Compra de paquetes de inversión</p>
        </div>
    </div>

    <div class="col-lg-4 col-md-6 mb-3">
        <div class="card mb-1">
            <div id="chart_semilla"></div>
        </div>

        <div class="card text-center py-3 bg-marine text-white">
            <h1 class="m-0 text-white">$<?php echo number_format( $data_semilla[ date( "Ym" ) ], 2 ); ?></h1>
            <p class="m-0">Capital semilla</p>
        </div>
    </div>

    
    <div class="col-lg-4 col-md-6 mb-3">
        <div class="card mb-1">
            <div id="chart_rendimiento"></div>
        </div>

        <div class="card text-center py-3 bg-teal text-white">
            <h1 class="m-0 text-white">$<?php echo number_format( $data_rendimiento[ date( "Ym" ) ], 2 ); ?></h1>
            <p class="m-0">Rendimiento total</p>
        </div>
    </div>


    <div class="col-lg-4 col-md-6 mb-3">
        <div class="card mb-1">
            <div id="chart_comisiones"></div>
        </div>

        <div class="card text-center py-3 bg-teal text-white">
            <h1 class="m-0 text-white">$<?php echo number_format( $total_comisiones, 2 ); ?></h1>
            <p class="m-0">Comisiones repartidas (10/5/3/2)</p>
        </div>
    </div>

    <div class="col-lg-4 col-md-6 mb-3">
        <div class="card mb-1">
            <div id="chart_retiros"></div>
        </div>

        <div class="card text-center py-3 bg-teal text-white">
            <h1 class="m-0 text-white">$<?php echo number_format( $total_comisiones, 2 ); ?></h1>
            <p class="m-0">Retiros de rendimientos</p>
        </div>
    </div>

    <div class="col-lg-4 col-md-6 mb-3">
        <div class="card mb-1">
            <div id="chart_bonos"></div>
        </div>

        <div class="card text-center py-3 bg-teal text-white">
            <h1 class="m-0 text-white">$<?php echo number_format( $total_comisiones, 2 ); ?></h1>
            <p class="m-0">Bono de liderazgo (0.33/0.66/1.00)</p>
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


    var options = {
        colors: ['var(--bs-teal)'],
        series: [{
            name: 'Rendimiento',
            data: <?php echo json_encode( array_values( $data_rendimiento ) ); ?>
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

    var chart = new ApexCharts(document.querySelector("#chart_rendimiento"), options);
    chart.render();    
    

    var options = {
        colors: ['var(--bs-marine)'],
        series: [{
            name: 'Comisiones',
            data: <?php echo json_encode( array_values( $data_comisiones[ "total"] ) ); ?>
        }],
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
            categories: <?php echo json_encode( $semanas ); ?>,
        },
        legend: {
            show: false
        },
        fill: {
            opacity: 1
        }
    };

    var chart = new ApexCharts(document.querySelector("#chart_comisiones"), options);
    chart.render();    

        

    var options = {
        colors: ['var(--bs-green)', 'var(--bs-mustard)', 'var(--bs-red)'],
        series: <?php echo json_encode( $data_compras ); ?>,
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
            categories: <?php echo json_encode( $semanas ); ?>,
        },
        legend: {
            show: false
        },
        fill: {
            opacity: 1
        }
    };

    var chart = new ApexCharts(document.querySelector("#chart_compras"), options);
    chart.render();    
</script>
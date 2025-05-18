<link href="<?php echo base_url(); ?>assets/css/datatables.css" rel="stylesheet"/>
<script src="<?php echo base_url(); ?>assets/js/datatables.js" type="text/javascript"></script>
<script src="<?php echo base_url(); ?>assets/js/datatables_bs5.js" type="text/javascript"></script>

<script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>

<h4 class="mt-1 mb-0"><?php echo $titulo; ?></h4>
<p>
    <a class="btn btn-light btn-sm" href="<?php echo base_url( "admin" ); ?>"><i class="fa fa-undo"></i> Regresar a administración</a>
</p>

<div class="row">


    <div class="col-lg-4 col-md-6 mb-5">
        <div class="card mb-1">
            <div class="card-body">
                <div class="row">
                    <div class="col-6">
                        <a href="<?php echo base_url( "capital24" ); ?>" class="btn btn-lg mb-3 mt-3 btn-outline-info w-100">Retiros</a>
                    </div>
                    <div class="col-6">
                        <a href="<?php echo base_url(); ?>periodos/50-INVERSION" class="btn btn-lg mb-3 mt-3 btn-outline-info w-100">Corte</a>
                    </div>
                    <div class="col-6">
                        <a href="<?php echo base_url( "bono_liderazgo" ); ?>" class="btn btn-lg mb-3 btn-outline-info w-100" >Rangos</a>
                    </div>

                    <div class="col-6">
                        <button class="btn btn-lg mb-3 btn-light w-100 disabled" disabled>&nbsp;</button>
                    </div>
                    <div class="col-6">
                        <button class="btn btn-lg mb-3 btn-light w-100 disabled" disabled>&nbsp;</button>
                    </div>
                    <div class="col-6">
                        <button class="btn btn-lg mb-3 btn-light w-100 disabled" disabled>&nbsp;</button>
                    </div>
                </div>
            </div>
        </div>

        <div class="card text-center bg-mustard py-3  text-white">
        <h1 class="m-0 text-white"><?php echo number_format( $total_activos ); ?></h1>
            <p class="m-0">Socios con inversión activa</p>
        </div>
    </div>

    
    <div class="col-lg-4 col-md-6 mb-5">
        <div class="card mb-1">
            <div id="chart_rangos"></div>
        </div>

        <div class="card text-center py-3">
        <div class="row">
                <?php
                    foreach( $rangos as $rango ){
                        echo "\n<div class=\"col-4\"><table align=\"center\"><tr><td class=\"pe-1\"><img src=\"".base_url()."assets/img/rangos/{$rango[ "codigo" ]}.png\" class=\"\" width=\"40\"></td><td><h1 class=\"m-0\">{$drangos_total[ $rango[ "codigo" ] ][1]}</h1><span class=\"badge bg-{$rango[ "color" ]}\">{$rango[ "nombre" ]}</span></td></tr></table></div>";      
                    }
                ?>
            </div> 
        </div>
    </div>

    <div class="col-lg-4 col-md-6 mb-5">
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

</div>

<?php if( $usuario->permiso( "45-ADMIN-CAPITAL") ){ ?>

<div class="row">
    
    <div class="col-lg-4 col-md-6 mb-5">
        <div class="card mb-1">
            <div id="chart_compras"></div>
        </div>

        <div class="card text-center py-3 bg-teal text-white">
            <h1 class="m-0 text-white"><?php echo number_format( $total_compras ); ?></h1>
            <p class="m-0">Compra de paquetes de inversión</p>
        </div>
    </div>

    <div class="col-lg-4 col-md-6 mb-5">
        <div class="card mb-1">
            <div id="chart_semilla"></div>
        </div>

        <div class="card text-center py-3 bg-teal text-white">
            <h1 class="m-0 text-white">$<?php echo number_format( $data_semilla[ date( "Ym" ) ], 2 ); ?></h1>
            <p class="m-0">Capital semilla</p>
        </div>
    </div>

    
    <div class="col-lg-4 col-md-6 mb-5">
        <div class="card mb-1">
            <div id="chart_rendimiento"></div>
        </div>

        <div class="card text-center py-3 bg-teal text-white">
            <h1 class="m-0 text-white">$<?php echo number_format( $data_rendimiento[ date( "Ym" ) ], 2 ); ?></h1>
            <p class="m-0">Rendimiento total</p>
        </div>
    </div>


    <div class="col-lg-4 col-md-6 mb-5">
        <div class="card mb-1">
            <div id="chart_comisiones"></div>
        </div>

        <div class="card text-center py-3 bg-red text-white">
            <h1 class="m-0 text-white">$<?php echo number_format( $total_comisiones, 2 ); ?></h1>
            <p class="m-0">Comisiones repartidas (10/5/3/2)</p>
        </div>
    </div>

    <div class="col-lg-4 col-md-6 mb-5">
        <div class="card mb-1">
            <div id="chart_retiros"></div>
        </div>

        <div class="card text-center py-3 bg-red text-white">
            <h1 class="m-0 text-white">$<?php echo number_format( $total_retiros, 2 ); ?></h1>
            <p class="m-0">Retiros de rendimientos</p>
        </div>
    </div>

    <div class="col-lg-4 col-md-6 mb-5">
        <div class="card mb-1">
            <div id="chart_bono"></div>
        </div>

        <div class="card text-center py-3 bg-red text-white">
            <h1 class="m-0 text-white">$<?php echo number_format( $total_bono, 2 ); ?></h1>
            <p class="m-0">Bono de liderazgo (0.33/0.66/1.00)</p>
        </div>
    </div>

</div>



<div class="card mb-4">
    <div class="card-header bg-marine text-white">
    <h5 class="text-white m-0">TOP 10 de socios</h5>
    </div>
    <table id="tabla_socios" class="table table-striped m-0">
        <thead>
            <tr>
                <th>Socio</th>
                <th>Nombre</th>
                <th>Teléfono</th>
                <th>Wallet</th>
                <th>Inversiones</th>
                <th>Bolsa de red</th>
                <th>Directos</th>
                <th></th>
            </tr>
        </thead>
        <tbody>
            <?php 
            foreach( $ranking as $s ){
                $socio = model( "UsuarioModel" )->find( $s[ "socio" ] );

                echo "<tr>";
                echo "<td nowrap>".$socio->id( "50-INVERSION" )."</td>";
                echo "<td>".$socio->avatar(24)." ".$socio->nombre( 2 )."</td>";
                echo "<td>{$socio->telefono}</td>";
                echo "<td>".( $socio->data->wallet ?? "" )."</td>";
                echo "<td class=\"text-end\"><strong>$".number_format( $s[ "semilla" ], 2 )."</strong></td>";
                echo "<td class=\"text-end\"><strong>$".number_format( $s[ "bolsa" ], 2 )."</strong></td>";
                echo "<td>{$s[ "directos" ]}</td>";
                echo "<td class=\"text-end\"><a target=\"_blank\" href=\"".base_url()."capital/".urlencode( base64_encode( $socio->password_original() ) )."\" class=\"btn btn-xs btn-secondary\"><i class=\"fa fa-magnifying-glass\"></i> Detalles</a></td>";
                echo "</tr>";
            }
            ?>
        </tbody>
    </table>
</div>

<?php } ?>


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
        colors: ['var(--bs-blue)', 'var(--bs-indigo)', 'var(--bs-deep-purple)'],
        series: <?php echo json_encode( $drangos ); ?>,
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

    var chart = new ApexCharts(document.querySelector("#chart_rangos"), options);
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


    var options = {
        colors: ['var(--bs-marine)'],
        series: [{
            name: 'Retiros',
            data: <?php echo json_encode( array_values( array_reverse( $retiros ) ) ); ?>
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
            categories: <?php echo json_encode( $meses ); ?>,
        },
        legend: {
            show: false
        },
        fill: {
            opacity: 1
        }
    };

    var chart = new ApexCharts(document.querySelector("#chart_retiros"), options);
    chart.render();  
    

    var options = {
        colors: ['var(--bs-marine)'],
        series: [{
            name: 'Retiros',
            data: <?php echo json_encode( array_values( array_reverse( $bono ) ) ); ?>
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
            categories: <?php echo json_encode( $meses ); ?>,
        },
        legend: {
            show: false
        },
        fill: {
            opacity: 1
        }
    };

    var chart = new ApexCharts(document.querySelector("#chart_bono"), options);
    chart.render();  
</script>
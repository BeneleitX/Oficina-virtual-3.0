<script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>

<div class="row">
    <div class="col-8">
        <h4 class="mt-1 mb-0"><?php echo $titulo; ?></h4>
    </div>
    <div class="col-4 text-end">
        <button class="btn btn-success col-12 mt-3" id="descarga_ingreso"><i class="fa fa-file-excel"></i><span class="d-none d-lg-inline"> Descargar Excel</span></button>
    </div>
</div>

<div class="row mb-4">
    <div class="col-lg-8">
        <?php echo pills( "ingreso_mensual", $modelo ); ?>
    </div>
    <div class="col-lg-4 text-end">
        <div class="row mt-3">
            <div class="col-4">
                <a href="<?php echo base_url()."balance/{$modelo}/".codigo_periodo( $modelo ); ?>" class="btn btn-outline-secondary"> Detalle SEMANAL</a>
            </div>
            <div class="col-4">
                <a href="<?php echo base_url()."ingreso_mensual/{$modelo}"; ?>" class="btn btn-secondary"> Ingreso MENSUAL</a>
            </div>
            <div class="col-4">
                <a href="<?php echo base_url()."depositos/{$modelo}"; ?>" class="btn btn-outline-secondary"> Depósitos recibidos</a>
            </div>
        </div>
    </div>
</div>

<div id="chart_ingreso"></div>

<?php
$data = [
    "meses" => [],
    "cantidades" => []
];

$mes  = date( "Ym" );

while( $mes >= date( "Ym", strtotime( $usuario->historial->registro < '2024-08-01' ? '2024-08-01' : $usuario->historial->registro ) ) ){

    $hoy = strtoupper( mes( substr( $mes, 4, 2 ) ) )." ".substr( $mes, 0, 4 );
    $cantidad = $ingresosxdia[ $hoy ] ?? null;

    ?>
    <div class="card mb-4 col-lg-6">
        <div class="card-header bg-<?php echo isset( $ingreso[ $mes ] ) ? "marine" : "gray-500" ?>"><h5 class="m-0 text-white"><?php echo $hoy; ?></h5></div>

            <table class="mb-0 table table-striped bg-white tabla_comisiones" id="t_<?php echo date("Y-m-d"); ?>">
                <tbody>
                    <?php 
                        $suma = 0;
                        if( isset( $ingreso[ $mes ] ) ){
                            foreach( $ingreso[ $mes ] as $k => $c ){

                                $esquema = ESQUEMAS[ $k ][ "settings" ][ "titulo" ];
                                $suma += $c;
                                $mes_bono = strtoupper( mes( date( "m", strtotime( substr( $mes, 0, 4 )."-".substr( $mes, 4, 2 )."-01 + 2 month" ) ) ) )." ".date( "Y", strtotime( substr( $mes, 0, 4 )."-".substr( $mes, 4, 2 )."-01 + 2 month" ) );

                                

                                echo "\n<tr\">
                                    <td width=\"20%\" nowrap><span class=\"badge bg-".MODELOS[ $modelo ][ "settings" ][ "color" ]."\"><i class=\"fa fa-".MODELOS[ $modelo ][ "settings" ][ "icono" ]."\"></i> ".MODELOS[ $modelo ][ "nombre" ]."</span> {$esquema}".( $k == "530-LIDERAZGO" ? " <span class=\"badge opacity-50 border border-red text-red bg-white\"><i class=\"fa fa-warning text-red\"></i> Se paga en {$mes_bono}</span>" : "" )."</td>
                                    <td width=\"10%\" class=\"text-end\"><strong>$".number_format( $c, 2 )."</strong></td>

                                </tr>";
                            }
                        }
                    ?>
                
                </tbody>

            </table>
        <div class="card-footer text-end bg-gray-<?php echo isset( $ingreso[ $mes ] ) ? "600" : "400" ?>">
        <h5 class="m-0 text-white">$<?php echo number_format( $suma, 2 ); ?></h5>
        </div>
    </div>
    <?php 

    $data[ "meses" ][] = strtoupper( mes( substr( $mes, 4, 2 ), 3 ) );
    $data[ "cantidades" ][] = $suma;

    $mes = date( "Ym", strtotime( substr( $mes, 0, 4 )."-".substr( $mes, 4, 2 )."-01 - 1 month" ) );
}


while( sizeof( $data[ "meses" ] ) < 12 ){
    $data[ "meses" ][] = "";
    $data[ "cantidades" ][] = 0;
}

?>



<script>
    var modelo = '<?php echo $modelo; ?>';

    var options = {
          series: [{
            name: "Ingresos",
            data: [ <?php echo implode( ",", array_reverse( $data[ "cantidades" ] ) ); ?> ]
        }],
          chart: {
          height: 350,
          type: 'area',
          zoom: {
            enabled: false
          }
        },
        colors: ['#009779'],
        fill: {
          type: 'gradient',
          gradient: {
              shadeIntensity: 1,
              inverseColors: false,
              opacityFrom: 0.5,
              opacityTo: 0.1,
           
            },
        },        
        markers: {
          size: 7,
          color: '#ff0000'
        },
        dataLabels: {
          enabled: false
        },
        stroke: {
          curve: 'smooth'
        },
        title: {
          text: 'Ingresos por mes en <?php echo MODELOS[ $modelo ][ "nombre" ]; ?>',
          align: 'left'
        },
        grid: {
          row: {
            colors: ['#f0f0f0', '#ffffff'], // takes an array which will be repeated on columns
            opacity: 0.5
          },
        },
        xaxis: {
          categories: [ '<?php echo implode( "','", array_reverse( $data[ "meses" ] ) ); ?>' ],
        }
        };

        var chart = new ApexCharts(document.querySelector("#chart_ingreso"), options);
        chart.render();
</script>

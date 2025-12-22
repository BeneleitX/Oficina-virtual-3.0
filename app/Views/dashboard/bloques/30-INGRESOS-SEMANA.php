<script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>
<div id="chart_ingresos"></div>

<div class="row mx-1 mb-0">

<?php

$total    = [];
$t_actual = 0;
$bot      = "";
$headers  = "";
$semanas  = [];
$data     = [];
$dto      = new \DateTime();

$dto->setISODate( date( "o" ), date( "W" ) );
$colores = [];

for( $q = 0; $q < 10; $q++ ){
    $semanas[] = $dto->format('W');

    foreach( MODELOS as $m ){
        $comisiones = $usuario->getComisiones( codigo_periodo( $m[ "codigo" ], $dto->format('Y-m-d') ) );

        $total[ $m[ "codigo" ] ][ $q ] = 0;

        if(!$q){
            $colores[] = $m[ "settings" ][ "color" ];
        }

        foreach( $comisiones as $c ){
            $total[ $m[ "codigo" ] ][ $q ] += ( $m[ "settings" ][ "periodo" ] == "SEMANAL" ? $c->cantidad : 0 );
        }   
        
        if( !$q ){
            $t_actual += $total[ $m[ "codigo" ] ][ $q ];

            echo "<div class=\"col-6 text-center mb-3\"><p class=\"m-0 text-center text-{$m[ "settings" ][ "color" ]}\"><i class=\"fa fa-{$m[ "settings" ][ "icono" ]}\"></i> {$m[ "nombre" ]}</p><h5><span style=\"xopacity:".( $total[ $m[ "codigo" ] ][ $q ] ? "1" : "0.4")."; display:inline-block\" class=\"col-12 badge p-2 bg-".( $total[ $m[ "codigo" ] ][ $q ] ? $m[ "settings" ][ "color" ] : "gray-400")." text-white\">$".number_format( $total[ $m[ "codigo" ] ][ $q ], 2 )."</span></h5></div>";
        }

        $total[ $m[ "codigo" ] ][ $q ] = (string)$total[ $m[ "codigo" ] ][ $q ];
    }

    $dto->modify('-7 days');
}


foreach( MODELOS as $m ){
    $data[] = [
        "name" => $m[ "nombre" ],
        "data" => array_reverse( $total[ $m[ "codigo" ] ] )
    ];
}

$date0 = new DateTime( date('Y-m-d H:i:s')." + 1 day" );
$date1 = new DateTime( $date0->format('Y-m-d H:i:s')." last monday" );
$date2 = new DateTime( $date1->format('Y-m-d H:i:s')." + 7 days" );

$interval   = $date1->diff( $date2 );
$total_dias = $interval->d;
$date2 = new DateTime( date( "Y-m-d H:i:s" ) );
$interval = $date1->diff( $date2 );

$transcurridos = ( $interval->d * 24 * 60 ) + ( $interval->h * 60 ) + $interval->i;
$porc_bono = ceil( $transcurridos * 100 / ( $total_dias * 24 * 60 ) );
// dd($data);
?>

</div>

<div class="card-body pt-0">
    <div class="mt-3 d-none badge col-12 bg-<?php echo $t_actual ? "teal" : "gray-400" ?> text-white">
        <div class="mt-2">Total acumulado SEMANA <?php echo date( "Y-W" ); ?><h1 class="my-1 text-white">$<?php echo number_format( $t_actual, 2 ); ?><?php echo $t_actual ? "<span class=\"badge\" data-bs-custom-class=\"tooltip-mustard\" title=\"<i class='fa fa-warning'></i> Antes del pago, se aplicará a esta cantidad la retención de ISR correspondiente. \" data-bs-toggle=\"tooltip\"><i class=\"fa fa-warning text-mustard small\"></i></span>" : "" ?></h1></div>
    </div>

    <p class="text-center mt-0 mb-0"><?php echo "Día ".ceil( $transcurridos / 24 / 60 )." de ".$total_dias; ?></p>

    <div class="progress" role="progressbar" aria-valuemin="0" aria-valuemax="100" style="height:24px; border-radius:10px">
        <div class="progress-bar bg-teal" style="width: <?php echo $porc_bono; ?>%"><?php echo $porc_bono."%"; ?></div>
    </div>  
</div>


<script>

    var options = {
        colors: ['var(--bs-teal)', 'var(--bs-cyan)', 'var(--bs-mustard)', 'var(--bs-light-pink)', 'var(--bs-purple)'],
        //colors: [<?php echo "'var(--bs-".implode( ")', 'var(--bs-", $colores ).")'"; ?>],
        series: <?php echo json_encode( $data ); ?>,
        chart: {
            type: 'bar',
            height: 250,
            stacked: true,
            toolbar: {
                show: false
            },
            zoom: {
                enabled: false
            }
        },
        plotOptions: {
            bar: {
                horizontal: false,
                borderRadiusApplication: 'end', // 'around', 'end'
                borderRadiusWhenStacked: 'last', // 'all', 'last'
            },
        },
        dataLabels: {
            enabled: false
        },        
        yaxis: {
            labels: {
                formatter: function (value) {
                    return "$" + value ;
                }
            },
        },        
        xaxis: {
            categories: <?php echo json_encode( array_reverse( $semanas ) ); ?>,
        },
        legend: {
            show: false
        },
        fill: {
            opacity: 1
        }
    };

    var chart = new ApexCharts(document.querySelector("#chart_ingresos"), options);
    chart.render();

</script>
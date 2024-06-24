<script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>

<?php
echo "<div class=\"card-header bg-{$b[ "data" ][ "fondo" ]}\"><h5 class=\"m-0 text-white\">{$b[ "data" ][ "titulo" ]}</h5></div><p class=\"text-center mb-0 mt-3\">Periodo actual: <span class=\"badge fs-6 bg-teal\">".date( "Y-W" )."</span></p>";

$total    = [];
$t_actual = 0;
$bot      = "";
$headers  = "";
$semanas  = [];
$data     = [];
$dto      = new \DateTime();

$dto->setISODate( date( "Y" ), date( "W" ) );

for( $q = 0; $q < 10; $q++ ){
    $semanas[] = $dto->format('W');

    foreach( MODELOS as $m ){
        $comisiones = $usuario->getComisiones( codigo_periodo( $m[ "codigo" ], $dto->format('Y-m-d') ) );

        $total[ $m[ "codigo" ] ][ $q ] = 0;

        foreach( $comisiones as $c ){
            $total[ $m[ "codigo" ] ][ $q ] += floatval( $c->cantidad );
        }   
        
        if( !$q ){
            $t_actual += $total[ $m[ "codigo" ] ][ $q ];

            $headers .= "<th class=\"text-center text-{$m[ "settings" ][ "color" ]}\"><i class=\"fa fa-{$m[ "settings" ][ "icono" ]}\"></i> {$m[ "nombre" ]}</td>";
            $bot .="<td style=\"opacity:".( $total[ $m[ "codigo" ] ][ $q ] ? "1" : "0.4")."\" class=\"col-4 rounded p-2 text-center bg-{$m[ "settings" ][ "color" ]} text-white\">$".number_format( $total[ $m[ "codigo" ] ][ $q ], 2 )."</td>";            
        }
    }

    $dto->modify('-7 days');
}


foreach( MODELOS as $m ){
    $data[] = [
        "name" => $m[ "nombre" ],
        "data" => array_reverse( $total[ $m[ "codigo" ] ] )
    ];
}

?>

<div id="chart_ingresos"></div>
<table class="px-2 w-100" style="border-spacing: 10px;border-collapse: separate; ">
        <tr><?php echo $headers; ?></tr>
        <tr><?php echo $bot; ?></tr>
    </table>

<div class="card-body pt-0">
    <div class="mt-3 badge col-12 bg-<?php echo $t_actual ? "teal" : "gray-400" ?> text-white">
        <div class="mt-2">Total acumulado SEMANA <?php echo date( "Y-W" ); ?><h1 class="my-1 text-white">$<?php echo number_format( $t_actual, 2 ); ?></h1></div>
    </div>
</div>


<script>

    var options = {
        colors: ['var(--bs-teal)', 'var(--bs-cyan)', 'var(--bs-light-green)'],
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
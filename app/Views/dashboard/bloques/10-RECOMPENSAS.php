

<a href="<?php echo base_url( "recompensas" ); ?>">
<div class="card-body p-0">
    <div class="row g-0">

        <?php 
        $r = $usuario->getRecompensas( true );
        $total_estrellas = $usuario->getEstrellas();

        // Se declaran arrays porque se buscan multiples datos, por ahora dejaremos solo uno
        $porcentaje = $r && $r[ "estrellas" ] > 0 ? intval( $total_estrellas * 100 / ( $r[ "estrellas" ] ) ) : 0;
        $serie = $r[ "estrellas" ] ?? 0;
        $label = $r[ "nombre" ] ?? '???';

        $rango = model( "RangoModel" )->find( $r[ "rango_codigo" ] );

        if( $usuario->data->recompensas->inicia ){
            $inicia  = date( "Y-m-d", strtotime( $usuario->data->recompensas->inicia ) );
            $termina = date( "Y-m-d", strtotime( $inicia." +30 month" ) );
        
            $date1 = new DateTime( $inicia );
            $date2 = new DateTime( $termina );
            $interval = $date1->diff( $date2 );
            $total = $interval->days;
         
            $date2 = new DateTime( date( "Y-m-d" ) );
            $interval = $date1->diff( $date2 );
            $resta = $interval->days;
        
            $porc  = ceil( $resta * 100 / $total );
        }
        else{

            $data = $usuario->data;
            $data->recompensas->inicia = date( "2024-08-15" );
            $usuario->data = $data;
            model( "UsuarioModel" )->save( $usuario );

            $inicia  = "No iniciado";
            $termina = "???";
            $resta = "???";
            $total = "???";
            $porc  = 0;
        }
        ?>

        <div class="col-7 pt-2" id="chart" style="position:relative"><div style="position:absolute; width:100%; text-align:center; top:10px"><?php echo $porcentaje."% <i class=\"fa fa-star text-amber\"></i> {$total_estrellas} de {$r[ "estrellas" ]}"; ?></div></div>
        
        <div class="col-5 small text-center" style="padding-top:40px; padding-right:25px"><p class="m-0 px-0"><img class="mb-1" src="<?php echo base_url()."assets/img/rangos/{$rango[ "codigo" ]}.png"; ?>" style="width:60px"><br><span class="badge bg-<?php echo $rango[ "color" ]; ?>" style="font-size:13px"><?php echo $rango[ "nombre" ]; ?></span></p><p>La recompensa <?php echo $r[ "nombre" ]; ?> requiere como mínimo el rango de <?php echo $rango[ "nombre" ]; ?></p><p><?php echo estatus( substr( $usuario->data->rango, 0, 2 ) < substr( $rango[ "codigo" ], 0, 2)  ? "155-NO-ALCANZADO" : "225-ALCANZADO" ) ?></p></div>

        <p class="text-center m-0"><?php echo "Día {$resta} de {$total}"; ?></p>
        <div class="px-3 mb-3">
            <div class="progress" role="progressbar" aria-valuemin="0" aria-valuemax="100" style="height:24px; border-radius:10px">
                <div class="progress-bar bg-teal" style="width: <?php echo $porc; ?>%"><?php echo $porc."%"; ?></div>
            </div>  
        </div>
    </div>
</div>

</a> 
<script>

$(document).ready(function(){
    var estrellas = <?php echo $total_estrellas; ?>,
        options = {
        series: [<?php echo $porcentaje; ?>],
        chart: {
        height: 400,
        type: 'radialBar',
        },
        stroke: {
            lineCap: 'round'
        },           
        plotOptions: {
            radialBar: {
                offsetY: 0,
                startAngle: -130,
                endAngle: 130,
                hollow: {
                    size: '50%',
                    image: base_url + 'assets/img/recompensas/<?php echo $r[ "codigo" ]; ?>.png',
                    imageOffsetY: -10,
                    imageWidth: 60,
                    imageHeight: 60,
                    imageClipped: false
                },
                barLabels: {
                    enabled: false,
                    useSeriesColors: true,
                    margin: 18,
                    fontSize: '16px'
                },
                dataLabels: {
                    show: true,
                    name: {
                        show: true,
                        offsetY: 80,
                    },
                    value: {
                        formatter: function(val) {
                            return estrellas;
                        },
                        color: 'var(--bs-amber)',
                        offsetY: 40,
                        fontSize: '30px',
                        show: true,
                    },
                    total: {
                        show: true,
                        label: '<?php echo $label; ?>',
                        formatter: function (w) {
                            return estrellas;
                        }
                    }      
                }               
            }
        },
        colors: ['var(--bs-teal)'],
        labels: '<?php echo $label; ?>'
    };

    var chart = new ApexCharts(document.querySelector("#chart"), options);
    chart.render();
});

</script>
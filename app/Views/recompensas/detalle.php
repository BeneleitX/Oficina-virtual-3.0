<script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>

<?php
    $recompensa = model( "RecompensaModel" )->find( $usuario->data->recompensas->activa ?? "010-CELULAR" );
    $total_estrellas = $usuario->getEstrellas();
    $porcentaje = intval( $total_estrellas * 100 / $recompensa[ "estrellas" ] );

?>

<h4 class="mt-1 mb-4"><?php echo $titulo; ?></h4>


<div class="alert alert-info">
    <div class="row">
        <div class="col-lg-4">
            <p><strong>ESTRELLAS</strong>: Son generadas por compras BIEX de socios en tercer nivel registrados y activados durante el ciclo.</p>
            <p>Cada socio en tu tercer nivel, puede generar con sus compras un máximo de 2 <i class="fa fa-star text-amber"></i>estrellas por mes.</p>
            <p>Para recibir y acumular <i class="fa fa-star text-amber"></i>estrellas es requisito indispensable contar con calificación EJECUTIVO en Nutrición</p>
        </div>
        <div class="col-lg-4">
        <p><strong>RECOMPENSAS</strong>: Las recompensas pueden conseguirse acumulando <i class="fa fa-star text-amber"></i>estrellas. Entre más grande sea tu red, más estrellas obtendrás cada mes.</p>
        <p>Al alcanzar el número de <i class="fa fa-star text-amber"></i>estrellas requeridas, tu decides si reclamas la recompensa o constinúas acumulandolas para una recompensa mas alta. De este modo las recompensas se obtienen en el orden que tu elijas. </p>
        
        </div>
        <div class="col-lg-4">
        <p><strong>CICLOS</strong>: Un ciclo es una colección de recompensas. Cuando se obtienen todas, se avanza al siguiente, iniciando desde cero el conteo de <i class="fa fa-star text-amber"></i>estrellas para nuevas recompensas.</p>
        <p>El límite para acumular <i class="fa fa-star text-amber"></i>estrellas es de 24 meses (a partir de tu primer compra), una vez transcurrido este tiempo, las <i class="fa fa-star text-amber"></i>estrellas acumuladas desaparecen y se reinician los contadores a cero, teniendo otros 24 meses para conseguir las recompensas pendientes.</p>
        <p>Dejar de calificar un mes también en Nutrición, causará el reinicio de los contadores.</p>
        </div>
    </div>
</div>

<div class="row">
    <div class="mb-3 col-lg-4">
        <div class="card">
        <?php 
            $r = model( "RecompensaModel" )->find( $usuario->data->recompensas->activa ?? '' );
            $total_estrellas = $usuario->getEstrellas();

            // Se declaran arrays porque se buscan multiples datos, por ahora dejaremos solo uno
            if( $r ){ 
                $porcentaje = intval( $total_estrellas * 100 / $r[ "estrellas" ] );
                $serie = $r[ "estrellas" ];
                $label = $r[ "nombre" ];
                $color = "var(--bs-marine)";

                echo "\n<div id=\"chart\"></div>";
            }
        ?>
        </div>
    </div>

    <div class="mb-3 col-lg-4">
        <div class="card" style="overflow:hidden">
        <div class="card-header">
            <div class="row">
                <div class="col-8">
                    <h5 class="m-0">Recompensa activa</h5>
                </div>
                <div class="col-4 text-end"><button class="btn btn-info btn-sm" onclick="$( '#modal_recompensas' ).modal( 'show' );"><i class="fa fa-cog"></i></button></div>
            </div>
        </div>
        <table class="m-0 table">
            <tr><td style="border:none">Recompensa:</td><td style="border:none"><?php echo "<i class=\"fa fa-{$r[ "icono" ]}\"></i> {$r[ "nombre" ]}"; ?></td></tr>
            <tr><td colspan="2">
                <div class="progress mb-2" role="progressbar" aria-label="Animated striped example" aria-valuemin="0" aria-valuemax="100">
                    <div class="progress-bar progress-bar-striped progress-bar-animated bg-teal" style="width: <?php echo $porcentaje; ?>%"></div>
                </div>
                <?php
                if( $r[ "estrellas" ] <= $total_estrellas ){
                    echo "<h4 class=\"text-center my-4\">¡Recompensa alcanzada!</h4><p class=\"text-center\"><button class=\"btn btn-primary\">Reclama tu recompensa aquí</button></p>";
                }
                ?>
            </td></tr>
            <tr><td>Estrellas requeridas:</td><td><?php echo $r[ "estrellas" ]; ?></td></tr>
            <tr><td>Estrellas faltantes:</td><td><?php echo ( $r[ "estrellas" ] > $total_estrellas ? $r[ "estrellas" ] - $total_estrellas : 0 ); ?></td></tr>
        </table>
        </div>
    </div>
    
<?php

if( $usuario->data->recompensas->inicia ){
    $inicia  = date( "Y-m-d", strtotime( $usuario->data->recompensas->inicia ) );
    $termina = date( "Y-m-d", strtotime( $inicia." +24 month" ) );

    $date1 = new DateTime( $inicia );
    $date2 = new DateTime( $termina );
    $interval = $date1->diff( $date2 );
    $total = $interval->days;
 
    $date1 = new DateTime( date( "Y-m-d" ) );
    $interval = $date1->diff( $date2 );
    $resta = $interval->days;

    $porc  = ceil( ( $total - $resta ) * 100 / $total );
}
else{
    $inicia  = "No iniciado";
    $termina = "???";
    $resta = "???";
    $porc  = 0;
}
?>


    <div class="mb-3 col-lg-4">
        <div class="card" style="overflow:hidden">
        <div class="card-header"><h5 class="m-0">Ciclo de recompensas <span class="badge bg-<?php echo $usuario->data->recompensas->inicia ? "teal" : "red" ?>">1</span> <span class="badge bg-gray-500"><?php echo $usuario->data->recompensas->inicia ? "Activo" : "No activo" ?></span></h5></div>
        <table class="m-0 table">
            <tr><td>Inicio de ciclo:</td><td><?php echo $inicia; ?></td></tr>
            <tr><td>Fin de ciclo:</td><td><?php echo $termina; ?></td></tr>
            <tr><td style="border:none">Días restantes:</td><td style="border:none"><?php echo $resta." de ".$total; ?></td></tr>
            <tr><td colspan="2">
                <div class="progress mb-2" role="progressbar" aria-label="Animated striped example" aria-valuemin="0" aria-valuemax="100">
                    <div class="progress-bar progress-bar-striped progress-bar-animated bg-teal" style="width: <?php echo $porc; ?>%"></div>
                </div>
            </td></tr>
        </table>
        </div>
    </div>
</div>

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
                    size: '60%',
                    image: base_url + 'assets/img/estrella.png',
                    imageOffsetY: -40,
                    imageWidth: 80,
                    imageHeight: 80,
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
                        offsetY: 100,
                    },
                    value: {
                        formatter: function(val) {
                            return estrellas;
                        },
                        color: '#ffc107',
                        offsetY: 40,
                        fontSize: '50px',
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
        colors: ['<?php echo $color; ?>'],
        labels: '<?php echo $label; ?>'
    };

    var chart = new ApexCharts(document.querySelector("#chart"), options);
    chart.render();
});

</script>


<div class="modal" tabindex="-1" id="modal_recompensas">
	<div class="modal-dialog modal-lg">
		<div class="modal-content">
			<div class="modal-header">
				<h5 class="modal-title">Elije tu recompensa activa</h5>
				<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
			</div>
			<div class="modal-body">
                <div class="alert alert-warning"><i class="fa fa-circle-info"></i> La recompensa activa es la recompensa que aparecerá en tu sección de inicio. Esto no significa que no puedas canjear tus <i class="fa fa-star text-amber"></i>estrellas por cualquiera de las otras recompensas cuando cumplas la cantidad requerida.</div>
                <div class="row">
                <?php
                foreach( RECOMPENSAS as $r ){
                    echo "\n<div class=\"col-6 col-md-3 mb-3\"><a href=\"".base_url()."switch_recompensa/{$r[ "codigo" ]}\" class=\"btn py-3 col-12 btn-primary\" style=\"height:100px\"><i class=\"fa fs-1 fa-{$r[ "icono" ]}\"></i><br>{$r[ "nombre" ]}</a></div>";
                }
                ?>
                </div>
			</div>
		</div>
	</div>
</div>


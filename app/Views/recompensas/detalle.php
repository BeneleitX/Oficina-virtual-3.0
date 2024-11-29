<style>
.ghost .card{
  color: var(--bs-teal);
  background: var(--bs-teal);
}
</style>

<script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>

<link href="<?php echo base_url(); ?>assets/css/datatables.css" rel="stylesheet"/>
<script src="<?php echo base_url(); ?>assets/js/datatables.js" type="text/javascript"></script>
<script src="<?php echo base_url(); ?>assets/js/datatables_bs5.js" type="text/javascript"></script>

<script src="https://unpkg.com/sortablejs-make/Sortable.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/jquery-sortablejs@latest/jquery-sortable.js"></script>

<?php
    $r = model( "RecompensaModel" )->find( $usuario->data->recompensas->activa ?? "010-CELULAR" );
    $total_estrellas = $usuario->getEstrellas( $r );

    $alcanzadas = $socio->recompensas_alcanzadas();
    $recibidas  = $socio->recompensas_recibidas();
?>

<h4 class="mt-1 mb-4"><?php echo $titulo; ?></h4>


<div class="alert alert-info">
    <div class="row">
        <div class="col-lg-4">
            <p><strong>ESTRELLAS</strong>: Son puntos acumulables generados por las compras BIEX (<i class="fa fa-star text-amber"></i>) y PREMIERE (<i class="fa fa-star text-amber"></i><i class="fa fa-star text-amber"></i>) de socios en tercer nivel registrados y activados durante el ciclo.</p>
            <p>Cada socio en tu tercer nivel, puede generar con sus compras un máximo de 2 <i class="fa fa-star text-amber"></i>estrellas por mes.</p>
            <p>Para recibir y acumular <i class="fa fa-star text-amber"></i>estrellas es requisito indispensable contar con calificación PREMIERE en Nutrición</p>
        </div>
        <div class="col-lg-4">
        <p><strong>RECOMPENSAS</strong>: Las recompensas pueden conseguirse acumulando <i class="fa fa-star text-amber"></i>estrellas. Entre más grande sea tu red, más estrellas obtendrás cada mes.</p>
        <p>Al alcanzar el número de <i class="fa fa-star text-amber"></i>estrellas requeridas, tu decides si reclamas la recompensa o constinúas acumulandolas para una recompensa mas alta. De este modo las recompensas se obtienen en el orden que tu elijas. </p>
        
        </div>
        <div class="col-lg-4">
        <p><strong>CICLOS</strong>: Un ciclo es una colección de recompensas. Cuando se obtienen todas, se avanza al siguiente, iniciando desde cero el conteo de <i class="fa fa-star text-amber"></i>estrellas para nuevas recompensas.</p>
        <p>El límite para acumular <i class="fa fa-star text-amber"></i>estrellas es de 30 meses (a partir de tu primer compra), una vez transcurrido este tiempo, las <i class="fa fa-star text-amber"></i>estrellas acumuladas desaparecen y se reinician los contadores a cero, teniendo 24 meses a partir del segundo ciclo para conseguir las recompensas pendientes.</p>
        <p><strong>Dejar de calificar un mes en Nutrición, causará el reinicio de los contadores.</strong></p>
        </div>
    </div>
</div>

<!-- NUEVA GRAFICA -->
 

<?php
$ciclos = [];

if( $usuario->data->recompensas->inicia ){
    $inicia  = date( "Y-m-d", strtotime( $usuario->data->recompensas->inicia ) );
    $termina = date( "Y-m-d", strtotime( $inicia." +30 month" ) );

    $date1 = new DateTime( $inicia );
    $date2 = new DateTime( $termina );
    $interval = $date1->diff( $date2 );
    $total = $interval->days ?? 0;

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
    $total =  0;
}

foreach( RECOMPENSAS as $r ){
    $ciclos[ $r[ "ciclo" ] ][] = $r;
}

if( isset( $usuario->data->recompensas->orden ) ){
    foreach( $usuario->data->recompensas->orden as $ccc => $aaa ){
        $ciclos[ $ccc ] = [];
        foreach( $aaa as $cdx ){
            $ciclos[ $ccc ][] = RECOMPENSAS[ $cdx ];
        }
    }
}


foreach( $ciclos as $k => $c ){
    echo "\n<div class=\"card mb-3\">
                <div class=\"card-header bg-teal\">
                    <table class=\"w-100\"><tr>
                        <td width=\"25%\"><h5 class=\"m-0 text-white\">ciclo {$k}</h5></td>
                        <td width=\"25%\" class=\" text-white text-center\">
                            Del ".date( "d-m-Y", strtotime( $inicia) ) ." al ".date( "d-m-Y", strtotime( $termina) ) ."
                        </td>
                        <td width=\"25%\" class=\" text-white text-center\">Días restantes: {$resta} de {$total}</td>
                        <td width=\"25%\">
                            <div style=\"height:24px; border-radius:10px\" class=\"xbg-white progress mb-0\" aria-valuemin=\"0\" aria-valuemax=\"100\">
                                <div class=\"progress-bar progress-bar-striped progress-bar-animated bg-red\" style=\"width: {$porc}%\"></div>
                            </div>                        
                        </td>
                    </tr></table>
                </div>
                <div class=\"card-body\">
                    <div class=\"row\" id=\"ciclo_{$k}\">";

    $n = 0;
    foreach( $c as $d ){
        $n++;
        $te = $total_estrellas > $d[ "estrellas" ] || in_array( $d[ "codigo"], $alcanzadas ) ? $d[ "estrellas" ] : $total_estrellas ;
        $porcentaje = intval( $te * 100 / $d[ "estrellas" ] );
        $serie = $d[ "estrellas" ];
        $label = $d[ "nombre" ];
        $color = "var(--bs-".( $porcentaje == 100 ? ( in_array( $d[ "codigo"], $alcanzadas ) ? ( in_array( $d[ "codigo"], $recibidas ) ? "green" : "teal" ) : "red" ) : "marine" ).")";

        echo "\n<div class=\"col-3\" ciclo=\"{$k}\" recompensa_orden=\"{$d[ "codigo" ]}\"><div class=\"card\"><div class=\"card-body text-center\">
                    <div id=\"chart_{$d[ "codigo" ]}\"></div>
                    <p>".( in_array( $d[ "codigo"], $alcanzadas ) ? ( in_array( $d[ "codigo"], $recibidas ) ? "Recompensa recibida <i class=\"fa fa-check text-teal\"></i>" : "Esperando entrega <i class=\"fa fa-hourglass-half text-yellow\"></i>" ) : ( $porcentaje == 100 ? "Ya tienes las" : "Necesitas" )." <strong>{$d[ "estrellas" ]}</strong> <i class=\"fa fa-star text-amber\"></i>estrellas" )."</p>";

    if( in_array( $d[ "codigo"], $alcanzadas ) ){
            echo "<button disabled class=\"btn btn-outline-light\">&nbsp;</button>";
    }
    else{
        echo "<button onclick=\"reclama_recompensa( '{$d[ "codigo" ]}' )\" ".( $porcentaje == 100 ? "" : "disabled" )." class=\"btn btn-".( $porcentaje == 100 ? "success" : "outline-danger" )."\">".( $porcentaje == 100 ? "Reclamar recompensa" : "Faltan ".( $d[ "estrellas" ] - $total_estrellas ) )."</button>";
    }
        

        echo "</div></div></div>";
        ?>
        <script>

        $(document).ready(function(){
            var estrellas = <?php echo $te; ?>,
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
                            image: base_url + 'assets/img/recompensas/<?php echo $d[ "codigo" ]; ?>.png',
                            imageOffsetY: -40,
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
                                offsetY: 100,
                            },
                            value: {
                                formatter: function(val) {
                                    return estrellas;
                                },
                                color: '<?php echo $color; ?>',
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
        
            var chart = new ApexCharts(document.querySelector("#chart_<?php echo $d[ "codigo" ]; ?>"), options);
            chart.render();
        });
        
        </script>
        
        <?php
    }                    
                    
    echo "           </div>
                </div>
            </div>";
}
?>
<div class="mb-3">&nbsp;</div>

<table class="mb-0 table table-striped bg-white tabla_estrellas" id="t_<?php echo date("Y-m-d"); ?>">
    <thead>
        <tr>
            <th class="text-center">Compra</th>
            <th class="text-center">Fecha</th>
            <th>Cantidad</th>
            <th>Socio</th>
        </tr>
    </thead>

    <tbody>
        <?php 
            $socios = [];
            foreach( $comisiones as $c ){

                if( !isset( $socios[ $c->usuario_id ] ) ){
                    $socios[ $c->usuario_id ] = model( "UsuarioModel" )->find( $c->usuario_id );
                }

                $socios[ $c->usuario_id ] = model( "UsuarioModel" )->find( $c->usuario_id );
                echo "\n<tr\">
                    <td width=\"10%\" class=\"text-center\"><span class=\"badge bg-marine\">{$c->referencia}</span></td>
                    <td width=\"20%\" class=\"text-center\">".date( "d-m-Y", strtotime( $c->fecha ) )."</td>
                    
                    <td width=\"10%\" class=\"text-end\"><strong> ".number_format( $c->cantidad )."</strong><i class=\"fa fa-star text-amber\"></i></td>
                    <td nowrap>".$socios[ $c->usuario_id ]->avatar( 24 )." ".$socios[ $c->usuario_id ]->id( "10-NUTRICION" )."<span class=\"d-none d-lg-inline\"> ".$socios[ $c->usuario_id ]->nombre( 2 )."</span></td>
                </tr>";
            }
        ?>
    
    </tbody>
</table>


<div class="modal" tabindex="-1" id="reclama_recompensa">
	<div class="modal-dialog modal-lg">
		<div class="modal-content">
			<div class="modal-header">
				<h5 class="modal-title">Reclamar recompensa</h5>
				<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
			</div>
			<div class="modal-body">
                <div class="row">
                <div class="col-lg-6 text-center px-5">
                        <img src="" class="px-5 img-fluid img_recompensa">
                    </div>
                    <div class="col-lg-6 txext-center">
                        <h2>¡Felicidades!</h2>
                        <p>Ya puedes reclamar tu recompensa</p>
                        <p>Gracias al crecimiento de tu red, cuentas con suficientes estrellas para la recompensa <strong class="recompensa_nombre"></strong> la cual se te entregará de acuerdo al seguimiento que le dará la empresa una vez que confirmes que si quieres reclamarla.</p>
                        
                    </div>
                </div>
                
                <div class="alert alert-danger"><i class="fa fa-circle-info"></i> IMPORTANTE: Al reclamar esta recompensa, se te descontará la cantidad de <strong class="cantidad_estrellas"></strong> <i class="fa fa-star text-amber"></i>estrellas de tu bolsa total de <strong><?php echo $total_estrellas; ?></strong>, quedando un saldo de <strong class="saldo_estrellas"></strong> <i class="fa fa-star text-amber"></i>estrellas que se sumarán a las que continúes acumulando con nuevas compras de tu red hasta alcanzar más recompensas antes de la fecha límite del ciclo. </div>

                <p class="text-center mt-3"><a href="" class="boton_reclama btn btn-success">¡Sí! quiero reclamar mi recompensa ahora</a></p>
			</div>
		</div>
	</div>
</div>

<script>

var total_estrellas = <?php echo $total_estrellas; ?>,
    cat_recompensas = <?php echo json_encode( RECOMPENSAS ); ?>;

</script>
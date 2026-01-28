<?php
$meses = [
    202510 => $usuario->historial->modelos->{"10-NUTRICION"}->calificaciones->{"202510"}->{"010-DISTRIBUIDOR"} ?? 0,
    202511 => $usuario->historial->modelos->{"10-NUTRICION"}->calificaciones->{"202511"}->{"010-DISTRIBUIDOR"} ?? 0,
    202512 => $usuario->historial->modelos->{"10-NUTRICION"}->calificaciones->{"202512"}->{"010-DISTRIBUIDOR"} ?? 0,
    202601 => $usuario->historial->modelos->{"10-NUTRICION"}->calificaciones->{"202601"}->{"010-DISTRIBUIDOR"} ?? 0
];

$socios  = $usuario->getNuevosSocios( 6, "2025-10-01", "2025-10-31" ) +
           $usuario->getNuevosSocios( 6, "2025-11-01", "2025-11-30" ) +
           $usuario->getNuevosSocios( 6, "2025-12-01", "2025-12-31" ) +
           $usuario->getNuevosSocios( 6, "2026-01-01", "2026-01-31" );

$compras = $usuario->historial->viajecancun ?? 0;

$porcentaje1 = ceil( ( $compras > 20 ? 20 : $compras) * 100 / 20 );
$porcentaje2 = ceil( ( $compras > 32 ? 32 : $compras) * 100 / 32 );

$date0 = new DateTime( date( "Y-m-d H:i:s" ) );
$date1 = new DateTime( "2025-10-01 00:00:00" );
$date2 = new DateTime( "2026-01-31 23:59:59" );

$interval   = $date1->diff( $date2 );
$totales = $interval->format( "%a");

$interval = $date1->diff( $date0 );
$transcurridos = $interval->format( "%a");

$porc_bono = ceil( $transcurridos * 100 / $totales );


?>
<img src="<?php echo base_url(); ?>assets/img/avion.png" style="width:100px; display:block; position:absolute; right:-20px; top:-30px">
<p class="text-center mt-3 mb-1">
    <small class="fw-bold">Consumo mínimo de <span class="badge border border-gray-600 bg-white text-gray-600">6</span> puntos cada mes</small>
</p>
<table align="center">
    <tr>
        <?php 
        foreach($meses as $mes => $puntos){
            $actual = date("Ym");

            if( $mes < $actual ){
                $icono = $puntos < 6 ? "circle-xmark" : "circle-check";
                $color = $puntos < 6 ? "red" : "teal";
            }
            elseif( $mes == $actual ){
                $icono = $puntos < 6 ? "circle-notch fa-spin" : "circle-check";
                $color = $puntos < 6 ? "mustard" : "teal";
            }
            else{
                $icono = "circle";
                $color = "gray-400";
            }

            echo "\n<td class=\"text-center px-3\" style=\"position:relative\">
                    ".( $mes == $actual && $puntos < 6 ? "<div style=\"position:absolute; right:28px; top:3px; width:20px; text-align;center\" class=\"fw-bold text-mustard\">{$puntos}</div>" : "" )."
                    <i class=\"fa fa-{$icono} text-{$color}\" style=\"font-size:30px\"></i>
                    <br><span class=\"small\">".strtoupper( mes( substr( $mes, 4, 2 ), 3 ) )." ".substr( $mes, 0, 4 )."</span>
                    </td>";
        }
        ?>
    </tr>
</table>

<p class="text-center mt-3 mb-1">
    <small class="fw-bold">Tener <span class="badge border border-gray-600 bg-white text-gray-600">8</span> nuevos socios activos MASTER o ELITE</small>
</p>
<table align="center" class="w-100">
    <tr><td>&nbsp;</td>
        <?php 

        $llaves = array_keys( $socios );
        for( $i = 0; $i < 8; $i++ ){ 
                    
            if( isset( $llaves[ $i ] ) ){
                $icono = $socios[ $llaves[ $i ] ] == 0 ? "circle-check" : "circle-check";
                $color = $socios[ $llaves[ $i ] ] == 0 ? "teal" : "teal";

                echo "\n<td class=\"text-center px-0\" title=\"{$llaves[ $i ]}\" data-bs-toggle=\"tooltip\">";
                // echo $socios[ $i ]->avatar( 28 );
                echo "<i class=\"fa fa-{$icono} text-{$color}\" style=\"font-size:30px\"></i>";
            }
            else{
                echo "\n<td class=\"text-center px-0\">";
                echo "<i class=\"fa fa-circle text-gray-400\" style=\"font-size:30px\"></i>";
            }       
            
            echo "</td>";
        }

        ?>
    <td>&nbsp;</td></tr>
</table>

<div style="margin-bottom: -30px">
<p class="text-center mt-3 mb-0">
    <small class="fw-bold">Compras de <span class="badge border border-gray-600 bg-white text-gray-600">6</span> puntos en tus 3 niveles de red</small>
</p>
</div>

<div class="row">
    <div class="col-6 pt-2 text-center">
        <div id="chart1"></div>
        <p style="margin-top: -10px"><span class="border border-gray-600 badge text-gray-600"><?php echo ( $compras > 20 ? 20 : $compras )." de 20"; ?></span></p>
    </div>
    <div class="col-6 pt-2 text-center">
        <div id="chart2"></div>
        <p style="margin-top: -10px"><span class="border border-gray-600 badge text-gray-600"><?php echo ( $compras > 32 ? 32 : $compras )." de 32"; ?></span></p>
    </div>
</div>
<p class="text-center">
    <a href="javascript:updateCompras();" id="btn_compras" class="btn btn-sm btn-link"><i class="fa fa-refresh"></i> Actualizar conteo</a> | 
    <a href="javascript:$( '#listado_compras' ).modal( 'show');" id="btn_compras2" class="btn btn-sm btn-link"><i class="fa fa-refresh"></i> Ver listado de compras</a>
</p>



<div class="m-3 mt-0">
    <p class="text-center mt-0 mb-0"><?php echo "Día {$transcurridos} de {$totales}"; ?></p>

    <div class="progress" role="progressbar" aria-valuemin="0" aria-valuemax="100" style="height:24px; border-radius:10px">
        <div class="progress-bar fw-bold bg-teal" style="width: <?php echo $porc_bono; ?>%"><?php echo $porc_bono."%"; ?></div>
    </div>  
</div>



<div class="modal" tabindex="-1" id="listado_compras">
	<div class="modal-dialog modal-lg">
		<div class="modal-content">
			<div class="modal-header bg-mustard">
				<div class="modal-title">
                    <h5 class="text-white m-0"><i class="fa fa-qrcode"></i> Actualizar TxHash de inversión</h5>
				</div>
				<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
			</div>
            <div id="loader" class="modal-body text-center">
                <img src="<?php echo base_url(); ?>assets/img/loader.gif" style="width:150px; height:150px; opacity:0.4" class="m-5">
            </div>            
            <div class="modal-body text-center" id="principal">
                <p class="text-center">
                <h3 class="text-center">Pega aquí tu TxHash:</h3>
                    <input type="text" class="form-control text-center border-3 border-teal" name="_txhash">
                    <pre id="error" class="mt-2 alert alert-danger text-center" style="display:none"></pre>
                </p>

                <p class="text-end mt-4 mb-0"><button class="btn btn-warning my-2" id="confirma_hash"><i class="fa fa-check"></i> Registrar inversión</button></p>
            </div>
		</div>
	</div>
</div>


<script>



$(document).ready(function(){
    
    var compras1 = <?php echo $compras > 20 ? 20 : $compras; ?>,
        options1 = {
        series: [<?php echo $porcentaje1; ?>],
        chart: {
        height: '200px',
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
                    image: base_url + 'assets/img/viaje1.png',
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
                        offsetY: 70,
                    },
                    value: {
                        color: 'var(--bs-<?php echo $porcentaje1 == 100 ? "teal" : ( $porcentaje1 == 0 ? "gray-400" : "mustard" ); ?>)',
                        offsetY: 0,
                        fontSize: '30px',
                        show: true,
                    },
                    total: {
                        show: true,
                        label: '1 persona'
                    }      
                }               
            }
        },
        colors: ['var(--bs-<?php echo $porcentaje1 == 100 ? "teal" : ( $porcentaje1 == 0 ? "gray-400" : "mustard" ); ?>)'],
        labels: 'viaje'
    };

    var chart1 = new ApexCharts(document.querySelector("#chart1"), options1);

    var compras2 = <?php echo $compras > 32 ? 32 : $compras; ?>,
        options2 = {
        series: [<?php echo $porcentaje2; ?>],
        chart: {
        height: '200px',
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
                    image: base_url + 'assets/img/viaje1.png',
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
                        offsetY: 70,
                    },
                    value: {
                        color: 'var(--bs-<?php echo $porcentaje2 == 100 ? "teal" : ( $porcentaje2 == 0 ? "gray-400" : "mustard" ); ?>)',
                        offsetY: 0,
                        fontSize: '30px',
                        show: true,
                    },
                    total: {
                        show: true,
                        label: '2 personas'
                    }      
                }               
            }
        },
        colors: ['var(--bs-<?php echo $porcentaje2 == 100 ? "teal" : ( $porcentaje2 == 0 ? "gray-400" : "mustard" ); ?>)'],
        labels: 'viaje'
    };

    var chart2 = new ApexCharts(document.querySelector("#chart2"), options2);
    
    chart1.render();   
    chart2.render();    

});

</script>
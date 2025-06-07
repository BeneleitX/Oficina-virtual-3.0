<script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>

<h4 class="mt-1 mb-0"><?php echo $titulo; ?></h4>
<p>
    <a class="btn btn-light btn-sm" href="<?php echo base_url( "capital" ); ?>"><i class="fa fa-undo"></i> Regresar a Inversiones</a>
</p>



<div class="alert alert-info">
    <div class="row">
        <div class="col-lg-4">
            <p><strong>Los rangos son una recompensa por expandir tus conexiones y te dan derecho automático al bono de liderazgo.</strong></p>
            <p>Según el número de directos activos que tengas, será el porcentaje de ganancia que obtendrás del volumen grupal semilla invertido en tus primeros 4 niveles.</p>
        </div>

        <div class="col-lg-4">
            <table class="w-100"><tr>
            <?php
            $rangos = [ "510-PIONERO", "520-CONQUISTADOR", "530-LEYENDA" ];
            foreach( $rangos as $rango ){
                $r = RANGOS[ $rango ];
                echo "\n<td width=\"33%\" class=\"text-center\"><div class=\"card text-center\"><div class=\"card-body text-center px-0\">
                    <img src=\"".base_url()."assets/img/rangos/{$r[ "codigo" ]}.png\" style=\"width:80px\" alt=\"\">
                    <h5>{$r[ "nombre" ]}</h5>
                    <p class=\"m-0 small\">
                        Directos: <strong>{$r[ "cantidades" ][ "directos" ][ 0 ]}</strong><br>
                        Porcentaje: <strong>{$r[ "cantidades" ][ "porcentaje" ]}%</strong>
                    </p></div></div>
                </td>";    
            }
            ?>
                
            </tr></table>
        </div>

        <div class="col-lg-4">
            <h5>Cálculo de bono:</h5>
            <ol class="m-0">
                <li>El rango se calcula al finalizar el mes, haciendo un corte de socios directos activos y el volumen de capital semilla de la red.</li>
                <li>Se debe esperar a que transcurra el mes siguiente, para que ese volumen de capital semilla genere rendimientos.</li>
                <li>El bono se pagará al finalizar el mes siguiente, durante los primeros 3 días hábiles.</li>
            </ol>
        </div>
    </div>
</div>


<table class="table table-striped bg-white">
<thead>
    <tr>
        <th>Mes</th>
        <th>Directos</th>
        <th>Rango</th>
        <th>Bolsa</th>
        <th>Porcentaje</th>
        <th>Bono</th>
        <th></th>
    </tr>
</thead>
<tbody>
<?php 

foreach( $usuario->historial->modelos->{"50-INVERSION"}->corte_mensual as $mes => $data ){
    $rango = $usuario->getRangoInversion( $data->directos );

    $y = substr( $mes, 0, 4 );
    $m = substr( $mes, 4, 2 );

    $fecha = date( "Y-m-d", strtotime( "{$y}-{$m}-01 - 1 month" ) );

    echo "\n<tr>
            <td valign=\"middle\"><h5 class=\"mt-2 mb-0\">".strtoupper( fecha( $fecha, "mes" ) )."</h5></td>
            <td valign=\"middle\">{$data->directos}</td>
            <td valign=\"middle\" nowrap class=\"p-0\">
                <h5 class=\"m-0\"><table class=\"m-0\"><tr>
                    <td valign=\"middle\"><img src=\"".base_url()."assets/img/rangos/".$rango[ "codigo" ].".png\" style=\"width:60px\" alt=\"\"></td>
                    <td valign=\"middle\"><span class=\"badge bg-{$rango[ "color" ]}\">{$rango[ "nombre" ]}</span></td>
                </tr></table>
                </h5>
            </td>

            <td valign=\"middle\"><img src=\"https://static.tronscan.org/production/logo/usdtlogo.png\" style=\"width:18px\"> $".number_format( $data->bolsa, 2 )."</td>
            <td valign=\"middle\"><strong>{$data->bono}%</strong></td>
            <td valign=\"middle\"><img src=\"https://static.tronscan.org/production/logo/usdtlogo.png\" style=\"width:18px\"> $".number_format( $data->bolsa * $data->bono / 100, 2 )."</td>
        
            <td valign=\"middle\" class=\"text-end\"><button type=\"button\" class=\"btn btn-secondary btn-sm\" onclick=\"detalle_bono( '{$mes}' )\"><i class=\"fa fa-magnifying-glass\"></i> Detalles</button></td>
        </tr>";
}

?>
</tbody>
</table>



<div class="modal" tabindex="-1" id="detalle_bono">
	<div class="modal-dialog modal-lg">
		<div class="modal-content">
			<div class="modal-header bg-marine">
				<div class="modal-title">
                    <h5 class="text-white m-0"><i class="fa fa-magnifying-glass"></i> Detalle de bono de liderazgo</h5>
				</div>
				<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
			</div>
            <div class="modal-body"></div>
		</div>
	</div>
</div>


<script>
    var usuario = <?php echo json_encode( $usuario ); ?>;
</script>
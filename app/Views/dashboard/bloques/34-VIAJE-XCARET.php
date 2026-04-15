<?php

$date0 = new DateTime( date( "Y-m-d H:i:s" ) );
$date1 = new DateTime( "2026-03-01 00:00:00" );
$date2 = new DateTime( "2026-11-31 23:59:59" );

$interval   = $date1->diff( $date2 );
$totales = $interval->format( "%a");

$interval = $date1->diff( $date0 );
$transcurridos = $interval->format( "%a");

$porc_bono = ceil( $transcurridos * 100 / $totales );

$directos = $usuario->getXcaret();

?>
<img src="<?php echo base_url(); ?>assets/img/avion.png" style="width:100px; display:block; position:absolute; right:-20px; top:-30px">

<?php
if( sizeof( $directos ) > 0 ){

   echo "<div class=\"mt-2 mb-1 text-center text-marine fs-3\">Socios directos nuevos</div>";

    foreach( $directos as $k => $d ){
        $ingresos = json_decode( $d->ingresos );
        $puntos   = json_decode( $d->puntos );

        $s = model( "UsuarioModel" )->find( $d->id );

        $meses = "";
        $descalificado = false;

        foreach( $puntos as $k => $i ){
            if( $i < 6 && $ma > intval( $k ) ){
                $descalificado = true;
            }

            $ma = date( "m" );

            $meses .= "<div class=\"col-1 text-center mx-0 px-0\" style=\"width:10% !important\"><span class=\"badge bg-".( $descalificado ? ( $ma > intval( $k ) ? "red" : "gray-600" ) : ( $i >= 6 ? "teal" : ( $ma == intval( $k ) ? "mustard" : "gray-400" ) ) )."\" style=\"width:100%;display:inline-block;\">{$i}</span></div>";
        }

        echo "\n<div class=\" mx-3 mb-4 px-3 py-2\" style=\"position:relative\">
                <div style=\"position:absolute; left:-5px; top:-2px; width:60px\">
                    ".$s->avatar( 50 )."
                </div>
                <div class=\"row\">
                    <div class=\"col-3\" style=\"padding-left:20px\">
                    ".$s->id( "10-NUTRICION", false, false )."
                    </div>
                    <div class=\"col-9\">
                    <div class=\"row\">
                        {$meses}
                    </div>
                    </div>
                </div>
            </div>";
    }     
}
else{
    echo "<div class=\"my-4 text-center text-gray-500 fs-3\">Aun no tienes<br>socios directos nuevos</div>";
}

?>

<div class="m-3 mt-0">
    <p class="text-center mt-0 mb-0"><?php echo "Día {$transcurridos} de {$totales}"; ?></p>

    <div class="progress" role="progressbar" aria-valuemin="0" aria-valuemax="100" style="height:24px; border-radius:10px">
        <div class="progress-bar fw-bold bg-teal" style="width: <?php echo $porc_bono; ?>%"><?php echo $porc_bono."%"; ?></div>
    </div>  
</div>



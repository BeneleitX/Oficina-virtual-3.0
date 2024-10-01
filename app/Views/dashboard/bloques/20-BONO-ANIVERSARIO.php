<?php

$esquema = model( "EsquemaModel" )->find( "116-ANIVERSARIO" );
$bono = $usuario->getBono( $esquema[ "codigo" ] );

$date1 = new DateTime( $esquema[ "inicia" ] );
$date2 = new DateTime( $esquema[ "termina" ] );
$interval = $date1->diff( $date2 );
$total_dias = $interval->days;

$date2 = new DateTime( date( "Y-m-d" ) );
$interval = $date1->diff( $date2 );
$transcurridos = $interval->days;

$porc_bono = ceil( $transcurridos * 100 / $total_dias );

?>

<table class="px-2 w-100" style="border-spacing: 10px;border-collapse: separate; ">
    <tr>
        <th class="text-center">1° Nivel</td>
        <th class="text-center">2° Nivel</td>
        <th class="text-center">3° Nivel</td>
    </tr>

    <tr>
        <td style="line-height:1" class="col-4 rounded p-2 text-center bg-gray-<?php echo $bono[1] ? "300" : "100"; ?>">$<?php echo number_format( $bono[1], 2); ?><br><span class="small"><?php echo number_format( $bono[1] / 5 ); ?> productos</span></td>
        <td style="line-height:1" class="col-4 rounded p-2 text-center bg-gray-<?php echo $bono[2] ? "300" : "100"; ?>">$<?php echo number_format( $bono[2], 2); ?><br><span class="small"><?php echo number_format( $bono[2] / 5 ); ?> productos</span></td>
        <td style="line-height:1" class="col-4 rounded p-2 text-center bg-gray-<?php echo $bono[3] ? "300" : "100"; ?>">$<?php echo number_format( $bono[3], 2); ?><br><span class="small"><?php echo number_format( $bono[3] / 5 ); ?> productos</span></td>
    </tr>
</table>

<div class="card-body pt-0">
    <div class="mt-3 badge col-12 bg-<?php echo array_sum( $bono ) ? "teal" : "gray-400" ?> text-white"><div class="mt-1">Total acumulado</div><h1 class="m-0 text-white">$<?php echo number_format( array_sum( $bono ), 2 ); ?><?php echo array_sum( $bono ) ? "<span class=\"badge\"  title=\"<i class='fa fa-warning'></i> Antes del pago, se aplicará a esta cantidad la retención de ISR correspondiente. \" data-bs-toggle=\"tooltip\" data-bs-custom-class=\"tooltip-mustard\" ><i class=\"fa fa-warning text-mustard small\"></i></span>" : "" ?></h1></div>
    
    <p class="text-center mt-3 mb-0"><?php echo "Día {$transcurridos} de {$total_dias}"; ?></p>

    <div class="progress" role="progressbar" aria-valuemin="0" aria-valuemax="100" style="height:24px; border-radius:10px">
        <div class="progress-bar bg-teal" style="width: <?php echo $porc_bono; ?>%"><?php echo $porc_bono."%"; ?></div>
    </div>  
</div>
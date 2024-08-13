<?php

$esquema = model( "EsquemaModel" )->find( "116-ANIVERSARIO-24" );
$bono = $usuario->getBonoPromos();

$date1 = new DateTime( date( "Y-m-01" ) );
$date2 = new DateTime( date( "Y-m-01" )." + 1 month" );
$interval   = $date1->diff( $date2 );

$total_dias = $interval->days;
$date2 = new DateTime( date( "Y-m-d H:i:s" ) );
$interval = $date1->diff( $date2 );

$transcurridos = ( ($interval->days ) * 24 * 60 ) + ( $interval->h * 60 ) + $interval->i;
$porc_bono = ceil( $transcurridos * 100 / ( $total_dias * 24 * 60 ) );


$premieres = $usuario->getPremieres();

$p_factor = 2.5;
$p_clase  = "red";

if( sizeof( $premieres ) > 2 ){
    $p_factor = 15;
    $p_clase  = "blue";
}
elseif( sizeof( $premieres ) > 1 ){
    $p_factor = 10;
    $p_clase  = "mustard";
}
?>


<table class="px-2 w-100" style="border-spacing: 10px;border-collapse: separate; ">
    <tr>
        <td class="text-center"><p>Socios nuevos PREMIERE: <span class="badge bg-<?php echo $p_clase; ?>"><?php echo sizeof( $premieres ); ?></span></p>
            <table align="center" class="w-75 mb-3"><tr>
                <?php
                    $re = 0;
                    foreach( $premieres as $pr){
                        if( $re < 3 ){
                            $pr = model( "usuarioModel" )->find( $pr[ "id" ] );
                            echo "\n<td class=\"px-2 text-center\">".$pr->avatar(60)."<br>".$pr->id( "10-NUTRICION", false, false )."</td>";

                            $re++;
                        }
                    }

                    while($re++ < 3){

                        echo "\n<td class=\"px-2 text-center\"><div class=\"rounded-circle bg-gray-200 mb-0\" style=\"margin:0 auto; width:60px; height:60px; display:inline-block\">&nbsp;</div><br><div class=\"badge bg-gray-300 col-12 fw-light opacity-50\">&nbsp;</div></span></td>";

                    }
                ?>
            </tr></table>

        </td>
        <td class="text-center bg-gray-200" style="border-radius:6px">
            <p class="text-gray-600 fs-3 m-0"><i class="fa fa-tag"></i> <?php echo array_sum( $bono ); ?></p>
            <p class="small m-0">Factor de multiplicación<br>de bono</p>
            <h4><span class="badge px-2 bg-<?php echo $p_clase; ?>">x<?php echo number_format( $p_factor, 2 ); ?></span></h4>
        </td>
    </tr>
                </table>
    <table class="px-2 w-100" style="border-spacing: 10px;border-collapse: separate; ">
    <tr>
        <th class="text-center">1° Nivel</td>
        <th class="text-center">2° Nivel</td>
        <th class="text-center">3° Nivel</td>
    </tr>

    <tr>
        <td style="line-height:1" class="col-4 rounded p-2 text-center bg-gray-<?php echo $bono[1] ? "300" : "100"; ?>">$<?php echo number_format( $bono[1] * $p_factor, 2); ?><br><span class="small"><?php echo number_format( $bono[1] ); ?> <i class="fa fa-tag text-light-pink"></i>Promos</span></td>
        <td style="line-height:1" class="col-4 rounded p-2 text-center bg-gray-<?php echo $bono[2] ? "300" : "100"; ?>">$<?php echo number_format( $bono[2] * $p_factor, 2); ?><br><span class="small"><?php echo number_format( $bono[2] ); ?> <i class="fa fa-tag text-light-pink"></i>Promos</span></td>
        <td style="line-height:1" class="col-4 rounded p-2 text-center bg-gray-<?php echo $bono[3] ? "300" : "100"; ?>">$<?php echo number_format( $bono[3] * $p_factor, 2); ?><br><span class="small"><?php echo number_format( $bono[3] ); ?> <i class="fa fa-tag text-light-pink"></i>Promos</span></td>
    </tr>
</table>

<div class="card-body pt-0">
    <div class="mt-3 badge col-12 bg-<?php echo array_sum( $bono ) ? "teal" : "gray-400" ?> text-white"><div class="mt-1">Total acumulado</div><h1 class="m-0 text-white">$<?php echo number_format( array_sum( $bono ) * $p_factor, 2 ); ?><?php echo array_sum( $bono ) ? "<span class=\"badge\" data-bs-custom-class=\"tooltip-mustard\" title=\"<i class='fa fa-warning'></i> Antes del pago, se aplicará a esta cantidad la retención de ISR correspondiente. \" data-bs-toggle=\"tooltip\"><i class=\"fa fa-warning text-mustard small\"></i></span>" : "" ?></h1></div>
    
    <p class="text-center mt-3 mb-0"><?php echo "Día ".ceil( $transcurridos / 24 / 60 )." de ".$total_dias; ?></p>

    <div class="progress" role="progressbar" aria-valuemin="0" aria-valuemax="100" style="height:24px; border-radius:10px">
        <div class="progress-bar bg-teal" style="width: <?php echo $porc_bono; ?>%"><?php echo $porc_bono."%"; ?></div>
    </div>  
</div>
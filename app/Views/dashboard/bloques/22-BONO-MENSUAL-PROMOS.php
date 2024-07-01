<?php
echo "<div class=\"card-header bg-{$b[ "data" ][ "fondo" ]}\"><h5 class=\"m-0 text-white\">Bono de promociones ".strtoupper( mes( date( "m" ) ) )." ".date( "Y" )."</h5></div>";


$esquema = model( "EsquemaModel" )->find( "116-ANIVERSARIO-24" );
$bono = $usuario->getBono( $esquema[ "codigo" ] );

$date1 = new DateTime( date( "Y-m-01" ) );
$date2 = new DateTime( date( "Y-m-01" )." + 1 month" );
$interval   = $date1->diff( $date2 );

$total_dias = $interval->days;
$date2 = new DateTime( date( "Y-m-d H:i:s" ) );
$interval = $date1->diff( $date2 );

$transcurridos = ( ($interval->days ) * 24 * 60 ) + ( $interval->h * 60 ) + $interval->i;
$porc_bono = ceil( $transcurridos * 100 / ( $total_dias * 24 * 60 ) );

?>



<table class="px-2 w-100" style="border-spacing: 10px;border-collapse: separate; ">
    <tr>
        <td colspan="2" class="text-center"><p>Socios nuevos PREMIERE</p>
            <table align="center" class="w-75"><tr>
            <td class="px-2"><?php echo $usuario->avatar(60)."<br>".$usuario->id( "10-NUTRICION" ); ?></td>
            <td class="px-2"><?php echo $usuario->avatar(60)."<br>".$usuario->id( "10-NUTRICION" ); ?></td>
            <td class="px-2"><?php echo $usuario->avatar(60)."<br>".$usuario->id( "10-NUTRICION" ); ?></td>
            </tr></table>

        </td>
        <td class="text-center small pt-4">
            <p>Factor de multiplicación<br>de bono</p>
            <h1><span class="badge bg-red">2.50</span></h1>
        </td>
    </tr>

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
    <div class="mt-3 badge col-12 bg-<?php echo array_sum( $bono ) ? "teal" : "gray-400" ?> text-white"><div class="mt-1">Total acumulado</div><h1 class="m-0 text-white">$<?php echo number_format( array_sum( $bono ), 2 ); ?></h1></div>
    
    <p class="text-center mt-3 mb-0"><?php echo "Día ".ceil( $transcurridos / 24 / 60 )." de ".$total_dias; ?></p>

    <div class="progress" role="progressbar" aria-valuemin="0" aria-valuemax="100" style="height:24px; border-radius:10px">
        <div class="progress-bar bg-teal" style="width: <?php echo $porc_bono; ?>%"><?php echo $porc_bono."%"; ?></div>
    </div>  
</div>
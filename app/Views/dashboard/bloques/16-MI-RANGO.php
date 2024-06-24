<script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>

<?php
echo "<div class=\"card-header bg-{$b[ "data" ][ "fondo" ]}\"><h5 class=\"m-0 text-white\">{$b[ "data" ][ "titulo" ]}</h5></div>";
?>

<a href="<?php echo base_url( "recompensas" ); ?>">

<div class="card-body py-0">
    <div class="row g-0">

        <?php 
        $rango     = model( "RangoModel" )->find( $usuario->data->rango );
        $new_rango = model( "RangoModel" )->where( "SUBSTRING(codigo,1,2) = ".( substr( $rango[ "codigo" ], 0, 2) + 10 ) )->first();
        ?>
        
        <div class="col-6 small text-center" style="padding-top:20px;"><p class="m-0 px-4"><img src="<?php echo base_url()."assets/img/rangos/{$rango[ "codigo" ]}.jpg"; ?>" style="" class="img-fluid"></p><p class="fs-3 text-<?php echo $rango[ "color" ]; ?> m-0"><?php echo $rango[ "nombre" ]; ?></p></div>
        
        <div class="col-6 small text-center" style="padding-top:15px;">
        <p class="">Siguiente rango:</p><p><span class="badge fs-6 bg-<?php echo $new_rango[ "color" ]; ?>"><?php echo $new_rango[ "nombre" ] ?></span></p><p class="m-0">Durante 3 meses consecutivos debes alcanzar la meta o tener una calificación PREMIERE</p><p class="m-0 fs-3">
        <i class="fa fa-square-xmark text-gray-300"></i>
        <i class="fa fa-square-xmark text-gray-300"></i>
        <i class="fa fa-square-xmark text-gray-300"></i>
        </p></div>

        <?php
        $ingresos_mes = $total[ "10-NUTRICION" ][ 0 ];
        $porc_rango = ceil( $ingresos_mes * 100 / $new_rango[ "cantidades" ][ 0 ] );

        ?>
    </div>
</div>    

<table class="px-2 p-0 w-100" style="border-spacing: 10px;border-collapse: separate; ">
    <tr style="line-height:1.1">
        <th class="text-center" valign="top">Ingresos del mes actual</td>
        <th class="text-center" valign="top">Para rango<br><?php echo $new_rango[ "nombre" ]; ?></td>
        <th class="text-center" valign="top">Diferencia</td>
    </tr>

    <tr>
        <td class="col-4 rounded p-2 text-center bg-gray-<?php echo $ingresos_mes ? "300" : "100"; ?>">$<?php echo number_format( $ingresos_mes, 2); ?></td>
        <td class="col-4 rounded p-2 text-center bg-gray-<?php echo $new_rango[ "cantidades" ][ 0 ] ? "300" : "100"; ?>">$<?php echo number_format( $new_rango[ "cantidades" ][ 0 ], 2); ?></td>
        <td class="col-4 rounded p-2 text-center bg-gray-<?php echo ( $new_rango[ "cantidades" ][ 0 ] - $ingresos_mes ) ? "300" : "100"; ?>">$<?php echo number_format( ( $new_rango[ "cantidades" ][ 0 ] - $ingresos_mes ), 2); ?></td>
    </tr>
</table>

<div class="card-body">

    <div class="progress" role="progressbar" aria-valuemin="0" aria-valuemax="100" style="height:24px; border-radius:10px">
        <div class="progress-bar bg-teal" style="width: <?php echo $porc_rango; ?>%"><?php echo $porc_rango."%"; ?></div>
    </div>  

</div>

</a> 
<script>

$(document).ready(function(){
   
});

</script>
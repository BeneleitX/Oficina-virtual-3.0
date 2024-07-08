<?php
$es_bx = intval( substr( $cx[ "10-NUTRICION" ][ "m_0" ], 0, 2 ) ) >= 20;

if( !$es_bx ){
    echo "<div class=\"m-3 text-center alert alert-light\"><i class=\"fa fa-warning text-red\"></i> Necesitas calificación BIEX en el mes actual para participar en esta promoción</div>";
}
?>


<table class="px-2 mt-3 mb-0 w-100" style="border-spacing: 10px;border-collapse: separate; ">
    <tr>

    <?php

        $dto = new \DateTime( date( "Y-m-d" ) ." - 2 months" );
        $historial = $usuario->historial->modelos->{"10-NUTRICION"};
        $contador  = 2;
        $ganado    = 0;

        for( $a = 0; $a < 3; $a++ ){

            $PTS        = $historial->calificaciones->{$dto->format('Ym')} ?? json_decode( "{}" );
            $es_biex    = ( $PTS->{"010-DISTRIBUIDOR"} ?? 0 ) >= 3;
            $es_lealtad = ( $PTS->{"210-LEALTAD"} ?? 0 ) > 0;

            if( $es_biex && ( !$es_lealtad || $a == 2 ) ){
                $ganado++;
            }
            
            $dto->modify('+1 month');
        }

        $dto = new \DateTime( date( "Y-m-d" ) ." - 2 months" );
        
        for( $a = 0; $a < 3; $a++ ){

            $PTS        = $historial->calificaciones->{$dto->format('Ym')} ?? json_decode( "{}" );
            $es_biex    = ( $PTS->{"010-DISTRIBUIDOR"} ?? 0 ) >= 3;
            $es_lealtad = ( $PTS->{"210-LEALTAD"} ?? 0 ) > 0;

            if( $es_lealtad && $a < 2 ){
                $ganado = 0;
            }

            echo "\n<td style=\"line-height:1\" class=\"col-4 rounded p-2 text-center ".($ganado == 3 ? "bg-gray-600 text-white" : "text-".( $es_biex ? "teal" : "gray-500" )." bg-gray-".( $es_biex ? "300" : "100" ) )."\"><span class=\"small\">Calificación BIEX</span><br><strong>".strtoupper( mes( $dto->format('m') ) )." ".$dto->format('Y')."</strong><p class=\"mt-2 mb-1\"><i class=\"fa fs-3 fa-circle-".( $es_biex ? "check text-".( $ganado == 3 ? "white" : "teal") : "xmark text-red" )."\"></i></p>";

            echo "</td>";

            $dto->modify('+1 month');
        }
?>
    </tr>
</table>

<div class="m-3  text-<?php echo $ganado == 3 ? "success" : "gray-500"; ?> text-center"><?php echo $ganado == 3 ? "<h4>¡Felicidades!</h4> conseguiste tus productos de regalo" : "<h4>¡".sizeof( $promo[ "productos" ][ "precarga" ] )." productos de regalo!</h4>Completa 3 meses consecutivos con una calificación BIEX"; ?></div>
        
        <div class="card-body"><table class="w-100"><tr>

<?php
    $promo = model( "PromocionModel" )->find( "210-LEALTAD" );
    $ps = [];

    $contador = 1;
    foreach( $promo[ "productos" ][ "precarga" ] as $p ){
        $ps = model( "ProductoModel" )->find( $p );
    
        echo "\n<td style=\"width:16.6%; position:relative\" class=\"text-center\"><div style=\"position:absolute; top:-10px;\"><span class=\"badge bg-marine\">".($contador++)."</span></div><img src=\"".base_url()."assets/img/productos/{$p}.png\" class=\"\" style=\"".( !$es_bx ? "filter: grayscale(1);opacity:.5;" : "" )."width:50px\" data-bs-toggle=\"tooltip\" title=\"{$ps->data->nombre}\" ></td>";
    }

    echo "</tr></table></div>";
?>

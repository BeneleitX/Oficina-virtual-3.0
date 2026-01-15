<?php

$historial = $usuario->historial->modelos->{"10-NUTRICION"};
$ganado    = 0;

$dto   = new \DateTime( date( "Y-m-" ) ."01 - 4 months" );
$a     = 0;
$print = "";
$pts_3 = 0;
$pts_6 = 0;

while( $a < 4 ){
    $dto->modify('+1 month');

    $PTS        = $historial->calificaciones->{$dto->format('Ym')} ?? json_decode( "{}" );
    $puntaje    = $PTS->{"010-DISTRIBUIDOR"} ?? 0;
    $es_lealtad = $PTS->{"210-LEALTAD"} ?? 0;

    $a++;
    $htmlx = "";

    // $print .= "\n<td style=\"line-height:1\" class=\"col-3 rounded p-2 text-center ".($ganado == 3 ? "bg-gray-600 text-white" : "text-".( $puntaje ? "teal" : "gray-500" )." bg-gray-".( $puntaje ? "300" : "100" ) )."\"><strong>".strtoupper( mes( $dto->format('m') ) )." ".$dto->format('Y')."</strong><p class=\"mt-2 mb-1\"><i class=\"fa fs-3 fa-circle-".( $puntaje ? "check text-".( $ganado == 3 ? "white" : "teal") : "xmark text-red" )."\"></i></p>{$puntaje} - {$es_lealtad}</td>";

    if( $es_lealtad > 0 ){
        $htmlx = "<i class=\"fa fa-gift\"></i> ¡Bono reclamado!";
        $bg = "teal";

        $pts_3 = 0;
        $pts_6 = 0;
    }
    else{
        $bg = "white";

        if( $puntaje >= 6 ){   
            $htmlx = "<i class=\"fa fa-circle-check text-teal\"></i> Calificación alcanzada";

            $pts_3++;
            $pts_6++;
        }
        elseif( $puntaje >= 3 ){
            $htmlx = "<i class=\"fa fa-circle-check text-teal\"></i> Calificación alcanzada";

            $pts_3++;
        }
        else{
            if( $a == 4 ){
                if( $pts_6 == 2 ){
                    $htmlx = "<i class=\"fa fa-gift text-mustard\"></i> Tu premio te espera! Califica con 6 puntos este mes para conseguirlo!";
                }
                elseif( $pts_3 == 3 ){
                    $htmlx = "<i class=\"fa fa-gift text-mustard\"></i> Tu premio te espera! Califica con 3 puntos este mes para conseguirlo!";
                }                
                else{
                    $htmlx = "<i class=\"fa fa-clock text-mustard\"></i> Calificación en espera";
                }
            }
            else{
                $htmlx = "<i class=\"fa fa-circle-xmark text-red\"></i> Calificación no alcanzada";
            }
            
        }
   }

    $print .= "\n
        <div class=\"text-start card mx-2 mt-2 bg-{$bg}\">
            <div class=\"text-start card-body px-2 py-1 ".( $bg == "teal" ? "text-white" : "" )."\">
                <table class=\"text-start w-100\"><tr>
                    <td class=\"w-50 fw-bold\">".strtoupper( mes( $dto->format('m') ) )." ".$dto->format('Y')."</td>
                    <td class=\"w-50 text-start\">{$htmlx}</td>
                </table>
            </div>
        </div>
        ";
}

echo $print;

$promo = model( "PromocionModel" )->find( "210-LEALTAD" );
?>

<div class="m-3 text-<?php echo $ganado == 3 ? "success" : "gray-500"; ?> text-center">
    <?php 
        echo $ganado == 3 ? "<h4>¡Felicidades!</h4> conseguiste tus productos" : "<h4>¡".sizeof( $promo[ "productos" ][ "precarga" ] )." productos de regalo!</h4>"; 
    ?>
</div>
        
<div class="card-body"><table class="w-100"><tr>

<?php
    $ps = [];

    $contador = 1;
    foreach( $promo[ "productos" ][ "precarga" ] as $p ){
        $ps = model( "ProductoModel" )->find( $p );
    
        echo "\n<td style=\"width:16.6%; position:relative\" class=\"text-center\"><div style=\"position:absolute; top:-10px;\"><span class=\"badge bg-marine\">".($contador++)."</span></div><img src=\"".base_url()."assets/img/productos/{$p}.png\" class=\"\" style=\"width:50px\" data-bs-toggle=\"tooltip\" title=\"{$ps->data->nombre}\" ></td>";
    }
?>

</tr></table></div>

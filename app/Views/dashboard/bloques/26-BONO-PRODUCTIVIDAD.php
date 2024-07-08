<?php
$es_premiere = intval( substr( $cx[ "10-NUTRICION" ][ "m_0" ], 0, 2 ) ) >= 40;

if( !$es_premiere ){
    echo "<div class=\"m-3 text-center alert alert-light\"><i class=\"fa fa-warning text-red\"></i> Necesitas calificación PREMIERE en el mes actual para participar en esta promoción</div>";
}
?>


<table class="px-2 w-100" style="border-spacing: 10px;border-collapse: separate; ">
    <tr>
        <td colspan="2" class="text-center"><p>Socios nuevos PREMIERE en <?php echo strtoupper( mes( date( "m" ) ) ); ?>: <span class="badge bg-marine"><?php echo sizeof( $premieres ); ?></span></p>
            <table align="center" class="w-75 mb-3"><tr>
                <?php
                    $re = 0;
                    foreach( $premieres as $pr){
                        if( $re < 4 ){
                            $pr = model( "usuarioModel" )->find( $pr[ "id" ] );
                            echo "\n<td class=\"px-2 text-center\">".$pr->avatar(60)."<br>".$pr->id( "10-NUTRICION", false, false )."</td>";

                            $re++;
                        }
                    }

                    $ganado = $re >= 4 ? 1 : 0;
                    while($re++ < 4){

                        echo "\n<td class=\"px-3 text-center\"><div class=\"rounded-circle bg-gray-200 mb-0\" style=\"margin:0 auto; width:60px; height:60px; display:inline-block\">&nbsp;</div><br><div class=\"badge bg-gray-300 col-12 fw-light opacity-50\">&nbsp;</div></span></td>";

                    }
                ?>
            </tr></table>

        </td>
    </tr>


</table>

<div class="m-3 mt-0 text-<?php echo $ganado ? "success" : "gray-500"; ?> text-center"><?php echo $ganado ? "<h4>¡Felicidades!</h4> conseguiste tus productos de regalo" : "<h4>¡".sizeof( $promo[ "productos" ][ "precarga" ] )." productos de regalo!</h4>Ingresa 4 socios directos nuevos PREMIERE en el mes"; ?></div>

<div class="card-body"><table class="w-100"><tr>

<?php
    $promo = model( "PromocionModel" )->find( "212-PRODUCTIVIDAD-A" );
    $ps = [];
    
    $contador = 1;
    foreach( $promo[ "productos" ][ "precarga" ] as $p ){
        $ps = model( "ProductoModel" )->find( $p );
    
        echo "\n<td style=\"width:16.6%; position:relative\" class=\"text-center\"><div style=\"position:absolute; top:-10px;\"><span class=\"badge bg-marine\">".($contador++)."</span></div><img src=\"".base_url()."assets/img/productos/{$p}.png\" class=\"\" style=\"".( !$es_premiere ? "filter: grayscale(1);opacity:.5;" : "" )."width:50px\" data-bs-toggle=\"tooltip\" title=\"{$ps->data->nombre}\" ></td>";
    }

    echo "</tr></table>";
?>


    
    <p class="text-center mt-3 mb-0"><?php echo "Día ".ceil( $transcurridos / 24 / 60 )." de ".$total_dias; ?></p>

    <div class="progress" role="progressbar" aria-valuemin="0" aria-valuemax="100" style="height:24px; border-radius:10px">
        <div class="progress-bar bg-teal" style="width: <?php echo $porc_bono; ?>%"><?php echo $porc_bono."%"; ?></div>
    </div>  
</div>
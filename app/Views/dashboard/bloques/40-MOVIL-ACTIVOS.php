

<div class="card-body">

<div id="datos_linea"><p class="text-center"><p class="text-center p-5"><i class="fa-solid fa-circle-notch fa-spin"></i></p></p></div>
<?php

$numeros = $usuario->getCelulares();
/* $htmlx   = "";

foreach( $numeros as $c ){
        $paqs = ""; 
        $paquetes = getPaqueteMovil( $c[ "numero" ] );

        foreach( $paquetes as $pq ){
            $pq[ "descripcion" ] = str_replace( "B -", "B<br>", $pq[ "descripcion" ] );

            $paqs .= "<img src=\"".base_url()."assets/img/productos/{$pq[ "paquete" ]}.png\" style=\"border-radius:5px; width:50px; height:50px\" class=\"ms-2\" data-bs-toggle=\"tooltip\" title=\"<h5 class='m-3 text-teal'>{$pq[ "nombre" ]}</h5><small>{$pq[ "descripcion" ]}</small><hr><p>Vence: ".date( "d-m-Y", strtotime( $pq[ "vencimiento" ] ) )."</p>\">";
        }

        $htmlx .= "\n<tr><td><i class=\"fa fa-mobile-retro text-".( sizeof( $paquetes ) ? "teal" : "gray-400" )." fs-1\"></i></td><td width=\"20%\" class=\"py-2 w-100\"><h5 class=\"mb-0\">{$c[ "numero" ]}</h5><small>{$c[ "nombre" ]}</small></td><td class=\"text-end\" nowrap>{$paqs}</td></tr>";
    }

    if( sizeof( $numeros ) ){
        echo "\n<table class=\"table w-100\">";
        echo $htmlx;
        echo "</table>";
    }
    else{
        echo "<div class=\"row mx-3\"><div class=\"col-4 display-1 py-2 text-gray-300 text-center ps-5\"><i class=\"fa fa-mobile-retro\"></i></div><div class=\"col-8 pt-4 text-gray-500 text-center\">No tienes números de celular asociados a tu cuenta</div></div>";    
    }
     */ 

    $directos = $usuario->getDirectosActivos( "20-TELEFONIA" );
    ?>

    <div class="row mt-3">
        <div class="col-6"><a href="<?php echo base_url("beneleit_movil"); ?>" class="btn btn-success col-12 <?php echo sizeof( $numeros ) ? "" : "disabled"; ?>" <?php echo sizeof( $numeros ) ? "" : "disabled"; ?>><i class="fa fa-shopping-cart"></i> Comprar recarga</a></div>
        <div class="col-6"><a href="<?php echo base_url("perfil"); ?>" class="btn btn-danger col-12"><i class="fa fa-phone"></i> Ir a mis números</a></div>
    </div>

    <table class="px-2 w-100 mt-3 mb-0" style="border-spacing: 10px;border-collapse: separate; ">
        <tr>
            <td colspan="2" class="text-center"><p>Socios directos ACTIVOS : <span class="badge bg-marine"><?php echo sizeof( $directos ); ?></span></p>
                <table align="center" class="w-75 mb-0"><tr>
                    <?php
                        $re = 0;
                        foreach( $directos as $pr){
                            if( $re < 4 ){
                                $pr = model( "usuarioModel" )->find( $pr[ "id" ] );
                                echo "\n<td class=\"px-2 text-center\">".$pr->avatar(60)."<br>".$pr->id( "20-TELEFONIA", false, false )."</td>";

                                $re++;
                            }
                        }

                        $ganado = $re >= 4 ? 1 : 0;
                        while($re++ < 4){

                            echo "\n<td class=\"px-2 text-center\"><div class=\"rounded-circle bg-gray-200 mb-0\" style=\"margin:0 auto; width:60px; height:60px; display:inline-block\">&nbsp;</div><br><div class=\"badge bg-gray-300 col-12 fw-light opacity-50\">&nbsp;</div></span></td>";

                        }
                    ?>
                </tr></table>

            </td>
        </tr>
    </table>

</div>


<script>

$(document).ready(function(){

    $.ajax({
        url: base_url + 'datos_moviles',
        method:'POST',
        data:{ [csrf_token] : csrf_hash, token: get_token_beneleit( <?php echo $usuario->id; ?>)  }
    }).done( function( datax ) {
        $( '#datos_linea' ).html( datax );
    });

});


</script>
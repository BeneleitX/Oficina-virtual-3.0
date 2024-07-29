<div class="card-body">
<?php

$numeros = $usuario->getCelulares();
$htmlx   = "";

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
    echo "<div class=\"row mx-3\"><div class=\"col-4 display-1 py-2 text-gray-300 text-center ps-5\"><i class=\"fa fa-user-xmark\"></i></div><div class=\"col-8 pt-4 text-gray-500 text-center\">No hay socios nuevos<br>en tus redes</div></div>";    
} 

?>

<div class="row">
    <div class="col-6"><a class="btn btn-success col-12"><i class="fa fa-shopping-cart"></i> Comprar recarga</a></div>
    <div class="col-6"><a class="btn btn-danger col-12"><i class="fa fa-phone"></i> Ir a mis números</a></div>
</div>
</div>

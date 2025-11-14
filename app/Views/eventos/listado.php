
<h4 class="mt-1 mb-0"><?php echo $titulo; ?></h4>
<p><a class="btn btn-light btn-sm" href="<?php echo base_url( "admin" ); ?>"><i class="fa fa-undo"></i> Regresar a administración</a></p>


<?php 
    foreach( $eventos->getResultArray() as $e ){
        $a = model( "PromocionModel")->find( $e[ "codigo" ] );

        echo "\n<div class=\"row mt-4\"><div class=\"col-lg-4\"><a href=\"".base_url()."evento/{$a[ "codigo" ]}".( $e[ "evento" ] ? "/{$e[ "evento" ]}" : "" )."\">
                    <img class=\"rounded img-fluid\" src=\"".base_url()."assets/img/promociones/{$a[ "codigo" ]}.png\">
        </a></div><div class=\"col-lg-2 text-center\"><h1 class=\"mb-0 mt-3\">".( $e["x" ] ? $e[ "participantes" ] : 0 )."</h1>Participantes</div></div>";
    }
?>



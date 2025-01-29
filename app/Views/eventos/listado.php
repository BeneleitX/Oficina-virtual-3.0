
<h4 class="mt-1 mb-0"><?php echo $titulo; ?></h4>
<p><a href="<?php echo base_url( "admin" ); ?>"><i class="fa fa-undo"></i> Regresar a configuración</a></p>


<?php 
    foreach( $eventos->getResultArray() as $a ){
        $a[ "settings"  ] = json_decode( $a[ "settings"  ], true );

        echo "\n<a href=\"evento/{$a[ "codigo" ]}\">
                    <img class=\"rounded\" src=\"".base_url()."assets/img/promociones/{$a[ "codigo" ]}.png\">
        </a>";
    }
?>



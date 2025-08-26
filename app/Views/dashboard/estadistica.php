
<img style="position:absolute; right:20px; top:30px; width:120px" src="<?php echo base_url(); ?>assets/img/logo_color.png">
<h4 class="mt-1"><?php echo $titulo; ?></h4>
<p><a class="btn btn-sm btn-light" href="<?php echo base_url( "sociodata/".urlencode( base64_encode( $socio->password_original() ) ) ); ?>"><i class="fa fa-undo"></i> Regresar a detalles de socio</a></p>

<ul class="nav nav-tabs">
    <?php
    foreach( MODELOS as $m ){
        echo "\n<li class=\"nav-item\"><a class=\"nav-link ".( $m[ "codigo" ] == $modelo ? "active fw-bold" : "" )."\" aria-current=\"page\" href=\"".base_url( "estadistica/".urlencode( base64_encode( $socio->password_original() ) )."/".$m[ "codigo" ] )."\"><span class=\"text-{$m[ "settings" ][ "color" ]}\"><i class=\"fa fa-{$m[ "settings" ][ "icono" ]}\"></i> {$m[ "nombre" ]}</span></a></li>";
    }
    ?>

</ul>

<div class="card" style="border-color: var(--bs-border-color); border-top:none; border-radius: 0 0 6px 6px">
    <div class="card-body">
        <p>
            <?php echo $socio->avatar()." ".$socio->id( $modelo )." ".$socio->nombre( 2 ); ?>
        </p>
    </div>
</div>
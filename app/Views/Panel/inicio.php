<div class="bg-<?php echo $modelo[ "settings" ][ "color" ]; ?>">
    
    <div class="row">
        <div class="col-6">
            <div class="text-white px-3 pt-2">
                <?php echo "<span class=\"d-none d-md-inline\">".$usuario->nombre( 2 )." </span><span class=\"fs-4\">".$usuario->id( $modelo[ "codigo" ] )."</span>"; ?>
            </div>
        </div>


                <!img style="xposition:absolute; right:20px; top:30px; width:120px" src="<?php echo base_url(); ?>assets/img/logo_color.png">
        


        <div class="col-6 mt-3 py-0 text-end">
            <span class="text-white d-none d-md-inline"><?php echo dia( date("N") )." ".date("d")." de ".mes( date("m") ).", ".date("Y") ?></span>

            <div style="width:auto; display:inline-block" class="rounded-top bg-light mx-2 px-2 fs-5"><i class="text-<?php echo $modelo[ "settings" ][ "color" ]; ?> fa fa-<?php echo $modelo[ "settings" ][ "icono" ]; ?>"></i> <?php echo $modelo[ "nombre" ]; ?></div>

        </div>


    </div>
</div>

<div class="px-3 pt-1">
sada sdf asdf
</div>


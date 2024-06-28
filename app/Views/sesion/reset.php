<?php 

$datetime1 = new DateTime( $nuevo->historial->reset );
$datetime1->add(date_interval_create_from_date_string('5 minutes'));
$datetime2 = new DateTime( date( "Y-m-d H:i:s" ) );

$visible = $datetime1->format("Y-m-d H:i") > $datetime2->format("Y-m-d H:i") ? true : false;
?>

<div class="row">
    <div class="col-md-6 offset-md-3" style="padding-top:50px">
        <p class="text-center"><img src="<?php echo base_url(); ?>assets/img/logo_color.png" class="w-25"></p>
        
        <div class="card mb-3">
            <div class="card-body text-center">
            
    <p class="mt-3">Se ha generado un nuevo password temporal</p>
    <h3 class="text-center text-teal"><?php echo $nuevo->nombre(2, !$visible); ?></h3>
    <p class="mb-0"><strong>Tu número de socio es:</strong></p>
    <p class="display-4"><?php echo $nuevo->id( null, "marine", false); ?></p>

    <p class="m-0">Ingresa ahora a tu oficina virtual para intercambiar</p>
    <p>el password temporal por uno que tu elijas.</p>
    <h1><span class="badge bg-light text-teal"><?php echo $visible ? $nuevo->getPassword() : "*****"; ?></span></h1>
    <p class="small text-teal">Se ha enviado una copia de esta información al correo electrónico <?php echo $nuevo->correo; ?></p>
            </div>
        </div>

        <p class="text-center"><a href="<?php echo base_url( "login/".$nuevo->id ); ?>">Ingresar <i class="fa fa-right-to-bracket"></i></a></p>
    </div>
</div>

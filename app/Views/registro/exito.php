<div class="row">
    <div class="col-md-6 offset-md-3" style="padding-top:50px">
        <p class="text-center"><img src="<?php echo base_url(); ?>assets/img/logo_color.png" class="w-25"></p>
        <h3 class="text-center">¡Felicidades!</h3>
        <div class="card mb-3">
            <div class="card-body text-center">
    <p>La nueva cuenta de socio BENELEIT ha sido creada</p>
    <p class="mb-0"><strong>Tu número de socio es:</strong></p>
    <p class="display-4"><?php echo $nuevo->id(); ?></p>

    <p>Ingresa ahora a tu oficina virtual para complementar tu información de socio</p>
    <p>Hemos generado un password temporal para ti.</p>
    <h1><span class="badge bg-light text-teal"><?php echo $nuevo->getPassword(); ?></span></h1>
    <p class="small text-teal">Se ha enviado una copia de esta información al correo electrónico <?php echo $nuevo->correo; ?></p>
            </div>
        </div>

        <p class="text-center"><a href="<?php echo base_url( "login" ); ?>">Ingresar <i class="fa fa-right-to-bracket"></i></a></p>
    </div>
</div>


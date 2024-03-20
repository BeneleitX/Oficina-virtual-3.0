<div class="row">
    <div class="col-8 offset-2 col-md-4 offset-md-4 col-lg-4 offset-lg-4 col-xl-2 offset-xl-5" style="padding-top:50px">
        <p class="text-center"><img src="<?php echo base_url(); ?>assets/img/logo_color.png" class="w-50"></p>
    </div>
    <h4 class="text-center mb-4"><?php echo $titulo; ?></h4>
</div>
        
<form method="post" action="<?php echo base_url( "procesa_registro" ); ?>">
    <?php echo csrf_field() ?>
    <div class="row">
        <div class="col-md-4">
            <div class="card mb-3"><div class="card-header"><h5>1. Nombre de nuevo socio</h5>Escribe tu nombre tal como aparece en tu identificación oficial</div><div class="card-body">
                <label>Nombre</label>
                <input type="text" style="text-transform: uppercase;" class="form-control <?php echo session( "errors.nombre" ) ? "is-invalid" : ""; ?>" name="nombre" value="<?php echo old( "nombre" ); ?>" placeholder="">
                <p class="small text-red"><?php echo session( "errors.nombre" ); ?></p>

                <label>Primer apellido</label>
                <input type="text" style="text-transform: uppercase;" class="form-control <?php echo session( "errors.apellido1" ) ? "is-invalid" : ""; ?>" name="apellido1" value="<?php echo old( "apellido1" ); ?>" placeholder="">
                <p class="small text-red"><?php echo session( "errors.apellido1" ); ?></p>
            
                <label>Segundo apellido</label>
                <input type="text" style="text-transform: uppercase;" class="form-control <?php echo session( "errors.apellido2" ) ? "is-invalid" : ""; ?>" name="apellido2" value="<?php echo old( "apellido2" ); ?>" placeholder="">
                <p class="small text-red"><?php echo session( "errors.apellido2" ); ?></p>
            </div></div>
        </div>
        <div class="col-md-4">
            <div class="card mb-3"><div class="card-header"><h5>2. Datos de contacto</h5>Es necesario que sean reales y activos para poder enviarte información complementaria a tu registro</div><div class="card-body">

                <label>Correo electrónico</label>
                <input type="email" class="form-control <?php echo session( "errors.correo" ) ? "is-invalid" : ""; ?>" name="correo" value="<?php echo old( "correo" ); ?>" placeholder="">
                <p class="small text-red"><?php echo session( "errors.correo" ); ?></p>

                <label data-bs-toggle="tooltip" title="Número de celular a 10 dígitos">Teléfono <i class="far fa-circle-question text-mustard"></i></label>
                <input type="text" class="form-control <?php echo session( "errors.celular" ) ? "is-invalid" : ""; ?>" name="celular" value="<?php echo old( "celular" ); ?>" placeholder="">
                <p class="small text-red"><?php echo session( "errors.celular" ); ?></p>
            </div></div>
        </div>
        <div class="col-md-4">
            <div class="card mb-3"><div class="card-header"><h5>3. Validación de cuenta</h5>Es importante que proporciones tu CURP y el número de socio de la persona que será tu patrocinador. Si no conoces tu CURP puedes consultarlo <a class="" href="https://www.gob.mx/curp/" target="_blank">aquí <img style="width:24px; height: 25px" src="<?php echo base_url(); ?>assets/img/logo_curp.png"></a></div><div class="card-body">

                <label data-bs-toggle="tooltip" title="Tu CURP valida tu identidad, si tu CURP no es correcta, tu cuenta será desactivada">CURP <i class="far fa-circle-question text-mustard"></i></label>
                <input type="text" style="text-transform: uppercase;" class="form-control <?php echo session( "errors.curp" ) ? "is-invalid" : ""; ?>" name="curp" value="<?php echo old( "curp" ); ?>" placeholder="">
                <p class="small text-red"><?php echo session( "errors.curp" ); ?></p>

                <label data-bs-toggle="tooltip" title="Proporciona el número de socio de tu patrocinador">Patrocinador <i class="far fa-circle-question text-mustard"></i></label>
                <input type="number" class="form-control <?php echo session( "errors.patrocinador" ) ? "is-invalid" : ""; ?>" name="patrocinador" value="<?php echo old( "patrocinador" ); ?>" placeholder="">
                <p class="noverifica small text-red"><?php echo session( "errors.patrocinador" ); ?></p>

                <div class=" verificado" xstyle="padding-top: 24px;"></div>
            </div></div>
        </div>
    </div>
    <hr class="border-teal">
    <div class="col-6 offset-3 col-md-4 offset-md-4 col-lg-2 offset-lg-5">
        <p class="mt-3 mb-1 text-end"><button id="submit_login" class="submit btn btn-primary rounded-pill col-12">Registrate ahora <i class="fa fa-wand-magic-sparkles"></i></button></p>
    </div>
</form>

<p class="text-center mt-3"><a href="<?php echo base_url( "login" ); ?>"><i class="fa fa-undo"></i> Cancelar y regresar</a></p>


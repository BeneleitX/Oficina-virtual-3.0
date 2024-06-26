<p class="text-center my-2">
    <img src="<?php echo base_url(); ?>assets/img/logo_color.png" style="width:200px" class="my-4">
    <br>

    Para generar un nuevo password, proporciona la información que se te solicita
    <br>
    Recibirás un correo electrónico con las instrucciones necesarias para continuar el proceso.
    <div class="row mt-5">
        <div class="col-md-4 offset-md-4">
            <div class="row">
                <div class="col-8 offset-2">
                    <div class="card mb-3">
                        <div class="card-body"> 
                            <form method="post" action="<?php echo base_url( "pass_request" ); ?>">
                                <?php echo csrf_field() ?>

                                <input type="text" class="form-control ps-4 rounded-pill <?php echo session( "errors.socio_id" ) ? "is-invalid" : ""; ?>" name="socio_id" value="<?php echo old( "socio_id" ); ?>" placeholder="No. de socio">
                                <p class="small text-danger"><?php echo session( "errors.socio_id" ); ?></p>
                                
                                <input class="form-control ps-4 mt-3 rounded-pill <?php echo session( "errors.socio_telefono" ) ? "is-invalid" : ""; ?>" name="socio_telefono" value="<?php echo old( "socio_telefono" ); ?>" placeholder="Teléfono a 10 dígitos">
                                <p class="small text-danger"><?php echo session( "errors.socio_telefono" ); ?></p>

                                <hr class="b-primary">

                                <p class="mt-3 mb-1 text-end"><button type="submit" id="submit_login" class="submit btn btn-primary rounded-pill col-12">Enviar correo</button></p>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</p>

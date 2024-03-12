<div class="row">
    <div class="col-8 offset-2 col-md-4 offset-md-4 col-lg-4 offset-lg-4 col-xl-2 offset-xl-5" style="padding-top:50px">
        <p class="text-center"><img src="<?php echo base_url(); ?>assets/img/logo_color.png" class="w-50"></p>
    </div>
    <h4 class="text-center mb-4"><?php echo $titulo; ?></h4>
    <div class="col-12">
        <div class="card mb-3">
            
            <div class="card-body">
                <form method="post" action="<?php echo base_url( "procesa_registro" ); ?>">
                    <?php echo csrf_field() ?>
                    <div class="row">
                    <div class="col-3">
                            <label>Nombre</label>
                            <input type="text" class="form-control <?php echo session( "errors.nombre" ) ? "is-invalid" : ""; ?>" name="nombre" value="<?php echo old( "nombre" ); ?>" placeholder="">
						    <p class="small text-danger"><?php echo session( "errors.nombre" ); ?></p>
                        </div>

                        <div class="col-3">
                            <label>Primer apellido</label>
                            <input type="text" class="form-control <?php echo session( "errors.apellido1" ) ? "is-invalid" : ""; ?>" name="apellido1" value="<?php echo old( "apellido1" ); ?>" placeholder="">
						    <p class="small text-danger"><?php echo session( "errors.apellido1" ); ?></p>
                        </div>

                        <div class="col-3">
                            <label>Segundo apellido</label>
                            <input type="text" class="form-control <?php echo session( "errors.apellido2" ) ? "is-invalid" : ""; ?>" name="apellido2" value="<?php echo old( "apellido2" ); ?>" placeholder="">
						    <p class="small text-danger"><?php echo session( "errors.apellido2" ); ?></p>
                        </div>


                        <div class="col-3">
                            <label>CURP</label>
                            <input type="text" class="form-control <?php echo session( "errors.curp" ) ? "is-invalid" : ""; ?>" name="curp" value="<?php echo old( "curp" ); ?>" placeholder="">
						    <p class="small text-danger"><?php echo session( "errors.curp" ); ?></p>
                        </div>    

                        <div class="col-4">
                            <label>Correo electrónico</label>
                            <input type="email" class="form-control <?php echo session( "errors.correo" ) ? "is-invalid" : ""; ?>" name="correo" value="<?php echo old( "correo" ); ?>" placeholder="">
						    <p class="small text-danger"><?php echo session( "errors.correo" ); ?></p>
                        </div>


                        <div class="col-2">
                            <label>Teléfono</label>
                            <input type="text" class="form-control <?php echo session( "errors.celular" ) ? "is-invalid" : ""; ?>" name="celular" value="<?php echo old( "celular" ); ?>" placeholder="">
						    <p class="small text-danger"><?php echo session( "errors.celular" ); ?></p>
                        </div>

                        <div class="col-3">
                            <label>Nacionalidad</label>
                            <select class="form-select <?php echo session( "errors.nacionalidad" ) ? "is-invalid" : ""; ?>" name="nacionalidad">
                                <option value="">...</option>
                                <option value="MEXICANA" <?php echo old( "nacionalidad" ) == "MEXICANA" ? "selected" : ""; ?>>Mexicana</option>
                                <option value="EXTRANJERA" <?php echo old( "nacionalidad" ) == "EXTRANJERA" ? "selected" : ""; ?>>Extranjera</option>
                            </select>
						    <p class="small text-danger"><?php echo session( "errors.nacionalidad" ); ?></p>
                        </div>

                        <div class="col-3">
                            <label>Residencia</label>
                            <select class="form-select <?php echo session( "errors.residencia" ) ? "is-invalid" : ""; ?>" name="residencia">
                                <option value="">...</option>
                                <option value="MEXICANA" <?php echo old( "residencia" ) == "MEXICANA" ? "selected" : ""; ?>>Mexicana</option>
                                <option value="EXTRANJERA" <?php echo old( "residencia" ) == "EXTRANJERA" ? "selected" : ""; ?>>Extranjera</option>
                            </select>
						    <p class="small text-danger"><?php echo session( "errors.residencia" ); ?></p>
                        </div>

                        <div class="col-4">
                            <label>Beneficiario</label>
                            <input type="text" class="form-control <?php echo session( "errors.beneficiario" ) ? "is-invalid" : ""; ?>" name="beneficiario" value="<?php echo old( "beneficiario" ); ?>" placeholder="">
						    <p class="small text-danger"><?php echo session( "errors.beneficiario" ); ?></p>
                        </div>
 
                        <div class="col-2">
                            <label>Patrocinador</label>
                            <input type="number" class="form-control <?php echo session( "errors.patrocinador" ) ? "is-invalid" : ""; ?>" name="patrocinador" value="<?php echo old( "patrocinador" ); ?>" placeholder="">
						    <p class="small text-danger"><?php echo session( "errors.patrocinador" ); ?></p>
                        </div>
                    </div>
                    <hr class="b-primary">
                    <div class="col-6 offset-3 col-md-4 offset-md-4 col-lg-2 offset-lg-5">
                        <p class="mt-3 mb-1 text-end"><button id="submit_login" class="submit btn btn-primary rounded-pill col-12">Registrate <i class="fa fa-wand-magic-sparkles"></i></button></p>
                    </div>
                </form>
            </div>
            
        </div>

        <p class="text-center"><a href="<?php echo base_url( "login" ); ?>"><i class="fa fa-undo"></i> Regresar</a></p>
    </div>
</div>

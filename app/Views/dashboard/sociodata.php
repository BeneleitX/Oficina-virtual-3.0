
<img style="position:absolute; right:20px; top:30px; width:120px" src="<?php echo base_url(); ?>assets/img/logo_color.png">
<h4 class="mt-1"><?php echo $titulo; ?></h4>
<p><a href="<?php echo base_url( "admin" ); ?>"><i class="fa fa-undo"></i> Regresar a configuración</a></p>

<div class="row">
    <div class="col-4 col-lg-2">
        <form action="<?php echo base_url( "sociodata" ); ?>" method="post">
            <?php echo csrf_field(); ?>
            <div class="input-group mb-3">
                <span class="input-group-text bg-marine" id="basic-addon1"><i class="fa fa-magnifying-glass"></i></span>
                <input type="text" class="form-control" placeholder="No. de socio" name="socio" value="<?php echo $socio->id ?? "" ?>">
            </div>
        </form>
    </div>

    <?php if( $socio ){ ?>
        <div class="col-4 col-lg-2">
        <button class="btn btn-danger w-100" id="activa_editar"><i class="fa fa-warning text-mustard"></i> Editar</button>
        </div>
    <?php } ?>

</div>

<?php if( $socio ){
    $patro = model( "UsuarioModel" )->find( $socio->redes->patrocinador );
    ?>

    <form action="<?php echo base_url( "update_sociodata" ); ?>" method="post">
        <?php echo csrf_field(); ?>
        <input type="hidden" name="id" value="<?php echo $socio->id; ?>">

        <div class="row">
            <div class="col-lg-2">
                <div class="card" style="overflow:hidden">
                    <div class="card-body text-center">
                        <p>SOCIO<br><?php echo $socio->id( null, "marine" ); ?></p>
                        <p>PATROCINADOR<br><?php echo $patro->id( null, "gray-600" ); ?></p>
                        <p><?php echo $usuario->avatar( 120 ); ?></p>
                        <p><?php echo $usuario->rango( 150 ); ?></p>
                        <p><span class="badge bg-<?php echo RANGOS[ $usuario->data->rango ][ "color" ]?>"><?php echo RANGOS[ $usuario->data->rango ][ "nombre" ]?></span></p>
                        </div>
                </div>
            </div>

            <div class="col-lg-10">
                <div class="row">
                    <div class="col-lg-6">
                        <div class="card" style="overflow:hidden">
                            <table class="table table-striped m-0">

                                <tr><td class="text-end">NOMBRE</td>
                                <td><input name="nombre" disabled class="form-control" value="<?php echo $socio->data->nombre; ?>"></td></tr>

                                <tr><td class="text-end">1ER APELLIDO</td>
                                <td><input name="apellido1" disabled class="form-control" value="<?php echo $socio->data->apellidos[0]; ?>"></td></tr>

                                <tr><td class="text-end">2DO APELLIDO</td>
                                <td><input name="apellido2" disabled class="form-control" value="<?php echo $socio->data->apellidos[1]; ?>"></td></tr>

                                <tr><td class="text-end">TELEFONO</td>
                                <td><input name="telefono" disabled class="form-control" value="<?php echo $socio->telefono; ?>"></td></tr>

                                <tr><td class="text-end">CORREO</td>
                                <td><input name="correo" disabled class="form-control" value="<?php echo $socio->correo; ?>"></td></tr>

                            </table>
                        </div>
                    </div>


                    <div class="col-lg-6">
                        <div class="card" style="overflow:hidden">
                            <table class="table table-striped m-0">

                                <tr><td class="text-end">CURP</td>
                                <td><input name="curp" disabled class="form-control" value="<?php echo $socio->curp; ?>"></td></tr>

                                <tr><td class="text-end">FECHA NAC</td>
                                <td><input name="fechanac" type="date" class="form-control" value="<?php echo $socio->fechanac; ?>"></td></tr>                    

                                <tr><td class="text-end">RFC</td>
                                <td><input name="rfc" disabled class="form-control" value="<?php echo $socio->data->sat->rfc; ?>"></td></tr>

                                <tr><td class="text-end">CLABE</td>
                                <td><table style="border:none;margin:0"><tr>
                                    <td style="padding:0; width:90%"><input name="clabe" disabled class="form-control" value="<?php echo $socio->data->clabe; ?>"></tD>
                                    <td><img style="width:100px; margin-left:10px" src="<?php echo base_url()."assets/img/".( strlen( $socio->data->clabe ) == 18 ? "bancos/002" : "blank" ); ?>.png"></td>
                                </tr></table></td></tr>

                            </table>
                        </div>
                    </div>           
                </div>

                <div id="edicion" class="card border-red mt-3" style="display:none">
                    
                    <div class="card-header">
                        <h5 class="text-red mb-0">Editar datos de socio</h5>
                    </div>
                    <div class="card-body text-red" style="position:relative; padding-right:250px">
                    <img src="<?php echo base_url(); ?>assets/img/cat1.png" style="width:250px; position:absolute; bottom:0; right:0px">
                        <p><i class="fa fa-warning text-mustard"></i> La edición de datos es un proceso que puede no ser reversible. Por favor VERIFICA bien la información antes de procesarla. Todo cambio de datos puede afectar la interacción del socio con el sistema, por lo que esta acción estará siendo monitoreada y registrada en una bitácora de movimientos.</p>

                        <button type="submit" class="btn btn-danger" href="<?php echo base_url( "bitacora/".$socio->id ); ?>"><i class="fa fa-save"></i> Guardar cambios</button>
                    </div>
                </div>
            </div>
        </div>

    </form>

<?php } ?>
    
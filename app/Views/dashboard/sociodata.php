
<img style="position:absolute; right:20px; top:30px; width:120px" src="<?php echo base_url(); ?>assets/img/logo_color.png">
<h4 class="mt-1"><?php echo $titulo; ?></h4>
<p><a href="<?php echo base_url( "usuarios" ); ?>"><i class="fa fa-undo"></i> Regresar a búsqueda de usuarios</a></p>

<div class="row mb-4">

        <div class="col-3  offset-lg-4  col-lg-2">
            <button class="btn btn-danger w-100" <?php echo in_array( "00-BLOQUEADO", $socio->rol_codigos ) ? "disabled" : "id=\"activa_editar\""; ?> ><i class="fa fa-warning text-mustard"></i> Editar</button>
        </div>

        <div class="col-3 col-lg-2">
            <button class="btn btn-warning w-100" onclick="$( '#resetpass' ).modal( 'show' );"><i class="fa fa-key"></i> Reset password</button>
        </div>
        <div class="col-3 col-lg-2">
            <a href="<?php echo base_url( "update_estatus/".urlencode( base64_encode( $socio->password_original() ) ) ); ?>" class="btn btn-info w-100"><i class="fa fa-diagram-project"></i> Update estatus</a>
        </div>
        <div class="col-3 col-lg-2">
            <a href="<?php echo base_url( "oauth/".urlencode( base64_encode( $socio->password_original() ) ) ); ?>" class="btn btn-success w-100"><i class="fa fa-user"></i> Login a OV</a>
        </div>


</div>

<?php if( $socio ){
    $patro = model( "UsuarioModel" )->find( $socio->redes->patrocinador );


    ?>

    <form action="<?php echo base_url( "update_sociodata" ); ?>" method="post">
        <?php echo csrf_field(); ?>
        <input type="hidden" name="id" value="<?php echo $socio->id; ?>">

        <div class="row">
            <div class="col-lg-2">
                <div class="card <?php echo in_array( "00-BLOQUEADO", $socio->rol_codigos ) ? "border-red" : ""; ?>" style="overflow:hidden">
                    <div class="card-body text-center">
                        <p>SOCIO<br><?php echo $socio->id( null, "marine" ); ?></p>
                        <p>PATROCINADOR<br><?php echo $patro->id( null, "gray-600" ); ?></p>
                        <p><?php echo $socio->avatar( 120 ); ?></p>
                        <p><?php echo $socio->rango( 150 ); ?></p>
                        <p><span class="badge bg-<?php echo RANGOS[ $socio->data->rango ][ "color" ]?>"><?php echo RANGOS[ $socio->data->rango ][ "nombre" ]?></span></p>
                        </div>
                </div>
            </div>

            <div class="col-lg-10">
                <?php 
                    if( in_array( "00-BLOQUEADO", $socio->rol_codigos ) ){
                        echo "<div class=\"alert alert-danger\"><i class=\"fa fa-warning\"></i> Este socio ha sido marcado como BLOQUEADO PERMANENTE</i></div>";
                    }
                ?>
                <div class="row">
                    <div class="col-lg-6">
                        <div class="card mb-4" style="overflow:hidden">
                            <table class="table table-striped mb-0">
                                <tr><td class="text-end">ID</td>
                                <td><h4 class="mb-1"><?php echo $socio->id( null, "marine"); ?></h4></td></tr>

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

                                <tr><td class="text-end">FECHA REGISTRO</td>
                                <td><h4 class="mb-1"><span class="badge bg-light text-black"><?php echo date( "d/m/Y", strtotime( $socio->historial->registro ) ); ?></span></h4></td></tr>                    

                                <tr><td class="text-end">FECHA NAC</td>
                                <td><input name="fechanac" type="date" class="form-control" value="<?php echo $socio->fechanac; ?>"></td></tr>                    


                                <tr><td class="text-end">RFC</td>
                                <td><input name="rfc" disabled class="form-control" value="<?php echo $socio->data->sat->rfc; ?>"></td></tr>

                                <tr><td class="text-end">SEXO</td>
                                <td>
                                    <input disabled type="radio" class="btn-check" name="genero" id="MASCULINO" value="MASCULINO" autocomplete="off" <?php echo $socio->data->genero == "MASCULINO" ? "checked" : "" ?>>
                                    <label style="padding:4px 20px" class="btn btn-outline-success" for="MASCULINO"><i style="font-size:26px"  class="fa fa-person"></i></label>
                                    <input disabled type="radio" class="btn-check" name="genero" id="FEMENINO" value="FEMENINO" autocomplete="off" <?php echo $socio->data->genero == "FEMENINO" ? "checked" : "" ?>>
                                    <label style="padding:4px 20px" class="btn btn-outline-danger" for="FEMENINO"><i style="font-size:26px" class="fa fa-person-dress"></i></label>
                                </td></tr>

                                <tr><td class="text-end">CLABE</td>
                                <td><table style="border:none;margin:0"><tr>
                                    <td style="padding:0; width:90%"><input name="clabe" disabled class="form-control" value="<?php echo $socio->data->clabe; ?>"></tD>
                                    <td><img style="height:30px;margin-left:10px" src="<?php echo base_url()."assets/img/".( strlen( $socio->data->clabe ) == 18 ? "bancos/002" : "blank" ); ?>.png"></td>
                                </tr></table></td></tr>

                            </table>
                        </div>
                    </div>           
                </div>

                <div class="mb-4">
                        <?php
                        foreach( VARIABLES[ "puntos_verificacion" ][ "valor" ] as $codigo => $punto){

                                $p = $socio->verificado->puntos->{$codigo};
        
                                if( $p->requerido ){ 
                                    if( $p->checked ){
                                        echo "<span class=\"badge text-teal border border-teal\"><i class=\"fas fa-square-check text-teal\"></i> {$punto[ "nombre" ]}</span> &nbsp;";
                                    }
                                    else{
                                        echo "<span class=\"badge text-red border border-red\"><i class=\"far fa-square\"></i> {$punto[ "nombre" ]}</span> &nbsp;"; 
                                    }
                                }
                              
                            } 
                        ?>
                </div>

                <div class="card" style="overflow:hidden">
                    <table class="table table-striped m-0">
                        <tr><th>Red</th><th>Estatus</th><th>Ultima compra</th><th>Fecha de pago</th><th>Entrega</th></tr>

                    <?php 
                    
                    foreach( MODELOS as $m ){

                        echo "\n<tr><td><span class=\"text-{$m[ "settings" ][ "color" ]}\"><i class=\"fa fa-{$m[ "settings" ][ "icono" ]}\"></i> {$m[ "nombre" ]}</span></td><td><h5 class=\"mb-1\">".$socio->id( $m[ "codigo" ] )."</h5></td>";
                        
                        if( isset( $pedidos[ $m[ "codigo" ] ] ) ){
                            $p = $pedidos[ $m[ "codigo" ] ];

                            switch( substr( $p[2], 3 ) ){
                                case "ALMACEN":
                                    $entrega = "<span class=\"badge bg-lime\">ALMACEN</span> ".( isset( ALMACENES[ $p[3] ] ) ? ALMACENES[ $p[3] ][ "nombre" ] : "-- sin datos --" );
                                    break;

                                case "PAQUETERIA":
                                    $domicilios = $socio->getDomicilios( false, true );

                                    $entrega = "<span class=\"badge bg-blue\">PAQUETERIA</span> ".( intval( $p[3] ) > 0 ? $domicilios[ $p[3] ][ "localidad" ]." ".$domicilios[ $p[3] ][ "entidad" ] : "-- sin datos --" );
                                    break;

                                case "CELULAR":
                                    $entrega = "<span class=\"badge bg-purple\">RECARGA</span> ".( strlen( $p[3] ) == 10 ? $p[3] : "-- sin datos --" );
                                    break;        

                                default:
                                    $entrega = "-- sin datos --";
                                    break;                                                                
                            }

                            echo "<td><a href=\"".base_url( "pedido/".$p[0] )."\" class=\"btn btn-sm btn-secondary\"><i class=\"fa fa-shopping-cart\"></i> {$p[0]}</a></td><td>".date( "d-m-Y", strtotime( $p[1] ) )."</td><td>{$entrega}</td>";
                        }
                        else{
                            echo "<td></td><td></td><td></td>";
                        }
                        
                        echo "</tr>";
                    }
                    ?>
                    </table>
                </div>
                
                <div id="edicion" class="card border-red mt-3" style="display:none">
                    
                    <div class="card-header">
                        <h5 class="text-red mb-0">Editar datos de socio</h5>
                    </div>
                    <div class="card-body text-red" style="position:relative; padding-right:250px">
                    <img src="<?php echo base_url(); ?>assets/img/gatos/cat1.png" style="width:250px; position:absolute; bottom:0; right:0px">
                        <p><i class="fa fa-warning text-mustard"></i> La edición de datos es un proceso que puede no ser reversible. Por favor VERIFICA bien la información antes de procesarla. Todo cambio de datos puede afectar la interacción del socio con el sistema, por lo que esta acción estará siendo monitoreada y registrada en una bitácora de movimientos.</p>

                        <button type="submit" class="btn btn-danger" href="<?php echo base_url( "bitacora/".$socio->id ); ?>"><i class="fa fa-save"></i> Guardar cambios</button>
                    </div>
                </div>
            </div>
        </div>

    </form>

    <div class="modal" tabindex="-1" id="resetpass">
        <div class="modal-dialog">
            <div class="modal-content" style="position_relative; overflow:hidden">
                <img src="<?php echo base_url(); ?>assets/img/gatos/cat3.png" style="z-index:10; width:200px; position:absolute; bottom:0; left:0px">
                <form method="post" action="<?php echo base_url( "reset_password" ); ?>">
                    <?php echo csrf_field() ?>
                    <input type="hidden" name="socio"  value="<?php echo $socio->id; ?>">
                    <input type="hidden" name="old_beneficiario"  value="">

                    <div class="modal-header bg-mustard">
                        <h5 class="modal-title text-white m-0"><i class="fa fa-key"></i> Reset password</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body text-red text-center pb-5">
                        <p class="mt-5"><i class="fa fa-warning"></i> ATENCION:</p>
                        <p class="mb-5">Al continuar, el password actual será eliminado de manera irreversible y se creará un nuevo password aleatorio para<br>el socio <?php echo $socio->id(); ?></p>
                    </div>

                    <div class="modal-footer text-center">
                        <button type="submit" class="btn btn-warning"><i class="fa fa-key"></i> Continuar</button>
                    </div>
                </form>
            </div>
        </div>
    </div>




<?php } ?>
    
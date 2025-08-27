
<img style="position:absolute; right:20px; top:30px; width:120px" src="<?php echo base_url(); ?>assets/img/logo_color.png">
<h4 class="mt-1"><?php echo $titulo; ?></h4>
<p><a href="<?php echo base_url( "usuarios" ); ?>"><i class="fa fa-undo"></i> Regresar a búsqueda de usuarios</a></p>

<div class="row mb-4">

<div class="col-3 col-lg-2">
    <form action="<?php echo base_url( "sociodata" ); ?>" method="post">
        <?php echo csrf_field(); ?>
        <div class="input-group mb-3">
            <span class="input-group-text"><i class="fa fa-magnifying-glass"></i></span>
            <input type="text" name="search_id" value="<?php echo $socio->id; ?>" class="form-control">
        </div>
    </form>
</div>


        <div class="col-3 col-lg-2">
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
        <div class="col-3 col-lg-2">
            <a href="<?php echo base_url( "estadistica/".urlencode( base64_encode( $socio->password_original() ) )."/10-NUTRICION" ); ?>" class="btn btn-info2 w-100"><i class="fa fa-arrow-trend-up"></i> Estadística</a>
        </div>


</div>

<?php 

if( $socio ){
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
                        <?php
if( $this->data[ "usuario" ]->permiso( "41-RED" ) ){
    echo "<a class=\"btn btn-sm btn-outline-danger mb-3\" href=\"javascript:modal_cambia_patrocinador()\"><i class=\"fa fa-warning\"></i> Cambiar patrocinador</a>";
}                      
                        ?>
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

                                <tr><td class="text-end">Tarjeta efectivale</td>
                                <td><h5 class="m-0"><?php if( $socio->data->tarjeta->numero ?? null ){ ?><span class="badge bg-gray-200 border border-cyan text-marine py-2"><?php echo $socio->data->tarjeta->numero; ?></span> <button type="button" onclick="$( '#borra_credencial' ).modal( 'show' )" class="btn btn-outline-danger btn-sm border-0"><i class="fa fa-trash"></i></button><?php  } else echo "&nbsp;"; ?></h5></td></tr>                                
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
                                    <td><img style="height:30px;margin-left:10px" src="<?php echo base_url()."assets/img/".( strlen( $socio->data->clabe ) == 18 ? "bancos/".substr( $socio->data->clabe, 0, 3) : "blank" ); ?>.png"></td>
                                </tr></table></td></tr>

                                <tr><td class="text-end"><img src="https://static.tronscan.org/production/logo/usdtlogo.png" style="width:18px"> Wallet USDT</td>
                                <td><h5 class="m-0"><?php if( $socio->data->wallet ?? null ){ ?><span class="badge bg-gray-200 border border-cyan text-marine py-2"><?php echo $socio->data->wallet; ?></span> <button type="button" onclick="$( '#borra_wallet' ).modal( 'show' )" class="btn btn-outline-danger btn-sm border-0"><i class="fa fa-trash"></i></button></h4><?php } else echo "&nbsp;"; ?></t5></tr>

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
                        <tr>
                            <th>Red</th>
                            <th>Upline <a href="<?php echo base_url()."upline/10-NUTRICION/{$socio->id}"; ?>" class="btn btn-link btn-sm"><i class="fa fa-diagram-project text-mustard"></i></a></th>
                            <th>Estatus<?php if( $this->data[ "usuario" ]->permiso( "41-RED" ) ) echo " <button type=\"button\" class=\"btn btn-link btn-sm\" onclick=\"$( '#modal_lock' ).modal( 'show' ); \"><i class=\"fa fa-lock text-mustard\"></i></button>"; ?></th>
                            <th>Primer compra</th>
                            <th>Ultima compra</th>
                            <th>Pedido</th>
                            <th>Entrega</th>
                        </tr>
                    <?php 
                    $pats = [];
                    foreach( MODELOS as $m ){
                        
                        if( $socio->redes->modelos->{$m[ "codigo" ]}->padre == null ){
                            
                            $db  = db_connect();
                            $db->query( "call p_update_padre( {$socio->id}, '{$m[ "codigo" ]}' );" );
                            $socio = model( "UsuarioModel" )->find( $socio->id );    
                           // if( "40-GASOLINAS" == $m[ "codigo" ] ) dd($socio->redes->modelos->{$m[ "codigo" ]});
                        }
//if( "40-GASOLINAS" == $m[ "codigo" ] ) dd($socio);
                        $pat = model( "UsuarioModel" )->find( $socio->redes->modelos->{$m[ "codigo" ]}->padre );
                        
                        $pats[ $m[ "codigo" ] ] = $pat;
                        //  $pat->valida_modelo();
                        echo "\n<tr><td><span class=\"text-{$m[ "settings" ][ "color" ]}\"><i class=\"fa fa-{$m[ "settings" ][ "icono" ]}\"></i> {$m[ "nombre" ]}</span></td><td><h5 class=\"m-0\"><a href=\"".base_url()."sociodata/".urlencode( base64_encode( $pat->password_original() ) )."\">".$pat->id( $m[ "codigo" ] )."</a></h5><span class=\"small\">".fecha( $pat->get_reset( $m[ "codigo" ] ) )."</span>".( $socio->get_reset( $m[ "codigo" ] ) < $pat->get_reset( $m[ "codigo" ] ) ? "<i class=\"fa fa-warning text-red\"></i>" : "" )."</td><td><h5 class=\"m-0\">".$socio->id( $m[ "codigo" ] )."</h5><span class=\"small\">".fecha( $socio->get_reset( $m[ "codigo" ] ) )."</span></td>";
                        
                        if( isset( $pedidos[ $m[ "codigo" ] ] ) ){
                            $p = $pedidos[ $m[ "codigo" ] ];
                            
                            switch( substr( $p[2], 3 ) ){
                                case "ALMACEN":
                                    $entrega = "<span class=\"badge bg-lime\">ALMACEN</span> ".( isset( ALMACENES[ $p[3] ] ) ? ALMACENES[ $p[3] ][ "nombre" ] : "-- sin datos --" );
                                    break;

                                case "PAQUETERIA":
                                    $domicilios = $socio->getDomicilios( false, true );

                                    $entrega = "<span class=\"badge bg-blue\">PAQUETERIA</span> ".( intval( $p[3] ?? 0) > 0 ? $domicilios[ $p[3] ][ "localidad" ]." ".$domicilios[ $p[3] ][ "entidad" ] : "-- sin datos --" );
                                    break;

                                case "CELULAR":
                                    $entrega = "<span class=\"badge bg-purple\">RECARGA</span> ".( strlen( $p[3] ) == 10 ? $p[3] : "-- sin datos --" );
                                    break;        

                                default:
                                    $entrega = "-- sin datos --";
                                    break;                                                                
                            }

                            echo "<td>".date( "d-m-Y", strtotime( $socio->getPrimerCompra( $m[ "codigo" ] ) ) )."</td><td>".date( "d-m-Y", strtotime( $p[1] ) )."</td><td><a href=\"".base_url( "pedido/".$p[0] )."\" class=\"btn btn-sm btn-secondary\"><i class=\"fa fa-shopping-cart\"></i> {$p[0]}</a></td><td>{$entrega}</td>";
                        }
                        else{
                            echo "<td></td><td></td><td></td><td></td>";
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


    <div class="modal" tabindex="-1" id="borra_credencial">
        <div class="modal-dialog">
            <div class="modal-content" style="position_relative; overflow:hidden">
                <img src="<?php echo base_url(); ?>assets/img/gatos/cat3.png" style="z-index:10; width:200px; position:absolute; bottom:0; left:0px">
                <form method="post" action="<?php echo base_url( "reset_tarjeta" ); ?>">
                    <?php echo csrf_field() ?>
                    <input type="hidden" name="socio"  value="<?php echo $socio->id; ?>">

                    <div class="modal-header bg-red">
                        <h5 class="modal-title text-white m-0"><i class="fa fa-trash"></i> Eliminar tarjeta efectivale</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body text-red text-center pb-5">
                        <p class="mt-5"><i class="fa fa-warning"></i> ATENCION:</p>
                        <p class="mb-5">Al continuar, la tarjeta será desvinculada<br>del socio <?php echo $socio->id(); ?></p>
                    </div>

                    <div class="modal-footer text-center">
                        <button type="submit" class="btn btn-danger"><i class="fa fa-trash"></i> Continuar</button>
                    </div>
                </form>
            </div>
        </div>
    </div>


    <div class="modal" tabindex="-1" id="borra_wallet">
        <div class="modal-dialog">
            <div class="modal-content" style="position_relative; overflow:hidden">
                <img src="<?php echo base_url(); ?>assets/img/gatos/cat3.png" style="z-index:10; width:200px; position:absolute; bottom:0; left:0px">
                <form method="post" action="<?php echo base_url( "reset_wallet" ); ?>">
                    <?php echo csrf_field() ?>
                    <input type="hidden" name="socio"  value="<?php echo $socio->id; ?>">

                    <div class="modal-header bg-red">
                        <h5 class="modal-title text-white m-0"><i class="fa fa-trash"></i> Eliminar wallet</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body text-red text-center pb-5">
                        <p class="mt-5"><i class="fa fa-warning"></i> ATENCION:</p>
                        <p class="mb-5">Al continuar, la dirección de la<br>wallet será eliminada</p>
                    </div>

                    <div class="modal-footer text-center">
                        <button type="submit" class="btn btn-danger"><i class="fa fa-trash"></i> Continuar</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    
    <div class="modal" tabindex="-1" id="modal_lock">
        <div class="modal-dialog modal-lg">
            <div class="modal-content" style="position_relative; overflow:hidden">
                <form method="post" action="<?php echo base_url( "update_lock" ); ?>">
                    <?php echo csrf_field() ?>
                    <input type="hidden" name="socio"  value="<?php echo $socio->id; ?>">

                    <div class="modal-header bg-red">
                        <h5 class="modal-title text-white m-0"><i class="fa fa-lock"></i> Bloqueo de usuarios</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body text-red text-start"><div class="xsmall">
                            <p class=""><i class="fa fa-warning"></i> ATENCION:</p>
                            <p class="mb">Al apagar una red, el estatus del socio <?php echo $socio->id( false, "marine" ); ?> cambiará a BAJA MANUAL (gris) cancelando cualquier calificación o estatus activo en esa red y generando una compresión definitiva por lo que nunca podrá cobrar comisiones en esa red y perdiendo toda su estructura de socios en línea descendente. Si se apagan todas las redes, la cuenta se inactiva y el socio ya no podrá utilizar la oficina virtual.</p><p>El bloqueo podrá ser revertido para que se active y califique pero no recupera los socios que por compresión ahora pertenecen a su patrocinador.</p>
                        </div>
                        <h5 class="mt-2 mb-3">Permisos de uso de redes para el socio <?php echo $socio->id(); ?></h5>

                        <table class="w-100"><tr>
                            <?php 

                            foreach( MODELOS as $m ){
                                echo "\n
                                    <td width=\"20%\" class=\"text-center px-1\">
                                        <div class=\"card\"><div class=\" px-1 card-body text-center\">
                                            <div class=\"form-check form-switch text-center\" style=\"padding-left: 0; zoom: 1.5\">
                                            <input modelo=\"{$m[ "codigo" ]}\" name=\"modelos[{$m[ "codigo" ]}]\" value=\"1\" class=\"form-check-input bg-red\" type=\"checkbox\" role=\"switch\" id=\"switch_{$m[ "codigo" ]}\" style=\"clear: both; margin-left: auto; float:none\" ".( $socio->data->estatus->modelos->{$m[ "codigo" ]} == "110-ELIMINADO" ? "" : "checked" )."><label class=\"form-check-label\" for=\"switch_{$m[ "codigo" ]}\"></label></div>";

                                echo "\n
                                            <span class=\"badge bg-{$m[ "settings" ][ "color" ]}\"><i class=\"{$m[ "settings" ][ "icono" ]}\"></i> {$m[ "nombre" ]}</span></div></div>";

 
                                echo "\n<small>Califica permanente</small><br><select name=\"calificaciones[{$m[ "codigo" ]}]\" modelo=\"{$m[ "codigo" ]}\" class=\"select_permanentes small form-select form-select-sm\">";

                                foreach( CALIFICACIONES as $c ){
                                    if( $c[ "modelo_codigo" ] == $m[ "codigo" ] ){
                                        echo "\n<option value=\"{$c[ "codigo" ]}\" ".( ( $socio->data->permanentes->{$m[ "codigo" ]} ?? "" ) == $c[ "codigo" ] ? "selected" : "" ).">{$c[ "descripcion" ]}</option>";
                                    }
                                }

                                            
                                echo "\n</select>";


                                echo "\n        
                                    </td>
                                ";
                            }
                        ?></tr>
                        </table>
                    </div>

                    <div class="modal-footer text-center">
                        <button type="submit" class="btn btn-danger"><i class="fa fa-lock"></i> Aplicar cambios</button>
                    </div>
                </form>
            </div>
        </div>
    </div>


<?php if( $this->data[ "usuario" ]->permiso( "41-RED" ) ){ ?>
    <div class="modal" tabindex="-1" id="modal_cambia_patrocinador">
        <div class="modal-dialog modal-lg">
            <div class="modal-content" style="position_relative; overflow:hidden">

                <div class="modal-header bg-red">
                    <h5 class="modal-title text-white m-0"><i class="fa fa-warning"></i> Cambiar patrocinador</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form action="<?php echo base_url( "cambia_patrocinador" ); ?>" method="post">
                    <div class="modal-body text-red text-center">
                        <p><i class="fa fa-warning"></i> ATENCION:</p>
                        <p>Cambiar este dato afectará las redes del patrocinador anterior y el nuevo</p>
                        <div class="card mb-3" style="overflow:hidden">
                        <table class="table m-0" id="datos_patrocinador" original="<?php echo $socio->redes->patrocinador; ?>"><tr>
                        </tr></table>
                        </div>

                        <table><tr>
                            <?php 
                            foreach( MODELOS as $m ){
                                echo "\n<td class=\"px-3\"><input class=\"form-control text-center pat\" name=\"patrocinador[{$m[ "codigo" ]}]\" modelo=\"{$m[ "codigo" ]}\" value=\"".$socio->patrocinador( $m[ "codigo" ] )."\"></td>";
                            }
                            ?>
                        </tr></table>
                        
                    </div>

                    <div class="modal-footer text-center">
                        
                            <?php echo csrf_field() ?>
                            <input type="hidden" name="n_socio" value="<?php echo $socio->id; ?>">
                            <input type="hidden" name="n_patrocinador" value="">
                            <button class="btn btn-outline-danger" id="previsualizar">Previsualizar</button> <button type="submit" class="btn btn-danger" disabled id="aplicar_cambio"><i class="fa fa-warning"></i> Aplicar cambio de patrocinador</button>
                        
                    </div>
                </form>
            </div>
        </div>
    </div>


<?php 
    } 
}
?>
    
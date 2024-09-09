<link href="<?php echo base_url(); ?>assets/css/datatables.css" rel="stylesheet"/>
<script src="<?php echo base_url(); ?>assets/js/datatables.js" type="text/javascript"></script>
<script src="<?php echo base_url(); ?>assets/js/datatables_bs5.js" type="text/javascript"></script>

<h4 class="mt-1 mb-0"><?php echo $titulo; ?></h4>
<p><a href="<?php echo base_url( "admin" ); ?>"><i class="fa fa-undo"></i> Regresar a administración</a></p>

<div class="alert alert-info">
    <div class="row">
        <div class="col-md-4">
            <h5>1. Abrir fotografías</h5>
            <p class="mb-0">Click en los botones grises para ver la imagen detallada del frente y reverso de la credencial del socio.</p>
        </div>

        <div class="col-md-4">
            <h5>2. Verificar datos</h5>
            <p class="mb-0">Validar que el nombre, la CURP, el sexo y la fecha de nacimiento coincidan fielmente con los datos impresos en la credencial.</p>
        </div>

        <div class="col-md-4">
            <h5>3. Aceptar o rechazar</h5>
            <p class="mb-0">Si todo coincide, click en el botón verde para aceptar el documento. Si hay algun error o la imagen no es legible, rechazar con el botón rojo.</p>
        </div>
    </div>    

</div>

<table class="table table-striped bg-white" id="tabla_credenciales">
    <thead>
        <tr>
            <th>Socio</th>
            <th>Nombre</th>
            <th>CURP</th>
            <th>Sexo</th>
            <th>Fecha de nacimiento</th>
            <th>Documentos</th>
            <th></th>
        </tr>
    </thead>

    <tbody>
        <?php 
            foreach( $socios as $socio ){
                if( !is_dir( "data/{$socio->id}/ine" ) ) mkdir( "data/{$socio->id}/ine", 0755, true);

                if( $socio->es_menor() ){
                    if( 
                        !file_exists( "data/{$socio->id}/ine/{$socio->data->credencial->acta}" ) 
                    ){
                        $data  = $socio->data;

                        $files = scandir( "data/{$socio->id}/ine" );
                        foreach( $files as $file ){
                            if( strpos( $file, "acta"  ) !== false ){ $data->credencial->acta =  $file; }
                        }

                        $socio->data = $data;
                        model( "UsuarioModel" )->save( $socio );
                    }
                }
                else{

                if( 
                    !file_exists( "data/{$socio->id}/ine/{$socio->data->credencial->frente}" ) ||
                    !file_exists( "data/{$socio->id}/ine/{$socio->data->credencial->reverso}" ) 
                ){
                    $data  = $socio->data;

                    $files = scandir( "data/{$socio->id}/ine" );
                    foreach( $files as $file ){
                        if( strpos( $file, "frente"  ) !== false ){ $data->credencial->frente =  $file; }
                        if( strpos( $file, "reverso" ) !== false ){ $data->credencial->reverso = $file; }
                    }

                    $socio->data = $data;
                    model( "UsuarioModel" )->save( $socio );
                }
            }
                if( 
                   ( !$socio->es_menor() &&
                    file_exists( "data/{$socio->id}/ine/{$socio->data->credencial->frente}" ) &&
                    file_exists( "data/{$socio->id}/ine/{$socio->data->credencial->reverso}" ) 
                    )
                    ||
                    ( $socio->es_menor() &&
                    file_exists( "data/{$socio->id}/ine/{$socio->data->credencial->acta}" ) 
                    )
                ){            
                    echo "\n<tr socio=\"{$socio->id}\">
                        <td>{$socio->id()}</td>
                        <td>{$socio->nombre(2)}</td>
                        <td>{$socio->curp}</td>
                        <td>{$socio->data->genero}</td>
                        <td>{$socio->fechanac}</td>
                        <td><div class=\"row\"><div class=\"col-6\">".( $socio->es_menor() && ($socio->data->credencial->acta ?? $socio->data->credencial->acta = null ) ? "<a href=\"".base_url()."data/{$socio->id}/ine/{$socio->data->credencial->acta}\" target=\"_blank\" class=\"btn btn-xs btn-outline-secondary col-12\">ACTA</a>" : "<a href=\"".base_url()."data/{$socio->id}/ine/{$socio->data->credencial->frente}\" target=\"_blank\" class=\"btn btn-xs btn-outline-secondary col-12\">FRENTE</a></div><div class=\"col-6\"><a href=\"".base_url()."data/{$socio->id}/ine/{$socio->data->credencial->reverso}\" target=\"_blank\" class=\"btn btn-xs btn-outline-secondary col-12\">REVERSO</a>" )."</div></div></td>
                        <td class=\"text-end\">
                            <button onclick=\"aprueba({$socio->id})\" class=\"btn btn-xs btn-primary xaprueba\">APROBAR</button> 
                            <button onclick=\"rechaza({$socio->id})\" class=\"btn btn-xs btn-danger xrechaza\">RECHAZAR</button>
                        </td>
                    </tr>";
                }
            }
        ?>
     
    </tbody>
</table>

<div class="modal" tabindex="-1" id="modal_aceptar">
	<div class="modal-dialog">
		<div class="modal-content">
            <form method="post" action="<?php echo base_url( "resolucion_ine" ); ?>">
                <?php echo csrf_field() ?>
                <input type="hidden" name="accion" value="acepta">
                <input type="hidden" name="socio"  value="">
                <input type="hidden" name="motivo" value="">


                <div class="modal-header">
                    <h5 class="modal-title">Aceptar documento</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p>Al ACEPTAR el documento , se estará confirmando la identidad del socio, permitiendole participar en todas las actividades de la empresa.</p>
                    <p>Nombre de quien acepta:<br><strong><?php echo $usuario->nombre(2); ?></strong></p>
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-primary">Entendido, continuar</button>
                </div>
            </form>
		</div>
	</div>
</div>

<div class="modal" tabindex="-1" id="modal_rechazar">
	<div class="modal-dialog">
		<div class="modal-content">
            <form method="post" action="<?php echo base_url( "resolucion_ine" ); ?>">
                <?php echo csrf_field() ?>
                <input type="hidden" name="accion" value="rechaza">
                <input type="hidden" name="socio"  value="">

                <div class="modal-header">
                    <h5 class="modal-title">Rechazar documento</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p>Al RECHAZAR el documento, se solicitará al socio que vuelva a enviarlo de nuevo, su participación en las actividades de la empresa seguirá limitada.</p>
                    <p class="mb-0">Por favor introduzca el motivo del rechazo</p>
                    <textarea class="form-control mb-3" name="motivo"></textarea>
                    <p>Nombre de quien rechaza:<br><strong><?php echo $usuario->nombre(2); ?></strong></p>
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-danger">Entendido, continuar</button>
                </div>    
            </form>         
		</div>
	</div>
</div>
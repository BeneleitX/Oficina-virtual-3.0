<link href="<?php echo base_url(); ?>assets/css/datatables.css" rel="stylesheet"/>
<script src="<?php echo base_url(); ?>assets/js/datatables.js" type="text/javascript"></script>
<script src="<?php echo base_url(); ?>assets/js/datatables_bs5.js" type="text/javascript"></script>

<h4 class="mt-1 mb-0"><?php echo $titulo; ?></h4>
<p><a class="btn btn-light btn-sm mb-4" href="<?php echo base_url( "pines/10-NUTRICION" ); ?>"><i class="fa fa-undo"></i> Regresar a Rangos</a></p>


<table class="table table-striped bg-white" id="tabla_rangos">
    <thead>
        <tr>
            <th>Id de pin</th>
            <th>Socio</th>
            <th>Nombre</th>
            <th>Alcanzado</th>
            <th>Entregado</th>
            <th></th>
            <th>Estatus</th>
            <th></th>
        </tr>
    </thead>

    <tbody>
        <?php 
            foreach( $pines->getResult() as $pin ){

                $u = model( "UsuarioModel" )->where( "id", $pin->usuario_id )->first();

                if( !in_array( "42-PERMANENTE", $u->rol_codigos ) ){

                    $estatus = ESTATUS[ $pin->estatus_codigo ]; 
                    $color = $estatus[ "color" ];

                    echo "\n<tr pin=\"{$pin->id}\" estatus_codigo=\"{$pin->estatus_codigo}\" usuario_id=\"{$pin->usuario_id}\" fecha=\"{$pin->fecha}\" entrega_fecha=\"{$pin->entrega_fecha}\" entrega_lugar=\"{$pin->entrega_lugar}\" comentarios=\"{$pin->comentarios}\">
                        <td class=\"text-end\"><span class=\"badge bg-marine\">{$pin->id}</span></td>
                        <td>".$u->id( "10-NUTRICION" )."</td>
                        <td>".$u->avatar(24)." ".$u->nombre( 2 )."</td>
                        <td><span class=\"d-none\">{$pin->fecha}</span>".fecha( $pin->fecha )."</td>
                        <td><span class=\"d-none\">{$pin->entrega_fecha}</span>".( $pin->entrega_fecha != "0000-00-00" ? fecha( $pin->entrega_fecha ) : "" )."</td>
                        <td>".( $pin->entrega_lugar ? "<span class=\"badge rounded-pill bg-white text-{$color} border border-{$color}\">{$pin->entrega_lugar}}</span>" : "" )."</td>
                        <td>".estatus( $estatus[ "codigo" ] )."</td>
                        <td class=\"text-end\">".( strlen( $pin->comentarios ) ? "<span class=\"fs-5 \" style=\"display:inline-block; line-height: 0;\" data-bs-toggle=\"tooltip\" data-bs-title=\"{$pin->comentarios}\"><i class=\"fa fa-note-sticky text-mustard\"></i> <span class=\"d-none\">{$pin->comentarios}</span></span>" : "" )."
                            <button onclick=\"actualiza_pin( {$pin->id} )\" class=\"btn btn-secondary btn-sm\"><i class=\"fa fa-refresh\"></i> Actualizar</button
                        </td>
                    </tr>";
                }
            }
        ?>
     
    </tbody>
</table>


<div class="modal" tabindex="-1" id="actualiza_pin">
	<div class="modal-dialog">
		<div class="modal-content">
			<form method="post" action="<?php echo base_url( "update_pin" ); ?>">
                <?php echo csrf_field() ?>
                <input type="hidden" name="pin" value="">

				<div class="modal-header bg-marine">
					<h5 class="modal-title m-0 text-white"><i class="fa fa-refresh"></i> Actualizar pin</h5>
					<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
				</div>
				<div class="modal-body">
                    <div class="row mb-2">
                        <div class="col-5"><label for="entrega_fecha">Estatus</label></div>
                        <div class="col-7"><select class="form-select" name="estatus_codigo">
                            <?php
                            $estatuses = [ "225-ALCANZADO", "623-ENTREGA", "150-CANCELADO" ];

                            foreach( $estatuses as $e ){
                                echo "\n<option value=\"{$e}\">".estatus( $e, false, false )."</option>";
                            }
                            ?>
                            
                        </select></div>
                    </div>

                    <div class="row mb-2">
                        <div class="col-5"><label for="entrega_fecha">Fecha de entrega</label></div>
                        <div class="col-7"><input type="date" class="form-control" name="entrega_fecha"></div>
                    </div>

                    <div class="row mb-2">
                        <div class="col-5"><label for="entrega_fecha">Lugar de entrega</label></div>
                        <div class="col-7"><select class="form-select" name="entrega_lugar">
                            <?php

                            foreach( VARIABLES[ "entrega_pines" ][ "valor" ] as $e ){
                                echo "\n<option value=\"{$e}\">{$e}</option>";
                            }
                            ?>
                            
                        </select></div>
                    </div>

                    <div class="row">
                        <div class="col-5"><label for="entrega_fecha">Comentarios</label></div>
                        <div class="col-7"><textarea class="form-control" name="comentarios"></textarea></div>
                    </div>                    
				</div>
				<div class="modal-footer">
					<button type="submit" class="btn btn-secondary"><i class="fa fa-save"></i> Guardar cambios</button>
				</div>
			</form>
		</div>
	</div>
</div>



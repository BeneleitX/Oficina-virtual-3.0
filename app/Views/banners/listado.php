<h4 class="mt-1 mb-3"><?php echo $titulo; ?></h4>

<div class="row mb-4">
    <div class="col-6">
        <p><a href="<?php echo base_url( "admin" ); ?>"><i class="fa fa-undo"></i> Regresar a administración</a></p>
    </div>
    <div class="col-6 text-end">
        <button class="btn btn-warning" id="nuevo_banner"><i class="fa fa-file-upload"></i> Nuevo banner</button>
    </div>
</div>


<table class="table table-display table-striped">
    <thead>
        <tr>
            <th>Orden</th>
            <th>Imagen</th>
            <th>Descripción</th>
            <th>Estatus</th>
            <th>Fecha de inicio</th>
            <th>Fecha de vigencia</th>
            <th class="text-end">Acciones</th>
        </tr>
    </thead>
    <tbody>
    <?php
    foreach( $banners as $b ){

        // si ya inició
        if( $b[ "inicia" ] < date( "Y-m-d" ) ){
            // si está vigente
            if( $b[ "vigencia" ] > date( "Y-m-d" ) ){
                $estatus = "202-VIGENTE";                
            }
            else{
                $estatus = "124-VENCIDO";
            }
        }
        else{
            $estatus = "330-EN-ESPERA";
        }

        $botones = "";

        if( sizeof( $banners ) > 1 ){
            $botones  = " <a".( $b[ "posicion" ] == sizeof( $banners ) ? " style=\"opacity:0\" class=\"disabled " : " class=\" " )." btn btn-sm btn-success\" href=\"".base_url( "mueve_banner/abajo/".$b[ "id" ] )."\"><i class=\"fa fa-chevron-down\"></i></a>";
            $botones .= " <a".( $b[ "posicion" ] == 1 ? " style=\"opacity:0\" class=\"disabled " : " class=\" " )." btn btn-sm btn-success\" href=\"".base_url( "mueve_banner/arriba/".$b[ "id" ] )."\"><i class=\"fa fa-chevron-up\"></i></a>";
        }

        $botones .= " <button class=\"btn btn-sm btn-secondary lanza_modal\"><i class=\"xtext-marine fa fa-gear\"></i></a>";

        echo "\n<tr banner=\"{$b[ "id" ]}\" inicia=\"{$b[ "inicia" ]}\" vigencia=\"{$b[ "vigencia" ]}\">
                    <td class=\"text-center\"><span class=\"badge bg-marine\">{$b[ "posicion" ]}</span></td>
                    <td><img class=\"border border-4 border-gray-400 rounded\" width=\"100\" src=\"".base_url()."assets/img/banners/{$b[ "archivo" ]}\"></td>
                    <td>{$b[ "descripcion" ]}</td>
                    <td nowrap class=\"pe-5\">".estatus( $estatus )."</td>
                    <td nowrap class=\"pe-5\"><i class=\"far fa-calendar text-light-blue\"></i><i class=\"fa fa-caret-right text-pink\"></i> ".date( "d-m-Y", strtotime( $b[ "inicia" ] ) )."</td>
                    <td nowrap class=\"pe-5\"><i class=\"fa fa-caret-left text-pink\"></i><i class=\"far fa-calendar text-light-blue\"></i> ".date( "d-m-Y", strtotime( $b[ "vigencia" ] ) )."</td>
                    <td nowrap class=\"text-end\">{$botones}</td>
                </tr>";
    }
    ?>
    </tbody>
</table>



<div class="modal" tabindex="-1" id="edita_banner">
	<div class="modal-dialog modal-xl">
		<div class="modal-content">
            <form method="post" action="<?php echo base_url( "save_banner" ); ?>" id="form_banner">
                <?php echo csrf_field() ?>
                <input type="hidden" name="banner_id" value="">
                <input type="hidden" name="banner_archivo" value="">

                <div class="modal-header bg-marine">
                    <h5 class="modal-title text-white"><i class="fa fa-gear"></i> Banner</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                
                <div class="row">
                    <div class="col-lg-6">
                        <img id="preview" src="" gato="<?php echo base_url()."assets/img/gatos/cat7.png"; ?>" class="img-fluid rounded-3 rounded-top-0 rounded-end-0" onclick="$( 'input[name=banner_imagen]' ).click()">
                    </div>
                    <div class="col-lg-6 mt-3">
                        <div class="modal-body">
                            <input type="file" name="banner_imagen" class="d-none upload">

                            <div class="row mb-3">
                                <label class="col-sm-4 col-form-label">Descripción</label>
                                <div class="col-sm-8">
                                    <textarea name="banner_descripcion" class="form-control" rows="3"></textarea>
                                </div>
                            </div>
                            <div class="row mb-3">
                                <label class="col-sm-4 col-form-label">Fecha de inicio</label>
                                <div class="col-sm-4">
                                    <input type="date" name="banner_fecha_inicia" class="form-control">
                                </div>
                            </div>
                            <div class="row mb-3">
                                <label class="col-sm-4 col-form-label">Fecha de vigencia</label>
                                <div class="col-sm-4">
                                    <input type="date" name="banner_fecha_vigencia" class="form-control">
                                </div>
                            </div>

                            <div class="alert alert-info m-0">
                                Las fechas determinan el estatus. La fecha de inicio siempre debe ser menor a la fecha de vigencia.
                            </div>
                            <p class="mb-0 mt-3 text-end"><button disabled type="submit" id="banner_submit" class="btn btn-secondary">Guardar</button></p>
                        </div>
                    </div>

                </div>
            </form>
		</div>
	</div>
</div>
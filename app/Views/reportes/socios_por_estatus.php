<link href="<?php echo base_url(); ?>assets/css/datatables.css" rel="stylesheet"/>
<script src="<?php echo base_url(); ?>assets/js/datatables.js" type="text/javascript"></script>
<script src="<?php echo base_url(); ?>assets/js/datatables_bs5.js" type="text/javascript"></script>

<h4 class="mt-1 mb-0"><?php echo $titulo; ?></h4>
<p><a class="btn btn-sm btn-light" href="<?php echo base_url( "reportes" ); ?>"><i class="fa fa-undo"></i> Regresar a reportes</a></p>

<div class="row">
    <div class="col-lg-6">

    <div class="row mb-3">
        <label class="col-sm-4 col-form-label">Modelo de negocio</label>
        <div class="col-sm-8">
            <select class="form-select" name="d_modelo">
                <option value="">...</option>

                <?php
                foreach( MODELOS as $m ){
                    echo "\n<option value=\"{$m[ "codigo" ]}\">{$m[ "nombre" ]}</option>";
                }
                ?>
            </select>
        </div>
    </div>

    <fieldset class="row mb-3">
        <legend class="col-form-label col-sm-4">Estatus</legend>
        <div class="col-sm-8">
            <?php 
            foreach( $estatuses as $e => $modelos ){

                echo "\n<div class=\"form-check\" modelos=\" ".implode( " ", $modelos )." \">
                    <input class=\"form-check-input\" type=\"radio\" name=\"d_estatus\" id=\"{$e}\" value=\"{$e}\">
                    <label class=\"form-check-label\" for=\"{$e}\"><span class=\"badge bg-".ESTATUS[ $e ][ "color" ]."\">&nbsp;</span> ".ESTATUS[ $e ][ "descripcion" ]."</label>
                </div>";
            }
            ?>
        </div>
    </fieldset>

    <button type="button" id="submit_button" class="btn btn-primary" disabled><i class="fa fa-circle-down"></i> Descargar Excel</button>

</div></div>
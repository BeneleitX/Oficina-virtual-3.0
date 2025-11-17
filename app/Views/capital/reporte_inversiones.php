<h4 class="mt-1 mb-0"><?php echo $titulo; ?></h4>
<p><a class="btn btn-sm btn-light" href="<?php echo base_url( "reportes" ); ?>"><i class="fa fa-undo"></i> Regresar a reportes</a></p>

<div class="row mt-5">
    <div class="col-lg-6">

    <div class="row mb-3">
        <legend class="col-form-label col-sm-4">Fecha de depósito inicial</legend>
        <div class="col-sm-8">
            <input class="form-control" style="display:inline; width: auto" type="date" value="<?php echo date( "Y-m-d" ); ?>" name="f_inicia"> 
        </div>
    </div>
    <div class="row mb-3">
        <legend class="col-form-label col-sm-4">Fecha de depósito final</legend>
        <div class="col-sm-8">
            <input class="form-control" style="display:inline; width: auto" type="date" value="<?php echo date( "Y-m-d" ); ?>" name="f_final"> 
        </div>
    </div>

    <div class="row mb-3">
        <legend class="col-form-label col-sm-4">Tipo de inversión</legend>
        <div class="col-sm-8">
            <select class="sel form-select" name="d_tipoinversion">
                <option value="TODOS" selected>TODOS</option>
                <?php

                foreach( PRODUCTOS as $promo ){
                    echo "\n<option value=\"{$promo["data"][ "porcentaje"]}\">".mb_strtoupper( $promo["data"][ "nombre" ] )."</option>";
                }
                ?>
            </select>
        </div>
    </div>



    <button type="button" id="submit_button" class="btn btn-primary"><i class="fa fa-circle-down"></i> Descargar Excel</button>

</div></div>
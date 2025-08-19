<h4 class="mt-1 mb-0"><?php echo $titulo; ?></h4>
<p><a class="btn btn-sm btn-light" href="<?php echo base_url( "reportes" ); ?>"><i class="fa fa-undo"></i> Regresar a reportes</a></p>

<div class="row mt-5">
    <div class="col-lg-6">

    <div class="row mb-3">
        <label class="col-sm-4 col-form-label">Modelo de negocio</label>
        <div class="col-sm-8">
            <select class="form-select" name="d_modelo">
                <option value="">...</option>

                <?php
                foreach( MODELOS as $m ){
                    echo "\n<option value=\"{$m[ "codigo" ]}\">".mb_strtoupper( $m[ "nombre" ] )."</option>";
                }
                ?>
            </select>
        </div>
    </div>

    <div class="row mb-3">
        <legend class="col-form-label col-sm-4">Fecha de venta inicial</legend>
        <div class="col-sm-8">
            <input class="form-control" style="display:inline; width: auto" type="date" value="<?php echo date( "Y-m-d" ); ?>" name="f_inicia"> 
        </div>
    </div>
    <div class="row mb-3">
        <legend class="col-form-label col-sm-4">Fecha de venta final</legend>
        <div class="col-sm-8">
            <input class="form-control" style="display:inline; width: auto" type="date" value="<?php echo date( "Y-m-d" ); ?>" name="f_final"> 
        </div>
    </div>

    <div class="row mb-3">
        <legend class="col-form-label col-sm-4">Estatus</legend>
        <div class="col-sm-8">
            <select class="form-select" name="d_estatus">
                <option value="TODOS" selected>TODOS</option>
                <option value="400">SOLO LOS QUE ESTAN PENDIENTES DE ENVIO/ENTREGA</option>
                <option value="500">SOLO LOS YA ENVIADOS/ENTREGADOS</option>
            </select>
        </div>
    </div>

    <div class="row mb-3">
        <legend class="col-form-label col-sm-4">Método de entrega</legend>
        <div class="col-sm-8">
            <select class="form-select" name="d_metodosentrega">
                <option value="TODOS">TODOS</option>
                <?php
                foreach( $metodosentrega as $mod => $dat ){
                    foreach( $dat as $met ){                    
                        echo "\n<option style=\"display:none\" modelo=\"{$mod}\" value=\"{$met}\">".substr( $met, 3 )."</option>";
                    }
                }
                ?>
            </select>
        </div>
    </div>

    <?php /*
    <div class="row mb-3">
        <legend class="col-form-label col-sm-4">Es primer compra</legend>
        <div class="col-sm-8">
            <select class="form-select" name="c_primercompra" >
                <option value="TODOS" selected>TODOS</option>
                <option value="1">SI, SOLO LOS QUE SON PRIMER COMPRA</option>
                <option value="0">NO, SOLO LOS QUE SON RECOMPRAS</option>
            </select>
        </div>
    </div>

    */ ?>


    <button type="button" id="submit_button" class="btn btn-primary" disabled><i class="fa fa-circle-down"></i> Descargar Excel</button>

</div></div>
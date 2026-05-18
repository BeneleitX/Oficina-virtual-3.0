<link href="<?php echo base_url(); ?>assets/css/datatables.css" rel="stylesheet"/>
<script src="<?php echo base_url(); ?>assets/js/datatables.js" type="text/javascript"></script>
<script src="<?php echo base_url(); ?>assets/js/datatables_bs5.js" type="text/javascript"></script>

<h4 class="mt-1 mb-0"><?php echo $titulo; ?></h4>
<p><a class="btn btn-sm btn-light" href="<?php echo base_url( "reportes" ); ?>"><i class="fa fa-undo"></i> Regresar a reportes</a></p>

<div class="row mt-5">
    <div class="col-lg-6">

        <div class="row mb-3">
            <label class="col-sm-4 col-form-label">Empresa</label>
            <div class="col-sm-8">
                <select class="form-select" name="d_modelo">

                    <?php
                    foreach( MODELOS as $m ){
                        if( $m[ "codigo" ] == "10-NUTRICION" ){
                            echo "\n<option value=\"{$m[ "codigo" ]}\" selected>".mb_strtoupper( $m[ "nombre" ] )."</option>";
                        } 
                    }
                    ?>
                </select>
            </div>
        </div>

        <div class="row mb-3">
            <legend class="col-form-label col-sm-4">Fecha de venta inicial</legend>
            <div class="col-sm-8">
                <input class="form-control" style="display:inline; width: auto" type="date" value="<?php echo date( "Y-m-d" ); ?>" name="d_inicia"> 
            </div>
        </div>
        <div class="row mb-3">
            <legend class="col-form-label col-sm-4">Fecha de venta final</legend>
            <div class="col-sm-8">
                <input class="form-control" style="display:inline; width: auto" type="date" value="<?php echo date( "Y-m-d" ); ?>" name="d_termina"> 
            </div>
        </div>

        <div class="row mb-3">
            <legend class="col-form-label col-sm-4">Almacen</legend>
            <div class="col-sm-8">
                <div class="alert alert-warning py-2 mb-0">Elige una empresa</div>
                <select class="sel d-none form-select" name="d_almacen">
                    <option value="TODOS" selected>TODOS</option>
                    <?php
                    foreach( ALMACENES as $alm ){
                        echo "\n<option modelo=\"{$alm[ "modelo_codigo" ]}\" value=\"{$alm[ "codigo" ]}\">{$alm[ "nombre" ]}</option>";
                    }
                    ?>
                </select>
            </div>
        </div>

        <div class="row mb-3">
            <legend class="col-form-label col-sm-4">Tipo de venta</legend>
            <div class="col-sm-8">
                <div class="alert alert-warning py-2 mb-0">Elige una empresa</div>
                <select class="sel d-none form-select" name="d_filtro">
                    <option value="0" selected>TODOS</option>
                    <option value="1">Solo DISTRIBUIDOR y PROMOS 50%</option>
                </select>
            </div>
        </div>
        

        <div class="row mb-3">
            <button type="button" id="submit_button" class="btn btn-secondary" disabled><i class="fa fa-redo"></i> Actualizar datos</button>
            <button type="button" id="download_button" class="d-none btn btn-primary" disabled><i class="fa fa-circle-down"></i> Descargar Excel</button>
        </div>
    
        <div class="xcard mt-4">
        <table id="tabla_datos" class="table table-bordered table-striped m-0 bg-white">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Producto</th>
                    <th>Cantidad</th>
                </tr>
            </thead>
            <tbody>
            </tbody>
        </table>
        </div>

</div></div>


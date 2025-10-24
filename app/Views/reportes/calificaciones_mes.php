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
                <option value="">...</option>

                <?php
                foreach( MODELOS as $m ){
                    if( !$usuario->permiso( "43-CONSULTA", true ) || $m[ "codigo" ] == "10-NUTRICION" ){
                        echo "\n<option value=\"{$m[ "codigo" ]}\">".mb_strtoupper( $m[ "nombre" ] )."</option>";
                    }
                }
                ?>
            </select>
        </div>
    </div>

    <div class="row mb-3">
        <legend class="col-form-label col-sm-4">Mes</legend>
        <div class="col-sm-8">
            <input class="form-control" style="display:inline; width: auto" type="month" value="<?php echo date( "Y-m" ); ?>" name="f_mes"> 
        </div>
    </div>


    <div class="row mb-3">
        <legend class="col-form-label col-sm-4">Socios nuevos</legend>
        
        <div class="col-sm-8">
            <select class="form-select" name="c_primercompra" >
                <option value="TODOS" selected>TODOS</option>
                <option value="1">SI, SOLO LOS QUE ESTAN EN SU PRIMER MES</option>
                <option value="0">NO, SOLO LOS QUE VIENEN ACTIVOS DE MESES ANTERIORES</option>
            </select>
        </div>
    </div>

    <div class="row mb-3">
        <legend class="col-form-label col-sm-4">Calificaciones</legend>
        <div class="col-sm-8">
            <div class="alert alert-warning py-2 mb-0">Elige una empresa</div>    
            <div class="row d-none" id="lista_calificaciones">
            <?php
            foreach( CALIFICACIONES as $c ){
                if(substr( $c[ "estatus_codigo" ], 0, 3 ) > 200 )
                echo "\n
                    <div class=\"col-md-6 form-check form-switch calificaciones\" modelo=\"{$c[ "modelo_codigo" ]}\">
                        <input class=\"form-check-input\" type=\"checkbox\"  modelo=\"{$c[ "modelo_codigo" ]}\" role=\"switch\" id=\"switch_{$c[ "codigo" ]}\" name=\"d_promociones[{$c[ "codigo" ]}]\" value=\"{$c[ "codigo" ]}\" checked>
                        <label class=\"form-check-label\" for=\"switch_{$c[ "codigo" ]}\">{$c[ "descripcion" ]}</label>
                    </div>";
            }
            ?>
        </div></div>
    </div>

    <button type="button" id="reload_button" class="btn btn-secondary" disabled><i class="fa fa-refresh"></i> Actualizar datos</button>
    <button type="button" id="submit_button" class="d-none btn btn-primary" disabled><i class="fa fa-circle-down"></i> Descargar Excel</button>

</div></div>
<div id="data" class="my-5"></div>
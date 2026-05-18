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
        

        <div class="row mb-3 d-none">
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

<?php /*

nunca entenderé la obsesión de algunos maestros en querer reprobar alumnos... transferir al alumno la carga del fracaso de sus padres, de la precariedad en la que viven o del mismo maestro que no logra aplicar las técnicas correctas de aprendizaje para el o canalizarlo a la ayuda necesaria... porque si un alumno no aprende, es culpa de todo su entorno, reprobar es lo más fácil: culparlo a el, que no es "disciplinado", que es "flojo", que "no se esfuerza lo suficiente" y todas las etiquetas clasistas que se les ocurran... del fracaso de todo el sistema que lo rodea


es correcto, en la mayoría de los casos no hay ayuda especializada, me estas dando la razón entonces, en que el sistema ahí está fallando y no puedes hacer que el alumno cargue con la culpa de eso... reprobarlo es la salida más fácil y la más injusta, es el fracaso del sistema transferido al alumno

 todos somos parte del sistema, el creer que las fallas del sistema son ajenas, es un error... si un sindicato nacional puede hacer marchas y huelgas para defender prestaciones, tambien podría hacerlo para que ese niño tenga ayuda especializada... es llevar la famosa "defensa de la educación" a la práctica... y no solo cuando se trata de pedir beneficios al docente.. y obviamente los padres tambien tenemos nuestra responsabilidad ahí, normalmente nos preocupamos cuando nuestro hijo es el afectado, cuando no... ni nos importa, tristemente

  y tienes razón, yo tambien pienso eso, culpar a los maestros es injusto... pero entonces culpar al alumno y "reprobarlo", tambien es injusto... sin embargo hay maestros, como la doña de la publicación que compartes, que justifica hacerlo... y hasta dice que hay que reprobarlos porque "son indisciplinados y no se esfuerzan" lo cual es un prejuicio terrible...


  */ ?>
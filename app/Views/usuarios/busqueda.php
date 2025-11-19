<link href="<?php echo base_url(); ?>assets/css/datatables.css" rel="stylesheet"/>
<script src="<?php echo base_url(); ?>assets/js/datatables.js" type="text/javascript"></script>
<script src="<?php echo base_url(); ?>assets/js/datatables_bs5.js" type="text/javascript"></script>

<img style="position:absolute; right:20px; top:30px; width:120px" src="<?php echo base_url(); ?>assets/img/logo_color.png">
<h4 class="mt-1"><?php echo $titulo; ?></h4>
<p><a href="<?php echo base_url( "admin" ); ?>"><i class="fa fa-undo"></i> Regresar a administración</a></p>


<div class="alert alert-info mb-4">
    <div class="row">
        <div class="col-lg-4">
            <ul  class="mb-0">
                <li>Escribe el dato a buscar en la información del socio</li>
                <li>Usar palabras de mínimo 3 caracteres</li>
                <li>El sistema buscará automáticamente en números de socio, nombres y apellidos, teléfono, correo electrónico, CLABE interbancaria o CURP</li>
            </ul>
        </div>
        <div class="col-lg-4">
            <form action="<?php echo base_url( "usuarios" ); ?>" method="post">
                <?php echo csrf_field(); ?>
                <div class="input-group">
                    <span class="input-group-text bg-marine" id="basic-addon1"><i class="fa fa-magnifying-glass fs-3 px-2"></i></span>
                    <input type="text" class="form-control fs-3 px-3" placeholder="Buscar" name="query" value="<?php echo $query; ?>">
                </div>
            </form>
        </div>
        <?php
            if( isset( $socios ) ){
                echo "<div class=\"col-lg-4 text-end\"><span class=\"fs-4\">Resultados encontrados: <span class=\"badge bg-".( sizeof( $socios ) ? "teal" : "red" )."\">".sizeof( $socios )."</span></span></div>";
            }
        ?>
    </div>
</div>

<ul class="nav nav-tabs">
    <li class="nav-item">
        <a class="nav-link <?php echo $socios ? "": "active"; ?>" aria-current="page" href="<?php echo base_url( "usuarios" ); ?>">Historial de consultas</a>
    </li>
    <li class="nav-item">
        <a class="nav-link  <?php echo $socios ? "active": "disabled"; ?>" aria-disabled="true">Resultados de búsqueda</a>
    </li>
</ul>

<div class="tab-content bg-white p-3" id="myTabContent">
    <table class="table table-display table-striped" id="tabla_historial">
        <thead>
            <tr>
                <td>Número</td>
                <td>Socio</td>
                <td>Teléfono</td>
                <td>Correo electrónico</td>
                <td>CLABE interbancaria</td>
                <td>CURP</td>
                <td></td>
            </tr>
        </thead>

        <tbody>
            <?php 
            if( isset( $socios ) ){
                foreach( $socios as $h ){
                    echo "\n<tr>
                                <td>".marca( $queries, $h->id() )."</td>
                                <td>".$h->avatar( 24 )." ".marca( $queries, $h->nombre( 2 ), "upper" )."</td>
                                <td>".marca( $queries, $h->telefono )."</td>
                                <td>".marca( $queries, $h->correo, "lower" )."</td>
                                <td>".marca( $queries, $h->data->clabe, "upper" )."</td>
                                <td>".marca( $queries, $h->curp, "upper" )."</td>
                                <td class=\"text-end\"><a href=\"".base_url( "sociodata/".urlencode( base64_encode( $h->password_original() ) ) )."\" class=\"btn btn-sm btn-success\">Detalles</a></td>
                            </tr>";
                }                
            }
            else{
                $queries = [];
                
                foreach( $bitacoras as $b ){
                    $h = $historial[ $b[ "s" ] ];
                    echo "\n<tr>
                                <td>".marca( $queries, $h->id() )."</td>
                                <td>".$h->avatar( 24 )." ".marca( $queries, $h->nombre( 2 ), "upper" )."</td>
                                <td>".marca( $queries, $h->telefono )."</td>
                                <td>".marca( $queries, $h->correo, "lower" )."</td>
                                <td>".marca( $queries, $h->data->clabe, "upper" )."</td>
                                <td>".marca( $queries, $h->curp, "upper" )."</td>
                                <td class=\"text-end\"><a href=\"".base_url( "sociodata/".urlencode( base64_encode( $h->password_original() ) ) )."\" class=\"btn btn-sm btn-success\">Detalles</a></td>
                            </tr>";
                }
            }
            ?>
        </tbody>
    </table>
</div>
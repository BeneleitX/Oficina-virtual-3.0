<link href="<?php echo base_url(); ?>assets/css/datatables.css" rel="stylesheet"/>
<script src="<?php echo base_url(); ?>assets/js/datatables.js" type="text/javascript"></script>
<script src="<?php echo base_url(); ?>assets/js/datatables_bs5.js" type="text/javascript"></script>

<h4 class="mt-1 mb-0"><?php echo $titulo; ?></h4>
<p><a href="<?php echo base_url( "almacenes/".$modelo ); ?>"><i class="fa fa-undo"></i> Regresar a almacenes</a></p>


<div class="row">
    <div class="col-lg-6">
        <div class="card mb-4">
            <div class="card-header"><h5 class="m-0">Datos de transferencia</h5></div>
            <div class="card-body pb-0">
                <div class="row">
                    <div class="col-6 mb-3">
                        Almacen origen      
                    </div>
                    <div class="col-6 mb-3">
                        <select name="" class="form-select">
                        <?php 
                        foreach( ALMACENES as $a ){
                            echo "\n<option value=\"{$a[ "nombre" ]}\">{$a[ "nombre" ]}</option>";
                        }
                        ?>
                        </select>
                    </div>
                    <div class="col-6 mb-3">
                        Almacen destino      
                    </div>
                    <div class="col-6 mb-3">
                        <select name="" class="form-select">
                            <option value="">...</option>
                        <?php 
                        foreach( ALMACENES as $a ){
                            echo "\n<option value=\"{$a[ "nombre" ]}\">{$a[ "nombre" ]}</option>";
                        }
                        ?>
                        </select>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>





<div class="card mb-4">
    <div class="card-header"><h5 class="m-0">Productos a transferir</h5></div>
    <div class="card-body">
        <div class="row" id="inventario">
            <?php  
                foreach( PRODUCTOS as $p ){
                    if( !isset( $almacen[ "productos" ][ $p[ "codigo" ] ] ) ){
                        $almacen[ "productos" ][ $p[ "codigo" ] ]  = 0;
                    } 
                    
                    $e = intval( $almacen[ "productos" ][ $p[ "codigo" ] ]);
                    $d = intval( $almacen[ "productos" ][ $p[ "codigo" ] ]);

                    $avatar = file_exists( "assets/img/productos/{$p[ "codigo" ]}.png" );

                    $tabla = "<img class='img150 img-fluid p-2' src='".base_url()."assets/img/productos/".( $avatar ? $p[ "codigo" ] : "NO-IMAGEN" ).".png'><p class='m-0'><span class='d-inline-block w-60'>Existencia</span><span class='d-inline-block w-40 text-end'>".number_format( $e )."</span></p><div class='d-none'><p class='m-0'><span class='d-inline-block w-60'>Disponible</span><span class='d-inline-block w-40 text-end'>".number_format( $d )."</span></p><p class='m-0'><span class='d-inline-block w-60'>Por entregar</span><span class='d-inline-block w-40 text-end'>".number_format( $e- $d )."</span></p></div><a class='btn btn-info mt-3 col-12'>Agregar producto</a>";

                    echo "
                    <div class=\"col-6 col-lg-3 mb-2\" producto=\"{$p[ "codigo" ]}\">
                        <table class=\"w-100 m-0\"><tr>
                            <td><img  style=\"width:30px !important\" src=\"".base_url()."assets/img/productos/".( $avatar ? $p[ "codigo" ] : "NO-IMAGEN" ).".png\"></td>
                            <td nowrap><input class=\"form-control mx-2\" type=\"number\" style=\"width:100px !important\"></td>
                            <td width=\"100%\">".mb_strtoupper( $p[ "data" ][ "nombre" ] )."</td>
                        </tr></table>
                    </div>";
                }
            ?>
        </div>
    </div>
</div>


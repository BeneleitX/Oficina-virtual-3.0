<link href="<?php echo base_url(); ?>assets/css/datatables.css" rel="stylesheet"/>
<script src="<?php echo base_url(); ?>assets/js/datatables.js" type="text/javascript"></script>
<script src="<?php echo base_url(); ?>assets/js/datatables_bs5.js" type="text/javascript"></script>

<h4 class="mt-1 mb-0"><?php echo $titulo; ?></h4>
<p><a href="<?php echo base_url( "almacenes/".$modelo ); ?>"><i class="fa fa-undo"></i> Regresar a almacenes</a></p>

<form action="<?php echo base_url( "aplica_transfer" ); ?>" method="post">
    <?php echo csrf_field(); ?>
    <input type="hidden" name="modelo" value="<?php echo $modelo; ?>">
    
    <div class="card mb-4">
        <div class="card-header"><h5 class="m-0">Datos de transferencia</h5></div>
        <div class="card-body pb-0">
            <div class="row">

                <div class="col-6 col-lg-3 mb-3 pt-2">
                    Almacen origen      
                </div>
                <div class="col-6 col-lg-3 mb-3">
                    <select name="origen" class="form-select">
                    <?php 
                    foreach( ALMACENES as $a ){
                        echo "\n<option value=\"{$a[ "codigo" ]}\">{$a[ "nombre" ]}</option>";
                    }
                    ?>
                    </select>
                </div>

                <div class="col-6 col-lg-3 mb-3 pt-2">
                    Almacen destino      
                </div>
                <div class="col-6 col-lg-3 mb-3">
                    <select name="destino" class="form-select">
                        <option value="">...</option>
                    <?php 
                    foreach( ALMACENES as $a ){
                        echo "\n<option value=\"{$a[ "codigo" ]}\">{$a[ "nombre" ]}</option>";
                    }
                    ?>
                    </select>
                </div>

                <div class="col-6 col-lg-3 mb-3 pt-2">
                    Notas de la transferencia
                </div>
                <div class="col-6 col-lg-3 mb-3">
                    <input name="notas" class="form-control">
                </div>

                <div class="col-6 col-lg-3 mb-3 pt-2">
                    Fecha
                </div>
                <div class="col-6 col-lg-3 mb-3">
                    <input type="date" name="fecha" class="form-control" value="<?php echo date( "Y-m-d" ); ?>">
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
                        if( intval( $p[ "data" ][ "dimensiones" ][ "peso" ] ) > 0 ){
                            if( !isset( $almacen[ "productos" ][ $p[ "codigo" ] ] ) ){
                                $almacen[ "productos" ][ $p[ "codigo" ] ]  = 0;
                            } 
                            
                            $e = intval( $almacen[ "productos" ][ $p[ "codigo" ] ]);
                            $d = intval( $almacen[ "productos" ][ $p[ "codigo" ] ]);

                            $avatar = file_exists( "assets/img/productos/{$p[ "codigo" ]}.png" );

                            echo "
                            <div class=\"col-6 col-lg-3 mb-2\" producto=\"{$p[ "codigo" ]}\">
                                <table class=\"w-100 m-0\"><tr>
                                    <td><img  style=\"width:30px !important\" src=\"".base_url()."assets/img/productos/".( $avatar ? $p[ "codigo" ] : "NO-IMAGEN" ).".png\"></td>
                                    <td nowrap><input name=\"productos[{$p[ "codigo" ]}]\" class=\"form-control mx-2\" type=\"number\" style=\"width:100px !important\"></td>
                                    <td width=\"100%\">".mb_strtoupper( $p[ "data" ][ "nombre" ] )."</td>
                                </tr></table>
                            </div>";
                        }
                    }
                ?>
            </div>
        </div>
    </div>

    <div class="card border-red my-3">
        <div class="card-header">
            <h5 class="text-red mb-0">Ejecutar transferencia</h5>
        </div>
        <div class="card-body text-red" style="position:relative; padding-right:250px">
        <img src="<?php echo base_url(); ?>assets/img/gatos/cat2.png" style="width:250px; position:absolute; bottom:0; right:0px">
            <p><i class="fa fa-warning text-mustard"></i> Verificar las cantidades antes de avanzar. Al aplicar la acción, se marcarán los productos como EN TRANSITO hacia el almacen destino, descontandose de la existencia del almacen origen. Una vez que el socio responsable los reciba físicamente, podrá marcarlos como ENTREGADOS y las cantidades se añadirán en automático al stock destino.</p>
            <p>Se registra como responsable de esta acción a <?php echo $usuario->avatar(24)." ".$usuario->id( null, "marine")." ".$usuario->nombre(2); ?></p>

            <button type="submit" class="btn btn-danger" href="<?php echo base_url( "bitacora" ); ?>"><i class="fa fa-truck-arrow-right"></i> Ejecutar transferencia</button>
        </div>
    </div>
</form>
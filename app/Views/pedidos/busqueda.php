<link href="<?php echo base_url(); ?>assets/css/datatables.css" rel="stylesheet"/>
<script src="<?php echo base_url(); ?>assets/js/datatables.js" type="text/javascript"></script>
<script src="<?php echo base_url(); ?>assets/js/datatables_bs5.js" type="text/javascript"></script>

<img style="position:absolute; right:20px; top:30px; width:120px" src="<?php echo base_url(); ?>assets/img/logo_color.png">
<h4 class="mt-1"><?php echo $titulo; ?></h4>
<p><a class="btn btn-sm btn-light" href="<?php echo base_url( "admin" ); ?>"><i class="fa fa-undo"></i> Regresar a administración</a></p>


<div class="alert alert-warning mb-4">
    <div class="row">
        <div class="col-lg-4">
            <ul  class="mb-0">
                <li>Escribe el dato a buscar en la información del pedido</li>
                <li>Usar al menos 4 numeros</li>
                <li>El sistema buscará automáticamente en referencias que su terminación coincida con el dato buscado</li>
            </ul>
        </div>
        <div class="col-lg-4">
            <form action="<?php echo base_url( "pedidos" ); ?>" method="post">
                <?php echo csrf_field(); ?>
                <div class="input-group">
                    <span class="input-group-text bg-marine" id="basic-addon1"><i class="fa fa-magnifying-glass fs-3 px-2"></i></span>
                    <input type="text" class="form-control fs-3 px-3" placeholder="Buscar" name="query" value="<?php echo $query; ?>">
                </div>
            </form>
        </div>
        <?php
            if( isset( $pedidos ) ){
                echo "<div class=\"col-lg-4 text-end\"><span class=\"fs-4\">Resultados encontrados: <span class=\"badge bg-".( sizeof( $pedidos ) ? "teal" : "red" )."\">".sizeof( $pedidos )."</span></span></div>";
            }
        ?>
    </div>
</div>

<ul class="nav nav-tabs">
    <li class="nav-item">
        <a class="nav-link <?php echo isset( $pedidos ) ? "": "active"; ?>" aria-current="page" href="<?php echo base_url( "usuarios" ); ?>">Historial de consultas</a>
    </li>
    <li class="nav-item">
        <a class="nav-link  <?php echo isset( $pedidos ) ? "active": "disabled"; ?>" aria-disabled="true">Resultados de búsqueda</a>
    </li>
</ul>

<div class="tab-content bg-white p-3" id="myTabContent">
    <table class="table table-display table-striped" id="tabla_historial">
        <thead>
            <tr>
                <td>Referencia</td>
                <td>Empresa</td>
                <td>Socio</td>
                <td>Nombre</td>
                <td>Cantidad</td>
                <td>Fecha pago</td>
                <td>Tipo pago</td>
                <td>Tipo entrega</td>
                <td></td>
            </tr>
        </thead>

        <tbody>
            <?php 
            if( isset( $pedidos ) ){
                foreach( $pedidos as $h ){

                    if( !isset( $socios[ $h[ "usuario_id" ] ] ) ){
                        $socios[ $h[ "usuario_id" ] ] = model( "UsuarioModel" )->find( $h[ "usuario_id" ] );
                    }
                    $u = $socios[ $h[ "usuario_id" ] ];


                    if( !isset( $metodopago[ $h[ "metodopago_codigo" ] ] ) ){
                        $metodopago[ $h[ "metodopago_codigo" ] ] = model( "MetodopagoModel" )->find( $h[ "metodopago_codigo" ] );
                    }
                    $mp = $metodopago[ $h[ "metodopago_codigo" ] ];

                    if( !isset( $metodoentrega[ $h[ "metodoentrega_codigo" ] ] ) ){
                        $metodoentrega[ $h[ "metodoentrega_codigo" ] ] = model( "MetodoentregaModel" )->find( $h[ "metodoentrega_codigo" ] );
                    }
                    $me = $metodoentrega[ $h[ "metodoentrega_codigo" ] ];

                    $m = MODELOS[ $h[ "modelo_codigo" ] ];

                    echo "\n<tr>
                                <td nowrap>".referencia( $h )."</td>
                                <td nowrap class=\"\"><span class=\"text-{$m[ "settings" ][ "color" ]}\"><i class=\"fa fa-{$m[ "settings" ][ "icono" ]}\"></i> {$m[ "nombre" ]}</span></td>
                                <td>". $u->id( null, "marine")."</td>
                                <td>".$u->avatar( 24 )." ".$u->nombre( 2 )."</td>
                                <td class=\"text-end fw-bold\">$".number_format( $h[ "data" ][ "comisionentrega" ] + $h[ "data" ][ "comisionbanco" ] + $h[ "data" ][ "total" ] - $h[ "data" ][ "saldo" ], 2 )."</td>
                                <td>".( $h[ "fechas" ][ "pagado" ] ?? false ? fecha( $h[ "fechas" ][ "pagado" ] ) : "--" )."</td>
                                <td>".( $mp[ "nombre" ] ?? "--" )."</td>
                                <td>".( $me[ "nombre" ] ?? "--" )."</td>
                                <td class=\"text-end\"><a href=\"".base_url( "pedido/{$h[ "referencia" ]}" )."\" class=\"btn btn-sm btn-success\">Detalles</a></td>
                            </tr>";
                }                
            }
            else{
                $socios  = [];
                $metodopago = [];
                $metodoentrega = [];

                foreach( $bitacoras as $b ){
                    $h = $historial[ $b[ "s" ] ];

                    if( !isset( $socios[ $h[ "usuario_id" ] ] ) ){
                        $socios[ $h[ "usuario_id" ] ] = model( "UsuarioModel" )->find( $h[ "usuario_id" ] );
                    }
                    $u = $socios[ $h[ "usuario_id" ] ];


                    if( !isset( $metodopago[ $h[ "metodopago_codigo" ] ] ) ){
                        $metodopago[ $h[ "metodopago_codigo" ] ] = model( "MetodopagoModel" )->find( $h[ "metodopago_codigo" ] );
                    }
                    $mp = $metodopago[ $h[ "metodopago_codigo" ] ];

                    if( !isset( $metodoentrega[ $h[ "metodoentrega_codigo" ] ] ) ){
                        $metodoentrega[ $h[ "metodoentrega_codigo" ] ] = model( "MetodoentregaModel" )->find( $h[ "metodoentrega_codigo" ] );
                    }
                    $me = $metodoentrega[ $h[ "metodoentrega_codigo" ] ];

                    $m = MODELOS[ $h[ "modelo_codigo" ] ];

                    echo "\n<tr>
                                <td nowrap>".referencia( $h )."</td>
                                <td nowrap class=\"\"><span class=\"text-{$m[ "settings" ][ "color" ]}\"><i class=\"fa fa-{$m[ "settings" ][ "icono" ]}\"></i> {$m[ "nombre" ]}</span></td>
                                <td>". $u->id( null, "marine")."</td>
                                <td>".$u->avatar( 24 )." ".$u->nombre( 2 )."</td>
                                <td class=\"text-end fw-bold\">$".number_format( $h[ "data" ][ "comisionentrega" ] + $h[ "data" ][ "comisionbanco" ] + $h[ "data" ][ "total" ] - $h[ "data" ][ "saldo" ], 2 )."</td>
                                <td>".( $h[ "fechas" ][ "pagado" ] ?? false ? fecha( $h[ "fechas" ][ "pagado" ] ) : "--" )."</td>
                                <td>".( $mp[ "nombre" ] ?? "--" )."</td>
                                <td>".( $me[ "nombre" ] ?? "--" )."</td>
                                <td class=\"text-end\"><a href=\"".base_url( "pedido/{$h[ "referencia" ]}" )."\" class=\"btn btn-sm btn-success\">Detalles</a></td>
                            </tr>";
                }
            }
            ?>
        </tbody>
    </table>
</div>
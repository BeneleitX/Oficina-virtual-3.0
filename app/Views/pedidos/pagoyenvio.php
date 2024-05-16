<h4 class="mt-1 mb-3"><?php echo $titulo; ?></h4>

<ul class="nav nav-pills mb-3">
    <?php 
    foreach( MODELOS as $m ){
        if( $m[ "settings" ][ "efectivo" ] ){
            echo "\n<li class=\"nav-item\"><a class=\"nav-link ".( $modelo == $m[ "codigo" ] ? "active" : "")."\" aria-current=\"page\" href=\"".base_url( "tienda/".$m[ "codigo" ] )."\"><i class=\"fa fa-{$m[ "settings" ][ "icono" ]}\"></i> {$m[ "nombre" ]}</a></li>";
        }
    }
    ?>
</ul>

<div class="alert alert-info">
    <div class="row">
        <div class="col-md-3">
            <h5>1. Elegir modo de entrega</h5>
            <p class="mb-0">Click en los botones grises para ver la imagen detallada del frente y reverso de la credencial del socio.</p>
        </div>        

        <div class="col-md-3">
            <h5>2. Elegir método de pago</h5>
            <p class="mb-0">Validar que el nombre, la CURP, el sexo y la fecha de nacimiento coincidan fielmente con los datos impresos en la credencial.</p>
        </div>

        <div class="col-md-3">
            <h5>3. Revisar contenido</h5>
            <p class="mb-0">Click en los botones grises para ver la imagen detallada del frente y reverso de la credencial del socio.</p>
        </div>

        <div class="col-md-3">
            <h5>4. Finalizar compra</h5>
            <p class="mb-0">Si todo coincide, click en el botón verde para aceptar el documento. Si hay algun error o la imagen no es legible, rechazar con el botón rojo.</p>
        </div>
    </div>    

</div>

<div class="row">

    <div class="col-md-6 col-lg-3 mb-3">
		<div class="card">
            <div class="card-header"><h5 class="mb-1 me_nombre"><?php echo $pedido[ "metodoentrega_codigo" ] ? METODOSENTREGA[ $pedido[ "metodoentrega_codigo" ] ][ "nombre" ] : "<i class=\"fa fa-warning text-mustard\"></i> Elija tipo de Entrega"; ?></h5></div>

            <div class="card-body m-0 ">
                <?php 
                foreach( METODOSENTREGA as $me ){
                    echo "\n<input type=\"radio\" class=\"btn-check\" id=\"me-{$me[ "codigo" ]}\" autocomplete=\"off\" name=\"metodosentrega\" value=\"{$me[ "codigo" ]}\" ".( $me[ "codigo" ] == $pedido[ "metodoentrega_codigo" ] ? "checked" : "").">
                    <label class=\"btn btn-outline-secondary col-12 mb-1\" for=\"me-{$me[ "codigo" ]}\">{$me[ "nombre" ]}</label>";
                }
                ?>
            </div>            

            <div class="card-body me_respuesta" <?php if( !$pedido[ "metodoentrega_codigo" ] ) echo "style=\"display:none\""; ?>>
                <p class="me_descripcion mb-3"><?php if( $pedido[ "metodoentrega_codigo" ] ) echo METODOSENTREGA[ $pedido[ "metodoentrega_codigo" ] ][ "settings" ][ "descripcion" ]; ?></p>

                <div class="me_formulario" mp="almacen"  <?php if( substr( $pedido[ "metodoentrega_codigo" ], 0, 2 ) != "00" ) echo "style=\"display:none\""; ?>>
                    <select class="form-select mb-3" name="select_almacen">
                    <?php
                        foreach( ALMACENES as $a ){
                            echo "\n<option ".( $a[ "codigo" ] == $pedido[ "data" ][ "entrega" ] || !$pedido[ "data" ][ "entrega" ] ? "selected" : "" )." value=\"{$a[ "codigo" ]}\">{$a[ "nombre" ]}</option>";
                        }
                    ?>
                    </select>
                </div>

                <div class="me_formulario" mp="domicilio" <?php if( substr( $pedido[ "metodoentrega_codigo" ], 0, 2 ) == "00" ) echo "style=\"display:none\""; ?>>
                    <?php 
                    $domicilios = $socio->getDomicilios();

                    if( sizeof( $domicilios ) ){
                        $d = $domicilios[ $usuario->data->domicilio ];
                        if( intval( $pedido[ "data" ][ "entrega" ] ) && substr( $pedido[ "metodoentrega_codigo" ], 0, 2 ) != "00" ){
                            $d = $domicilios[ $pedido[ "data" ][ "entrega" ] ];
                        }
                        
                        echo "\n<div domicilio_id=\"{$d[ "id" ]}\" class=\"card border-teal text-teal text-start mb-3 p-2\"><p><strong>{$d[ "nombre" ]}</strong></p>
                        {$d[ "calleynumero" ]}<br>
                        Colonia {$d[ "colonia" ]}<br>
                        {$d[ "localidad" ]}, {$d[ "entidad" ]}<br>
                        C.P. {$d[ "codigopostal" ]}
                        </div>";
                    }
                    else{
                        echo "<div domicilio_id=\"0\" class=\"alert alert-danger\"><i class=\"fa fa-warning\"></i> Para utilizar paquetería como tipo de entrega, primero necesitas dar de alta un domicilio.</div>";
                    }
                    ?>

                    <p><button class="btn btn-sm btn-primary" onclick="$( '#modal_domicilios' ).modal( 'show' )">Ver mis domicilios</button></p>
                </div>

                <div class="alert p-2 alert-info me_costo mb-0" <?php if( !$pedido[ "metodoentrega_codigo" ] ) echo "style=\"display:none\""; ?>><?php if( $pedido[ "metodoentrega_codigo" ] ) echo "Utilizar este método de entrega, genera un costo de $".number_format( substr( $pedido[ "metodoentrega_codigo" ], 0, 2 ) == "00" ? VARIABLES[ "tarifas_almacen" ][ "valor" ][ ALMACENES[ $pedido[ "data" ][ "entrega" ] ][ "settings" ][ "tarifa" ] ] : METODOSENTREGA[ $pedido[ "metodoentrega_codigo" ] ][ "settings" ][ "costo" ], 2 ); ?></div>
            </div>
        </div>
	</div>



    <div class="col-md-6 col-lg-3 mb-3">
		<div class="card mb-0">
            <div class="card-header"><h5 class="mb-1 mp_nombre"><?php echo $pedido[ "metodopago_codigo" ] ? METODOSPAGO[ $pedido[ "metodopago_codigo" ] ][ "nombre" ] : "<i class=\"fa fa-warning text-mustard\"></i> Elija método de pago"; ?></h5></div>

            <div class="card-body m-0 ">
                <?php 
                foreach( METODOSPAGO as $mp ){
                    echo "\n<input type=\"radio\" class=\"btn-check\" id=\"mp-{$mp[ "codigo" ]}\" autocomplete=\"off\" name=\"metodospago\" value=\"{$mp[ "codigo" ]}\" ".( $mp[ "codigo" ] == $pedido[ "metodopago_codigo" ] ? "checked" : "").">
                    <label class=\"btn btn-outline-secondary col-12 mb-1\" for=\"mp-{$mp[ "codigo" ]}\">{$mp[ "nombre" ]}</label>";
                }
                ?>
            </div>            

            <div class="card-body mp_respuesta" <?php if( !$pedido[ "metodopago_codigo" ] ) echo "style=\"display:none\""; ?>>
                <p class="mp_descripcion mb-3"><?php if( $pedido[ "metodopago_codigo" ] ) echo METODOSPAGO[ $pedido[ "metodopago_codigo" ] ][ "settings" ][ "descripcion" ]; ?></p>
                <div class="alert  p-2 alert-info mp_costo mb-0"><?php if( $pedido[ "metodopago_codigo" ] ) echo "Utilizar este método de pago, genera una comisión bancaria de $"
                .number_format( METODOSPAGO[ $pedido[ "metodopago_codigo" ] ][ "settings" ][ "tipocomision" ] == "porcentaje" ? $pedido[ "data" ][ "total" ] * METODOSPAGO[ $pedido[ "metodopago_codigo" ] ][ "settings" ][ "comision" ] / 100 : METODOSPAGO[ $pedido[ "metodopago_codigo" ] ][ "settings" ][ "comision" ], 2 ); ?></div>
            </div>
        </div>
	</div>

    <div class="col-md-6 col-lg-3 mb-3">
		<div class="card p-2">
            <table class="w-100">
                <?php
                foreach( PROMOCIONES as $promocion ){
                    
                    if( sizeof( (array)$pedido[ "promociones" ][ $promocion[ "codigo" ] ][ "productos" ] ) ){
                        echo "\n<tr><th colspan=\"2\" style=\"color:var(--bs-".( $promocion[ "settings" ][ "clase" ] ).")\">".( $promocion[ "settings" ][ "nombre" ] )."</th></tr>";

                        foreach( $pedido[ "promociones" ][ $promocion[ "codigo" ] ][ "productos" ] as $producto => $data ){
                            echo "\n<tr style=\"border-bottom:1px solid var(--bs-{$promocion[ "settings" ][ "clase" ]})\"><td class=\"text-end\"><span class=\"badge my-1 bg-{$promocion[ "settings" ][ "clase" ]}\">{$data[ "cantidad" ]}</span></td><td>".strtoupper( PRODUCTOS[ $producto ][ "data" ][ "nombre" ] )."</td><td class=\"text-end\">$".number_format( $data[ "cantidad" ] * $data[ "precio" ], 2 )."</td></tr>";
                        }

                        echo "\n<tr style=\"color:var(--bs-".( $promocion[ "settings" ][ "clase" ] ).")\"><th></th><th class=\"text-end\">Subtotal</th><th class=\"text-end\">$".number_format( $pedido[ "promociones" ][ $promocion[ "codigo" ] ][ "precio" ], 2 )."</th></tr>";
                    }

                }
                ?>
            </table>
        </div>
	</div>

	<div class="col-md-6 col-lg-3">
        <div class="card mb-3"en">
            <table class="table rounded-3 m-0">
                <tr><td valign="middle" class="">Total de productos</td><td valign="middle" class="text-end"><h4 class="text-teal" total_productos="<?php echo $pedido[ "data" ][ "total" ]; ?>">$<?php echo number_format( $pedido[ "data" ][ "total" ], 2 ); ?></h4></td></tr>
                <tr><td valign="middle" class="">Gastos de entrega</td><td valign="middle" class="text-end"><h4 class="text-teal" total_entrega="<?php echo number_format( $pedido[ "data" ][ "comisionentrega" ], 2 ); ?>">$<?php echo number_format( $pedido[ "data" ][ "comisionentrega" ], 2 ); ?></h4></td></tr>
                <tr><td valign="middle" class="">Comisión bancaria</td><td valign="middle" class="text-end"><h4 class="text-teal" total_banco="<?php echo $pedido[ "data" ][ "comisionbanco" ]; ?>">$<?php echo number_format( $pedido[ "data" ][ "comisionbanco" ], 2 ); ?></h4></td></tr>
            </table>
        </div>

        <div class=" alert alert-secondary px-2 py-1">
            <table class="w-100">
                <tr><td valign="middle">Total de pedido</td><td valign="middle" class="text-end"><h4 class="text-teal" gran_total="<?php echo $pedido[ "data" ][ "total" ] + $pedido[ "data" ][ "comisionentrega" ] + $pedido[ "data" ][ "comisionbanco" ]; ?>">$<?php echo number_format( $pedido[ "data" ][ "total" ] + $pedido[ "data" ][ "comisionentrega" ] + $pedido[ "data" ][ "comisionbanco" ], 2 ); ?></h4></td></tr>
            </table>
        </div>
        
        <form method="post" action="<?php echo base_url( "checkout" ); ?>">
            <?php echo csrf_field(); ?>
            <input type="hidden" name="modelo" value="<?php echo $modelo; ?>">
		    <button type="submit" id="checkout_a" class="btn btn-success col-12"><i class="fa fa-check"></i> Finalizar</button>
        </form>
	</div>
</div>


<div class="modal" tabindex="-1" id="modal_domicilios">
	<div class="modal-dialog">
		<div class="modal-content">
            <?php echo csrf_field() ?>
            <input type="hidden" name="socio"  value="<?php echo $socio->id; ?>">
            <input type="hidden" name="old_beneficiario"  value="">

            <div class="modal-header">
                <h5 class="modal-title">Elige el domicilio destino</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <?php 
                if( !sizeof( $domicilios ) ){
                    echo "<h5 class=\"mb-4\">:</h5>";
                }

                foreach( $domicilios as $d ){
                    echo "\n<button domicilio_id=\"{$d[ "id" ]}\" class=\"w-100 btn btn-outline-success text-start mb-3\"><p><strong>{$d[ "nombre" ]}</strong></p>
                        {$d[ "calleynumero" ]}<br>
                        Colonia {$d[ "colonia" ]}<br>
                        {$d[ "localidad" ]}, {$d[ "entidad" ]}<br>
                        C.P. {$d[ "codigopostal" ]}
                        </button>";
                }
                
                ?>

                <div class="alert alert-info">
                    <p>Puedes editar tus domicilios o agregar uno nuevo desde tu perfil de socio.</p>
                    <a class="btn btn-secondary" href="<?php echo base_url( "perfil" ); ?>">Ir a perfil de socio</a>
                </div>
            </div>
		</div>
	</div>
</div>


<script>
	var modelo 			= '<?php echo $modelo; ?>',
        usuario 		= <?php echo json_encode( $socio ) ?>,
        metodospago		= <?php echo json_encode( METODOSPAGO ) ?>,
        metodosentrega	= <?php echo json_encode( METODOSENTREGA ) ?>,
        almacenes	    = <?php echo json_encode( ALMACENES ) ?>,
		pedido  		= <?php echo json_encode( $pedido ); ?>,
        tarifas         = <?php echo json_encode( VARIABLES[ "tarifas_almacen" ][ "valor" ] ); ?>;
</script>
<h4 class="mt-2"><?php echo $titulo; ?></h4>

<div class="row">
	<div class="col-md-9 mb-3">
		<ul class="nav nav-pills">
			<?php 
			foreach( MODELOS as $m ){
				if( $m[ "settings" ][ "efectivo" ] ){
					echo "\n<li class=\"nav-item\"><a class=\"nav-link ".( $pedido[ "modelo_codigo" ] == $m[ "codigo" ] ? "active" : "")."\" aria-current=\"page\" href=\"".base_url( "tienda/".$m[ "codigo" ] )."\"><i class=\"fa fa-{$m[ "settings" ][ "icono" ]}\"></i> {$m[ "nombre" ]}</a></li>";
				}
			}
			?>
		</ul>
	</div>
	<div class="col-md-3 mb-3 text-end">

			<button id="borra_todo" class="btn btn-outline-danger d-none"><i class="fa fa-xmark"></i> Borrar todo</button>
            
            <?php echo "Pedido No. <span class=\"badge bg-marine\">{$pedido[ "referencia" ]}</span> ".estatus( $pedido[ "estatus_codigo" ]); ?>

	</div>
</div>


<div class="row">

    <div class="col-lg-6 mb-3">
                <?php
                foreach( PROMOCIONES as $promocion ){

                    if( sizeof( (array)$pedido[ "promociones" ][ $promocion[ "codigo" ] ] ) && sizeof( (array)$pedido[ "promociones" ][ $promocion[ "codigo" ] ][ "productos" ] ) ){
                        echo "\n<div class=\"card mb-3\" style=\"overflow:hidden\">
                        <div class=\"card-header bg-{$promocion[ "settings" ][ "clase" ]}\"><h5 class=\"text-white m-0\">{$promocion[ "settings" ][ "nombre" ]}</h5></div>
                        <table class=\"table mb-0 w-100\">";
    
                        foreach( $pedido[ "promociones" ][ $promocion[ "codigo" ] ][ "productos" ] as $producto => $data ){
                            echo "\n<tr style=\"border-bottom:1px solid var(--bs-{$promocion[ "settings" ][ "clase" ]})\"><td class=\"d-none d-lg-table-cell\" width=\"50\"><img src=\"".base_url()."assets/img/productos/{$producto}.png\" width=\"50\"></td><td valign=\"xmiddle\" width=\"30\" class=\"text-end\"><h5 class=\"my-0 ms-2\"><span >{$data[ "cantidad" ]}</span></h5></td><td valign=\"xmiddle\" width=\"100%\">{$data[ "nombre" ]}<br><span class=\"d-none d-lg-inline text-gray-500\">{$data[ "descripcion" ]}</span></td><td valign=\"xmiddle\" class=\"text-end\">$".number_format( $data[ "cantidad" ] * $data[ "precio" ], 2 )."</td></tr>";
                        }

                        echo "\n<tr><th class=\"d-none d-lg-table-cell\" style=\"background: var(--bs-gray-200) !important\"></th><th style=\"background: var(--bs-gray-200) !important\"></th><th style=\"background: var(--bs-gray-200) !important\" class=\"text-end\">Subtotal</th><th style=\"background: var(--bs-gray-200) !important\" class=\"text-end\">$".number_format( $pedido[ "promociones" ][ $promocion[ "codigo" ] ][ "precio" ], 2 )."</th></tr>";

                        echo "</table></div>";
                    }
                }
                ?>
	</div>


    <div class="col-lg-6">
		<div class="card mb-3">
            <div class="card-header bg-teal"><h5 class="mb-0 text-white">Método de Entrega</h5></div>

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

                    <p><button class="btn btn-secondary" onclick="$( '#modal_domicilios' ).modal( 'show' )">Ver mis domicilios</button></p>
                </div>

                <?php if( intval( substr( $pedido[ "estatus_codigo" ], 0, 3 ) ) < 400 ){ ?>
                <div class="alert p-2 alert-info me_costo mb-0" <?php if( !$pedido[ "metodoentrega_codigo" ] ) echo "style=\"display:none\""; ?>><?php if( $pedido[ "metodoentrega_codigo" ] ) echo "Utilizar este método de entrega, genera un costo de $".number_format( substr( $pedido[ "metodoentrega_codigo" ], 0, 2 ) == "00" ? VARIABLES[ "tarifas_almacen" ][ "valor" ][ ALMACENES[ $pedido[ "data" ][ "entrega" ] ][ "settings" ][ "tarifa" ] ] : METODOSENTREGA[ $pedido[ "metodoentrega_codigo" ] ][ "settings" ][ "costo" ], 2 ); ?></div>
                <?php } ?>
            </div>
        </div>

        <div class="row">
            <div class="col-lg-6">
                <div class="card mb-3" style="overflow:hidden">
                    <table class="table rounded-3 m-0">
                        <tr><td valign="middle" class="">Total de productos</td><td valign="middle" class="text-end"><h5 class="m-0 text-teal" total_productos="<?php echo $pedido[ "data" ][ "total" ]; ?>">$<?php echo number_format( $pedido[ "data" ][ "total" ], 2 ); ?></h5></td></tr>
                        <tr><td valign="middle" class="">Gastos de entrega</td><td valign="middle" class="text-end"><h5 class="m-0 text-teal" total_entrega="<?php echo number_format( $pedido[ "data" ][ "comisionentrega" ], 2 ); ?>">$<?php echo number_format( $pedido[ "data" ][ "comisionentrega" ], 2 ); ?></h5></td></tr>
                        <tr><td valign="middle" class="">Saldo a favor</td><td valign="middle" class="text-end"><h5 class="m-0 text-<?php echo $socio->data->saldo->{$pedido[ "modelo_codigo" ]} > 0 ? "red" : "gray-500"; ?>" total_saldo="<?php echo $socio->data->saldo->{$pedido[ "modelo_codigo" ]}; ?>">$<?php echo number_format(  $socio->data->saldo->{$pedido[ "modelo_codigo" ]}, 2 ); ?></h5></td></tr>
                        <tr><td valign="middle" class="text-white" style="background:var(--bs-marine) !important">Total de pedido</td><td valign="middle" class="text-end" style="background:var(--bs-marine) !important"><h5 class="text-white my-0" gran_total="<?php echo $tt = $pedido[ "data" ][ "total" ] + $pedido[ "data" ][ "comisionentrega" ] - $socio->data->saldo->{$pedido[ "modelo_codigo" ]}; ?>">$<?php echo number_format( $pedido[ "data" ][ "total" ] + $pedido[ "data" ][ "comisionentrega" ] - $socio->data->saldo->{$pedido[ "modelo_codigo" ]}, 2 ); ?></h5></td></tr>
                    </table>
                </div>
                
            </div>
            <div class="col-lg-6">
                <form method="post" action="<?php echo base_url( "checkout" ); ?>">
                    <?php echo csrf_field(); ?>
                    <input type="hidden" name="modelo" value="<?php echo $pedido[ "modelo_codigo" ]; ?>">

                    <?php 
                    foreach( METODOSPAGO as $mp ){
                        echo "\n<button class=\"btn col-12 btn-success mb-3\" type=\"submit\" name=\"metodopago\" value=\"{$mp[ "codigo" ]}\">{$mp[ "nombre" ]}<h4 class=\"cantidad m-0 text-white\">$".number_format( $tt, 2 )."</h4></button>";
                    }
                    ?>

                </form>
            </div>
        </div>
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
	var modelo 			= '<?php echo $this->data[ "pedido" ][ "modelo_codigo" ]; ?>',
        usuario 		= <?php echo json_encode( $socio ) ?>,
        metodospago		= <?php echo json_encode( METODOSPAGO ) ?>,
        metodosentrega	= <?php echo json_encode( METODOSENTREGA ) ?>,
        almacenes	    = <?php echo json_encode( ALMACENES ) ?>,
		pedido  		= <?php echo json_encode( $pedido ); ?>,
        tarifas         = <?php echo json_encode( VARIABLES[ "tarifas_almacen" ][ "valor" ] ); ?>;
</script>
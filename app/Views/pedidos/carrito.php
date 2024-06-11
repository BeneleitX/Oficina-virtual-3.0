<h4 class="mt-1 mb-0"><?php echo $titulo; ?> - <?php echo "Pedido No. <span class=\"badge bg-marine\">{$pedido[ "referencia" ]}</span> <span style=\"font-size:16px\">".estatus( $pedido[ "estatus_codigo" ])."</span>"; ?></h4>

<p><a href="<?php echo base_url( "historial/".$modelo ); ?>"><i class="fa fa-receipt"></i> Ir a historial de compras</a></p>
<p><?php echo $socio->avatar()." ".$socio->id( $modelo )." ".$socio->nombre( 2 ); ?></p>

<?php if( !$pagado ){ ?>
    <div class="row">
	    <div class="col-md-6 mb-3">
		    <ul class="nav nav-pills">
                <?php 
                foreach( MODELOS as $m ){
                    if( $m[ "settings" ][ "efectivo" ] ){
                        echo "\n<li class=\"nav-item\"><a class=\"nav-link ".( $modelo == $m[ "codigo" ] ? "active" : "")."\" aria-current=\"page\" href=\"".base_url( "tienda/".$m[ "codigo" ] )."\"><i class=\"fa fa-{$m[ "settings" ][ "icono" ]}\"></i> {$m[ "nombre" ]}</a></li>";
                    }
                }
                ?>
            </ul>
        </div>
        <div class="col-md-6 mb-3 text-end">
                <button id="borra_todo" class="btn btn-outline-danger xd-none"><i class="fa fa-xmark"></i> Reiniciar pedido</button>
        </div>
    </div>
<?php }
else{
    if( intval( $pedido[ "data" ][ "mesanterior" ] ) ){
        echo "<div class=\"alert alert-danger\"><i class=\"fa fa-circle-info\"></i> Los puntos generados por esta compra son abonados al mes anterior de la fecha de pago.</div>";
    }
}
?>

<div class="row">
	<div class="col-lg-6">
		<div id="shoppingcart">
			<?php

			foreach( PROMOCIONES as $p ){
                $cant_productos = isset( $pedido[ "promociones" ][ $p[ "codigo" ] ][ "productos"] ) ? sizeof( $pedido[ "promociones" ][ $p[ "codigo" ] ][ "productos"] ) : 0;
				echo "\n<div class=\"card mb-3\" ".( !$cant_productos ? " style=\"display:none\" " : "")." promocion=\"{$p[ "codigo" ]}\"><div class=\"card-header text-white bg-{$p[ "settings" ][ "clase" ]}\"><div class=\"row\"><div class=\"col-md-4\"><h5 class=\"text-white m-0\">{$p[ "settings" ][ "nombre" ]}</h5></div><div class=\"col-md-8\"><small conteo>{$cant_productos} productos</small>".( $pagado ? "" : "<button onclick=\"show_modal_productos('{$p[ "codigo" ]}')\" class=\"btn btn-sm btn-light float-end agrega_productos\"><i class=\"fa fa-plus\"></i><span class=\"d-none d-lg-inline\"> Agregar productos</span></button>" )."</div></div></div><table productos class=\"w-100\"></table><div class=\"card-footer bg-gray-300 text-end\"><table align=\"right\"><tr><td>Total de {$p[ "settings" ][ "nombre" ]} &nbsp; </td><td><h5 class=\"m-0 total_promo\">$".number_format( isset( $pedido[ "promociones" ][ $p[ "codigo" ] ][ "precio"] ) ? $pedido[ "promociones" ][ $p[ "codigo" ] ][ "precio"] : 0, 2 )."</h5></td></tr>
				</table></div></div>";
			}
			?>
		</div>

	</div>

	<div class="col-lg-6">
    	<div class="card mb-3">
            <div class="card-header bg-teal"><h5 class="mb-0 text-white">Método de Entrega</h5></div>

            <div class="card-body m-0 ">
                <?php 
                foreach( METODOSENTREGA as $me ){

                    echo "\n<input type=\"radio\" class=\"".( $pagado && $me[ "codigo" ] != $pedido[ "metodoentrega_codigo" ] ? "d-none" : "" )." btn-check\" id=\"me-{$me[ "codigo" ]}\" autocomplete=\"off\" name=\"metodosentrega\" value=\"{$me[ "codigo" ]}\" ".( $me[ "codigo" ] == $pedido[ "metodoentrega_codigo" ] ? "checked" : "").">
                    <label class=\"".( $pagado && $me[ "codigo" ] != $pedido[ "metodoentrega_codigo" ] ? "d-none" : "" )." btn btn-outline-secondary col-12 mb-1\" for=\"me-{$me[ "codigo" ]}\">{$me[ "nombre" ]}</label>";
                }
                ?>
            </div>            

            <div class="card-body me_respuesta" <?php if( !$pedido[ "metodoentrega_codigo" ] ) echo "style=\"display:none\""; ?>>
                <p class="me_descripcion mb-3"><?php if( $pedido[ "metodoentrega_codigo" ] ) echo METODOSENTREGA[ $pedido[ "metodoentrega_codigo" ] ][ "settings" ][ "descripcion" ]; ?></p>

                <div class="me_formulario" mp="almacen"  <?php if( substr( $pedido[ "metodoentrega_codigo" ], 0, 2 ) != "00" ) echo "style=\"display:none\""; ?>>

                    <div class="row">
                        <div class="col-lg-6">

                            <select class="form-select" name="select_almacen">
                            <?php
                                foreach( ALMACENES as $a ){
                                    if( !$pagado || $a[ "codigo" ] == $pedido[ "data" ][ "entrega" ] )
                                    echo "\n<option ".( $a[ "codigo" ] == $pedido[ "data" ][ "entrega" ] ? "selected" : "" )." value=\"{$a[ "codigo" ]}\">{$a[ "nombre" ]}</option>";
                                }
                            ?>
                            </select>
                        
                        </div>
                        <div class="col-lg-6">
                            <?php if( $pagado && !$entregado ){ ?>
                                <form method="post" action="<?php echo base_url("entrega"); ?>">
                                    <?php echo csrf_field(); ?>
                                    <input type="hidden" name="pedido" value="<?php echo $pedido[ "id" ]; ?>">
                                    <button type="submit" class="btn col-12 btn-warning">Entregar Pedido en Almacen</button>
                                </form>
                            <?php } ?>
                        </div>
                    </div>
                </div>

                <div class="me_formulario" mp="domicilio" <?php if( substr( $pedido[ "metodoentrega_codigo" ], 0, 2 ) == "00" ) echo "style=\"display:none\""; ?>>
                    <?php 


                    $domicilios = $socio->getDomicilios();

                    if( $pagado && sizeof( $domicilios ) && isset( $pedido[ "data" ][ "domicilio" ] ) ){
                        $domicilios[ 0 ] = $pedido[ "data" ][ "domicilio" ];
                        $dom = 0;
                    }
                    else{
                        $dom = $usuario->data->domicilio ?? 0;
                        if( intval( $pedido[ "data" ][ "entrega" ] ) && substr( $pedido[ "metodoentrega_codigo" ], 0, 2 ) != "00" ){
                            $d = $pedido[ "data" ][ "entrega" ];
                        }
                    }


                    if( sizeof( $domicilios ) ){
                        
                        $d = $domicilios[ $dom ];
                                 
                        echo "\n<div domicilio_id=\"{$d[ "id" ]}\" class=\"card border-teal text-teal text-start mb-3 p-2\"><p><strong>{$d[ "nombre" ]}</strong></p>
                        {$d[ "calleynumero" ]}<br>
                        Colonia {$d[ "colonia" ]}<br>
                        {$d[ "localidad" ]}, {$d[ "entidad" ]}<br>
                        C.P. {$d[ "codigopostal" ]}
                        </div>
                        ";
                    }
                    else{
                        echo "<div domicilio_id=\"0\" class=\"alert alert-danger\"><i class=\"fa fa-warning\"></i> Para utilizar paquetería como tipo de entrega, primero necesitas dar de alta un domicilio.</div>";
                    }
                    ?>
                    <div class="row">
                        
                            <?php if( $pagado){ 
                                if( $entregado ){ ?>
                                <div class="col-12">
                                    <div class="alert alert-warning m-0">
                                        Este pedido ha sido enviado por paquetería con fecha <?php echo substr( $pedido[ "fechas" ][ "enviado" ], 0, 10 ); ?>.<br>Guía de rastreo: <span class="badge bg-mustard"><?php echo $pedido[ "data" ][ "guia" ]; ?></span>
                                    </div>
                                </div>
                                <?php }
                                else{
                                ?>
                                <div class="col-lg-6">
                                <form method="post" action="<?php echo base_url("envia"); ?>">
                                    <?php echo csrf_field(); ?>
                                    <input type="hidden" name="pedido" value="<?php echo $pedido[ "id" ]; ?>">
                                    <button type="submit" class="btn col-12 btn-warning">Enviar pedido por paquetería</button>
                                </form>
                                </div>
                            <?php } 
                                }
                                else{ ?>
                                <div class="col-lg-6"><button class="btn btn-secondary col-12" onclick="$( '#modal_domicilios' ).modal( 'show' )">Ver mis domicilios</button></div>
                            <?php } ?>
                    </div>
                </div>

                <div class="alert alert-danger mt-3" id="no_stock" style="display:none"><p><i class="fa fa-warning"></i> El almacen no cuenta con producto suficiente para surtir tu pedido</p><ul class="m-0"></ul></div>

                <?php if( !$pagado ){ ?>
                <div class="alert p-2 alert-info me_costo mt-3 mb-0" <?php if( !$pedido[ "metodoentrega_codigo" ] ) echo "style=\"display:none\""; ?>><?php if( $pedido[ "metodoentrega_codigo" ] ) echo "Utilizar este método de entrega, genera un costo de $".number_format( substr( $pedido[ "metodoentrega_codigo" ], 0, 2 ) == "00" ? VARIABLES[ "tarifas_almacen" ][ "valor" ][ ALMACENES[ $pedido[ "data" ][ "entrega" ] ][ "settings" ][ "tarifa" ] ] : METODOSENTREGA[ $pedido[ "metodoentrega_codigo" ] ][ "settings" ][ "costo" ], 2 ); ?></div>
                <?php } ?>
            </div>
        </div>

        <div id="puntajes" class="mb-3"><?php
        if( $pagado){
            foreach( PROMOCIONES as $p ){
                if( isset( $pedido[ "PTS" ][ $p[ "codigo" ] ] ) and $pedido[ "PTS" ][ $p[ "codigo" ] ] > 0 ){

                    echo "\n<div class=\"pts text-white bg-white\"><div class=\"pts-titulo bg-{$p[ "settings" ][ "clase" ]}\">{$p[ "settings" ][ "siglas" ]}</div><div class=\"pts-numero bg-{$p[ "settings" ][ "clase" ]}\">{$pedido[ "PTS" ][ $p[ "codigo" ] ]}</div></div>";
                }
            }

        }
        ?></div>

        <?php if( !$pagado ){ ?>
            <div id="alert_anterior" class="alert alert-<?php echo intval( $pedido[ "data" ][ "mesanterior" ] ) ? "danger" : "info"; ?>">
                <i class="fa fa-circle-info"></i> Los puntos de este pedido aplican para el mes de <div class="input-group mb-0 input-group-sm" style="display:inline-flex; width:auto">
                <span style="font-weight:bold" class="input-group-text <?php if( intval( $pedido[ "data" ][ "mesanterior" ] ) ) echo "bg-red border-red"; ?>" id="mescalifica"><?php echo strtoupper( mes( date( "m" ) - intval( $pedido[ "data" ][ "mesanterior" ] ) ) ); ?></span>
                <button onclick="$( '#mes_califica' ).modal( 'show' )" class="btn btn-outline-info btn-sm">Cambiar</buttn>
                </div> 
            </div>
        <?php } ?>


        


        <div class="row">
            <div class="col-lg-6">
                <div class="card mb-3" style="overflow:hidden">
                    <table class="table rounded-3 m-0">
                        <tr><td valign="middle" class="">Total de productos</td><td valign="middle" class="text-end"><h5 class="m-0 text-teal" total_productos="<?php echo $pedido[ "data" ][ "total" ]; ?>">$<?php echo number_format( $pedido[ "data" ][ "total" ], 2 ); ?></h5></td></tr>
                        <tr><td valign="middle" class="">Gastos de entrega</td><td valign="middle" class="text-end"><h5 class="m-0 text-teal" total_entrega="<?php echo number_format( $pedido[ "data" ][ "comisionentrega" ], 2 ); ?>">$<?php echo number_format( $pedido[ "data" ][ "comisionentrega" ], 2 ); ?></h5></td></tr>
                        <?php
                            if( $pagado ){
                                $saldo = $pedido[ "data"][ "saldo" ] ?? 0;
                            }
                            else{
                                $saldo = $socio->data->saldo->{$modelo} ?? 0;
                            }
                        ?>
                        <tr><td valign="middle" class="">Saldo a favor</td><td valign="middle" class="text-end"><h5 class="m-0 text-<?php echo $saldo > 0 ? "red" : "gray-500"; ?>" total_saldo="<?php echo $saldo; ?>">$<?php echo number_format(  $saldo, 2 ); ?></h5></td></tr>
                        
                        <?php 
                        $comisionbanco = $pagado ? $pedido[ "data"][ "comisionbanco" ] : 0;
                        if( $pagado ){ ?>

                            <tr><td valign="middle" class="">Comision bancaria</td><td valign="middle" class="text-end"><h5 class="m-0 text-teal">$<?php echo number_format(  $comisionbanco, 2 ); ?></h5></td></tr>

                        <?php } ?>

                        <tr><td valign="middle" class="text-white" style="background:var(--bs-marine) !important">Total de pedido</td><td valign="middle" class="text-end" style="background:var(--bs-marine) !important"><h5 class="text-white my-0" gran_total="<?php echo $tt = $pedido[ "data" ][ "total" ] + $comisionbanco + $pedido[ "data" ][ "comisionentrega" ] - $saldo; ?>">$<?php echo number_format( $tt, 2 ); ?></h5></td></tr>
                    </table>
                </div>
                
            </div>
            <div class="col-lg-6">
                <form method="post" action="<?php echo base_url( "checkout" ); ?>">
                    <?php echo csrf_field(); ?>
                    <input type="hidden" name="modelo" value="<?php echo $pedido[ "modelo_codigo" ]; ?>">

                    <?php 
                    if( $pagado ){
                        ?>
                        <div class="card mb-3" style="overflow:hidden">
                            <table class="table rounded-3 m-0">
                                <tr><td valign="middle" class="">Fecha de pago</td><td valign="middle" class="text-end"><h5 class="m-0"><?php echo substr( $pedido[ "fechas" ][ "pagado" ], 0, 10 ); ?></h5></td></tr>
                                <tr><td valign="middle" style="<?php if( intval( $pedido[ "data" ][ "mesanterior" ] ) ) echo "background:red; color:white"; ?>">Calificación</td><td style="<?php if( intval( $pedido[ "data" ][ "mesanterior" ] ) ) echo "background:red"; ?>" valign="middle" class="text-end"><h5 class="m-0" style="<?php if( intval( $pedido[ "data" ][ "mesanterior" ] ) ) echo "color:white"; ?>"><?php echo strtoupper( mes(substr( $pedido[ "fechas" ][ "califica" ], 5, 2 ) ) )." ".substr( $pedido[ "fechas" ][ "califica" ], 0, 4 ); ?></h5></td></tr>
                            </table>
                        </div>

                        <?php if( $pagado ){ ?>
                <a href="<?php echo base_url( "ticket/".urlencode( $link ) ); ?>" target="_new" class="btn btn-light col-12" id="imprime">Imprimir ticket</a>
                <?php } ?>                        
                        <?php
                    }
                    else{
                        foreach( METODOSPAGO as $mp ){
                            echo "\n<button class=\"btn col-12 mb-3 ";
                            if($mp[ "settings" ][ "tipocomision" ] == "saldo"){
                                echo " btn-warning ";

                                if( !$socio->data->saldo->{$modelo} || $socio->data->saldo->{$modelo} < ($tt + $socio->data->saldo->{$modelo}) ){
                                    echo " d-none ";
                                }
                            }else{
                                echo " btn-success ";
                            } 
                            
                            echo "\" type=\"submit\" name=\"metodopago\" value=\"{$mp[ "codigo" ]}\">{$mp[ "nombre" ]}<h4 class=\"cantidad m-0 text-white\"></h4></button>";
                        }
                    }
                    ?>

                </form>
            </div>
        </div>
	</div>
</div>

<?php if( $pagado ){
    
    echo "<div class=\"card\"><div class=\"card-header bg-blue\"><h5 class=\"m-0 text-white\">Comisiones generadas por esta compra</h5></div><table class=\"table m-0\"><thead><tr>
    <th class=\"text-center\">Folio</th>
    <th>Esquema</th>
    <th>Nivel</th>
    <th class=\"text-end\">Comisión</th>
    <th>Estatus</th>
    <th>Socio</th>
    </tr></thead><tbody>"; 
    $db = db_connect();
    
    
    $db->query( "select f_reparte_comisiones( {$pedido[ "id" ]} ) as afectados" );

    $comisiones = $db->query( "select * from t_comisiones where pedido_id = {$pedido[ "id" ]}" )->getResult();

    foreach( $comisiones as $c ){
        $u = $c->usuario_id ? model( "UsuarioModel" )->find( $c->usuario_id ) : "SIN RECEPTOR";
        echo "<tr class=\"".( substr( $c->estatus_codigo, 0, 3 ) < 200 ? "opaco" : "" )."\">
        <td class=\"text-center\"><span class=\"badge bg-marine\">{$c->id}</span></td>
        <td>".ESQUEMAS[ $c->esquema_codigo ][ "settings" ][ "titulo" ]."</td>
        <td>{$c->nivel}</td>
        <td class=\"text-end\">".( ESQUEMAS[ $c->esquema_codigo ][ "settings" ][ "reparto" ] != "puntos" ? "$".number_format( $c->cantidad, 2 ) : number_format( $c->cantidad )." Estrellas")."</td>
        <td>".estatus( $c->estatus_codigo )."</td>
        <td>".( isset($u->id) ? $u->avatar(25)." ".$u->id( $modelo )." ".$u->nombre( 2 ) : $u )."</td>
        </tr>"; 
    }

    echo "</tbody></table></div>"; 
}
else{ ?>
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

<div class="modal" tabindex="-1" id="modal_productos" promocion="">
	<div class="modal-dialog modal-lg">
		<div class="modal-content">

			<div class="modal-header">
				<div class="modal-title me-3">
							<div class="input-group">
								<span class="input-group-text"><i class="fa fa-magnifying-glass"></i></span>
								<input type="text" class="form-control" placeholder="Buscar productos" id="busca_producto">
							</div>
				</div>
				<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
			</div>

			<div class="modal-body">
				<div class="row">
					<?php 
                  
					foreach( $productos as $p ){
						echo "
						<div class=\"col-lg-6\" producto=\"{$p->codigo}\">
							<div class=\"card mb-3 boton\" title=\"Click para agregar al pedido\" onclick=\"agrega_producto( '{$p->codigo}' )\">
								<div class=\"row g-0\">
									<div class=\"col-2\">
										<img src=\"".base_url()."assets/img/productos/".( $p->data->avatar ? $p->codigo : "NO-IMAGEN" ).".png\" class=\"img-fluid rounded-start\">
									</div>
									<div class=\"col-10\">
										<div class=\"card-body\">
											<h5>".strtoupper( $p->data->nombre )."</h5>
										<p class=\"small m-0\">{$p->data->descripcion}</p>
										</div>
									</div>
								</div>
							</div>
						</div>";
					}
					?>
				</div>
			</div>
		</div>
	</div>
</div>

<div class="modal" tabindex="-1" id="mes_califica" mesanterior="<?php echo $pedido[ "data" ][ "mesanterior" ]; ?>">
	<div class="modal-dialog">
		<div class="modal-content">
            <?php echo csrf_field() ?>
            <input type="hidden" name="socio"  value="<?php echo $socio->id; ?>">
            <input type="hidden" name="old_beneficiario"  value="">

            <div class="modal-header">
                <h5 class="modal-title"><i class="fa fa-clock-rotate-left"></i> Mes de calificación</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body" ma="0" <?php if( intval( $pedido[ "data" ][ "mesanterior" ] ) ) echo "style=\"display:none\""; ?>>
                <p>Los puntos obtenidos por las compras de este pedido, aplicarán para las calificaciones del mes ACTUAL, es decir, el correspondiente a la fecha de pago. Si deseas que los puntos de esta compra apliquen para la calificación del mes ANTERIOR, activa la opción haciendo click en el botón:</p>
            </div>
            <div class="modal-body" ma="1" <?php if( !intval( $pedido[ "data" ][ "mesanterior" ] ) ) echo "style=\"display:none\""; ?>>
                <p>Los puntos obtenidos por las compras de este pedido, aplicarán para las calificaciones del mes ANTERIOR a la fecha de pago. Si deseas que los puntos de esta compra apliquen para la calificación del mes ACTUAL, activa la opción haciendo click en el botón:</p>
            </div>
            <div class="modal-footer" ma="0">
                <button id="cambia_mes_califica" class="btn btn-<?php echo $pedido[ "data" ][ "mesanterior" ] == "1" ? "info" : "danger"; ?>">Aplicar compra para mes <?php echo $pedido[ "data" ][ "mesanterior" ] == "1" ? "actual" : "anterior"; ?></button>
            </div>
		</div>
	</div>
</div>

<?php } 

$prods = [];
foreach( $productos as $p ){
    $prods[ $p->codigo ] = $p;
}

?>

	<script>
		var modelo 			= '<?php echo $modelo; ?>',
			usuario 		= <?php echo json_encode( $socio ) ?>,
			cat_promociones = <?php echo json_encode( PROMOCIONES ); ?>,
			cat_productos   = <?php echo json_encode( $prods ); ?>,
			metodosentrega	= <?php echo json_encode( METODOSENTREGA ) ?>,
			metodospago		= <?php echo json_encode( METODOSPAGO ) ?>,
        	almacenes	    = <?php echo json_encode( ALMACENES ) ?>,			
			pedido  		= <?php echo json_encode( $pedido ); ?>,
			pesoxbulto      = <?php echo MODELOS[ $modelo ][ "settings" ][ "pesoxbulto" ]; ?>,
			tarifas         = <?php echo json_encode( VARIABLES[ "tarifas_almacen" ][ "valor" ] ); ?>,
            pagado 		    = <?php echo $pagado; ?>,
            mesesactuales   = [ '<?php echo strtoupper( mes( date( "m" ) ) ); ?>', '<?php echo strtoupper( mes( date( "m" ) - 1 ) ); ?>' ],
            domicilios      = <?php echo json_encode( $domicilios ); ?>,
            hoy = new Date();
	</script>


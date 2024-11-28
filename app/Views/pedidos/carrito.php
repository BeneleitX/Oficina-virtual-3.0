<h4 class="mt-1 mb-0">
    <?php echo $titulo; ?> - 
    <?php echo "Pedido No. <span class=\"badge bg-marine\">{$pedido[ "referencia" ]}</span> 
    <span style=\"font-size:16px\">".estatus( $pedido[ "estatus_codigo" ])."</span>"; ?>
</h4>

<p>
    <a class="btn btn-light btn-sm" href="<?php echo base_url( "historial/".$modelo ); ?>"><i class="fa fa-undo"></i> Regresar a historial de compras</a>
</p>

<div class="row">
    <div class="col-md-6 mb-3">
    
        <?php 
        if( !$pagado && !$bloqueado && !$cancelado ){ 
            echo "\n<ul class=\"nav nav-pills my-4\">";
            
            foreach( MODELOS as $m ){
                if( $m[ "settings" ][ "efectivo" ] ){
                    echo "\n<li class=\"nav-item\">
                                <a class=\"text-{$m[ "settings" ][ "color" ]} nav-link ".( $modelo == $m[ "codigo" ] ? "text-white bg-".$m[ "settings" ][ "color" ] : "")."\" aria-current=\"page\" href=\"".base_url( "tienda/".$m[ "codigo" ] )."\">
                                    <i class=\"fa fa-{$m[ "settings" ][ "icono" ]}\"></i> {$m[ "nombre" ]}
                                </a>
                            </li>";
                }
            }
            
            echo "</ul>";
        }
        ?>

        <p>
            <?php echo $socio->avatar()." ".$socio->id( $modelo )." ".$socio->nombre( 2 ); ?>
        </p>
    </div>

    <?php 
    if( !$pagado && !$bloqueado && !$cancelado ){ 
        ?>
        
        <div class="col-md-6 mb-3 pt-4 text-end">
            <p>
                <button id="borra_todo" class="btn btn-outline-danger"><i class="fa fa-xmark"></i> Reiniciar pedido</button>
            </p>

            <table align="right">
                <tr>
                    <td class="text-end small pe-3">Puntajes acumulados por compras<br>anteriores en este mes:</td>
                    <td id="pre_puntajes">
                        <?php
                        $k = 0;

                        foreach( PROMOCIONES as $p ){
                            if( isset( $usuario->PTS[ $p[ "codigo" ] ][ "meses" ][ date( "Ym" ) ] ) and intval( $usuario->PTS[ $p[ "codigo" ] ][ "meses" ][ date( "Ym" ) ] ) > 0 ){
                                $k++;

                                echo "\n<div class=\"pts text-white bg-white\">
                                            <div class=\"pts-titulo bg-{$p[ "settings" ][ "clase" ]}\">{$p[ "settings" ][ "siglas" ]}</div>
                                            <div class=\"pts-numero bg-{$p[ "settings" ][ "clase" ]}\">{$usuario->PTS[ $p[ "codigo" ] ][ "meses" ][ date( "Ym" ) ]}</div>
                                        </div>";
                            }
                        }

                        if( !$k ){
                            echo "\n<div class=\"pts text-white bg-white\">
                                <div class=\"pts-titulo bg-gray-400\">PTS</div>
                                <div class=\"pts-numero bg-gray-400\">0</div>
                            </div>";
                        }
                        ?>
                    </td>
                </tr>
            </table>
        </div>
        
    <?php 
    }
    else{
        if( intval( $pedido[ "data" ][ "mesanterior" ] ) ){
            echo "\n<div class=\"col-12\"><div class=\"alert alert-danger\">
                        <i class=\"fa fa-circle-info\"></i> Los puntos generados por esta compra son abonados al mes anterior de la fecha de pago.
                    </div></div>";
        }
        if( $cancelado ){
            echo "\n<div class=\"col-12\"><div class=\"alert alert-danger\">
                        <i class=\"fa fa-circle-info\"></i> Este pedido fue cancelado.
                    </div></div>";
        }
        elseif( $bloqueado && !$pagado ){
            echo "\n<div class=\"col-12\"><div class=\"alert alert-warning\">
                        <i class=\"fa fa-circle-info\"></i> Este pedido está en espera de pago
                    </div></div>";
        }  
    }
    ?>
</div>

<div class="row">
	<div class="col-lg-6">
		<div id="shoppingcart">
			<?php

            if( !sizeof( $pedido[ "promociones" ] ) ){
                echo "\n<div class=\"alert alert-light text-center text-mustard\">
                            <p><i class=\"fa fa-triangle-exclamation\" style=\"font-size:200px\"></i></p>
                            <p>Hay un problema con este pedido, parece estar vacío.<br>porfavor reportalo a soporte técnico</p>
                        </div>";
            }

			foreach( PROMOCIONES as $p ){
                if( $p[ "estatus_codigo" ] == "201-ACTIVO" || isset( $pedido[ "promociones" ][ $p[ "codigo" ] ] ) ){
                    $cant_productos = isset( $pedido[ "promociones" ][ $p[ "codigo" ] ][ "productos"] ) ? sizeof( $pedido[ "promociones" ][ $p[ "codigo" ] ][ "productos"] ) : 0;

                    $evento  = "false";
                    $estatus = "false";

                   //  if( ( $p[ "settings" ][ "evento" ] ?? "false" ) == "true" ){
                        
                    if( ( $p[ "settings" ][ "evento" ] ?? "false" )  == "true" ){
                        $evento = "true";

                        if( ( $pedido[ "promociones" ][ $p[ "codigo" ] ][ "estatus" ] ?? "false" ) == "true" ){
                            $estatus = "true";
                        }
                    }
                    
                    echo "\n<div evento=\"{$evento}\" estatus=\"{$estatus}\" class=\"card mb-3 rounded-2\" style=\"overflow:hidden; ".( !$cant_productos ? " display:none" : "")."\"  promocion=\"{$p[ "codigo" ]}\">";
 
                    if( $evento == "true" ){
                        echo "\n<div style=\"position:relative;width:100%\">
                                    <img src=\"".base_url()."assets/img/promociones/{$p[ "codigo" ]}.jpg?".filemtime( "assets/img/promociones/{$p[ "codigo" ]}.jpg" )."\" class=\"img-fluid\">
                                    <div title=\"Click aquí para agregar o quitar la promoción de tu pedido\" data-bs-toggle=\"tooltip\" class=\"form-check form-switch switch-evento ".( $pagado ? "d-none" : "" )."\">
                                        <input class=\"form-check-input\" type=\"checkbox\" role=\"switch\" >
                                    </div>
                                </div>";
                    }


                    echo "<div style=\"position:relative;".( $evento == "true" ? "display:none" : "" )."\" contenido class=\"card-header text-white bg-{$p[ "settings" ][ "clase" ]} ".( $evento == "true" ? "rounded-top-0" : "" )."\">
                                    <div class=\"row\">
                                        <div class=\"col-md-5\"><h5 class=\"text-white m-0\">{$p[ "settings" ][ "nombre" ]}</h5></div>
                                        <div class=\"col-md-7\">
                                            <small conteo>{$cant_productos} productos</small>
                                            ".( $pagado || $bloqueado || $cancelado ? "" : "<button onclick=\"show_modal_productos('{$p[ "codigo" ]}')\" class=\"btn btn-sm btn-light float-end agrega_productos ".( $p[ "settings" ][ "forced" ] == "true" ? "d-none" : "" )."\"><i class=\"fa fa-plus\"></i><span xclass=\"d-none d-lg-inline\"> Agregar productos</span></button>" )."
                                        </div>
                                    </div>
                                </div>
                                
                                <table productos contenido style=\"".( $evento == "true" ? "display:none" : "" )."\" class=\"w-100\"></table>
                                
                                <div contenido class=\"card-footer bg-gray-300 text-end\" style=\"".( $evento == "true" ? "display:none" : "" )."\">
                                    <table align=\"right\">
                                        <tr>
                                            <td>Total de {$p[ "settings" ][ "nombre" ]} &nbsp; </td>
                                            <td>
                                                <h5 class=\"m-0 total_promo\">$".number_format( isset( $pedido[ "promociones" ][ $p[ "codigo" ] ][ "precio"] ) ? $pedido[ "promociones" ][ $p[ "codigo" ] ][ "precio"] : 0, 2 )."</h5>
                                            </td>
                                        </tr>
                                    </table>
                                </div>
                            </div>";
                }
			}

            if( !($pagado || $bloqueado || $cancelado )){
            if( isset( $p[ "settings" ][ "extras" ] ) ){
                echo "\n<div class=\"alert alert-info small\"><ul class=\"m-0\">";
                foreach( $p[ "settings" ][ "extras" ] as $e ){
                    echo "<li>{$e}</li>";
                }
                echo "</ul></div>";
            }


            if( $modelo == '20-TELEFONIA' ){
                echo "<a class=\"btn btn-lg my-4 col-12 btn-success\" href=\"".base_url( "beneleit_movil" )."\">¿Buscas recargas y activaciones? haz click aqui</a>";
            }
        }

            $domicilios = $socio->getDomicilios();
            $celulares  = $socio->getCelulares();
			?>
		</div>
	</div>

    <div class="col-lg-6">
        <div class="card mb-3">
            <div class="card-header bg-teal"><h5 class="mb-0 text-white">Método de Entrega</h5></div>
            <div class="card-body m-0 ">
                <?php 
                // Si no hay metodos de entrega para este modelo de negocio, no es requerido para finalizar compra
                // Al colocar metodo de entrega CELULAR a telefonía, ya todos los modelos tienen, por lo que
                // esta situación nunca debe presentarse
                if(  !sizeof( METODOSENTREGA ) ){
                    echo "<div class=\"alert alert-info m-0 text-marine\"><i class=\"fa fa-circle-info\"></i> Este pedido no requiere datos de entrega</div>";
                }

                // Si ya esta pagado, avisar que hay un problema con el pedido
                elseif( ( $bloqueado || $pagado ) && $pedido[ "metodoentrega_codigo" ] == null ){
                    echo "<div class=\"alert alert-danger m-0 text-red\"><i class=\"fa fa-warning\"></i> Este pedido no cuenta con datos de entrega</div>";
                }
        
                // botones para metodo de entrega
               // if( $pedido[ "metodoentrega_codigo" ] ){

                foreach( METODOSENTREGA as $me ){
                    // PROBLEMA PARA STAFF
                    // ocultar si no requiere almacen, si no hay domicilios o si no hay celulares

                    $metodos = [ "almacen", "efectivo", "recarga" ];

                    if( $modelo == "40-GASOLINAS" ){
                        unset( $metodos[ 0 ] );
                    }

                    if( (  $me[ "estatus_codigo" ] == "201-ACTIVO" || $pagado ) && in_Array( $me[ "settings" ][ "tipocosto" ], $metodos ) ){
                        echo "\n<input type=\"radio\" class=\"".( ( $pagado || $bloqueado || $cancelado ) && $me[ "codigo" ] != $pedido[ "metodoentrega_codigo" ] ? "d-none" : "" )." btn-check\" id=\"me-{$me[ "codigo" ]}\" autocomplete=\"off\" name=\"metodosentrega\" value=\"{$me[ "codigo" ]}\" ".( $me[ "codigo" ] == $pedido[ "metodoentrega_codigo" ] ? "checked" : "")."><label class=\"".( ( $pagado || $bloqueado || $cancelado ) && $me[ "codigo" ] != $pedido[ "metodoentrega_codigo" ] ? "d-none" : "" )." btn btn-outline-secondary col-12 mb-1\" for=\"me-{$me[ "codigo" ]}\">{$me[ "nombre" ]}</label>";
                    }
                }
                /* }
                else{
                    echo "<span class=\"text-red\"><i class=\"fa fa-warning\"></i> Este pedido aun no cuenta con información para entrega</span>";
                }      */               
            
                $pedido[ "data" ][ "entrega" ] = $pedido[ "data" ][ "entrega" ] ?? "";
                ?>
            </div>       

            <div class="card-body me_respuesta" <?php if( !$pedido[ "metodoentrega_codigo" ] ) echo "style=\"display:none\""; ?>>
                <p class="me_descripcion mb-3">
                    <?php 
                    if( $pedido[ "metodoentrega_codigo" ] && isset( METODOSENTREGA[ $pedido[ "metodoentrega_codigo" ] ] ) ){ 
                        echo METODOSENTREGA[ $pedido[ "metodoentrega_codigo" ] ][ "settings" ][ "descripcion" ];
                    }
                    ?>
                </p>

                <div class="me_formulario" mp="almacen" <?php if( substr( $pedido[ "metodoentrega_codigo" ] ?? "", 0, 2 ) != "00" ) echo "style=\"display:none\""; ?>>

                    <div class="row">
                        <div class="col-lg-6">
                            <select class="form-select" name="select_almacen">
                                <?php
                                $existe_almacen = 0;

                                foreach( ALMACENES as $a ){

                                    if( $a[ "settings" ][ "tipo" ] != "ALMACEN" ){
                                        if( !$existe_almacen && $a[ "codigo" ] == $pedido[ "data" ][ "entrega" ] ){
                                            $existe_almacen = 1;
                                        }
                                        
                                        if( ( !$pagado && !$bloqueado && !$cancelado ) || $a[ "codigo" ] == $pedido[ "data" ][ "entrega" ] )
                                        echo "\n<option ".( $a[ "codigo" ] == $pedido[ "data" ][ "entrega" ] ? "selected" : "" )." value=\"{$a[ "codigo" ]}\">{$a[ "nombre" ]}</option>";
                                    }
                                }
                                ?>
                            </select>
                        </div>

                        <div class="col-lg-6">
                            <?php 
                            if(0 && $pagado  && $bloqueado && !$entregado ){ 
                                ?>
                                <form method="post" action="<?php echo base_url("entrega"); ?>">
                                    <?php echo csrf_field(); ?>
                                    <input type="hidden" name="pedido" value="<?php echo $pedido[ "id" ]; ?>">
                                    <button type="submit" <?php echo $existe_almacen ? "" : "disabled"; ?> class="btn col-12 btn-warning">Entregar Pedido en Almacen</button>
                                </form>
                                <?php 
                            } 
                            ?>
                        </div>
                    </div>
                </div>

                <div class="me_formulario" mp="celular" <?php if( substr( $pedido[ "metodoentrega_codigo" ] ?? "", 3 ) != "CELULAR" ) echo "style=\"display:none\""; ?>>
                    
                    <?php 
                    if( substr( $pedido[ "metodoentrega_codigo" ] ?? "", 3 ) == "CELULAR" && sizeof( $celulares ) ){ 
                        ?>
                        <div class="row">
                            <div class="col-lg-6">
                            
                                <select class="form-select form-select-lg fw-bold" name="select_celular">
                                    <?php
                                    $existe_almacen = 0;

                                    foreach( $celulares as $c ){
                                        if( !$existe_almacen && $c[ "numero" ] == $pedido[ "data" ][ "entrega" ] ){
                                            $existe_almacen = 1;
                                        }
                                        
                                        if( ( !$pagado && !$bloqueado && !$cancelado ) || $c[ "numero" ] == $pedido[ "data" ][ "entrega" ] ){
                                            echo "\n<option ".( $c[ "numero" ] == $pedido[ "data" ][ "entrega" ] ? "selected" : "" )." value=\"{$c[ "numero" ]}\">{$c[ "numero" ]}</option>";
                                        }

                                        if( !$pagado &&!$bloqueado && !$cancelado && !$pedido[ "data" ][ "entrega" ] ){
                                            $pedido[ "data" ][ "entrega" ] = $c[ "numero" ];
                                        }
                                    }
                                    ?>
                                </select>
                            </div>
                        </div>
                        <?php
                    }
                    else{
                        if( ( $pagado || $bloqueado || $cancelado ) ){
                            echo "<div class=\"alert alert-danger\"><i class=\"fa fa-warning\"></i> Por el momento no se cuenta con información relacionada con el número celular al cual fue aplicado el servicio</div>";
                        }
                        else{
                            echo "\n<div class=\"alert alert-danger\">
                                        <i class=\"fa fa-warning\"></i> Para seleccionar este tipo de entrega, primero necesitas vincular un número telefónico a tu cuenta.
                                    </div>
                                    <p class=\"m-0\">
                                        <a class=\"btn btn-info\" href=\"".base_url()."perfil\"><i class=\"fa fa-mobile-retro\"></i> Ver mis números en mi perfil</a>
                                    </p>";
                        }
                    }
                    ?>
                </div>


                <div class="me_formulario" mp="tarjeta" <?php if( substr( $pedido[ "metodoentrega_codigo" ] ?? "", 3 ) != "GAS" ) echo "style=\"display:none\""; ?>>
                    
                    <?php 
                    if( substr( $pedido[ "metodoentrega_codigo" ] ?? "", 3 ) == "GAS" && sizeof( $celulares ) ){ 
                        ?>
                        <div class="row">
                            <div class="col-lg-6">
                            
                                <select class="form-select form-select-lg fw-bold" name="select_tarjeta">
                                    <?php
                                    $existe_almacen = 0;

                                    foreach( $celulares as $c ){
                                        if( !$existe_almacen && $c[ "numero" ] == $pedido[ "data" ][ "entrega" ] ){
                                            $existe_almacen = 1;
                                        }
                                        
                                        if( ( !$pagado && !$bloqueado && !$cancelado ) || $c[ "numero" ] == $pedido[ "data" ][ "entrega" ] ){
                                            echo "\n<option ".( $c[ "numero" ] == $pedido[ "data" ][ "entrega" ] ? "selected" : "" )." value=\"{$c[ "numero" ]}\">{$c[ "numero" ]}</option>";
                                        }

                                        if( !$pagado &&!$bloqueado && !$cancelado && !$pedido[ "data" ][ "entrega" ] ){
                                            $pedido[ "data" ][ "entrega" ] = $c[ "numero" ];
                                        }
                                    }
                                    ?>
                                </select>
                            </div>
                        </div>
                        <?php
                    }
                    else{
                        if( ( $pagado || $bloqueado || $cancelado ) ){
                            echo "<div class=\"alert alert-danger\"><i class=\"fa fa-warning\"></i> Por el momento no se cuenta con información relacionada con el número celular al cual fue aplicado el servicio</div>";
                        }
                        else{
                            echo "\n<div class=\"alert alert-danger\">
                                        <i class=\"fa fa-warning\"></i> Para seleccionar este tipo de entrega, primero necesitas vincular un número telefónico a tu cuenta.
                                    </div>
                                    <p class=\"m-0\">
                                        <a class=\"btn btn-info\" href=\"".base_url()."perfil\"><i class=\"fa fa-mobile-retro\"></i> Ver mis números en mi perfil</a>
                                    </p>";
                        }
                    }
                    ?>
                </div>


                <div class="me_formulario" mp="domicilio" <?php if( substr( $pedido[ "metodoentrega_codigo" ] ?? "", 3 ) != "PAQUETERIA" ) echo "style=\"display:none\""; ?>>
                    <?php 
                 
                    $dom = $usuario->data->domicilio ?? 0;

                    if( sizeof( $domicilios ) > 0 ){
                        if( ( $pagado || $bloqueado || $cancelado ) && substr( $pedido[ "metodoentrega_codigo" ] ?? "", 3 ) == "PAQUETERIA" && intval( $pedido[ "data" ][ "entrega" ] ) > 0 ){
                            if( isset( $pedido[ "data" ][ "entrega" ] )){

                                $domicilios[ 0 ] = isset( $pedido[ "data" ][ "domicilio" ] ) && is_array( $pedido[ "data" ][ "domicilio" ] ) ? $pedido[ "data" ][ "domicilio" ] : $domicilios[ $pedido[ "data" ][ "entrega" ] ];
                                
                                $dom = 0;
                            }
                            elseif( ( $pedido[ "data" ][ "entrega_xpace" ] ?? 0 ) > 0 ){
                                $dom = $pedido[ "data" ][ "entrega" ];
                            }

                            $d   = $domicilios[ $dom ];
                        }
                        else{
                            if( intval( $pedido[ "data" ][ "entrega" ] ) > 0 && isset( $domicilios[ $pedido[ "data" ][ "entrega" ] ] ) ){
                                $dom = intval( $pedido[ "data" ][ "entrega" ] );
                                
                            }
                            else{
                                $dom = array_keys( $domicilios )[ 0 ];

                                if( substr( $pedido[ "metodoentrega_codigo" ] ?? "", 3 ) == "PAQUETERIA" ){ 
                                    $pedido[ "data" ][ "entrega" ]   = $dom;
                                    $pedido[ "data" ][ "domicilio" ] = $domicilios[ $dom ];
                                    model( "PedidoModel" )->save( $pedido );
                                }
                            }

                            $d   = $domicilios[ $dom ];
                            $pedido[ "data" ][ "domicilio" ] = $d;
                        }

                        echo "\n<div domicilio_id=\"{$d[ "id" ]}\" class=\"card ".( $d[ "colonia" ] ? "border-teal text-teal mb-3" : "border-red text-red" )." text-start p-2\">
                                    <p><strong>{$d[ "nombre" ]}</strong></p>
                                    
                                    <p>{$d[ "calleynumero" ]}<br>
                                    Colonia ".( $d[ "colonia" ] ?? "DESCONOCIDA * Domicilio con errores" )."<br>
                                    ".( $d[ "colonia" ] ? "
                                    {$d[ "localidad" ]}, {$d[ "entidad" ]}<br>
                                    C.P. {$d[ "codigopostal" ]} " : "" )."</p>
                                    <p>".( strlen( trim( $d[ "referencias" ] ) ) ? $d[ "referencias" ] : "<div class=\"alert alert-warning\"><i class=\"fa fa-warning\"></i> Tu domicilio no tiene referencias, es MUY IMPORTANTE que nos proporciones este dato para darte un mejor servicio de entrega. Antes de finalizar tu compra, ingresa a la sección de MI PERFIL y edita el domicilio.</div>" )."</p>
                                    <p><i class=\"fa fa-mobile-retro\"></i> {$socio->telefono}</p>
                                </div>";
                    }
                    else{
                        echo "\n<div domicilio_id=\"0\" class=\"alert alert-danger\">
                                    <i class=\"fa fa-warning\"></i> Para utilizar paquetería como tipo de entrega, primero necesitas dar de alta un domicilio.
                                </div>";

                    }
                    ?>
                    <div class="row">           
                        <?php if( ( $pagado || $bloqueado || $cancelado ) && substr( $pedido[ "metodoentrega_codigo" ] ?? "", 3 ) == "PAQUETERIA" ){ 
                            if( $entregado ){ 
                                ?>
                                <div class="col-12">
                                    <div class="alert alert-info m-0">
                                        Este pedido ha sido enviado por paquetería <?php echo $pedido[ "fechas" ][ "enviado" ] ? " con fecha de ".date( "d-m-Y", strtotime( $pedido[ "fechas" ][ "enviado" ] ) ) : ""; ?>.
                                        <br>Guía de rastreo: 
                                        <span class="badge bg-marine fs-5"><?php echo $pedido[ "data" ][ "guia" ] ?? ""; ?></span>
                                        <?php if( 1 ){
                                            echo "<button data-bs-toggle=\"tooltip\" title=\"Editar guía\" class=\"btn btn-warning btn-sm\" onclick=\"$( '#edita_guia' ).modal( 'show' )\"><i class=\"fa fa-edit\"></i></button>";
                                        }?>
                                    </div>
                                </div>
                                <?php 
                            }
                            elseif( 0 && $d[ "colonia" ] ?? 0 ) {
                                ?>
                                <div class="col-lg-6">
                                    <form method="post" action="<?php echo base_url("envia"); ?>">
                                        <?php echo csrf_field(); ?>
                                        <input type="hidden" name="pedido" value="<?php echo $pedido[ "id" ]; ?>">
                                        <button type="submit" <?php echo $existe_almacen ? "" : "disabled"; ?>  class="btn col-12 btn-warning">Enviar pedido por paquetería</button>
                                    </form>
                                </div>
                                <?php 
                            } 
                        }
                        else{ 
                            ?>
                            <div class="col-lg-6">
                                <button class="btn btn-secondary col-12" onclick="$( '#modal_domicilios' ).modal( 'show' )">Ver mis domicilios</button>
                            </div>
                            <?php 
                        } 
                        ?>
                    </div>
                </div>

                <div class="alert alert-danger mt-3" id="no_stock" style="display:none">
                    <p><i class="fa fa-warning"></i> El almacen no cuenta con producto suficiente para surtir tu pedido</p>
                    <ul class="m-0"></ul>
                </div>

                <?php 
                switch( substr( $pedido[ "metodoentrega_codigo" ] ?? "", 3 ) ){
                    case "ALMACEN" : 
                        if( !isset( ALMACENES[ $pedido[ "data" ][ "entrega" ] ] ) ){
                            $pedido[ "data" ][ "entrega" ] = (substr( $modelo, 0, 1 ) - 1 )."11-OFICINAS";
                        }

                        $costoentrega = $pedido[ "data" ][ "entrega" ] ? VARIABLES[ "tarifas_almacen" ][ "valor" ][ ALMACENES[ $pedido[ "data" ][ "entrega" ] ][ "settings" ][ "tarifa" ] ] : 0; 
                        break;
 
                    case "CELULAR" : 
                        $costoentrega = 0; 
                        break;
 
                    case "CELULAR" : 
                        $costoentrega = 0; 
                        break;
 
                    default : 
                        $costoentrega = $pedido[ "metodoentrega_codigo" ] ? METODOSENTREGA[ $pedido[ "metodoentrega_codigo" ] ][ "settings" ][ "costo" ] : 0;
                }

                if( $costoentrega && !( $pagado || $bloqueado || $cancelado ) ){ 
                    ?>
                    <div class="alert p-2 alert-info me_costo mt-3 mb-0" <?php if( !$pedido[ "metodoentrega_codigo" ] ) echo "style=\"display:none\""; ?>>
                        <?php if( $pedido[ "metodoentrega_codigo" ] ) echo "Utilizar este método de entrega, genera un costo de $".number_format( $costoentrega, 2 ); ?>
                    </div>
                    <?php 
                } 
                ?>
            </div>

            <?php
            /* if( !sizeof( $domicilios ) && !( $pagado  || $cancelado ) ){
                echo "<div domicilio_id=\"0\" class=\"alert alert-danger m-3 mt-0\"><i class=\"fa fa-warning\"></i> Para utilizar paquetería como tipo de entrega, primero necesitas dar de alta un domicilio. Puedes editar tus domicilios o agregar uno nuevo desde tu perfil de socio.<p class=\"mt-3 mb-0\"><a class=\"btn btn-danger btn-sm\" href=\"".base_url( "perfil" )."\">Ir a perfil de socio</a></p></div>";
            } */
            ?>

        </div>

        <div id="puntajes" class="mb-3">
            <?php
            if( ( $pagado || $bloqueado || $cancelado ) ){
                foreach( PROMOCIONES as $p ){
                    if( isset( $pedido[ "PTS" ][ $p[ "codigo" ] ] ) and $pedido[ "PTS" ][ $p[ "codigo" ] ] > 0 ){
                        echo "\n<div class=\"pts text-white bg-white\">
                                    <div class=\"pts-titulo bg-{$p[ "settings" ][ "clase" ]}\">{$p[ "settings" ][ "siglas" ]}</div>
                                    <div class=\"pts-numero bg-{$p[ "settings" ][ "clase" ]}\">{$pedido[ "PTS" ][ $p[ "codigo" ] ]}</div>
                                </div>";
                    }
                }
            }
            ?>
        </div>

        <?php 
        $pc = $socio->getPrimerCompra( $modelo );

        if( date( "d" ) < 6 && $modelo == '10-NUTRICION' &&  !( $pagado || $bloqueado || $cancelado ) && $pc && date( "Ym" ) > date( "Ym", strtotime( $pc ) ) ){ 
            ?>
            <div id="alert_anterior" class="alert alert-<?php echo intval( $pedido[ "data" ][ "mesanterior" ] ) ? "danger" : "info"; ?>">
                <i class="fa fa-circle-info"></i> Los puntos de este pedido aplican para el mes de 
                <div class="input-group mb-0 input-group-sm" style="display:inline-flex; width:auto">
                    <span style="font-weight:bold" class="input-group-text <?php if( intval( $pedido[ "data" ][ "mesanterior" ] ) ) echo "bg-red border-red"; ?>" id="mescalifica">
                        <?php echo strtoupper( mes( date( "m" ) - intval( $pedido[ "data" ][ "mesanterior" ] ) ) ); ?>
                    </span>
                    <button onclick="$( '#mes_califica' ).modal( 'show' )" class="btn btn-outline-info btn-sm">Cambiar</buttn>
                </div> 
            </div>
            <?php 
        } 
        ?>

        <div class="row">
            <div class="col-lg-6">
                <div class="card mb-3" style="overflow:hidden">
                    <table class="table rounded-3 m-0">
                        <tr>
                            <td valign="middle" class="">Total de productos</td>
                            
                            <td valign="middle" class="text-end">
                                <h5 class="m-0 text-teal" total_productos="<?php echo $pedido[ "data" ][ "total" ]; ?>">
                                    $<?php echo number_format( $pedido[ "data" ][ "total" ], 2 ); ?>
                                </h5>
                            </td>
                        </tr>
                        
                        <tr class="<?php echo sizeof( METODOSENTREGA ) ? "" : "d-none"; ?>">
                            <td valign="middle" class="">
                                Gastos de entrega <span id="bultos_cantidad"></span> <br>
                                <div class="row g-1" id="bultos" style="margin-top:1px"></div>
                            </td>
                            
                            <td valign="middle" class="text-end">
                                <h5 class="m-0 text-teal" total_entrega="<?php echo number_format( $pedido[ "data" ][ "comisionentrega" ], 2 ); ?>">
                                    $<?php echo number_format( $pedido[ "data" ][ "comisionentrega" ], 2 ); ?>
                                </h5>
                            </td>
                        </tr>
                        
                        <?php
                        if( ( $pagado || $cancelado ) ){
                            $saldo = $pedido[ "data"][ "saldo" ] ?? 0;
                        }
                        else{
                            $saldo = $socio->data->saldo->{$modelo}->estatus ? ( $socio->data->saldo->{$modelo}->cantidad ?? 0 ) : 0;
                        }

                        if( $saldo == "" ) $saldo = 0;

                        ?>

                        <tr>
                            <td valign="middle" class="">Saldo a favor</td>
                            
                            <td valign="middle" class="text-end">
                                <h5 class="m-0 text-<?php echo $saldo > 0 ? "red" : "gray-500"; ?>" total_saldo="<?php echo $saldo; ?>">
                                    $<?php echo number_format(  $saldo, 2 ); ?>
                                </h5>
                            </td>
                        </tr>
                        
                        <?php 
                        $comisionbanco = floatval( $pedido[ "data"][ "comisionbanco" ] ); //!( $pagado || $cancelado ) ? $pedido[ "data"][ "comisionbanco" ] : 0;
                        
                        if( ( $pagado || $bloqueado || $cancelado ) ){ 
                            ?>
                            <tr>
                                <td valign="middle" class="">Comision bancaria</td>
                                
                                <td valign="middle" class="text-end">
                                    <h5 class="m-0 text-teal">$<?php echo number_format(  $comisionbanco, 2 ); ?></h5>
                                </td>
                            </tr>
                            <?php 
                        } 

                        $tt2 = $pedido[ "data" ][ "total" ] + $pedido[ "data" ][ "comisionentrega" ] - $saldo;
                        ?>

                        <tr>
                            <td valign="middle" class="text-white" style="background:var(--bs-marine) !important">Total de pedido</td>
                            
                            <td valign="middle" class="text-end" style="background:var(--bs-marine) !important">
                                <h5 class="text-white my-0" gran_total="<?php echo $tt = $pedido[ "data" ][ "total" ] + $comisionbanco + $pedido[ "data" ][ "comisionentrega" ] - ( $pagado ? 0 : $saldo ); ?>">
                                    $<?php echo number_format( $tt, 2 ); ?>
                                </h5>
                            </td>
                        </tr>
                    </table>
                </div>
            </div>

            <div class="col-lg-6">
                <form method="post" action="<?php echo base_url( "checkout" ); ?>">
                    <?php echo csrf_field(); ?>
            
                    <input type="hidden" name="pedido" value="<?php echo $pedido[ "id" ]; ?>">

                    <?php 
                    if( $pagado ){
                        ?>
                        <div class="card mb-3" style="overflow:hidden">
                            <table class="table rounded-3 m-0">
                            <tr>
                                    <td valign="middle" class="">Fecha de pago</td>
                                    
                                    <td valign="middle" class="text-end">
                                        <h5 class="m-0">
                                            <?php echo $pedido[ "fechas" ][ "pagado" ] ? date( "d-m-Y", strtotime( $pedido[ "fechas" ][ "pagado" ] ) ) : ""; ?>
                                        </h5>
                                    </td>
                                </tr>
                                <tr>
                                    <td valign="middle" class="" nowrap>Método de pago</td>
                                    
                                    <td valign="middle" class="text-end">
                                        <h5 class="m-0">
                                            <?php 
                                            $mp = METODOSPAGO[ $pedido[ "metodopago_codigo" ] ];
                                            echo file_exists($file = "assets/img/metodospago/{$mp[ "codigo" ]}.png" ) ? "<img class=\"w-50 rounded\" src=\"".base_url()."{$file}\">" : "<span class=\"badge bg-gray-600\">".$pedido[ "metodopago_codigo" ]."</span>"; ?>
                                        </h5>
                                    </td>
                                </tr>                                

                                <tr>
                                    <td valign="middle" style="<?php if( intval( $pedido[ "data" ][ "mesanterior" ] ) ) echo "background:red; color:white"; ?>">Calificación</td>
                                    
                                    <td style="<?php if( intval( $pedido[ "data" ][ "mesanterior" ] ) ) echo "background:red"; ?>" valign="middle" class="text-end">
                                        <span class="badge bg-teal" style="<?php if( intval( $pedido[ "data" ][ "mesanterior" ] ) ) echo "color:white"; ?>">
                                            <?php echo strtoupper( mes(substr( $pedido[ "fechas" ][ "califica" ], 5, 2 ) ) )." ".substr( $pedido[ "fechas" ][ "califica" ], 0, 4 ); ?>
                                        </span>
                                    </td>
                                </tr>

                                <tr>
                                    <td valign="middle" style="<?php if( intval( $pedido[ "data" ][ "mesanterior" ] ) ) echo "background:red; color:white"; ?>">Pago comisiones</td>
                                    
                                    <td style="<?php if( intval( $pedido[ "data" ][ "mesanterior" ] ) ) echo "background:red"; ?>" valign="middle" class="text-end">
                                        
                                            <small><?php
                                            $periodo = model( "PeriodoModel" )->find( codigo_periodo( $pedido[ "modelo_codigo" ], $pedido[ "fechas" ][ "reparte" ] ) );

                                            if( isset( $periodo[ "estatus_codigo" ] ) and $periodo[ "estatus_codigo" ] ){
                                                echo estatus( $periodo[ "estatus_codigo" ] );
                                            }
                                            ?></small>
                                        <span class="badge bg-marine">
                                            <?php echo date( "W-Y", strtotime( $pedido[ "fechas" ][ "reparte" ] ) ); ?>
                                        </span>
                                        
                                    </td>
                                </tr>                                
                            </table>
                        </div>

                        <a href="<?php echo base_url( "ticket/".urlencode( $link ) ); ?>" target="_new" class="mb-3 btn btn-primary col-12 <?php echo 1 || $existe_almacen ? "" : "disabled"; ?> " id="imprime">Imprimir ticket</a>
                        <?php
                    }
                    elseif( $cancelado ){
                        ?>
                        <div class="card mb-3" style="overflow:hidden">
                            <table class="table rounded-3 m-0">
                                <tr>
                                    <td valign="middle" class="">Cancelación</td>
                                    
                                    <td valign="middle" class="text-end">
                                        <h5 class="m-0">
                                            <?php echo $pedido[ "fechas" ][ "cancela" ] ? date( "d-m-Y", strtotime( $pedido[ "fechas" ][ "cancela" ] ) ) : ""; ?>
                                        </h5>
                                    </td>
                                </tr>
                            </table>
                        </div>
                        <?php
                    }
                    else{
                        foreach( METODOSPAGO as $mp ){
                            $imagen = "";
                            $boton  = "";
                            if( !isset( $mp[ "settings" ][ "tipocomision" ] ) ){
                                $mp[ "settings" ][ "tipocomision" ] = "";
                            }

                            if( $mp[ "settings" ][ "tipocomision" ] != "saldo" || $socio->data->saldo->{$modelo}->estatus == 1 ){
                                if( ( !$bloqueado || $mp[ "codigo" ] == $pedido[ "metodopago_codigo" ] ) && $mp[ "estatus_codigo" ] == "201-ACTIVO" ){
                                    $boton .= "\n<button class=\"btn col-12 m-0 rounded-bottom-0";

                                    if( $mp[ "settings" ][ "tipocomision" ] == "saldo" ){
                                        $boton .= " btn-warning ";

                                        if( !$socio->data->saldo->{$modelo}->estatus || $socio->data->saldo->{$modelo}->cantidad < ($tt + $socio->data->saldo->{$modelo}->cantidad) ){
                                            // $boton .= " d-none ";
                                        }
                                    }else{
                                        $boton .= " btn-primary ";

                                        $imagen = file_exists($file = "assets/img/metodospago/{$mp[ "codigo" ]}.png" ) ? "<img class=\"img-fluid mb-3 rounded-bottom-1\" src=\"".base_url()."{$file}\" metodopago=\"{$mp[ "codigo" ]}\" style=\"zoom:2; display:none\">" : "<div class=\"mb-3\"></div>";
                                    } 
                                    
                                    $boton .= "\" type=\"submit\" name=\"metodopago\" value=\"{$mp[ "codigo" ]}\" style=\"line-height: 0.9; display:none\">{$mp[ "nombre" ]}<br><span class=\"small costo_extra text-marine\">$".number_format( $comisionbanco, 2 )."}</span><h4 class=\"cantidad m-0 mt-1 text-white\">$".number_format( $tt, 2 )."</h4></button>";                      
                                }

                                echo $boton.$imagen;
                            }
                        }

                        echo "<div class=\"alert alert-danger\" id=\"no_pago\"><i class=\"fa fa-bug\"></i> ATENCION: No es posible mostrar metodos de pago disponibles. Favor de contactar a soporte</div>";
                    }

                    
                    ?>
                </form>

                <?php 
                if( $bloqueado && !$pagado ){
                    echo "\n<button class=\"btn btn-warning mb-2 col-12\" onclick=\"$( '#cambia_edicion' ).modal( 'show' );\"><i class=\"fa fa-undo\"></i> Regresar a editar pedido</button>";
                
                    echo "\n<button class=\"btn btn-danger mb-2 col-12\" onclick=\"$( '#cancela_pedido2' ).modal( 'show' );\"><i class=\"fa fa-trash\"></i> Cancelar pedido</button>";
                } ?>
            </div>
        </div>
    </div>
</div>

<?php 
if( $this->data[ "usuario" ]->permiso( "28-INGRESA" ) || $this->data[ "usuario" ]->permiso( "40-ADMIN" ) ){
    if( $pagado ){
        echo "\n<div class=\"card mb-5\">
                    <div class=\"card-header bg-blue\">
                        <h5 class=\"m-0 text-white\">Comisiones generadas por esta compra</h5>
                    </div>
                    
                    <table class=\"table m-0\">
                        <thead><tr>
                            <th class=\"text-center\">Folio</th>
                            <th>Esquema</th>
                            <th>Nivel</th>
                            <th class=\"text-end\">Comisión</th>
                            <th>Estatus</th>
                            <th>Socio</th>
                        </tr></thead>
                        
                        <tbody>";  
                        
        $db = db_connect();

        $comisiones = $db->query( "select * from t_comisiones where pedido_id = {$pedido[ "id" ]}" )->getResult();

        foreach( $comisiones as $c ){
            $u = $c->usuario_id ? model( "UsuarioModel" )->find( $c->usuario_id ) : "SIN RECEPTOR";

            echo "\n<tr class=\"".( substr( $c->estatus_codigo, 0, 3 ) < 200 ? "opaco" : "" )."\">
                        <td class=\"text-center\"><span class=\"badge bg-marine\">{$c->id}</span></td>
                        <td>".ESQUEMAS[ $c->esquema_codigo ][ "settings" ][ "titulo" ]."</td>
                        <td><strong>{$c->nivel}</strong> ".($c->compresion ? "<span class=\"badge  border border-red text-red\">Compresion</span>" : "")."</td>
                        <td class=\"text-end\">".( in_array( ESQUEMAS[ $c->esquema_codigo ][ "settings" ][ "reparto" ], [ "efectivo", "porcentaje" ] ) ? "$".number_format( $c->cantidad, 2 ) : " ".( ESQUEMAS[ $c->esquema_codigo ][ "settings" ][ "reparto" ] == "estrellas" ? ( $c->cantidad == 1 ? "<i class=\"fa fa-star text-amber\"></i>" : "<i class=\"fa fa-star text-amber\"></i><i class=\"fa fa-star text-amber\"></i>") : number_format( $c->cantidad )." <i class=\"fa fa-tag text-pink\"></i>Promos" ) )."</td>
                        <td>".estatus( $c->estatus_codigo )." ".( $c->periodo_codigo ? "<span class=\"badge bg-marine\">".periodo( $c->periodo_codigo )."</span>" : "")."</td>
                        <td>".( isset($u->id) ? $u->avatar(25)." ".$u->id( $modelo )." ".$u->nombre( 2 ) : $u )."</td>
                    </tr>"; 
        }

        echo "</tbody></table></div>"; 
    }
    ?>

    <div class="alert alert-danger">
        <table>
            <tr>
                <td valign="top"><i class="fa fa-circle-radiation" style="font-size:32px"></i></td>
                <td><ul class="m-0">
                    <li>Modificar los siguientes parámetros puede ocasionar que las calificaciones y comisiones generadas por este pedido sufran cambios permanentes.</li>
                    <li>Una vez cerrado el periodo al que pertenece la fecha de compra, estas opciones serán bloqueadas.</li>
                </ul></td>
            </tr>
        </table>
    </div>  

    <div class="card border-red my-3">
        <div class="card-header">
            <h5 class="text-red mb-0">Administración</h5>
        </div>

        <div class="card-body">
            <div class="row">

                <div class="col-lg-4 m-0">
                    <button class="btn btn-danger col-12" onclick="$( '#cancela_pedido' ).modal( 'show' );"><i class="fa fa-trash"></i> Cancelar pedido</button>
                </div>

                <?php 
                if( $pagado ){ 
                    ?>
                    <div class="col-lg-4 m-0">
                        <button class="btn btn-danger col-12" onclick="$( '#cambia_fecha' ).modal( 'show' );"><i class="fa fa-calendar"></i> Cambiar fecha de compra</button>
                    </div>

                    <div class="col-lg-4 m-0">
                        <form method="post" action="<?php echo base_url( "reparte" ); ?>">
                            <?php echo csrf_field(); ?>
                            <button type="submit" class="btn btn-danger col-12"><i class="fa fa-calculator"></i> Recalcular comisiones</button>
                            <input type="hidden" name="pedido" value="<?php echo $pedido[ "id" ]; ?>">
                        </form>
                    </div>
                    <?php 
                } 
                else{
                    ?>
                    <div class="col-lg-4 m-0">
                        <button class="btn btn-danger col-12" onclick="$( '#marca_pagado' ).modal( 'show' );"><i class="fa fa-cash-register"></i> Marcar como pagado</button>
                    </div>
                    <?php
                }
                ?>
            </div>
        </div>
    </div>

    <div class="modal" tabindex="-1" id="cambia_fecha">
        <div class="modal-dialog">
            <div class="modal-content">
                <form method="post" action="<?php echo base_url( "cambia_fecha" ); ?>">
                    <?php echo csrf_field() ?>
                    <input type="hidden" name="socio"  value="<?php echo $socio->id; ?>">
                    <input type="hidden" name="pedido" value="<?php echo $pedido[ "id" ]; ?>">
                    <input type="hidden" name="old_beneficiario"  value="">

                    <div class="modal-header">
                        <h5 class="modal-title"><i class="fa fa-calendar"></i> Cambiar fecha de compra</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="alert alert-danger mb-3"><ul class="m-0">
                            <li>Los beneficios generados por este pedido, aplicarán para las calificaciones del mes al que corresponde la fecha que se aplique.</li>
                            <li>Un cambio de fecha de calificación altera la calificación del socio.</li>
                        </ul></div>

                        <label class="form-label">Actualiza la fecha y guarda los cambios usando el botón</label>
                        <div class="row">
                            <div class="col-6">
                                
                                <input class="form-control col-6" type="date" name="nueva" value="<?php echo substr( $pedido[ "fechas" ][ "califica" ] ?? "", 0, 10 ) ?>">
                            </div>
                        </div>
                    </div>

                    <div class="modal-footer">
                        <button type="submit" class="btn btn-<?php echo $pedido[ "data" ][ "mesanterior" ] == "1" ? "info" : "danger"; ?>">Aplicar cambios</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="modal" tabindex="-1" id="marca_pagado">
        <div class="modal-dialog">
            <div class="modal-content" style="position:relative; xoverflow:hidden">
                <img src="<?php echo base_url(); ?>assets/img/gatos/cat4.png" style="position:absolute; bottom:-17px; left:-10px; width:200px; z-index:1">
                <form method="post" action="<?php echo base_url( "paga_pedido" ); ?>">
                    <?php echo csrf_field() ?>
                    <input type="hidden" name="pedido" value="<?php echo $pedido[ "id" ]; ?>">

                    <div class="modal-header bg-red">
                        <h5 class="modal-title text-white"><i class="fa fa-cash-register"></i> Marcar pedido como pagado</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">

                        <h1 class="display-4 text-center mb-4"><span class="badge bg-light text-teal" id="calcula_total">$<?php echo number_format( $tt2, 2 ) ?></span></h1>
                        <label class="form-label"><strong>Elige el tipo de pago:</strong></label>
                                <select class="form-select mb-3" name="metodopago" id="calcula_pago">
                                    <?php

                                    $pago = METODOSPAGO[ "8".substr( $modelo, 0, 1 )."-DIRECTO" ];
                                    echo "\n<option tipo=\"{$pago[ "settings" ][ "tipocomision" ]}\" cantidad=\"{$pago[ "settings" ][ "comision" ]}\" value=\"{$pago[ "codigo" ]}\" selected>{$pago[ "settings" ][ "descripcion" ]} | Comisión: ".( $pago[ "settings" ][ "tipocomision" ] == "porcentaje" ? "" : "$" ).number_format( $pago[ "settings" ][ "comision" ], 2 ).( $pago[ "settings" ][ "tipocomision" ] == "porcentaje" ? "%" : "")."</option>"; 

                                    $pago = METODOSPAGO[ "9".substr( $modelo, 0, 1 )."-TERMINAL" ];
                                    echo "\n<option tipo=\"{$pago[ "settings" ][ "tipocomision" ]}\" cantidad=\"{$pago[ "settings" ][ "comision" ]}\" value=\"{$pago[ "codigo" ]}\">{$pago[ "settings" ][ "descripcion" ]} | Comisión: ".( $pago[ "settings" ][ "tipocomision" ] == "porcentaje" ? "" : "$" ).number_format( $pago[ "settings" ][ "comision" ], 2 ).( $pago[ "settings" ][ "tipocomision" ] == "porcentaje" ? "%" : "")."</option>"; 

                                    $pago = METODOSPAGO[ "1".substr( $modelo, 0, 1 )."-REFERENCIA" ];
                                    echo "\n<option tipo=\"{$pago[ "settings" ][ "tipocomision" ]}\" cantidad=\"{$pago[ "settings" ][ "comision" ]}\" value=\"{$pago[ "codigo" ]}\">{$pago[ "settings" ][ "descripcion" ]} | Comisión: ".( $pago[ "settings" ][ "tipocomision" ] == "porcentaje" ? "" : "$" ).number_format( $pago[ "settings" ][ "comision" ], 2 ).( $pago[ "settings" ][ "tipocomision" ] == "porcentaje" ? "%" : "")."</option>"; 

                                        ?>
                                </select>


                        <div class="alert alert-warning mb-0">
                            <div class="row mb-3">
                                <label class="col-5 col-form-label text-end">Fecha de pago</label>
                                <div class="col-6">
                                    <input class="form-control" type="date" name="fecha_pagado" value="<?php echo date( "Y-m-d" ); ?>">
                                </div>
                            </div>

                            <div class="row mb-3">
                                <label class="col-5 col-form-label text-end">Calificación</label>
                                <div class="col-6">
                                    <input class="form-control" type="date" name="fecha_califica" value="<?php echo date( "Y-m-d" ); ?>">
                                </div>
                            </div>

                            <div class="row mb-3">
                                <label class="col-5 col-form-label text-end">Reparto de comisiones</label>
                                <div class="col-6">
                                    <input class="form-control" type="date" name="fecha_reparte" value="<?php echo date( "Y-m-d" ); ?>">
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="modal-footer">
                        <button type="submit" class="btn btn-<?php echo $pedido[ "data" ][ "mesanterior" ] == "1" ? "info" : "danger"; ?>">Marcar pagado</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="modal" tabindex="-1" id="cancela_pedido">
        <div class="modal-dialog">
            <div class="modal-content">
                <form method="post" action="<?php echo base_url( "cancela_pedido" ); ?>">
                    <?php echo csrf_field() ?>
                    <input type="hidden" name="socio"  value="<?php echo $socio->id; ?>">
                    <input type="hidden" name="old_beneficiario"  value="">

                    <div class="modal-header">
                        <h5 class="modal-title"><i class="fa fa-xmark"></i> Cancelar pedido</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="alert alert-danger m-0"><ul class="m-0">
                            <li>Al cancelar el pedido se eliminarán todas las comisiones y puntos generados para calificación y recompensas.</li>
                            <li>Cancelar un pedido ya pagado altera la calificación del socio, por lo que se debe hacer un corte parcial para ajustar las comisiones recibidas y generadas por el socio.</li>
                        </ul></div>

                        <?php echo csrf_field(); ?>
                        <input type="hidden" name="pedido" value="<?php echo $pedido[ "id" ]; ?>">
                    </div>

                    <div class="modal-footer">
                        <button type="submit" class="btn btn-<?php echo $pedido[ "data" ][ "mesanterior" ] == "1" ? "info" : "danger"; ?>">Cancelar pedido</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <?php 
}
?>

<div class="modal" tabindex="-1" id="cambia_edicion">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="post" action="<?php echo base_url( "cambia_edicion" ); ?>">
                <?php echo csrf_field() ?>
                <input type="hidden" name="pedido" value="<?php echo $pedido[ "id" ]; ?>">

                <div class="modal-header bg-mustard">
                    <h5 class="modal-title text-white"><i class="fa fa-undo"></i> Regresar a edición de pedido</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>

                <div class="modal-body">
                Al regresar a su modo de edición, se podrán modificar las camtidades de productos, metodos de pago y de entrega.
                </div>

                <div class="modal-footer">
                    <button type="submit" class="btn btn-warning">Continuar</button>
                </div>
            </form>
        </div>
    </div>
</div>

<div class="modal" tabindex="-1" id="edita_guia">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="post" action="<?php echo base_url( "edita_guia" ); ?>">
                <?php echo csrf_field() ?>
                <input type="hidden" name="pedido" value="<?php echo $pedido[ "id" ]; ?>">

                <div class="modal-header bg-mustard">
                    <h5 class="modal-title text-white"><i class="fa fa-edit"></i> Editar guía de rastreo</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>

                <div class="modal-body">
                <p>Verificar que la información sea correcta antes de guardar los datos.</p>
                <input class="form-control text-center" name="guia_nueva" value="<?php echo $pedido[ "data" ][ "guia" ] ?? ""; ?>">
                </div>
                
                <div class="modal-footer">
                    <button type="submit" class="btn btn-warning">Continuar</button>
                </div>
            </form>
        </div>
    </div>
</div>


<div class="modal" tabindex="-1" id="cancela_pedido2">
        <div class="modal-dialog">
            <div class="modal-content">
                <form method="post" action="<?php echo base_url( "cancela_pedido" ); ?>">
                    <?php echo csrf_field() ?>

                    <div class="modal-header">
                        <h5 class="modal-title"><i class="fa fa-xmark"></i> Cancelar pedido</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="alert alert-danger m-0"><ul class="m-0">
                            <li>Al cancelar el pedido se liberarán todas las promociones incluídas por acumulación de puntos, como bono de lealtad o productos de regalo. Para obtenerlas deberá crear un nuevo pedido.</li>
                            <li>Esta acción no es reversible.</li>
                        </ul></div>

                        <?php echo csrf_field(); ?>
                        <input type="hidden" name="pedido" value="<?php echo $pedido[ "id" ]; ?>">
                    </div>

                    <div class="modal-footer">
                        <button type="submit" class="btn btn-danger">Cancelar pedido</button>
                    </div>
                </form>
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
                if( sizeof( $domicilios ) && substr( $pedido[ "metodoentrega_codigo" ], 3 ) == "PAQUETERIA" ){

                foreach( $domicilios as $d ){
                    echo "\n<button domicilio_id=\"{$d[ "id" ]}\" class=\"w-100 btn btn-outline-success text-start mb-3\"><p><strong>{$d[ "nombre" ]}</strong></p>
                        {$d[ "calleynumero" ]}<br>
                        Colonia {$d[ "colonia" ]}<br>
                        {$d[ "localidad" ]}, {$d[ "entidad" ]}<br>
                        C.P. {$d[ "codigopostal" ]}
                        </button>";
                }
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
	<div class="modal-dialog modal-xl">
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

                        $pts = $p->data->puntos->{"010-DISTRIBUIDOR"} ?? 0;

                        if( substr( $p->estatus_codigo, 0, 3 ) > 200 ){
						    echo "\n<div class=\"col-lg-6 col-xl-4\" producto=\"{$p->codigo}\"><div class=\"card mb-3 boton\" title=\"Click para agregar al pedido\" onclick=\"agrega_producto( '{$p->codigo}' )\" style=\"position:relative\"><div class=\"badge puntos bg-gray-500\" style=\"position:absolute; right:10px; top:10px\">".number_format( $pts, 1 )."<br>pts</div><div class=\"row g-0\"><div class=\"col-2 pt-3 ps-3\"><img src=\"".base_url()."assets/img/productos/".( $p->data->avatar ? $p->codigo : "NO-IMAGEN" ).".png\" class=\"img-fluid rounded\"></div><div class=\"col-10\"><div class=\"card-body pt-3\"><h5 class=\"mb-1\">".strtoupper( $p->data->nombre )."</h5><p class=\"small m-0\">{$p->data->descripcion}</p></div></div></div></div></div>";
                        }
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

<?php 
$pedido[ "no_stock" ] = false;
$prods = [];
foreach( $productos as $p ){
    $prods[ $p->codigo ] = $p;
}

?>

<script>
    
    var modelo 			= '<?php echo $modelo; ?>',
        es_primermes    = <?php echo !$pc || date( "Ym", strtotime( $pc ) ) == date( "Ym" ) ? "true" : "false"; ?>,
        usuario 		= <?php echo json_encode( $socio->getDatos() ) ?>,
        cat_promociones = <?php echo json_encode( PROMOCIONES ); ?>,
        cat_productos   = <?php echo json_encode( $prods ); ?>,
        metodosentrega	= <?php echo json_encode( METODOSENTREGA ) ?>,
        metodospago		= <?php echo json_encode( METODOSPAGO ) ?>,
        premieres       = <?php echo sizeof( $socio->getPremieres() ); ?>,
        almacenes	    = <?php echo json_encode( ALMACENES ) ?>,			
        pedido  		= <?php echo json_encode( $pedido ); ?>,
        pesoxbulto      = <?php echo MODELOS[ $modelo ][ "settings" ][ "pesoxbulto" ]; ?>,
        tarifas         = <?php echo json_encode( VARIABLES[ "tarifas_almacen" ][ "valor" ] ); ?>,
        pagado 		    = <?php echo $pagado; ?>,
        total_pedido    = <?php echo $tt2; ?>,
        bloqueado 	    = <?php echo $bloqueado; ?>,
        cancelado 	    = <?php echo $cancelado; ?>,
        mesesactuales   = [ '<?php echo strtoupper( mes( date( "m" ) ) ); ?>', '<?php echo strtoupper( mes( date( "m" ) - 1 ) ); ?>' ],
        domicilios      = <?php echo json_encode( $domicilios ); ?>,
        hoy = new Date();

    if( !pedido.data.productosxbulto ) pedido.data.productosxbulto = <?php echo MODELOS[ $modelo ][ "settings" ][ "productosxbulto" ]; ?>;

</script>


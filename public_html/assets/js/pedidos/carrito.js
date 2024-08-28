
function load_inventario( entrega ){

    if( entrega ){

        if( typeof(almacenes[ entrega ].productos ) === 'undefined' ){
            almacenes[ entrega ].productos = {};
        }

        if( !Object.keys( almacenes[ entrega ].productos ).length ){
            $.ajax({
                url: base_url + "get_inventario", 
                type: "POST",
                async: false,
                dataType: 'json',
                data: { 
                    [csrf_token] : csrf_hash, 
                    almacen      : entrega
                },
                success: function( result ){
                    almacenes[ entrega ].productos = result;
                }
            });
        }
    }
}


function revisa_stock(){
    var productos = {},
        metodoentrega_activo = $( '[name=metodosentrega]:checked' ).val();

        $( '#no_stock ul' ).empty();
        $( '#no_stock' ).hide();
        pedido.no_stock = false;

    if( metodoentrega_activo && metodoentrega_activo.substring(0,2) == '00'){
        entrega = $( '[name=select_almacen]' ).val();
        load_inventario( entrega );
        
        if( entrega ){
            $( '.card[promocion] table[productos] > tr[producto]' ).each( function(){
                var campo    = $( this ).find( 'input.cantidad' ),
                    cantidad = parseInt( campo.val() ),
                    producto = $( this ).attr( 'producto' );

                if( typeof( productos[ producto ] ) === 'undefined' ){
                    productos[ producto ] = 0;
                }

                productos[ producto ] += cantidad;
            });

            $.each( almacenes[ entrega ].productos, function( p, c ){

                if( typeof( productos[ p ] ) !== 'undefined' ){
                    if( c >= productos[ p ] ){
                        delete productos[ p ];
                    }
                    else{
                        productos[ p ] = productos[ p ] - c;
                    }
                }
            });

            $.each( Object.keys( productos ), function( k, p ){
                $( '#no_stock ul' ).append( '<li>' + cat_productos[ p ].data.nombre + '</li>' );
            });

            // si hay faltantes lanzar alerta
            if( Object.keys( productos ).length ){
                $( '#no_stock' ).show();
                pedido.no_stock = true;
            }
        }
    }
}


function update_puntos( promocion ){
    
    pedido.PTS[ promocion ] = 0;

    if( $('.card[promocion=' + promocion + '] > table[productos] > tr[producto]' ).length ){
        $( '.card[promocion=' + promocion + '] > table[productos] > tr[producto]' ).each( function(){
            var p = $( this ),
                producto = p.attr( 'producto' ),
                cantidad = p.find( 'input.cantidad' ).val(),
                puntos   = cat_productos[ producto ][ 'data' ].puntos[ promocion ] ?? 1,
                total    = cantidad * puntos;

            pedido.PTS[ promocion ] += total;
            pedido.data.peso += ( cantidad * cat_productos[ producto ][ 'data' ].dimensiones.peso );
            pedido.data.productos += parseInt( cantidad );
        });
    }
}


function cambia_cantidad( promocion, producto ){
    var cuenta_productos = 0,
        disponible       = eval( cat_promociones[ promocion ].formulas.disponible );

    if( disponible > 0){
        $( '.card[promocion=' + promocion + '] table[productos] > tr[producto] input.cantidad' ).each( function(){
            cuenta_productos += parseInt( $( this ).val() );
        });

        campo = $( '.card[promocion=' + promocion + '] table[productos] > tr[producto=' + producto + '] input.cantidad' );
        while( disponible < cuenta_productos ){
            campo.val( campo.val() - 1 );
            cuenta_productos--;
        }
    }
    
    update_pedido( "cambia cantidad" );
}


function update_pedido( flag = null ){
    if( pedido.estatus_codigo != '250-EN-PROCESO' ){
      // return;
    }

    pedido.data.total     = 0;
    pedido.data.productos = 0;
    pedido.data.peso      = 0;
    pedido.promociones    = {};

    $( '#puntajes' ).empty();

    pendientes = 0;

    var formula,
        total_productos_pedido = 0;

    $( '.card[promocion]' ).each( function(){

        
        var cuenta_productos = 0,
            total_promo      = 0,
            total_comisionable = 0,
            promocion        = $( this ).attr( 'promocion' ),
            disponible       = false;

        disponible = eval( cat_promociones[ promocion ].formulas.disponible );

        pedido.promociones[ promocion ] = {
            'productos'    : {},
            'precio'       : 0,
            'comisionable' : 0
        };

        formula = eval( cat_promociones[ promocion ].formulas.activacion );
        if( formula ){
            $( '.card[promocion=' + promocion + ']' ).show();
    
            if( cat_promociones[ promocion ].settings.forced == "true" ){
                $( '.card[promocion=' + promocion + '] table[productos]' ).empty();
    
                $.each( cat_promociones[ promocion ].productos.precarga, function( codigo, producto ){
                    agrega_producto( producto, promocion, 1, true );
                });
            }
        }
        else{
            $( '.card[promocion=' + promocion + '] table[productos]' ).empty();
            $( '.card[promocion=' + promocion + ']' ).hide();
        }  

        $( this ).find( 'table[productos] > tr[producto]' ).each( function(){
            var campo    = $( this ).find( 'input.cantidad' ),
                cantidad = parseInt( campo.val() ),
                producto = $( this ).attr( 'producto' ),
                unitario = parseFloat( campo.attr( 'unitario' ) ),
                orden    = parseInt( $( this ).attr( 'orden' ) );

            cuenta_productos += parseInt( cantidad );
            
            if( disponible > 0 && cuenta_productos > 0 ){
                while( disponible < cuenta_productos ){
                    campo.val( campo.val() - 1 );
                    cuenta_productos--;
                    cantidad--;

                    if( !cantidad ){
                        $( this ).remove();
                    }
                }
            }

            pedido.promociones[ promocion ][ 'productos' ][ producto ] = { 
                "cantidad"    : cantidad,
                "puntos"      : parseFloat( cat_productos[ producto ].data.puntos[ promocion ] ),
                "comisionable": parseFloat( cat_productos[ producto ].precio.base ),
                "orden"       : orden,
                "nombre"      : cat_productos[ producto ].data.nombre.toUpperCase(),
                "descripcion" : cat_productos[ producto ].data.descripcion,
                "reparte"     : cat_productos[ producto ].precio.reparte ?? null,
                "precio"      : unitario
            };

            total_promo += ( cantidad * unitario );
            total_comisionable += ( cantidad * cat_productos[ producto ].precio.base );
            $( this ).find( '[subtotal]' ).html( Moneda.format( cat_promociones[ promocion ].settings.paquete == "true" ? 0 : ( cantidad * unitario ) ) );
        });
      
        update_puntos( promocion );

        if( cat_promociones[ promocion ].settings.paquete == 'true' ){
            // pedido.PTS[ promocion ] = cuenta_productos ? 1 : 0;
            total_promo = (cuenta_productos == 0 ? 0 : eval( cat_promociones[ promocion ].formulas.precio ) );
        }

        pedido.data.total += total_promo;
        pedido.promociones[ promocion ][ 'precio' ] = total_promo;
        pedido.promociones[ promocion ][ 'comisionable' ] = total_comisionable;
        
        $( this ).find( '.total_promo' ).html( Moneda.format( total_promo ) );

        if( !cuenta_productos){
            $( this ).find( 'table[productos]' ).html( '<tr><td class="text-center text-gray-400"><i class="fa fa-cart-plus"></i> No hay productos aquí</td></tr>' );
        }
        
        // Si aun hay slots disponibles
        if( disponible < 0 || disponible > cuenta_productos && formula ){
            $( '.card[promocion=' + promocion + '] .agrega_productos' ).show();


            if( cat_promociones[ promocion ].settings.exacto == 'true' && ( cat_promociones[ promocion ].settings.obligatoria == "true" || ( cuenta_productos % 3 ) > 0 ) ){
                pendientes = 1;
                $( '.card[promocion=' + promocion + '] .agrega_productos' ).removeClass( 'btn-light' ).addClass( 'btn-warning' );
            }
            else{
                $( '.card[promocion=' + promocion + '] .agrega_productos' ).addClass( 'btn-light' ).removeClass( 'btn-warning' );
            }
        }

        // Si ya se llegó al límite
        else{
            $( '.card[promocion=' + promocion + '] .agrega_productos' ).hide();
        }

        if( pedido.PTS[ promocion ] ){
            $( '#puntajes' ).append( '<div class="pts text-white bg-white"><div class="pts-titulo bg-' + cat_promociones[ promocion ].settings.clase + '">' + cat_promociones[ promocion ].settings.siglas + '</div><div class="pts-numero bg-' + cat_promociones[ promocion ].settings.clase + '">' + pedido.PTS[ promocion ] + '</div></div>' );
        }

        $( this ).find( '[conteo]' ).html( cuenta_productos + ' productos' + ( disponible > 0 ? ' de ' + disponible + ' disponibles' + ( cat_promociones[ promocion ].settings.paquete == "true" ? ' (Paquete) ' : '') : '' ) );

        total_productos_pedido += cuenta_productos;
    });

    // update bultos
    var bultos1 = Math.ceil( pedido.data.peso / pesoxbulto );
    var bultos2 = 1; //Math.ceil( pedido.data.productos / pedido.data.productosxbulto );

    var bultos = bultos2 > bultos1 ? bultos2 : bultos1;
    pluses = 0;



    if( pedido.PTS["030-PLUS"] > 0 ){
        pluses = Math.floor( pedido.PTS["030-PLUS"] / 3 );
        bultos -= pluses;

        if( bultos < 0 ){
            bultos = 0;
        }
    }

    pedido.data.comisionentrega = ( pedido.data.costoxbulto ?? 0 ) * bultos;

    $( '[total_entrega]' ).attr( 'total_entrega', pedido.data.comisionentrega );
    $( '.me_costo' ).html( 'Utilizar este método de entrega, genera un costo de ' + 

    Moneda.format( pedido.data.comisionentrega ) ).show();

    porcentaje1 = 100 * pedido.data.peso / pedido.data.pesoxbulto;
    porcentaje2 = 100 * pedido.data.productos / pedido.data.productosxbulto;


    if( 0 && porcentaje2 > porcentaje1 ){
        $( '#bultos_cantidad' ).html( 'x' + bultos2 + ( pluses ? '<small><br>Envío gratis <span class="badge bg-blue">PLUS</span> x' + pluses + '</small>' : '' ) ); 
        $( '#bultos' ).empty();
        
        bultos = bultos2;
        porcentaje = porcentaje2;
    }
    else{
        $( '#bultos_cantidad' ).html( 'x' + bultos1 + ( pluses ? '<small><br>Envío gratis <span class="badge bg-blue">PLUS</span> x' + pluses + '</small>' : '' ) ); 
        $( '#bultos' ).empty();

        bultos = bultos1;
        porcentaje = porcentaje1;
    }

    for( a = 1; a <= bultos; a++ ){
        p = a < bultos ? 100 : 100 - ( 100 * a - porcentaje) ;
        $( '#bultos' ).append( '<div class="col-2"><div class="progress border border-mustard bg-white m-0" role="progressbar" aria-valuenow="' + p + '" style="height:12px; border-radius:3px !important"><div class="progress-bar bg-mustard text-white fw-bold" style="width: ' + p + '%; border-radius:3px !important">' + a + '</div></div></div>' );
    } 

    revisa_stock();
    $( '[total_productos]' ).attr( 'total_productos', pedido.data.total ).html( Moneda.format( pedido.data.total ) );

    total_productos = parseFloat( $( '[total_productos]' ).attr( 'total_productos' ) );
    total_entrega   = parseFloat( $( '[total_entrega]' ).attr( 'total_entrega' ) );
    total_saldo     = parseFloat( $( '[total_saldo]' ).attr( 'total_saldo' ) );
    gran_total      = total_productos + total_entrega - total_saldo ;

    if( gran_total < 0 ) gran_total = 0;

    $( '[total_entrega]' ).html( Moneda.format( total_entrega ) );    
    $( '[gran_total]' ).attr( 'gran_total', gran_total ).html( Moneda.format( gran_total ) );

    var subtotal = parseFloat( $( '[gran_total]' ).attr( 'gran_total' ) ),
        b = $( '[name=metodopago][value=0' + modelo.substring(0,1) + '-SALDO]' );

    if( (total_productos + total_entrega + total_productos_pedido) > 0 && total_saldo >= total_productos + total_entrega ){
        comision = 0;
        caption  = Moneda.format( total_productos + total_entrega );
        b.find( '.cantidad' ).html( caption );
        b.show();
        console.log( 'show' );
    }
    else{
        b.hide();
        console.log( b, 'hide' );
    }
      
    $( 'button[name=metodopago]' ).each( function( a, b){
        var metodopago  = $( this ).attr( 'value' ),
            cantidad    = $( this ).find( '.cantidad' ),
            costo_extra = $( this ).find( '.costo_extra' ),
            comision    = 0;

        switch( metodospago[ metodopago ].settings.tipocomision ){
            case 'porcentaje':
                comision = subtotal * parseFloat( metodospago[ metodopago ].settings.comision ) / 100;
                break;
            case 'efectivo':
                comision = parseFloat( metodospago[ metodopago ].settings.comision );
                break;                 
        }
    
        caption  = ( total_productos_pedido > 0 || subtotal > 0 ) ? ( Moneda.format( comision + subtotal ) ) : '--';
        cantidad.html( caption );
        costo_extra.html( 'Comisión bancaria por ' + Moneda.format( comision ) );

        es_paqueteria = pedido.metodoentrega_codigo ? pedido.metodoentrega_codigo.substring( 0, 2 ) != '00' && pedido.metodoentrega_codigo.substring( 0, 2 ) != '11' : false;

        permitepagos = !pedido.no_stock && total_productos_pedido > 0 /* && ( total_productos_pedido > 0 || ( subtotal > 0  || total_saldo > 0) ) */ && parseInt( pedido.data.entrega ) > 0 && ( ( es_paqueteria && pedido.data.domicilio !== undefined || !es_paqueteria && pedido.data.entrega.length > 0 ) );

        $( this ).prop( 'disabled',  !permitepagos || pendientes );
    });
    
    json = JSON.stringify( pedido );

    $.ajax({
        url: base_url + "save_pedido", 
        type: "POST",
        contentType: 'application/x-www-form-urlencoded; charset=UTF-8',
        data: { [csrf_token] : csrf_hash, json : json },
        success: function( result ){
            console.log( result );
        }
    });
}


function show_modal_productos( promocion ){
    $( '#modal_productos div[producto]' ).attr( 'activo', 'false' ).hide();
    $( '#busca_producto' ).val( '' );

    $( '#modal_productos div[producto]' ).each( function(){
        var producto = $( this ).attr( 'producto' );

        if( cat_promociones[ promocion ].productos.elegibles !== undefined && cat_promociones[ promocion ].productos.elegibles.includes( producto ) ){
            $( this ).attr( 'activo', 'true' ).show();
        }
    } ); 

    if( promocion == '010-DISTRIBUIDOR' ){
        $( '#modal_productos .puntos' ).show();
    }
    else{
        $( '#modal_productos .puntos' ).hide();
    }
    
    $( '#modal_productos' ).attr( 'promocion', promocion ).modal( 'show' );
}


function borra_producto( promocion, producto ){
    $( '.card[promocion="' + promocion + '"] table[productos] > tr[producto=' + producto + ']' ).remove();

    update_pedido( "borra producto" );
}


function agrega_producto( producto, promocion = null, cantidad = 1, auto = false ){
    if( !promocion ) promocion = $( '#modal_productos' ).attr( 'promocion' );

    var promo  = $( '.card[promocion="' + promocion + '"] table[productos]'),
        cuenta = promo.find( 'tr[producto]' ).length,
        existe = promo.find( 'tr[producto=' + producto + ']' ),
        campo  = existe.find( 'input.cantidad' );

    if( !cuenta ){
        $( '.card[promocion=' + promocion + '] table[productos]' ).empty();
    }

    if( existe.length ){
        campo.val( parseInt( campo.val() ) + cantidad );
    }
    else{
        orden  = get_orden_next( promocion );
        
        precio = cat_promociones[ promocion ].settings.paquete == "true" || cat_promociones[ promocion ].formulas.precio === undefined ? 0 : eval( cat_promociones[ promocion ].formulas.precio );

        $( '.card[promocion=' + promocion + '] table[productos]' ).append('<tr orden="' + orden + '" producto="' + producto + '"><td valign="top"><a href="javascript:lightbox( \'' + base_url + 'assets/img/productos/' + ( cat_productos[ producto ][ 'data' ][ 'avatar' ] ? cat_productos[ producto ][ 'codigo' ] : 'NO-IMAGEN') + '.png\' );" class="lightbox_trigger"><img src="' + base_url + 'assets/img/productos/' + ( cat_productos[ producto ][ 'data' ][ 'avatar' ] ? cat_productos[ producto ][ 'codigo' ] : 'NO-IMAGEN') + '.png" style=\"width:70px; height:70px; border-radius:5px\"></a></td><td class="w-100"><div class="row"><div class="col-md-9"><h5 class="m-0">' + cat_productos[ producto ].data.nombre.toUpperCase() + '</h5><p class="small mb-3">' + cat_productos[ producto ][ 'data' ][ 'descripcion' ] + '<br>' + ( promocion == '010-DISTRIBUIDOR' ? '<span class="badge bg-gray-500">' + cat_productos[ producto ][ 'data' ][ 'puntos' ][ promocion ] + ' pts' : '' ) + '</span></p></div><div class="col-md-3 small px-0">Cantidad: <input min="1" max="99" unitario="' + precio + '" ' + ( ( pagado || bloqueado || cancelado ) ? 'disabled' : ' onchange="cambia_cantidad(\'' + promocion + '\', \'' + producto + '\')"') + ' type="number" ' + ( cat_promociones[ promocion ].settings.forced == "true" ? 'disabled' : '' ) + ' class="cantidad form-control bg-white" value="' + cantidad + '"></div></div></td><td valign="top" class="text-end text-primary d-none d-lg-table-cell" nowrap><small>P. unitario</small><h5 class="text-gray-500">' +  Moneda.format( precio ) + '</h5></td><td valign="top" class="text-end text-primary" nowrap><small>Subtotal</small><h5 subtotal>' + Moneda.format( precio * cantidad ) + '</h5>' + ( ( pagado || bloqueado|| cancelado ) ? '' : '<p class="m-0"><button onclick="borra_producto(\'' + promocion + '\', \'' + producto + '\')" class="' + ( cat_promociones[ promocion ].settings.forced == "true" ? 'd-none' : '' ) + ' btn btn-sm btn-light text-red"><i class="fa fa-xmark"></i> Eliminar</button></p>' ) + '</td></tr>');

        $( '.card[promocion=' + promocion + ']' ).show();
    }
        
    if( !auto ){
        $( '#modal_productos' ).attr( 'promocion', promocion ).modal( 'hide' );
        
        update_pedido( "agrega pedido" );
    }
}


function get_orden_next( promocion ){
    var next = 0;

    $( '.card[promocion=' + promocion + '] table[productos] tr[producto]' ).each( function(){
        var actual = parseInt( $( this ).attr( 'orden' ) );
        if( actual > next ){
            next = actual;
        }
    });

    return next + 1;
}

function lightbox( image_href ){

    if ($('#lightbox').length > 0) { 
        $('#content').html('<img src="' + image_href + '" /><br>Click para cerrar');
        $('#lightbox').show();
    }
    else { 
        var lightbox = 
        '<div id="lightbox">' +
            '<div id="content">' + //insert clicked link's href into img src
                '<img src="' + image_href +'" /><br>Click para cerrar' +
            '</div>' +	
        '</div>';
            
        $('body').append(lightbox);
    }

    $('body').on('click', '#lightbox', function() { 
        $('#lightbox').hide();
    });

    return false;
}

var pendientes = 0;



$(document).ready(function()
{ 
    
	function delay(fn, ms) {
		let timer = 0
		return function(...args) {
		  clearTimeout(timer)
		  timer = setTimeout(fn.bind(this, ...args), ms || 0)
		}
    }

	// Buscador de productos en modal de catálogo
	$( '#busca_producto' ).keyup( delay( function( e ){

        var busqueda = $( this ).val();

        $( '#modal_productos div[producto][activo=true]' ).show();
        $( '#modal_productos div[producto][activo=true]' ).each( function(){
            var texto = $( this ).find( 'h5' ).text();

            if( !( texto.toLowerCase().indexOf( busqueda.toLowerCase() ) >= 0 ) )
                $( this ).hide();
        } ); 

    }, 400));

	// Arranca carrito
	$( '#borra_todo' ).on( 'click', function(){
		$( 'table[productos] > tr[producto]' ).remove();
        $( '[name=metodosentrega]' ).prop( 'checked', false); 

        pedido.data.entrega = null;
        pedido.data.comisionentrega = 0;
        pedido.data.costoxbulto = 0;
        pedido.metodoentrega_codigo = null;

        $( '[total_productos]' ).attr( 'total_productos', 0 );
        $( '[total_entrega]' ).attr( 'total_entrega', 0 );
        $( '[total_saldo]' ).attr( 'total_saldo', 0 );
        $( '[name=select_almacen]' ).val( '' );

        $( '.me_respuesta' ).hide();
        
		update_pedido( "borra todo" );
	});

    $( '#checkout_a' ).on( 'click', function(){
        // window.location.href = base_url + 'pagoyenvio/' + modelo;
        window.location.href = base_url + 'pedido/' + pedido.referencia;
    });

    $.each( pedido.promociones, function( promocion, data ){
        var agregar = [], conteo = 0;

        $.each( data.productos, function( producto, data2 ){
            if( cat_promociones[ promocion ] !== undefined ){
                agregar[ data2.orden ] = producto;
            }
        });

        $.each( agregar, function( orden, producto ){
            if( data.productos[ producto ] !== undefined ){
                agrega_producto( producto, promocion, data.productos[ producto ].cantidad, true );
                conteo += data.productos[ producto ].cantidad;
            }
        });

        $( '.card[promocion=' + promocion + '] [conteo]' ).text( conteo + ' productos' );
    });
        
    // elige metodo de entrega
    $( '[name=metodosentrega]' ).on( 'change', function(){
        var metodoentrega_activo = $( '[name=metodosentrega]:checked' ).val(),
            entrega = $( 'div[domicilio_id]' ).attr( 'domicilio_id' );
            
        pedido.data.costoxbulto = metodosentrega[ metodoentrega_activo ].settings.costo;

        $( '.me_descripcion' ).html( metodosentrega[ metodoentrega_activo ].settings.descripcion );
        $( '.me_formulario, .me_costo' ).hide();
        $( '.me_respuesta' ).show();

        if( metodoentrega_activo.substring(0,2) == '00'){
            $( '.me_formulario[mp=almacen]' ).show();
            entrega = $( '[name=select_almacen]' ).val();
            load_inventario( entrega );

            pedido.data.domicilio  = null;

            if( entrega ){
                pedido.data.costoxbulto = tarifas[ almacenes[ entrega ].settings.tarifa ];
            }
        }  

        else if( metodoentrega_activo.substring(0,2) == '11'){
            $( '.me_formulario[mp=celular]' ).show();
            entrega = $( '[name=select_celular]' ).val();
            pedido.data.domicilio = null;
            pedido.data.costoxbulto = 0;
        }

        else{
            $( '.me_formulario[mp=domicilio]' ).show();
            pedido.data.domicilio = $( 'div[domicilio_id]' ).attr( 'domicilio_id' ); // domicilios[ entrega ];
        }

        pedido.data.entrega = entrega;
        pedido.metodoentrega_codigo = metodoentrega_activo;

        update_pedido( "metodo entrega" ); 
    } );

    // Al cambiar almacen actualizar tarifa
    $( '[name=select_almacen]' ).on( 'change', function(){
        var entrega = $( this ).val(),
            metodoentrega_activo = $( '[name=metodosentrega]:checked' ).val();

        load_inventario( entrega );
        pedido.data.costoxbulto = tarifas[ almacenes[ entrega ].settings.tarifa ];
        pedido.data.entrega = entrega;
        pedido.metodoentrega_codigo = metodoentrega_activo;
        pedido.data.domicilio = null;
        update_pedido( "cambio almacen" );
    } );


    $( '[name=select_celular' ).on( 'change', function(){
        var entrega = $( this ).val(),
            metodoentrega_activo = $( '[name=metodosentrega]:checked' ).val();

        pedido.data.costoxbulto = 0;
        pedido.data.entrega = entrega;
        pedido.metodoentrega_codigo = metodoentrega_activo;
        pedido.data.domicilio = null;
        update_pedido( "cambio celular" );
    } );


    $( 'button[domicilio_id]' ).on( 'click', function(){
        var domicilio = $( this ).attr( 'domicilio_id' ),
            html      = $( this ).clone().html();

        $( 'div[domicilio_id]').attr( 'domicilio_id', domicilio )
        $( 'div[domicilio_id]').html( html );
        $( '#modal_domicilios' ).modal( 'hide' );

        pedido.data.entrega   = domicilio;
        pedido.data.domicilio = domicilios[ domicilio ];
        
        update_pedido( "cambio domicilio" );
    } );

    $( '#cambia_mes_califica' ).on( 'click', function(){
        var valor = parseInt( $( '#mes_califica' ).attr( 'mesanterior' ) ) == 1 ? 0 : 1;

        pedido.data.mesanterior = valor;
        $( '#mes_califica' ).attr( 'mesanterior', valor );
        $( '#mescalifica'  ).html( mesesactuales[ valor ] );
        $( '#mes_califica' ).modal( 'hide' );

        $( '#mes_califica .modal-body' ).hide();
        $( '#mes_califica .modal-body[ma=' + valor + ']' ).show();

        if( valor ){ 
            $( '#alert_anterior' ).attr( 'class', 'alert alert-danger');
            $( '#mescalifica'  ).addClass( 'bg-red border-red' );
            $( '#cambia_mes_califica' ).attr( 'class', 'btn btn-info' ).html( 'Aplicar compra para mes actual' );            
        }
        else{
            $( '#alert_anterior' ).attr( 'class', 'alert alert-info');
            $( '#mescalifica'  ).removeClass( 'bg-red border-red' );
            $( '#cambia_mes_califica' ).attr( 'class', 'btn btn-danger' ).html( 'Aplicar compra para mes anterior' );
        }

        update_pedido( "mesanterior" );
    });

    $( '#no_pago' ).hide();
    $( 'button[name=metodopago]' ).show();

    if( !( pagado || bloqueado || cancelado ) ) update_pedido( "inicial" );

    if( $( '[name=metodosentrega]' ).length == 1 ){
        $( '[name=metodosentrega]' ).click();
    }    
});

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

    if( metodoentrega_activo && pedido.data.peso > 0 ){
        var d = metodoentrega_activo.substring( 3 );

        if( d == 'ALMACEN' || d == 'PAQUETERIA' ){
            entrega = d == 'ALMACEN' ? $( '[name=select_almacen]' ).val() : ( modelo.substring( 0, 1 ) -1 ) + '10-PUEBLA';
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
                    
                        if( c >= productos[ p ] ){ // || cat_productos[ p ].data.dimensiones.peso == 0 ){
                            delete productos[ p ];
                        }
                        else{
                            productos[ p ] = productos[ p ] - c;
                        }
                    }
                });

                $.each( Object.keys( productos ), function( k, p ){
                    if( cat_productos[ p ].data.dimensiones.peso == 0 ){
                        delete productos[ p ];
                    }
                    else{
                        $( '#no_stock ul' ).append( '<li>' + cat_productos[ p ].data.nombre + '</li>' );
                    }
                });

                // si hay faltantes lanzar alerta
                if( Object.keys( productos ).length ){
                    $( '#no_stock' ).show();
                    pedido.no_stock = true;
                }
            }
        }
    }
}


function update_puntos( promocion ){
    
    pedido.PTS[ promocion ] = 0;

    evento   = $('.card[promocion=' + promocion + ']' ).attr( 'evento') ;
    estatus  = $('.card[promocion=' + promocion + ']' ).attr( 'estatus');

    if( evento == 'false' || estatus == 'true' ){
   
        if( $( '.card[promocion=' + promocion + '] > table[productos] > tr[producto]' ).length ){
            $( '.card[promocion=' + promocion + '] > table[productos] > tr[producto]' ).each( function(){
                var p = $( this ),
                    producto = p.attr( 'producto' ),
                    cantidad = p.find( 'input.cantidad' ).val(),
                    puntos   = cat_productos[ producto ][ 'data' ].puntos[ promocion ] ?? 1,
                    total    = Math.round( cantidad * puntos * 10 );

                pedido.PTS[ promocion ] = ( ( pedido.PTS[ promocion ] * 10 ) + total ) / 10 ;

                pedido.data.peso += ( cantidad * cat_productos[ producto ][ 'data' ].dimensiones.peso );
                pedido.data.productos += parseInt( cantidad );
            });
        }
    }
}


function cambia_cantidad( promocion, producto ){
    var cuenta_productos = 0,
        disponible       = eval( cat_promociones[ promocion ].formulas.disponible ),
        campo = $( '.card[promocion=' + promocion + '] table[productos] > tr[producto=' + producto + '] input.cantidad' );
    
    // console.log( 'disponible ' + promocion, disponible );
    
        // revisar máximos
    if( disponible > 0){
        $( '.card[promocion=' + promocion + '] table[productos] > tr[producto] input.cantidad' ).each( function(){
            cuenta_productos += parseInt( $( this ).val() );
        });

        while( disponible < cuenta_productos ){
            campo.val( campo.val() - 1 );
            cuenta_productos--;
        }
    }

    // revisar minimos
    if( cat_promociones[ promocion ].formulas.minimo !== undefined && campo.val() < cat_promociones[ promocion ].formulas.minimo ){
        campo.val( cat_promociones[ promocion ].formulas.minimo );
    }     

    update_pedido( "cambia cantidad" );
}


function update_costos(){

    pedido.data.total     = 0;
    pedido.data.productos = 0;
    pedido.data.peso      = 0;
    pedido.promociones    = {};

    $( '.card[promocion]' ).each( function(){

        var tag = $( this ), 
            cuenta_productos   = 0,
            total_promo        = 0,
            total_comisionable = 0,
            promocion          = tag.attr( 'promocion' );

        pedido.promociones[ promocion ] = {
            'productos'    : {},
            'precio'       : 0,
            'comisionable' : 0,
            'evento'  : tag.attr( 'evento' )  ?? 'false',
            'estatus' : tag.attr( 'estatus' ) ?? 'false',
            'activo' : 'false'
        };

        evento   = tag.attr( 'evento' )  ?? 'false';
        estatus  = tag.attr( 'estatus' ) ?? 'false';
    
        if( evento == 'false' || estatus == 'true' ){
            tag.find( 'table[productos] > tr[producto]' ).each( function(){
                var campo    = $( this ).find( 'input.cantidad' ),
                    cantidad = parseInt( campo.val() ),
                    producto = $( this ).attr( 'producto' ),
                    unitario = parseFloat( campo.attr( 'unitario' ) ),
                    orden    = parseInt( $( this ).attr( 'orden' ) );

                cuenta_productos += parseInt( cantidad );
                //console.log( promocion, producto, cantidad)
                if( cantidad ){
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
                }
            });
        }

        if( cat_promociones[ promocion ].settings.paquete == 'true' ){
            total_promo = (cuenta_productos == 0 ? 0 : eval( cat_promociones[ promocion ].formulas.precio ) );
        }

        pedido.data.total += total_promo;
        pedido.promociones[ promocion ][ 'precio' ] = total_promo;
        pedido.promociones[ promocion ][ 'comisionable' ] = total_comisionable;

        tag.find( '.total_promo' ).html( Moneda.format( total_promo ) );
    });

    json = JSON.stringify( pedido );

    $( '[total_productos]' ).attr( 'total_productos', pedido.data.total ).html( Moneda.format( pedido.data.total ) );

    total_productos = parseFloat( $( '[total_productos]' ).attr( 'total_productos' ) );
    total_entrega   = parseFloat( $( '[total_entrega]' ).attr( 'total_entrega' ) );
    total_saldo     = parseFloat( $( '[total_saldo]' ).attr( 'total_saldo' ) );
    total_banco     = parseFloat( $( '[total_banco]' ).attr( 'total_banco' ) );
    gran_total      = total_productos + total_entrega + total_banco- total_saldo ;
    
    if( gran_total < 0 ) gran_total = 0;

    $( '[gran_total]' ).attr( 'gran_total', gran_total ).html( Moneda.format( gran_total ) );

    $.ajax({
        url: base_url + "save_pedido", 
        type: "POST",
        contentType: 'application/x-www-form-urlencoded; charset=UTF-8',
        data: { [csrf_token] : csrf_hash, json : json },
        success: function( result ){
            console.log( 'UPDATED DATA' );
        }
    });
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
        update_puntos( $( this ).attr( 'promocion' ) );
    });

    $.each( pedido.PTS, function( promocion, puntos ){

        if( usuario.PTS[ promocion ] === undefined || ( cat_promociones[ promocion ] === undefined && !pagado ) ){
            pedido.PTS[ promocion ] = 0;
        }
        else{
            pedido.suma[ promocion ] = puntos + ( usuario.PTS[ promocion ][ 'meses' ][ mes_actual ] ?? 0 );
        }
    });

    $( '.card[promocion]' ).each( function(){

        var tag = $( this ), 
            cuenta_productos   = 0,
            total_promo        = 0,
            total_comisionable = 0,
            promocion          = tag.attr( 'promocion' ),
            disponible         = 0;

        disponible = eval( cat_promociones[ promocion ].formulas.disponible );
        // console.log( 'disponible ' + promocion, disponible );
        
        pedido.promociones[ promocion ] = {
            'productos'    : {},
            'precio'       : 0,
            'comisionable' : 0,
            'evento'  : tag.attr( 'evento' )  ?? 'false',
            'estatus' : tag.attr( 'estatus' ) ?? 'false',
            'activo' : 'false'
        };

        formula = eval( cat_promociones[ promocion ].formulas.activacion );
        // console.log( 'activacion: ' + promocion, formula, pedido.suma[ promocion] );

        if( formula ){
            $( '.card[promocion=' + promocion + ']' ).show();
    
            if( cat_promociones[ promocion ].settings.forced == "true" ){
                $( '.card[promocion=' + promocion + '] table[productos]' ).empty();

                $.each( cat_promociones[ promocion ].productos.precarga, function( codigo, producto ){
                    agrega_producto( producto, promocion, 1, true );
                });
            }   
            pedido.promociones[ promocion ].activo = 'true';
        }
        else{
            pedido.promociones[ promocion ].activo = 'false';
            $( '.card[promocion=' + promocion + '] table[productos]' ).empty();
            $( '.card[promocion=' + promocion + ']' ).hide();
        }  

        evento   = tag.attr( 'evento' )  ?? 'false';
        estatus  = tag.attr( 'estatus' ) ?? 'false';
    
        if( evento == 'false' || estatus == 'true' ){
            $( this ).find( 'table[productos] > tr[producto]' ).each( function(){
                var campo    = $( this ).find( 'input.cantidad' ),
                    cantidad = parseInt( campo.val() ),
                    producto = $( this ).attr( 'producto' ),
                    unitario = parseFloat( campo.attr( 'unitario' ) ),
                    orden    = parseInt( $( this ).attr( 'orden' ) );

                cuenta_productos += parseInt( cantidad );
                
                if( disponible > 0 && cuenta_productos > 0 ){
                    while( cantidad && disponible < cuenta_productos ){
                        campo.val( campo.val() - 1 );
                        cuenta_productos--;
                        cantidad--;
                    }
                }

                if( cantidad ){
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
                }
                else{
                    $( this ).remove();

                    console.log( 'borra', promocion, producto );
                }
            });
        }

        update_puntos( promocion );

        if( cat_promociones[ promocion ].settings.paquete == 'true' ){
            // pedido.PTS[ promocion ] = cuenta_productos ? 1 : 0;
            total_promo = (cuenta_productos == 0 ? 0 : eval( cat_promociones[ promocion ].formulas.precio ) );
        }

        pedido.data.total += total_promo;
        pedido.promociones[ promocion ][ 'precio' ] = total_promo;
        pedido.promociones[ promocion ][ 'comisionable' ] = total_comisionable;
        
        $( this ).find( '.total_promo' ).html( Moneda.format( total_promo ) );

        if( cuenta_productos){
            $( this ).find( 'table[productos], .card-footer' ).show();
        }
        else{
            $( this ).find( 'table[productos]' ).html( '<tr><td class="text-center text-gray-400"><i class="fa fa-cart-plus"></i> No hay productos aquí</td></tr>' );

            $( this ).find( 'table[productos], .card-footer' ).hide();
        }
        
        // Si aun hay slots disponibles
        if( disponible < 0 || disponible > cuenta_productos && formula ){
            $( '.card[promocion=' + promocion + '] .agrega_productos' ).show();


            if( pedido.promociones[ promocion ].activo == 'true' && cat_promociones[ promocion ].settings.exacto == 'true' && ( cat_promociones[ promocion ].settings.obligatoria == "true" || ( cuenta_productos % 3 ) > 0 ) ){
                pendientes = 1;

                // console.log(promocion);
                $( '.card[promocion=' + promocion + '] .agrega_productos' ).removeClass( 'btn-light text-teal' ).addClass( 'btn-danger text-white' );
            }
            else{
                $( '.card[promocion=' + promocion + '] .agrega_productos' ).addClass( 'btn-light text-teal' ).removeClass( 'btn-danger text-white' );
            }
        }

        // Si ya se llegó al límite
        else{
            $( '.card[promocion=' + promocion + '] .agrega_productos' ).hide();
        }

        if( pedido.PTS[ promocion ] ){
            $( '#puntajes' ).append( '<div class="pts text-white bg-white"><div class="pts-titulo bg-' + cat_promociones[ promocion ].settings.clase + '">' + cat_promociones[ promocion ].settings.siglas + '</div><div class="pts-numero bg-' + cat_promociones[ promocion ].settings.clase + '">' + ( Math.round( 10 * pedido.PTS[ promocion ] ) / 10 ) + '</div></div>' );
        }

        $( this ).find( '[conteo]' ).html( cuenta_productos + ' productos' + ( disponible > 0 ? ' de ' + disponible + ' disponibles' + ( cat_promociones[ promocion ].settings.paquete == "true" ? ' (Paquete) ' : '') : '' ) );

        total_productos_pedido += cuenta_productos;
    });

    pedido.data.peso = pedido.data.peso == false ? 0 : parseInt( pedido.data.peso );
    total_productos_pedido = pedido.data.productos ;

    // update bultos
    var bultos1 = Math.ceil( pedido.data.peso / pesoxbulto );
    var bultos2 = 1; //Math.ceil( pedido.data.productos / pedido.data.productosxbulto );

    var bultos = bultos2 > bultos1 ? bultos2 : bultos1;
    pluses = 0;
    packs  = 0;
    if( pedido.data.peso == 0 ){
        metodoentrega_activo    = null;
        // pedido.data.costoxbulto = 0;

        if( modelo == '40-GASOLINAS' ){
            pedido.metodoentrega_codigo = null;
            pedido.data.entrega = null;
            pedido.data.costoxbulto = 0;
        }        
    }
    else{
        
        if( pedido.metodoentrega_codigo == null && $( '[name=metodosentrega]:checked' ).val() !== undefined ){
            $( '[name=metodosentrega]' ).change();
        }

        if( pedido.PTS["030-PLUS"] > 0 ){
            pluses = Math.floor( pedido.PTS["030-PLUS"] / 3 );
            bultos -= pluses;

            if( bultos < 0 ){
                bultos = 0;
            }
        }  

        /*

        // promo de 5+envio, ya a la chingada, este bloque se puede borrar
        
         if( pedido.PTS["316-SIM-CARD"] > 0 ){
            bultos = 1;
            metodoentrega_activo = $( '[name=metodosentrega]:checked' ).val() ?? null;

            // diferencia de costo de envio si son 5 sims o más
            if( metodosentrega[ metodoentrega_activo ] && pedido.data.costoxbulto ){
                // pedido.data.costoxbulto =  parseFloat( pedido.PTS["316-SIM-CARD"] == 5 ? pedido.data.costoxbulto: 250, 2 );

                pedido.data.costoxbulto = metodosentrega[ metodoentrega_activo ].settings.costo;
            }
            // packs  = 5;
        } */
    }

    if( ( !pedido.data.peso && pedido.data.productos > 0 ) || modelo == '50-INVERSION' ){
        $( '.metodosentrega, .me_respuesta' ).hide();
        if( pedido.data.productos > 0 ) $( '#no_costo' ).show();
    }
    else{
        $( '.metodosentrega' ).show();
        if( pedido.metodoentrega_codigo != null ) $( '.me_respuesta' ).show();
        $( '#no_costo' ).hide();
    }

    // ***************************************

    // Costo de envío segun calificación del socio

    es_paqueteria = pedido.metodoentrega_codigo ? pedido.metodoentrega_codigo.substring( 3 ) == 'PAQUETERIA' : false;
    puntos = Math.floor( pedido.suma[ "010-DISTRIBUIDOR" ] / 3 );
    
   
    pedido.data.comisionentrega = ( pedido.data.costoxbulto ?? 0 ) * bultos;

    if( puntos < 2 ){
        $( '[for=me-12-EXPRESS]' ).addClass( 'd-none' );
        $( '[for=me-10-PAQUETERIA]' ).removeClass( 'd-none' );
    }

    else if( puntos == 2 ){
        pedido.data.comisionentrega = 0;

        $( '[for=me-12-EXPRESS]' ).addClass( 'd-none' );
        $( '[for=me-10-PAQUETERIA]' ).removeClass( 'd-none' );

        if( pedido.metodoentrega_codigo == '10-PAQUETERIA' ||pedido.metodoentrega_codigo == '12-EXPRESS' ){
            pedido.metodoentrega_codigo = '10-PAQUETERIA';    


            $( '#me-12-EXPRESS' ).prop( 'checked', false );
            $( '#me-10-PAQUETERIA' ).prop( 'checked', true );
        }
    }

    else if( puntos > 2 ){
        pedido.data.comisionentrega = 0;

        $( '[for=me-10-PAQUETERIA]' ).addClass( 'd-none' );
        $( '[for=me-12-EXPRESS]' ).removeClass( 'd-none' );

        if( pedido.metodoentrega_codigo == '12-EXPRESS' || pedido.metodoentrega_codigo == '10-PAQUETERIA' ){
            pedido.metodoentrega_codigo = '12-EXPRESS';  
            
            $( '#me-12-EXPRESS' ).prop( 'checked', true );
            $( '#me-10-PAQUETERIA' ).prop( 'checked', false );

        }
    }

    // console.log( puntos, pedido.metodoentrega_codigo, pedido.data.costoxbulto );

    // ***************************************


    $( '[total_entrega]' ).attr( 'total_entrega', pedido.data.comisionentrega );
    $( '.me_costo' ).html( parseInt( pedido.data.comisionentrega ) ? 'Utilizar este método de entrega, genera un costo de ' + Moneda.format( pedido.data.comisionentrega ) : 'Este método de entrega no genera costo' ).show();

    porcentaje1 = 100 * pedido.data.peso / pedido.data.pesoxbulto;
    porcentaje2 = 100 * pedido.data.productos / pedido.data.productosxbulto;

    if( 0 && porcentaje2 > porcentaje1 ){
        $( '#bultos_cantidad' ).html( 'x' + bultos2 + ( pluses ? '<small><br>Envío gratis <span class="badge bg-blue">PLUS</span> x' + pluses + '</small>' : '' ) + ( packs ? '<small><br>Envío gratis <span class="badge bg-light-blue">CHIPS</span> x' + packs + '</small>' : '' ) ); 
        $( '#bultos' ).empty();
        
        bultos = bultos2;
        porcentaje = porcentaje2;
    }
    else{
        $( '#bultos_cantidad' ).html( 'x' + bultos1 + ( pluses ? '<small><br>Envío gratis <span class="badge bg-blue">PLUS</span> x' + pluses + '</small>' : '' ) + ( packs ? '<small><br>Envío gratis <span class="badge bg-light-blue">CHIPS</span> x' + packs + '</small>' : '' ) ); 
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

    $( '[total_entrega]' ).html( Moneda.format( total_entrega ) );    
    
    if( gran_total < 0 ) gran_total = 0;

    $( '[gran_total]' ).attr( 'gran_total', gran_total ).html( Moneda.format( gran_total ) );

    var subtotal = parseFloat( $( '[gran_total]' ).attr( 'gran_total' ) ),
        b = $( '[name=metodopago][value=0' + modelo.substring(0,1) + '-SALDO]' );

    if( (total_productos + total_entrega + total_productos_pedido) > 0 && total_saldo >= total_productos + total_entrega ){
        comision = 0;
        caption  = Moneda.format( total_productos + total_entrega );
        b.find( '.cantidad' ).html( caption );
        b.show();
    }
    else{
        b.hide();
    }
     
    $( 'div.metodopago' ).each( function( a, b){
        var metodopago  = $( this ).attr( 'metodopago' ),
            boton       = $( this ).find( '[name=metodopago]' ),
            cantidad    = $( this ).find( '.cantidad' ),
            costo_extra = $( this ).find( '.costo_extra' ),
            comision    = 0;
        
        /*     $( 'button[name=metodopago]' ).each( function( a, b){
                var metodopago  = $( this ).attr( 'value' ),
                    cantidad    = $( this ).find( '.cantidad' ),
                    costo_extra = $( this ).find( '.costo_extra' ),
                    comision    = 0;
        */
        switch( metodospago[ metodopago ].settings.tipocomision ){
            case 'porcentaje':
                comision = Math.ceil( subtotal * parseFloat( metodospago[ metodopago ].settings.comision ) / 100 );
                break;
            case 'efectivo':
                comision = parseFloat( metodospago[ metodopago ].settings.comision );
                break;                 
        }
    
        caption  = ( total_productos_pedido > 0 || subtotal > 0 ) ? ( Moneda.format( comision + subtotal ) ) : '--';
        cantidad.html( caption );
        costo_extra.html( 'Cargo operativo por ' + Moneda.format( comision ) + ( 0 && metodopago.substring( 3 ) == 'CONEKTA' ? ' (el punto de pago cobrará una comisión extra aprox. de $10)' : '' ) );

        es_paqueteria = pedido.metodoentrega_codigo ? pedido.metodoentrega_codigo.substring( 3 ) == 'PAQUETERIA' : false;
        es_almacen    = pedido.metodoentrega_codigo ? pedido.metodoentrega_codigo.substring( 3 ) == 'ALMACEN' : false;
        
        permitepagos = 
            ( ( pedido.metodoentrega_codigo && pedido.data.peso > 0 ) || pedido.data.peso == 0 ) &&
            !pendientes &&
            !pedido.no_stock && 
            total_productos_pedido > 0 && 
            ( ( es_paqueteria && parseInt( pedido.data.entrega ) > 0 ) || !es_paqueteria ) &&
            ( ( es_almacen && pedido.data.entrega != null ) || !es_almacen );
        
            $errores = '';
        
        /*      
        console.log(
            ( ( pedido.metodoentrega_codigo && pedido.data.peso > 0 ) || pedido.data.peso == 0 ),
            !pendientes,
            !pedido.no_stock,
            total_productos_pedido > 0,
            ( ( es_paqueteria && parseInt( pedido.data.entrega ) > 0 ) || !es_paqueteria ),
            ( ( es_almacen && pedido.data.entrega != null ) || !es_almacen )
        );  
        */

        if( !permitepagos ){
            $( this ).prop( 'disabled', true );
            $( this ).removeClass( 'btn-primary' );
            $( this ).addClass( 'btn-light2 text-gray-500' );

            $( '#open_checkout' ).removeClass( 'btn-success' ).addClass( 'btn-light' ).prop( 'disabled', true );

            // mostrar error

            if( pendientes ){
                $errores = 'Hay productos obligatorios que no has seleccionados';
            }

            if( !pedido.metodoentrega_codigo && pedido.data.peso > 0 ){
                $errores = 'No has seleccionado método de entrega';
            }

            if( pedido.no_stock ){
                $errores = 'Stock insuficiente en almacen seleccionado';
            }

            if( total_productos_pedido == 0 ){
                $errores = 'Tu pedido está vacío';
            }

            if( es_paqueteria && !( parseInt( pedido.data.entrega ) > 0 ) ){
                $errores = 'Debes seleccionar un domicilio';
            }

            if( es_almacen && pedido.data.entrega == null ){
                $errores = 'Debes seleccionar un almacen';
            }
        }
        else{
            $( this ).prop( 'disabled', false );
            $( this ).removeClass( 'btn-light2 text-gray-500' );
            $( this ).addClass( 'btn-primary' );

            $( '#open_checkout' ).addClass( 'btn-success' ).removeClass( 'btn-light' ).prop( 'disabled', false );
            $errores = 'Click para finalizar el pedido y seleccionar método de pago';
        }

        const tooltip = bootstrap.Tooltip.getInstance('#btn-wrapper');
        if( !( pagado || bloqueado || cancelado ) )  tooltip.setContent( { '.tooltip-inner': $errores } ); 

    });
    
    json = JSON.stringify( pedido );

    $.ajax({
        url: base_url + "save_pedido", 
        type: "POST",
        contentType: 'application/x-www-form-urlencoded; charset=UTF-8',
        data: { [csrf_token] : csrf_hash, json : json },
        success: function( result ){
            // console.log( 'log' );
        }
    });

    // console.log( flag );
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

        if( cat_promociones[ promocion ].formulas.minimo !== undefined && campo.val() < cat_promociones[ promocion ].formulas.minimo ){
            campo.val( cat_promociones[ promocion ].formulas.minimo );
        }

    }
    else{

        if( cat_promociones[ promocion ].formulas.minimo !== undefined && cantidad < cat_promociones[ promocion ].formulas.minimo ){
            cantidad = cat_promociones[ promocion ].formulas.minimo;
        }        
        orden  = get_orden_next( promocion );
        
        precio = 
            modelo == '50-INVERSION' ? (
                auto ? pedido.data.total  : $( '#cantidad_' + producto ).val()
            ) : (
                    cat_promociones[ promocion ].settings.paquete == "true" || cat_promociones[ promocion ].formulas.precio === undefined ? 
                        0 : 
                        eval( cat_promociones[ promocion ].formulas.precio )
        );  

//        precio = ( cat_promociones[ promocion ].settings.paquete == "true" || cat_promociones[ promocion ].formulas.precio === undefined ? 0 : eval( cat_promociones[ promocion ].formulas.precio ) );

        $( '.card[promocion=' + promocion + '] table[productos]' ).append('<tr orden="' + orden + '" producto="' + producto + '"><td valign="top"><a href="javascript:lightbox( \'' + base_url + 'assets/img/productos/' + ( cat_productos[ producto ][ 'data' ][ 'avatar' ] ? cat_productos[ producto ][ 'codigo' ] : 'NO-IMAGEN') + '.png\' );" class="lightbox_trigger"><img src="' + base_url + 'assets/img/productos/' + ( cat_productos[ producto ][ 'data' ][ 'avatar' ] ? cat_productos[ producto ][ 'codigo' ] : 'NO-IMAGEN') + '.png" style=\"width:70px; height:70px; border-radius:5px\"></a></td><td class="w-100"><div class="row"><div class="col-md-9"><h5 class="m-0">' + cat_productos[ producto ].data.nombre.toUpperCase() + '</h5><p class="small mb-3">' + cat_productos[ producto ][ 'data' ][ 'descripcion' ] + '<br>' + ( promocion == '010-DISTRIBUIDOR' ? '<span class="badge bg-gray-500">' + cat_productos[ producto ][ 'data' ][ 'puntos' ][ promocion ] + ' pts' : '' ) + '</span></p></div><div class="' + ( modelo == '50-INVERSION' ? 'd-none ' : '' ) + 'col-md-3 small px-0">Cantidad: <input min="1" max="99" unitario="' + precio + '" ' + ( ( pagado || bloqueado || cancelado ) ? 'disabled' : ' onchange="cambia_cantidad(\'' + promocion + '\', \'' + producto + '\')"') + ' type="number" ' + ( cat_promociones[ promocion ].settings.forced == "true" ? 'disabled' : '' ) + ' class="cantidad form-control bg-white" value="' + cantidad + '"></div></div></td><td valign="top" class="' + ( modelo == '50-INVERSION' ? 'd-lg-none ' : '' ) + 'text-end text-primary d-none d-lg-table-cell" nowrap><small>P. unitario</small><h5 class="text-gray-500">' +  Moneda.format( precio ) + '</h5></td><td valign="top" class="text-end text-primary" nowrap><small>Subtotal</small><h5 subtotal>' + Moneda.format( precio * cantidad ) + '</h5>' + ( ( pagado || bloqueado|| cancelado ) ? '' : '<p class="m-0"><button onclick="borra_producto(\'' + promocion + '\', \'' + producto + '\')" class="' + ( cat_promociones[ promocion ].settings.forced == "true" ? 'd-none' : '' ) + ' btn btn-sm btn-light text-red"><i class="fa fa-xmark"></i> Eliminar</button></p>' ) + '</td></tr>');

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


function update_variantes(){
    $( '.variante_productos' ).each( function(){
        var value = parseInt( $( this ).val() );

        if( value > 0){
            $( this ).addClass( 'is-valid' ).removeClass( 'opacity-50' );
        }
        else{
            $( this ).removeClass( 'is-valid' ).addClass( 'opacity-50' );
        }
    })
}

var pendientes = 0;

$(document).ready(function()
{ 

    $( '.variante_productos' ).on( 'change', function(){
        update_variantes();
    }).on( 'keyup', function(){
        update_variantes();
    });

    $( '.limitado' ).on( 'blur', function(){
        var value = parseInt( $( this ).val() ),
            min   = $( this ).attr( 'min' ) ?? 0;

        if( value % 100 !== 0 ){
            res = Math.ceil( value / 100 ) * 100;
            $( this ).val( res < min ? min : res );
        }

        if( value < min ){
            $( this ).val( min );
        }
    });
    
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
        pedido.data.mesanterior = 0;

        $( '[total_productos]' ).attr( 'total_productos', 0 );
        $( '[total_entrega]' ).attr( 'total_entrega', 0 );
        $( '[total_saldo]' ).attr( 'total_saldo', 0 );
        $( '[name=select_almacen]' ).val( '' );

        $( 'div[evento=true][estatus=true]' ).each( function(a, b){
            $( this ).attr( 'estatus', 'false' );
            $( this ).find( 'input' ).prop( 'checked', false );
        });

        $( '[name=metodosentrega]:checked' ).prop( 'checked', false );
        $( '[name=metodosentrega]' ).change();
        $( '.me_formulario, .me_costo, .me_respuesta' ).hide();
        
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
        var metodoentrega_activo = $( '[name=metodosentrega]:checked' ).val() != '' ? $( '[name=metodosentrega]:checked' ).val() : null,
            entrega = $( 'div[domicilio_id]' ).attr( 'domicilio_id' );

        if( metodoentrega_activo == undefined) metodoentrega_activo = null;

        pedido.data.costoxbulto = 0;
        entrega = null;

        $( '.me_formulario, .me_costo, .me_respuesta' ).hide();

        if( metodoentrega_activo != null && parseInt( pedido.data.peso ) > 0 ) {
            pedido.data.costoxbulto = parseFloat( metodosentrega[ metodoentrega_activo ].settings.costo, 2 );

            $( '.me_descripcion' ).html( metodosentrega[ metodoentrega_activo ].settings.descripcion );
            $( '.me_respuesta' ).show();

            if( metodoentrega_activo.substring( 0, 2 ) == '00' ){
                $( '.me_formulario[mp=almacen]' ).show();
                entrega = $( '[name=select_almacen]' ).val();
                load_inventario( entrega );

                pedido.data.domicilio  = null;

                if( entrega ){
                    pedido.data.costoxbulto = parseFloat( tarifas[ almacenes[ entrega ].settings.tarifa ], 2 );
                }
                
            }  

            else if( metodoentrega_activo.substring(3) == 'CELULAR'){
                $( '.me_formulario[mp=celular]' ).show();
                entrega = $( '[name=select_celular]' ).val();
                pedido.data.domicilio = null;
                pedido.data.costoxbulto = 0;
            }

/*             else if( metodoentrega_activo.substring(3) == 'GAS'){
                $( '.me_formulario[mp=tarjeta]' ).show();
                entrega = pedido.usuario_id;
                pedido.data.domicilio = null;
                pedido.data.costoxbulto = 0;
            }
 */
            else{
                $( '.me_formulario[mp=domicilio]' ).show();
                pedido.data.domicilio = $( 'div[domicilio_id]' ).attr( 'domicilio_id' ); // domicilios[ entrega ];
                entrega = pedido.data.domicilio;
            }
        }
        else{
            metodoentrega_activo = null;
            entrega = null;
            pedido.data.domicilio = null;

            if( modelo == '40-GASOLINAS' ){
                metodoentrega_activo = '15-GAS';
                entrega = pedido.usuario_id;
                pedido.data.costoxbulto = 0;
            }
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
        pedido.data.costoxbulto = parseFloat( tarifas[ almacenes[ entrega ].settings.tarifa ], 2 );
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
    // $( 'button[name=metodopago]' ).show();
    $( 'button[name=metodopago], div.metodopago' ).show();
    // $( 'img[metodopago]' ).show();

    if( !( pagado || bloqueado || cancelado ) ) update_pedido( "inicial" );

    if( $( '[name=metodosentrega]' ).length == 1 ){
     //   $( '[name=metodosentrega]' ).click();
    }    

    $( '#calcula_pago' ).on( 'change', function(){
        var o = $( this ).val(),
            t = $( '#calcula_pago > option[value=' + o + ']' ).attr( 'tipo' ),
            c = parseFloat( $( '#calcula_pago > option[value=' + o + ']' ).attr( 'cantidad' ) ),
            v = 0;

        if( t == 'efectivo' ){
            v = total_pedido + c;
        }
        else if( t == 'porcentaje' ){
            v = total_pedido + Math.ceil( ( total_pedido * c / 100 ) );
        }

        $( '#calcula_total' ).text( Moneda.format( v ) );
    });


    $( '.switch-evento' ).on( 'change', function(){
        var padre   = $( this ).closest( 'div[promocion]' ),
            promo   = padre.attr( 'promocion' ),
            estatus = padre.find( 'input.form-check-input' ).is( ':checked' );

        padre.attr( 'estatus', estatus );

        if( estatus ){
            padre.find( '[contenido]' ).show();
        }
        else{
            padre.find( '[contenido]' ).hide();
        }

        if( !pagado )
            update_pedido( "switch evento" );
    });


    $( '.finp' ).on( 'change', function(){
        var rfc    = $( '[name=factura_rfc]' ).val(),
            uso    = $( '[name=factura_uso]' ).val(),
            correo = $( '[name=factura_correo]' ).val(),
            mp    = $( '[name=factura_mp]' ).val(),
            csf    = $( '[name=factura_csf]' ).val();

            if( rfc.length > 12 && 
                csf.length > 12 && 
                correo.length > 8 && 
                uso.length > 2 && 
                mp.length > 1 ){
            $( '#factura_submit' ).prop( 'disabled', false );
        }
        else{
            $( '#factura_submit' ).prop( 'disabled', true );
        }
    });


    $( '.switch-factura' ).on( 'change', function(){
        var alerta  = $( this ).closest( '.alert' ),
            estatus = alerta.find( 'input.form-check-input' ).is( ':checked' );

        if( pedido.data.sat == undefined ){ 
            pedido.data.sat = { 
                'factura' : '', 
                'cfd' : '' 
            }; 
        }

        if( estatus ){

            alerta.removeClass( 'alert-warning' ).addClass( 'alert-success' );
            $( '#factura_mensaje' ).text( 'Con comprobante fiscal' );

            $( '#modal_factura' ).modal( 'show' );
            $( '[name=factura_mp]' ).change();

            pedido.data.sat.factura = '144-FACTURA-PENDIENTE';
        }
        else{
            alerta.addClass( 'alert-warning' ).removeClass( 'alert-success' );
            $( '#factura_mensaje' ).text( '¿Requieres factura?' );

            pedido.data.sat.factura = false;
        }

        
        update_pedido( "switch factura" );
    });


    $( 'div[evento=true][estatus=true]' ).each( function(a, b){
        $( this ).find( 'input' ).click();
    });

    $( '#open_checkout' ).on( 'click', function(){
        $( '#modal_checkout' ).modal( 'show' );
    });

    update_variantes();

    if( update_productos ){
        update_costos();
    }    

    $( '#shoploader' ).hide();
});
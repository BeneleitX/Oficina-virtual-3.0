function update_cantidades(){

    total_productos = parseFloat( $( '[total_productos]' ).attr( 'total_productos' ) );
    total_entrega   = parseFloat( $( '[total_entrega]' ).attr( 'total_entrega' ) );
    total_banco     = parseFloat( $( '[total_banco]' ).attr( 'total_banco' ) );
    
    gran_total      = total_productos + total_entrega + total_banco;

    $( '[total_productos]' ).html( Moneda.format( total_productos ) );    
    $( '[total_entrega]' ).html( Moneda.format( total_entrega ) );    
    $( '[total_banco]' ).html( Moneda.format( total_banco ) );    
    $( '[gran_total]' ).attr( 'gran_total', gran_total ).html( Moneda.format( gran_total ) );

    json = JSON.stringify( pedido );
    $.ajax({
        url: base_url + "save_pedido", 
        type: "POST",
        contentType: 'application/x-www-form-urlencoded; charset=UTF-8',
        data: { [csrf_token] : csrf_hash, json : json },
        success: function( result ){
            // window.location.href = base_url + 'checkout/' + modelo;
        }
    });   
}


$(document).ready(function()
{
    // Elegir metodo de pago
	$( '[name=metodospago]' ).on( 'change', function(){
        var metodopago_activo = $( '[name=metodospago]:checked' ).val();
        
        $( '.mp_nombre' ).html( metodospago[ metodopago_activo ].nombre );
        $( '.mp_descripcion' ).html( metodospago[ metodopago_activo ].settings.descripcion );
        $( '.mp_formularios' ).hide();
        $( '.mp_formularios[mp=' + metodopago_activo + ']' ).show();
        $( '.mp_respuesta' ).show();

        if( metodospago[ metodopago_activo ].settings.tipocomision == 'porcentaje'){
            comision = Math.ceil( metodospago[ metodopago_activo ].settings.comision * $( '[total_productos]' ).attr( 'total_productos' ) / 100 );
        }
        else{
            comision = metodospago[ metodopago_activo ].settings.comision;
        }

        $( '.mp_costo' ).html( 'Utilizar este método de pago, genera un cargo operativo por ' + 
        Moneda.format( comision ) );

        $( '[total_banco]' ).attr( 'total_banco', comision );

        pedido.metodopago_codigo  = metodopago_activo;
        pedido.data.comisionbanco = comision;

        update_cantidades();
    });
    
    // elige metodo de entrega
    $( '[name=metodosentrega]' ).on( 'change', function(){
        var metodoentrega_activo = $( '[name=metodosentrega]:checked' ).val(),
            entrega = $( 'div[domicilio_id]' ).attr( 'domicilio_id' ),
            costo = metodosentrega[ metodoentrega_activo ].settings.costo;

        $( '.me_nombre' ).html( metodosentrega[ metodoentrega_activo ].nombre );
        $( '.me_descripcion' ).html( metodosentrega[ metodoentrega_activo ].settings.descripcion );
        $( '.me_formulario, .me_costo' ).hide();
        $( '.me_respuesta' ).show();

        if( metodoentrega_activo.substring(0,2) == '00'){
            $( '.me_formulario[mp=almacen]' ).show();
            entrega = $( '[name=select_almacen]' ).val();
            costo = tarifas[ almacenes[ entrega ].settings.tarifa ];
        }
        else{
            $( '.me_formulario[mp=domicilio]' ).show();
        }

        $( '[total_entrega]' ).attr( 'total_entrega', costo );
        $( '.me_costo' ).html( 'Utilizar este método de entrega, genera un costo de ' + 
        Moneda.format( costo ) ).show();

        pedido.data.entrega = entrega;
        pedido.data.comisionentrega = costo;
        pedido.metodoentrega_codigo = metodoentrega_activo;

        update_cantidades(); 
    } );

    // Al cambiar almacen actualizar tarifa
    $( '[name=select_almacen]' ).on( 'change', function(){
        var entrega = $( this ).val(),
            metodoentrega_activo = $( '[name=metodosentrega]:checked' ).val(),
            costo = tarifas[ almacenes[ entrega ].settings.tarifa ];

        $( '[total_entrega]' ).attr( 'total_entrega', costo );
        $( '.me_costo' ).html( 'Utilizar este método de entrega, genera un costo de ' + 
        Moneda.format( costo ) ).show();

        pedido.data.entrega = entrega;
        pedido.data.comisionentrega = costo;
        pedido.metodoentrega_codigo = metodoentrega_activo;

        update_cantidades();
    } );

    $( 'button[domicilio_id]' ).on( 'click', function(){
        var domicilio = $( this ).attr( 'domicilio_id' ),
            html      = $( this ).clone().html();

        $( 'div[domicilio_id]').attr( 'domicilio_id', domicilio )
        $( 'div[domicilio_id]').html( html );
        $( '#modal_domicilios' ).modal( 'hide' );

        pedido.data.entrega = domicilio;
        
        update_cantidades();
    } );

});
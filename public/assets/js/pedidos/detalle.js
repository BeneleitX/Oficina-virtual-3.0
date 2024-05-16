function update_cantidades(){

    total_productos = parseFloat( $( '[total_productos]' ).attr( 'total_productos' ) );
    total_entrega   = parseFloat( $( '[total_entrega]' ).attr( 'total_entrega' ) );
    total_saldo     = parseFloat( $( '[total_saldo]' ).attr( 'total_saldo' ) );
    gran_total      = total_productos + total_entrega - total_saldo ;

    $( '[total_entrega]' ).html( Moneda.format( total_entrega ) );    
    $( '[gran_total]' ).attr( 'gran_total', gran_total ).html( Moneda.format( gran_total ) );

    $( '[name=metodopago]' ).each( function( a, b){
        var metodopago = $( this ).attr( 'value' ),
            cantidad   = $( this ).find( '.cantidad' ),
            subtotal   = parseInt( $( '[gran_total]' ).attr( 'gran_total' ) ),
            total_pago = parseInt( metodospago[ metodopago ].settings.comision );



        cantidad.html( Moneda.format( total_pago + subtotal ) );
    });

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

    
    // elige metodo de entrega
    $( '[name=metodosentrega]' ).on( 'change', function(){
        var metodoentrega_activo = $( '[name=metodosentrega]:checked' ).val(),
            entrega = $( 'div[domicilio_id]' ).attr( 'domicilio_id' ),
            costo = metodosentrega[ metodoentrega_activo ].settings.costo;

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
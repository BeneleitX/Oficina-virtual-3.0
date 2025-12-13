
function actualiza_pin( pin ){
    var tr = $( 'tr[pin=' + pin + ']' ),
        ventana = $( '#actualiza_pin' ),
        pin = tr.attr( 'pin' ),
        estatus_codigo = tr.attr( 'estatus_codigo' ),
        fecha = tr.attr( 'fecha' ),
        entrega_fecha = tr.attr( 'entrega_fecha' ),
        entrega_lugar = tr.attr( 'entrega_lugar' ),
        comentarios = tr.attr( 'comentarios' );

    ventana.find( '[name=pin]' ).val( pin );
    ventana.find( '[name=estatus_codigo]' ).val( estatus_codigo );
    ventana.find( '[name=fecha]' ).val( fecha );
    ventana.find( '[name=entrega_fecha]' ).val( entrega_fecha );
    ventana.find( '[name=entrega_lugar]' ).val( entrega_lugar );
    ventana.find( '[name=comentarios]' ).val( comentarios );

    ventana.modal( 'show' );
}


$(document).ready(function(){

    new DataTable('#tabla_rangos', {
        pageLength: 100
    });

});
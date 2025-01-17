function registra_folio( pedido ){
    var referencia = $( 'tr[pedido=' + pedido + ']' ).attr( 'referencia' ),
        rfc        = $( 'tr[pedido=' + pedido + ']' ).attr( 'rfc' ),
        link       = $( 'tr[pedido=' + pedido + ']' ).attr( 'link' );
    
    $( '[name= r_pedido]' ).val( pedido );
    $( '[name= r_referencia]' ).val( referencia );
    $( '[name= r_rfc]' ).val( rfc );
    $( '#r_link' ).attr( 'href', link );

    $( '#modal_factura' ).modal( 'show' );
}

$(document).ready(function(){

    new DataTable('#tabla_pedidos', {
        pageLength: 500
    });

});
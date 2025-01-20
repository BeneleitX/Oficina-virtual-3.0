function registra_folio( pedido ){
    var referencia = $( 'tr[pedido=' + pedido + ']' ).attr( 'referencia' ),
        rfc        = $( 'tr[pedido=' + pedido + ']' ).attr( 'rfc' ),
        correo     = $( 'tr[pedido=' + pedido + ']' ).attr( 'correo' ),
        mp         = $( 'tr[pedido=' + pedido + ']' ).attr( 'mp' ),
        uso        = $( 'tr[pedido=' + pedido + ']' ).attr( 'uso' ),
        link       = $( 'tr[pedido=' + pedido + ']' ).attr( 'link' );
    
    switch( mp){
        case 'DR' : mp = 'Depósito referenciado'; break;
        case 'DE' : mp = 'Depósito en efectivo'; break;
        case 'TE' : mp = 'Transferencia electrónica'; break;
        case 'TC' : mp = 'Tarjeta de crédito'; break;
        case 'TD' : mp = 'Tarjeta de débito'; break;
    }


    switch( uso){
        case 'G01' : uso = 'Adquisición de mercancías'; break;
        case 'G03' : uso = 'Gastos en general'; break;
        case 'S01' : uso = 'Sin efectos fiscales'; break;
    }


    $( '[name= r_pedido]' ).val( pedido );
    $( '[name= r_referencia]' ).val( referencia );
    $( '[name= r_correo]' ).val( correo );
    $( '[name= r_mp]' ).val( mp );
    $( '[name= r_uso]' ).val( uso );
    $( '[name= r_rfc]' ).val( rfc );
    $( '#r_link' ).attr( 'href', link );

    $( '#modal_factura' ).modal( 'show' );
}

$(document).ready(function(){

    new DataTable('#tabla_pedidos', {
        pageLength: 500
    });

});
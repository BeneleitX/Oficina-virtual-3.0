function edita_saldo( socio ){
    var tr      = $( 'tr[socio=' + socio + ']' ),
        s_id    = tr.find( '.s_id' ),
        s_datos = tr.find( '.s_datos' )

        $( '#contenido' ).html( s_id.html() + ' ' + s_datos.html() );
        $( '[name=socio_saldo]' ).val( socio );

        $.each( modelos, function( a, b ){
            var saldo = tr.find( 'td[modelo=' + a + ']' ).attr( 'saldo' );
            $( '.saldo[modelo=' + a + ']' ).val( saldo );
        });

    $( '#edita_saldo' ).modal( 'show' );
}


$(document).ready(function(){

    new DataTable('#tabla_saldos', {
        pageLength: 50
    });

});
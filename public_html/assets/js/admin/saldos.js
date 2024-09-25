function edita_saldo( socio ){
    var tr      = $( 'tr[socio=' + socio + ']' ),
        s_id    = tr.find( '.s_id' ),
        s_datos = tr.find( '.s_datos' )

        $( '#contenido' ).html( s_id.html() + ' ' + s_datos.html() );
        $( '[name=socio_saldo]' ).val( socio );

        $.each( modelos, function( a, b ){
            var saldo = tr.find( 'td[modelo=' + a + ']' ).attr( 'saldo' );
            $( '#edita_saldo tr[modelo=' + a + '] input.saldo' ).val( saldo ?? 0 );
            var estatus = tr.find( 'td[modelo=' + a + ']' ).attr( 'estatus' );

            $( '#edita_saldo tr[modelo=' + a + '] input[estatus]' ).prop( 'checked', (estatus ?? 0 ) == 1 );
        });

    $( '#edita_saldo' ).modal( 'show' );
}


$(document).ready(function(){

    new DataTable('#tabla_saldos', {
        pageLength: 50
    });

});
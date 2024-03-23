
$(document).ready(function(){

    new DataTable('#tabla_credenciales');

    $( '.aprueba' ).on( 'click', function(){
        var socio = $( this ).closest( 'tr' ).attr( 'socio' );

        $( 'input[name=socio]' ).val( socio );
        $( '#modal_aceptar' ).modal( 'show' );
    });

    $( '.rechaza' ).on( 'click', function(){
        var socio = $( this ).closest( 'tr' ).attr( 'socio' );

        $( 'input[name=socio]' ).val( socio );
        $( '#modal_rechazar' ).modal( 'show' );
    });
});

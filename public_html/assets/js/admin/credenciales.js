function aprueba( socio ){
    $( 'input[name=socio]' ).val( socio );
    $( '#modal_aceptar' ).modal( 'show' );
}

function rechaza( socio ){
    $( 'input[name=socio]' ).val( socio );
    $( '#modal_rechazar' ).modal( 'show' );
}


$(document).ready(function(){

    new DataTable('#tabla_credenciales', {
        pageLength: 50
    });

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

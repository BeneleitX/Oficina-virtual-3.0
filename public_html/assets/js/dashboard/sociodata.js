

$(document).ready(function(){

    $( '#activa_editar' ).on( 'click', function(){
        $( 'input' ).prop( 'disabled', false ).addClass( 'border border-red' );
        $( '#edicion' ).show();
    });    
});

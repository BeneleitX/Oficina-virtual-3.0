$(document).ready(function(){
    $( '#form_recover' ).on( 'submit', function(){
        
        id = $( '[name=socio_id]' ).val();
        $( '[name=socio_id]' ).val( id.trim() );

        correo = $( '[name=socio_correo]' ).val();
        $( '[name=socio_correo]' ).val( correo.trim() );

        telefono = $( '[name=socio_telefono]' ).val();
        $( '[name=socio_telefono]' ).val( telefono.trim() );

    });
});

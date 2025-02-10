function modal_cambia_patrocinador( $patrocinador = null){
    $('#modal_cambia_patrocinador').modal('show');

    
    load_padres();
}


function load_padres( extra = null){
    $( '#datos_patrocinador tr' ).html( loader );
    
    $.ajax({
        url: base_url + 'load_padres',
        type: 'POST',
        data: {
            [csrf_token] : csrf_hash, 
            'patrocinador': $( '#patrocinador_id' ).val()
        },
        success: function( response ){
            $( '#datos_patrocinador tr' ).html( response );

            if( extra ){
                $( '[name=n_patrocinador]' ).val( $( '#patrocinador_id' ).val() );
                $( '#aplicar_cambio' ).prop( 'disabled', false );
            }

            $('[data-bs-toggle="tooltip"]').tooltip({
                container: 'body',
                html: true,
                placement : 'top'
            });               
        }
    });
}

$(document).ready(function(){

    $( '#activa_editar' ).on( 'click', function(e){
        e.stopPropagation();
        e.preventDefault();

        $( 'input' ).prop( 'disabled', false ).addClass( 'border border-red' );
        $( '#edicion' ).show();
    });
});

function modal_cambia_patrocinador( $patrocinador = null){
    $('#modal_cambia_patrocinador').modal('show');

    
    load_padres();
}


function load_padres( extra = null){
    var patrocinadores = {};

    $( '#datos_patrocinador tr' ).html( loader );
    
    $( 'input.pat' ).each( function(){
        patrocinadores[ $( this ).attr( 'modelo' ) ] = $( this ).val();
    });

    $.ajax({
        url: base_url + 'load_padres',
        type: 'POST',
        data: {
            [csrf_token] : csrf_hash, 
            'patrocinador': patrocinadores,
            'n_socio' : $( '[name=n_socio]' ).val()
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

    $( '.form-check-input' ).on( 'change', function(e){
        var valor  = $( this ).is( ':checked' ),
            modelo = $( this ).attr( 'modelo' );

        if( valor ){
            $( '.select_permanentes[modelo=' + modelo + ']' ).prop( 'disabled', false );
        }
        else{
            ninguno = $( '.select_permanentes[modelo=' + modelo + '] > option:first' ).attr( 'value' );
            $( '.select_permanentes[modelo=' + modelo + ']' ).val( ninguno ).prop( 'disabled', true ).change();
        }
    });

    $( '.select_permanentes' ).on( 'change', function(e){
        var valor = $( this ).val();

        if( valor.substring( 3, 5 ) == "--" ){
            $( this ).removeClass( 'bg-teal text-white' );
        }
        else{ 
            $( this ).addClass( 'bg-teal text-white' );
        }
    });

    $( '.select_permanentes' ).each( function(){ 
        $( this ).change(); 
    });

    $( '.form-check-input' ).each( function(){ 
        $( this ).change(); 
    });

    $( '#activa_editar' ).on( 'click', function(e){
        e.stopPropagation();
        e.preventDefault();

        $( 'input' ).prop( 'disabled', false ).addClass( 'border border-red' );
        $( '#edicion' ).show();
    });

    
    $( '#previsualizar' ).on( 'click', function(e){
        e.stopPropagation();
        e.preventDefault();

        load_padres( true );
    });

    new DataTable('#tabla_bitacora', {
        order: [[0, 'desc']],
        pageLength: 50
    });
    
});

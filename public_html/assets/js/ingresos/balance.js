function define_boton(){
    var url    = base_url,
        cadena = [];

    $( '#actualiza' ).hide();

    $( '.opciones' ).each( function( k, e ){
        var valor   = $( this ).val(),
            estatus = $( this ).is( ':checked' );

            if( estatus ){ 
                cadena.push( valor );
                $( '#actualiza' ).show();
            }
    });

    $( '#actualiza' ).attr( 'href', url + 'balance/' + modelo + '/' + periodo + '/' + encodeURIComponent( btoa( JSON.stringify( cadena ) ) ) );
}


$(document).ready(function(){

    new DataTable('.tabla_comisiones', {
        pageLength: 50,
        responsive: true
    });


    $( ".heatmap_dia" ).heatmapper();

    $( ".heatmap_columna" ).on( 'click', function(){
        var periodo = $( this ).attr( 'periodo' );

        window.location.href = base_url + 'balance/' + modelo + '/' + periodo;
    });

    $( '.opciones' ).on( 'change', function(){
        define_boton();
    } );
});
function define_boton(){
    $( '.opciones' ).each( function( k, e ){

    });
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
        var valor   = $( this ).val(),
            estatus = $( this ).is( ':checked' );

        console.log(valor, estatus);

        define_boton();
    } );
});
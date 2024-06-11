
$(document).ready(function(){

    $( ".heatmap_dia" ).heatmapper();

    $( ".heatmap_columna" ).on( 'click', function(){
        var periodo = $( this ).attr( 'periodo' );

        window.location.href = base_url + 'balance/' + modelo + '/' + periodo;
    });
});

$(document).ready(function(){
console.log(3);
    new DataTable('.tabla_comisiones', {
        pageLength: 50
    });


    $( ".heatmap_dia" ).heatmapper();

    $( ".heatmap_columna" ).on( 'click', function(){
        var periodo = $( this ).attr( 'periodo' );

        window.location.href = base_url + 'balance/' + modelo + '/' + periodo;
    });
});
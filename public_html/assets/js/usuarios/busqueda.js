
$(document).ready(function(){

    new DataTable('#tabla_historial', {
        pageLength: 10,
        "ordering": false,
    });

    new DataTable('#tabla_resultados', {
        pageLength: 50,
        "ordering": false,
    });    
});
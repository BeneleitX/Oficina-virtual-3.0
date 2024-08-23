
$(document).ready(function(){

    new DataTable('#tabla_paqueteria', {
        pageLength: 50,
        order: [ [ 4, 'desc' ] ]
    });

});
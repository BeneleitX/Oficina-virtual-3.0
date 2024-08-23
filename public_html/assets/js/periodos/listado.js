
$(document).ready(function(){

    new DataTable('#tabla_cortes', {
        pageLength: 50,
        order: [ [ 0, 'desc' ] ]
    });

});
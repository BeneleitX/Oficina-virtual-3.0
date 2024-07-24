
$(document).ready(function(){

    new DataTable('#tabla_productos', {
        pageLength: 50,
        order: [ [ 1, 'asc' ] ]
    });

});
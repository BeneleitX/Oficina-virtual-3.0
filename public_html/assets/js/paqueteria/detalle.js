
$(document).ready(function(){

    new DataTable('#tabla_pedidos', {
        pageLength: 50,
        order: [ [ 4, 'desc' ] ]
    });

});
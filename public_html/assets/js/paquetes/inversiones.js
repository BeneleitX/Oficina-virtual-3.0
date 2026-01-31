

$(document).ready(function(){

    new DataTable('#tabla_socios', {
        pageLength: 10, 
        order: [ [ 4, 'desc' ] ],
        dom: 'rt'
    });

});
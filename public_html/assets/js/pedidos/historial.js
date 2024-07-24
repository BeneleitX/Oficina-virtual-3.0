
$(document).ready(function(){

    new DataTable('#tabla_pedidos', {
        pageLength: 50,
        order: [ [ 6, 'desc' ] ]
    });

});


/*

$(document).ready(function(){

    new DataTable('#tabla_pedidos', {
        pageLength: 20,
        ordering: false,
        ajax: {
            url : base_url + 'historial/fuente',
            type: 'POST',
            data : { 
                [csrf_token] : csrf_hash,
                modelo : modelo,
                socio: socio
            },
            dataType: "json",
			contentType: 'application/x-www-form-urlencoded; charset=UTF-8'
        },
        processing: true,
        serverSide: true        
    });
});

*/
function detalle_pago( folio ){
    var formData = new FormData(),
        modal    = $( '#detalle_pago' );

    formData.append( 'folio', folio );
    formData.append( [csrf_token] , csrf_hash ),
    
    modal.find( '.modal-title' ).html( 'Detalles de pago <span class="badge bg-mustard">' + folio + '</span>' );
    modal.find( '.modal-body' ).html( loader );

    modal.modal( 'show' );
    $.ajax({
        url: base_url + 'pagodata',
        data: formData,
        type: 'POST',
        processData: false,
        contentType: false,
        cache: false,        
        async: true,
        success: function( respuesta ){
            modal.find( '.modal-body' ).html(  respuesta );
        }
    });
}

$( document ).ready(function()
{
    new DataTable('.tabla_pagos', {
        pageLength: 50,
        responsive: true,
        order: [ [ 0, 'desc' ] ]
    });

});


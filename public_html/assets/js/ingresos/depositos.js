function detalle_pago( folio, semana ){
    var formData = new FormData(),
        modal    = $( '#detalle_pago' );
        
    formData.append( 'folio', folio );
    formData.append( [csrf_token] , csrf_hash ),
    
    modal.find( '.modal-title' ).html( 'Detalles de pago <span class="badge bg-mustard">' + folio + '</span> <span class="badge bg-marine">' + semana + '</span>' );
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

    $( '#descarga_pagos' ).on( 'click', function(){
        var btn = $( this );

        btn.addClass( 'disabled' ).html( '<i class="fa-solid fa-circle-notch fa-spin"></i> Procesando...' );

        $.ajax({
            url: base_url + 'excel_pago_comisiones',
            data: { 'modelo' : modelo, [csrf_token] : csrf_hash },
            type: 'POST',
            success: function( file ){
                // download
                btn.removeClass( 'disabled' ).html( '<i class="fa fa-file-excel"></i><span class="d-none d-lg-inline"> Descargar Excel</span>' );
                window.location.href = base_url + file;
            }
        });  
    });
});


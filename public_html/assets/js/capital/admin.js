function transferir_fondos( socio, inversion ){
    var tr = $( 'tr[socio=' + socio + ']' );
    $( '[name=r_socio]' ).val( socio );
    $( '#m_titulo' ).html( '<span class="badge bg-teal">' + tr.find( 'td:eq(1)' ).html() + '</span> Inversión: ' + tr.find( 'td:eq(2)' ).html() );
    $( '#modal_retiros .modal-body' ).html( loader );
    $( '#modal_retiros' ).modal( 'show' );

    $.ajax({
        url: base_url + "get_retiros", 
        type: "POST",
        data: { 
            [csrf_token] : csrf_hash, 
            socio        : socio,
            mes          : $( '#mes_retiros' ).val(),
            inversion    : inversion
        },
        success: function( result ){
            $( '#modal_retiros .modal-body' ).html( result );

            new DataTable('#tabla_retiros', {
                pageLength: 500,
                order: [ [ 1, 'desc' ] ]
            });            
        }
    });    
}

$(document).ready(function(){

    new DataTable('#tabla_solicitudes', {
        pageLength: 50, 
    });

    $( '#mes_retiros' ).on( 'change', function(){
        window.location.href = base_url + "capital24/" + $( '#mes_retiros' ).val();
    });

    if( g_todas > 0 ){
        $( '#totales' ).text( g_todas );
        $( '#totales' ).removeClass( 'bg-gray-500' ).addClass( g_todas == g_pagadas ? 'bg-teal' : 'bg-red' );
    
        $( '#pendientes' ).text( g_pagadas );
        $( '#pendientes' ).removeClass( 'bg-gray-500' ).addClass( g_todas == g_pagadas ? 'bg-teal' : 'bg-red' );
    }      
});
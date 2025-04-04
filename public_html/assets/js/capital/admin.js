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


function excel_retiros( mes ){
    var btn = $( this );

    btn.addClass( 'disabled' ).html( '<i class="fa-solid fa-circle-notch fa-spin"></i> Procesando...' );

    $.ajax({
        url: base_url + 'excel_retiros',
        data: { 'mes' : mes, [csrf_token] : csrf_hash },
        type: 'POST',
        success: function( file ){
            // download
            btn.removeClass( 'disabled' ).html( '<i class="fa fa-file-excel"></i><span class="d-none d-lg-inline"> Descargar Excel</span>' );
            window.location.href = base_url + file;
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

    if( g_todas == 0 || g_todas == g_pagadas ) || !es_admin ){
        $( '#entregar_retiros' ).prop( 'disabled', true ).addClass( 'disabled' );
    }

    if( g_todas > 0 ){
        $( '#totales' ).text( g_todas );
        $( '#totales' ).removeClass( 'bg-gray-500' ).addClass( g_todas == g_pagadas ? 'bg-teal' : 'bg-red' );
    
        $( '#pendientes' ).text( g_pagadas );
        $( '#pendientes' ).removeClass( 'bg-gray-500' ).addClass( g_todas == g_pagadas ? 'bg-teal' : 'bg-red' );
    }      
});
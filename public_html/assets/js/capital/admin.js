
$(document).ready(function(){

    new DataTable('#tabla_solicitudes', {
        pageLength: 50
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
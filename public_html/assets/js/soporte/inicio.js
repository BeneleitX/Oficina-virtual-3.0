
function open_modal(){
    $( '#nuevo_ticket' ).modal( 'show' );
}



$(document).ready(function(){
    
    new DataTable('#tabla_tickets', {
        pageLength: 50
    });


});
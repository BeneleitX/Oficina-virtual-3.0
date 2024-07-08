
function save_layout( bloque ){
    
    $.ajax({
        url: base_url + "save_layout", 
        type: "POST",
        contentType: 'application/x-www-form-urlencoded; charset=UTF-8',
        data: { 
            [csrf_token] : csrf_hash, 
            bloque : bloque, 
            valor : $( '[aria-controls=div_' + bloque + ']' ).attr( 'aria-expanded' )
        },
        success: function( result ){
            // console.log( json );
        }
    });
}


$(document).ready(function(){

});

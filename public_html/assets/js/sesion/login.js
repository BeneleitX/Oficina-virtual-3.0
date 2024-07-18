$(document).ready(function(){

    $.ajax({
        url: base_url + 'API/validate_login',
        data: {
            'API_key' : '1234'
        },
        type: 'POST',
        success: function( respuesta ){
            console.log( respuesta );
        }
    });
});

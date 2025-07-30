$(document).ready(function(){

    $( '#update_indicadores' ).on( 'click', function(){
        var modelo = $( '#empresa_indicadores' ).val(),
            mes    = $( '#mes_indicadores' ).val(),
            year   = $( '#year_indicadores' ).val(),
            target = 'indicadores/' + modelo + '/' + year + mes;
        window.location.href = base_url + target;
    });
});

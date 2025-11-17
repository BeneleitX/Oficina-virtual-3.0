
$(document).ready(function(){
    var modelo = null;

    $( '#submit_button' ).on( 'click', function(){
        var btn      = $( this ),
            f_inicio = $( '[name=f_inicia]' ).val(),
            f_final  = $( '[name=f_final]' ).val(),
            d_tipoinversion   = $( '[name=d_tipoinversion]' ).val()

        btn.html( '<i class="fa-solid fa-circle-notch fa-spin"></i> Procesando...' );

        $.ajax({
            url: base_url + 'excel_reporte_inversiones',
            data: { 
                [csrf_token] : csrf_hash, 
                'd_tipoinversion' : d_tipoinversion,
                'f_inicio'   : f_inicio, 
                'f_final'    : f_final },
            type: 'POST',
            success: function( file ){
                // download
                btn.html( '<i class="fa fa-circle-down"></i> Descargar Excel' );
                window.location.href = base_url + file;
            }
        });  
    });
});

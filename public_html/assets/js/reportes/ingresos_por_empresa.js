
$( document ).ready(function(){
    
    

    $( '#submit_button' ).on( 'click', function(){
        $( '#tabla_datos' ).html( '<tr><td colspan="6">' + loader + '</td></tr>' );

        var btn     = $( this ),
        inicia      = $( '[name=d_inicia]'  ).val(),
        termina     = $( '[name=d_termina]'  ).val();

        btn.addClass( 'disabled' ).html( '<i class="fa-solid fa-circle-notch fa-spin"></i> Procesando...' );

        $.ajax({
            url: base_url + 'tabla_ingresos_por_empresa',
            data: { 'inicia' : inicia, [csrf_token] : csrf_hash, termina: termina },
            type: 'POST',
            success: function( data ){
                // download
                btn.removeClass( 'disabled' ).html( '<i class="fa fa-redo"></i> Actualizar datos' );
                $( '#tabla_datos' ).html( data);
            }
        });  
    });

    $( '#submit_button' ).click();
});

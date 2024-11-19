
$(document).ready(function(){
    var ee = '';

    $( '[name=d_modelo]' ).on ( 'change', function(){
        var modelo = $( this ).val();

        $( 'div.form-check' ).each( function(){
            var modelos = $( this ).attr( 'modelos' );

            $( this ).css( 'display', modelo.length > 3 && modelos.includes( modelo ) ? 'block' : 'none' );
            $( this).find( '[name=d_estatus]' ).prop( 'checked', false );
        });

        $( '#submit_button' ).prop( 'disabled', true );
    });

    $( '[name=d_modelo]' ).change();

    $( '[name=d_estatus]' ).on ( 'change', function(){
        $( '#submit_button' ).prop( 'disabled', false );
        ee =  $( this ).val();
    });

    $( '#submit_button' ).on( 'click', function(){
        var btn     = $( this ),
            modelo  = $( '[name=d_modelo]'  ).val(),
            estatus = ee;

        btn.addClass( 'disabled' ).html( '<i class="fa-solid fa-circle-notch fa-spin"></i> Procesando...' );

        $.ajax({
            url: base_url + 'excel_socios_por_estatus',
            data: { 'modelo' : modelo, [csrf_token] : csrf_hash, estatus: estatus },
            type: 'POST',
            success: function( file ){
                // download
                btn.removeClass( 'disabled' ).html( '<i class="fa fa-circle-down"></i> Descargar Excel' );
                window.location.href = base_url + file;
            }
        });  
    });
});

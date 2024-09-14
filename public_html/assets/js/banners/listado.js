
$(document).ready(function(){

    $( '#nuevo_banner' ).on( 'click', function(){
        var banner_id = null,
            banner_descripcion    = '',
            banner_fecha_inicia   = '',
            banner_fecha_vigencia = '',
            imagen    = $( '#preview' ).attr( 'gato' );

        $( 'input[name=banner_imagen]' ).prop( 'disabled', false );

        $( '#preview' ).attr( 'src', imagen );
        $( '#banner_submit' ).prop( 'disabled', true );

        $( 'input[name=banner_id]' ).val( banner_id );
        $( 'textarea[name=banner_descripcion]' ).val( banner_descripcion    );
        $( 'input[name=banner_fecha_inicia]'   ).val( banner_fecha_inicia   );
        $( 'input[name=banner_fecha_vigencia]' ).val( banner_fecha_vigencia );
        
        $( '#edita_banner' ).modal( 'show' );
    } );


    $( '.lanza_modal' ).on( 'click', function(){
        var tr        = $( this ).closest( 'tr' ),
            banner_id = tr.attr( 'banner' ),
            banner_descripcion    = tr.find( 'td:eq(2)' ).text(),
            banner_fecha_inicia   = tr.attr( 'inicia' ),
            banner_fecha_vigencia = tr.attr( 'vigencia' ),
            imagen    = tr.find( 'img' ).attr( 'src' );

        $( 'input[name=banner_imagen]' ).prop( 'disabled', true );
        $( '#banner_submit' ).prop( 'disabled', false );

        $( '#preview' ).attr( 'src', imagen );
        $( 'input[name=banner_id]' ).val( banner_id );
        $( 'textarea[name=banner_descripcion]' ).val( banner_descripcion    );
        $( 'input[name=banner_fecha_inicia]'   ).val( banner_fecha_inicia   );
        $( 'input[name=banner_fecha_vigencia]' ).val( banner_fecha_vigencia );
        

        $( '#edita_banner' ).modal( 'show' );
    } );


    $( 'input.upload').on( 'change', function(){
		var campo = $( this ),
			formData = new FormData();

		formData.append( 'image', $( 'input.upload' )[0].files[0] ); 
		formData.append( [csrf_token] , csrf_hash ),

		$.ajax({
			url: base_url + 'upload_banner',
			data: formData,
			type: 'POST',
			dataType: "json",
			async: true,
			contentType: false, // NEEDED, DON'T OMIT THIS (requires jQuery 1.6+)
			processData: false, // NEEDED, DON'T OMIT THIS
			success: function( respuesta ){
				$( '#preview' ).attr( 'src', respuesta[ 0 ] + respuesta[ 1 ] );
                $( 'input[name=banner_archivo]' ).val( respuesta[ 1 ] );
                $( '#banner_submit' ).prop( 'disabled', false );
			}
		});
	});


    $("#form_banner").submit(function()
    {
        var inicia   = $( 'input[name=banner_fecha_inicia]'   ).val(),
            vigencia = $( 'input[name=banner_fecha_vigencia]' ).val();

        if( !inicia || !vigencia || inicia > vigencia ){
            $( '.alert.alert-info' ).removeClass( 'alert-info' ).addClass( 'alert-danger' );
            return false;
        }

        return true; // ensure form still submits
    });
});


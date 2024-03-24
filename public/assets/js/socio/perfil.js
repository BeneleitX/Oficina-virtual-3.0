
function borra_beneficiario( id){

	$( '[name=old_beneficiario]' ).val( id );
	$( '#borra_beneficiario' ).modal( 'show' );
}

function edita_clabe(){
	$( '#nota_clabe' ).slideDown();
	$( '#clabe' ).attr( 'disabled', false );
	$( '#clabe' ).focus();
}


$(document).ready(function(){

    $( '#imagen_avatar' ).on( 'mouseover', function(){
        $( '#cambia_avatar' ).show();
    }).on( 'mouseout', function(){
        $( '#cambia_avatar' ).hide();
    });
 
    $( '#cambia_avatar' ).on( 'mouseover', function(){
        $( '#cambia_avatar' ).show();
    }).on( 'click', function(){
        $('#profileImageModal').modal( 'show' );    
    });


	$( '#clabe' ).on( 'keyup', function(){
		var clabe = $( this ).val();

		if( clabe.length < 3 ){
			$( '#clabe_banco' ).attr( 'src', base_url + 'assets/img/blank.png' );
		}
		else{
			$( '#clabe_banco' ).attr( 'src', base_url + 'assets/img/bancos/' + clabe.slice(0,3) + '.png' );
		}
	});

	$('#clabe_banco').on('error', function() {
		$( this ).attr( 'src', base_url + 'assets/img/blank.png' );
		$( this ).onerror = null;
    });


	$( 'input.upload').on( 'change', function(){
		var campo = $( this ),
			tipo  = campo.attr( 'tipo' ),
			formData = new FormData();

		formData.append( 'tipo', tipo );
		formData.append( 'image', $( 'input.upload[tipo=' +tipo+ ']' )[0].files[0] ); 
		formData.append( [csrf_token] , csrf_hash ),

		$.ajax({
			url: base_url + 'credencial',
			data: formData,
			type: 'POST',
			dataType: "json",
			async: true,
			contentType: false, // NEEDED, DON'T OMIT THIS (requires jQuery 1.6+)
			processData: false, // NEEDED, DON'T OMIT THIS
			success: function( respuesta ){
				$( '.ct_' + tipo ).html( '<img src="' + respuesta.path + respuesta[tipo] + '" alt="" class="img-fluid rounded-3">\n<a href="' + base_url + 'cancela_ine/' + tipo + '" class="small"><i class="fa fa-trash"></i> Cancelar esta foto</a>' );

				
				if( respuesta.frente && respuesta.reverso ){
					$( '#valida_credencial' ).removeClass( 'disabled' );
				}
			}
		});
	});

});    

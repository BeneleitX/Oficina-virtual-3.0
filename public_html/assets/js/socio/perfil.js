
function borra_beneficiario( id){

	$( '[name=old_beneficiario]' ).val( id );
	$( '#borra_beneficiario' ).modal( 'show' );
}

function edita_clabe(){
	$( '#nota_clabe' ).slideDown();
	$( '#clabe' ).attr( 'disabled', false );
	$( '#clabe' ).focus();
}


function agrega_colonia(){
	$( '[name=tipo_colonia]').val( 'nueva' );
	$( '#colonia_select' ).hide();
	$( '#aviso_colonia_select' ).hide();
	$( '#colonia_nueva' ).val( '' ).show();
	$( '#aviso_colonia_nueva' ).show();
}

function regresar_colonia(){
	$( '[name=tipo_colonia]').val( 'select' );
	$( '#colonia_select' ).val( 0 ).show();
	$( '#aviso_colonia_select' ).show();
	$( '#colonia_nueva' ).hide();
	$( '#aviso_colonia_nueva' ).hide();
}

function edita_domicilio( dom_id ){
	var dom = $( '[dom_id=' + dom_id + ']' );

	$( '[name=dom_id]' ).val( dom_id );

	$( '[name=n_nombre]' ).val( dom.find( '.d_nombre' ).text() );
	$( '[name=n_calle]' ).val( dom.find( '.d_calle' ).text() );
	$( '[name=n_referencias]' ).val( dom.attr( 'referencias' ) );
	$( '[name=n_cp]' ).val( dom.find( '.d_cp' ).text() );
	go_cp( dom.attr( 'colonia_id' ) );

	$( '#modal_domicilio' ).modal( 'show' );

	$( '[name=n_cp]' ).attr( 'carga_colonia', '');
}


function go_cp( colonia = null ){
	$( '#getCP' ).removeClass( 'is-invalid' );
	$( '#n_colonia').empty();
	regresar_colonia();

	var cp = $( '#getCP' ).val();

	if( cp.length == 5 ){
		$.ajax({
			url: base_url + "valida_cp", 
			type: "POST",
			dataType: "json",
			contentType: 'application/x-www-form-urlencoded; charset=UTF-8',
			data: { [csrf_token] : csrf_hash, cp : cp },
			success: function( result ){
				
				if( result.error ){
					$( '#getCP' ).addClass( 'is-invalid' );
					$( '#n_colonia').prop( 'disabled', true );
				}
				else{
					// si hay entidad y localidad
					

					$( '#n_localidad' ).val( result.localidad.nombre );
					$( '[name=n_localidad_id]' ).val( result.localidad.id );
					$( '#n_entidad' ).val( result.entidad.nombre );
					$( '[name=n_entidad_id]' ).val( result.entidad.id );

					if( result.total > 0){
						$( '#n_colonia').prop( 'disabled', false );
						$( '#n_colonia' ).append( '<option value="0" selected>SELECCIONA...</option>' );

						$.each( result.colonias, function( k, c ){
							selected = '';

							$( '#n_colonia' ).append( '<option value="' + c.id + '" ' + ( colonia == c.id ? 'selected' : '' ) + '>' + c.nombre + '</option>' );
						});
					}
					else{
						$( '#n_colonia').prop( 'disabled', true );
					}
				}
			}
		});
	}
	else{
		$( '#getCP' ).addClass( 'is-invalid' );
	}	
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

				
				if( ( respuesta.frente && respuesta.reverso ) || respuesta.acta ){
					$( '#valida_credencial' ).removeClass( 'disabled' );
				}
			}
		});
	});

	$( '#nuevo_domicilio' ).on( 'click', function(){
		$( '[name=dom_id]' ).val( 0 );

		$( '#modal_domicilio' ).modal( 'show' );
	});


	$( '#getCP' ).keyup(delay(function (e) {
		go_cp();
	}, 1000));


	$( '#submit_domicilio' ).on( 'click', function(){
		var ok = true,
			tipo = $( '[name=tipo_colonia]' ).val();

		if( $( '[name=n_nombre]' ).val().length < 1 ){
			$( '[name=n_nombre]' ).addClass( 'is-invalid' );
			ok = false;
		}
		else{
			$( '[name=n_nombre]' ).removeClass( 'is-invalid' );
		}
		
		if( $( '[name=n_calle]' ).val().length < 3 ){
			$( '[name=n_calle]' ).addClass( 'is-invalid' );
			ok = false;
		}
		else{
			$( '[name=n_calle]' ).removeClass( 'is-invalid' );
		}

		if( $( '[name=n_cp]' ).val().length < 3 ){
			$( '[name=n_cp]' ).addClass( 'is-invalid' );
			ok = false;
		}
		else{
			$( '[name=n_cp]' ).removeClass( 'is-invalid' );
		}

		if( tipo == 'nueva' ){
			if( $( '[name=n_colonia_nueva]' ).val().length < 3 ){
				$( '[name=n_colonia_nueva]' ).addClass( 'is-invalid' );
				ok = false;
			}
			else{
				$( '[name=n_colonia_nueva]' ).removeClass( 'is-invalid' );
			}	
		}
		else{
			if( $( '[name=n_colonia]' ).val() == 0 ){
				$( '[name=n_colonia]' ).addClass( 'is-invalid' );
				ok = false;
			}
			else{
				$( '[name=n_colonia]' ).removeClass( 'is-invalid' );
			}	
		}	

		if( ok ){
			formData = new FormData( $( '#form_domicilio' )[0] );

			$.ajax({
				url: base_url + "create_domicilio", 
				type: "POST",
				contentType: false,
				processData: false,
				data: formData,
				success: function( result ){
					if( result > 0 ){
						window.location.href = base_url + "perfil";
					}
				}
			});
		}
	});

	$( '#check_sat' ).on( 'change', function(){
		var valor = $( this ).is( ':checked' );

		$.ajax({
			url: base_url + "check_csf", 
			type: "POST",
			dataType: "json",
			contentType: 'application/x-www-form-urlencoded; charset=UTF-8',
			data: { [csrf_token] : csrf_hash, check : valor }
		});

		// activo
		if( valor ){
			$( '#sube_csf' ).slideUp();
		}

		// inactivo
		else{
			$( '#sube_csf' ).slideDown();
		}
	});

	$( '#carga_csf').on( 'change', function(){
		var campo = $( this ),
			formData = new FormData();

		formData.append( 'pdf', $( this )[0].files[0] ); 
		formData.append( [csrf_token] , csrf_hash ),

		$.ajax({
			url: base_url + 'carga_csf',
			data: formData,
			type: 'POST',
			dataType: "json",
			async: true,
			contentType: false, // NEEDED, DON'T OMIT THIS (requires jQuery 1.6+)
			processData: false, // NEEDED, DON'T OMIT THIS
			success: function( respuesta ){
				window.location.href = base_url + "perfil";
			}
		});
	});

});    

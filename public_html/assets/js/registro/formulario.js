function get_pat(){
	var pat = parseInt( $( '[name=patrocinador]' ).val() );

	$( '.verificado, .noverifica' ).empty();
	$( '[name=patrocinador]' ).removeClass( 'is-invalid' );

	if( pat ){
		$.ajax({
			url: base_url + "valida_patrocinador", 
			type: "POST",
			dataType: "json",
			contentType: 'application/x-www-form-urlencoded; charset=UTF-8',
			data: { [csrf_token] : csrf_hash, id : pat, avatar_size: 40 },
			success: function( result ){

				
				if( result.error ){
					 $( '.noverifica' ).html( result.error );
					 $( '[name=patrocinador]' ).addClass( 'is-invalid' );
				}
				else{
					// se debe de meter un true a un campo hidden 

					$( '.verificado' ).html( '<table><tr><td style="padding-right:10px">' + result.avatar+ '</td><td class="lh-1">' + result.nombre + '<br><span class="small text-green"><i class="fa fa-check"></i> Verificado</span></td></tr></table>' );
				}
			}
		});
	}	
}


function selectOption() {
	var icon 	   = $( this ).find( '.iconify' ).clone( true ),
		phone_code = $( this ).find( 'strong' ).text(),
		nombre     = $( this ).attr( 'nacionalidad' ),
		code 	   = $( this ).attr( 'country' ),
		padre 	   = $( this ).closest( '.select-box' ).attr( 'id' );

	$( '[tipo=' + padre + '] div' ).html( '' ).append( icon );
    $( '[tipo=' + padre + '] div' ).removeClass( 'active' );
    $( '#' + padre + ' .options' ).removeClass( 'active' );
    $( '#' + padre + ' .options' ).find('.hide').removeClass( 'hide' );
    $( '#' + padre + ' .search-box' ).val( '' );

	if( padre == 'nacionalidad' ){
		$( 'input[name=nacion]' ).val( nombre );
		$( 'input[name=origen]' ).val( code );
	}
	else if( padre == 'telefono' ){
		$( '#codigo' ).text( phone_code );
		$( 'input[name=pais]' ).val( code );
		$( 'input[name=code]' ).val( phone_code );
	}
}

function searchCountry() {
	var padre        = $( this ).closest( '.select-box' ).attr( 'id' );

    let search_query = $( '#' + padre + ' .search-box' ).val().toLowerCase();
    
	$( '#' + padre + ' .option' ).each( function( id, opcion ){
        let is_matched = $( this ).find('.country-name').text().toLowerCase().includes(search_query);
        $( this ).toggleClass( 'hide', !is_matched );
    });
}





$(document).ready(function(){

	if( parseInt( $( '[name=patrocinador]' ).val() ) ){
		get_pat();
	}

	$( '#submit_login' ).on( 'click', function( e ){
		var patrocinador = parseInt( $( '[name=patrocinador]' ).val() );

		if( patrocinador ){
			$( '#modal_confirma' ).modal( 'show' );
		}
		else{
			$( '#hidden_submit' ).click();
		}
	});

	$( '#submit_ok' ).on( 'click', function(){
		$( this ).attr( 'disabled', true );
		$( '#hidden_submit' ).click();
	});

	$( '[name=patrocinador]' ).keyup(delay(function (e) {
		get_pat();
	}, 600));

	
	// claves telefónicas de país

	$.each( countries, function( id, country ){
		var option = '<li class="option" country="' + country.code + '" nacionalidad="' + country.name + '"><div><span class="iconify rounded-1" data-width="36" data-icon="flag:' + country.code.toLowerCase() + '-4x3"></span><span class="country-name">' + country.name + '</span></div><strong>+' + country.phone + '</strong></li>';
	
		$( '.options' ).find('ol').append( option );
	});
	
	$( '.selected-option' ).on( 'click', function( e ){
		var padre = $( this ).attr( 'tipo' );

		$( this ).find( 'div' ).toggleClass( 'active' );
		$( '#' + padre + ' .options' ).toggleClass( 'active' );
		$( '#' + padre + ' .search-box').focus();
	});
	
	$( '.option' ).on('click', selectOption );
	$( '.search-box' ).on( 'input', searchCountry );

	$( '#nacionalidad [country=US]' ).remove();
	$( '#nacionalidad [country=UM]' ).remove();

	$( '#telefono [country=' + country + ']' ).click();
	$( '#nacionalidad [country=' + origen + ']' ).click();
});


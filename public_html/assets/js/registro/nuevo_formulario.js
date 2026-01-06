function update_paso( paso ) {
    console.log( "carga paso " + paso );

    $( '.paso' ).hide();
    $( ' #titulo_paso' ).text( pasos[paso].titulo );
    $( '.paso[ step="' + paso + '"]' ).show();
}

function valida_paso( paso ) {
    console.log( "valida paso " + paso );

    return true;
}


function bar_progress( direction ) {

    var progress_line_object = $('.f1-progress-line');
	var now_value = progress_line_object.data('now-value');
	var new_value = 0;

	if(direction == 'right') {
		new_value = now_value + ( 100 / pasos.length );
	}
	else if(direction == 'left') {
		new_value = now_value - ( 100 / pasos.length );
	}

    if( paso_activo == 0 ){
        $('.btn-previous').hide();
    }
    else if( paso_activo == pasos.length - 1 ){
        $('.btn-next').hide();
        $('.btn-end').show();
    }
    else{
        $('.btn-previous').show();
        $('.btn-next').show();
        $('.btn-end').hide();
    }

	progress_line_object.attr('style', 'width: ' + new_value + '%;').data('now-value', new_value);
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

var paso_activo = 0;

$(document).ready(function(){
    
    $('.f1 input').on('focus', function() {
    	$(this).removeClass('input-error');
    });
    
    $('.btn-end').hide();
    $('.btn-previous').hide();

    update_paso( paso_activo );

    $('.btn-next').on('click', function() {
    	if( valida_paso( paso_activo ) ) {

            current_active_step = $( '[step=' + paso_activo + ']' );
            current_active_step.removeClass('active flared').addClass('activated').next().addClass('active flared');

            paso_activo++;

            bar_progress('right');
            update_paso( paso_activo );
    	}
    });

    $('.btn-previous').on('click', function() {
    	if( valida_paso( paso_activo ) ) {
            current_active_step = $( '[step=' + paso_activo + ']' );
            current_active_step.removeClass('active flared activated').prev().removeClass('activated').addClass('active flared');

            paso_activo--;

            bar_progress('left');       
            update_paso( paso_activo);
    	}
    });

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

	$( '#telefono [country=MX]' ).click();
	$( '#nacionalidad [country=MX]' ).click();    
});

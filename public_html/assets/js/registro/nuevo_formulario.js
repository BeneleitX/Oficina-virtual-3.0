function update_paso( paso ) {

    bar_progress();      

    $( '.paso' ).hide();
    $( ' #titulo_paso' ).text( pasos[paso].titulo );
    $( '.paso[ step="' + paso + '"]' ).show();

    switch( paso ) {
        case 1:
            // datos personales
            if( request.nacionalidad == 'MX' ) {
                $( '#instrucciones_datos' ).text( 'Tu CURP ha sido validada exitosamente con la autoridad. Tus datos personales se autocompletaron.' );
                $( '.paso[step=1] input, .paso[step=1] select' ).prop( 'disabled', true );
            }
            else{
                $( '#instrucciones_datos' ).text( 'Captura la información tal como aparece en tu identificación oficial vigente con fotografía, la cual será requerida más adelante para validar los datos.' );
                $( '.paso[step=1] input, .paso[step=1] select' ).prop( 'disabled', false );
            }

            break;
    }
}


function error( campo, mensaje ){

    switch( campo ){
        case 'nacionalidad':
            $( '.campo_nacionalidad' ).addClass( 'es-invalido' );
            break;

        case 'sexo':
            $( 'select[name=' + campo + ']' ).addClass( 'is-invalid' );
            break;

        default:
            $( 'input[name=' + campo + ']' ).addClass( 'is-invalid' );
    }

    $( '#' + campo + '_error' ).html( mensaje );
}


function valida_paso( paso ) {
    var avance = true;

    switch( paso ) {

        case 0:
            if( request.nacionalidad == undefined || request.nacionalidad.length != 2 ){
                error( 'nacionalidad', 'Selecciona tu país de residencia para continuar.' );
                avance = false;
            }
            else{
                if( request.nacionalidad == 'US' || request.nacionalidad == 'UM' ) {
                    error( 'nacionalidad', 'Lo sentimos. Por el momento no se aceptan registros de usuarios residentes en los Estados Unidos de América.' );
                    avance = false;
                }
            }

            if( request.nacionalidad == 'MX' ) {
                if( request.curp_verificado == 0 ){
                    error( 'curp', 'CURP no verificada.' );
                    avance = false;
                }
            }
            else{
                if( request.dni != $( 'input[name=dni]' ).val().trim().toUpperCase() ){
                    request.dni = $( 'input[name=dni]' ).val().trim().toUpperCase();
                }

                if( request.dni.length < 5 ){
                    
                    error( 'dni', 'Ingresa tu documento de identificación correctamente.' );
                    avance = false;
                }
            }

            break;

        case 1:

            if( request.nacionalidad != 'MX' ) {
                request.nombre       = $( 'input[name=nombre]' ).val().trim().toUpperCase(),
                request.apellido1    = $( 'input[name=apellido1]' ).val().trim().toUpperCase(),
                request.apellido2    = $( 'input[name=apellido2]' ).val().trim().toUpperCase(),
                request.fechanac     = $( 'input[name=fechanac]' ).val();
                request.sexo         = $( 'select[name=sexo]' ).val();
            }
            
            if( request.nombre == undefined || request.nombre.length < 3 ){
                error( 'nombre', 'Nombre no válido.' );
                avance = false;
            }

            if( request.apellido1 == undefined || request.apellido1.length < 3 ){
                error( 'apellido1', 'Primer apellido no válido.' );
                avance = false;
            }

            if( request.apellido2.length > 0 && request.apellido2.length < 3 ){
                error( 'apellido2', 'Segundo apellido no válido.' );
                avance = false;
            }

            if( request.fechanac == undefined || request.fechanac.length < 3 ){
                error( 'fechanac', 'Fecha de nacimiento no válida.' );
                avance = false;
            }
            else{
                var edad = calcular_edad( request.fechanac );

                if( edad === false ){
                    error( 'fechanac', 'Fecha de nacimiento no válida.' );
                    avance = false;
                }
                else if( edad < 18 ){
                    error( 'fechanac', 'Debes ser mayor de edad para registrarte.' );
                    avance = false;
                }
            }

            if( request.sexo == null || request.sexo.length == 0 ){
                error( 'sexo', 'Selección no válida.' );
                avance = false;
            }
            break;

        case 2:
            if( request.correo_verificado == 0 ){
                error( 'correo', 'Correo electrónico no verificado.' );
                avance = false;
            }
            break;

        case 3:           
            if( request.pat_verificado == 0 ){
                error( 'patrocinador', 'Patrocinador no verificado.' );
                avance = false;
            }
            break;

        case 4:

        default:
            avance = false;
    }

    return avance;
}


function bar_progress() {

    var progress_line_object = $('.f1-progress-line'),
        new_value = ( paso_activo + 1 ) * 100 / pasos.length;

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

    $( 'input[name=nacion]' ).val( nombre );
    $( 'input[name=origen]' ).val( code );

    request.nacionalidad = code;
    request.curp      = null;
    request.nombre    = null;
    request.apellido1 = null;
    request.apellido2 = null;
    request.fechanac  = null;
    request.sexo      = null;

    $( 'input[name=curp], input[name=dni]' ).val( '' )
    $( 'input[name=nombre]' ).val( '' )
    $( 'input[name=apellido1]' ).val( '' )
    $( 'input[name=apellido2]' ).val( '' )
    $( 'input[name=fechanac]' ).val( '' )
    $( 'select[name=sexo]' ).val( '' )

    $('.form-control, .form-select').removeClass('is-invalid');
    $('.form-control, .form-select').next( 'p.small' ).text( '' );

    if( code == 'MX' ){
        $( '#curp_group' ).removeClass( 'd-none' );
        $( '#dni_group' ).addClass( 'd-none' );
    }
    else{
        $( '#curp_group' ).addClass( 'd-none' );
        $( '#dni_group' ).removeClass( 'd-none' );
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

var paso_activo = 0
    request = {
        'version'      : 2,
        'nacionalidad' : null,
        "nombre"       : null,
        "apellido1"    : null,
        "apellido2"    : null,
        "correo"       : null,
        "curp"         : null,
        "sexo"         : null,
        "dni"          : null,
        "patrocinador" : null,
        "valida_curp"  : null,
        "pat_verificado": 0,
        "curp_verificado": 0,
        "correo_verificado": 0
    };


function shoot( modo ) {
    $( '#camara_ine' ).modal( 'show' );
    $( '#camara' ).attr( 'src', base_url + 'camara/' + modo + '/' + tempID );
}

window.closeModal = function( modo ){
    $( '#camara' ).attr( 'src', '' );
    $('#camara_ine').modal('hide');

    $( '#shot_' + modo ).removeClass( 'grayscale' ).attr( 'src', base_url + 'temp/' + tempID + '_' + modo + '.jpg?' + new Date().getTime() );
};

$(document).ready(function(){
    

    $( '.vertical-center').on( 'mouseover', function(){ $( this ).find( '.center-btn').show(); } ).on( 'mouseout', function(){ $( this ).find( '.center-btn').hide(); } );

    $('.form-control, .form-select').on('focus', function() {
    	$(this).removeClass('is-invalid');
        $(this).next( 'p.small' ).text( '' );
        $(this).parent().next( 'p.small' ).text( '' );
    });
    
    $( '.paso' ).css( 'display', 'inline-block' );
    $('.btn-end').hide();
    $('.btn-previous').hide();
    
    paso_activo = 4;
    update_paso( paso_activo );

    // PATROCINADOR

    $( '#patrocinador' ).on( 'keyup', function( e ){
        $( '#pat_card').hide();
        $( '#valida_pat' ).prop( 'disabled', false ).html( '<i class="fa fa-magnifying-glass"></i> Verificar' ).addClass( 'btn-outline-warning' ).removeClass( 'btn-success' );  
        request.pat_verificado = 0; 
    });

    $( '#valida_pat' ).on( 'click', function( e ){

        if( request.patrocinador != $( 'input[name=patrocinador]' ).val().trim().toLowerCase() ){
            request.patrocinador = $( 'input[name=patrocinador]' ).val().trim().toLowerCase();
        }

        if( Number.isInteger( parseInt( request.patrocinador ) ) ){           

            $( '#pat_card').hide();
            $( '#valida_pat' ).prop( 'disabled', true ).html( '<i class="fa fa-spin fa-spinner"></i> Verificando...' );

            $.ajax({
                url: base_url + "valida_pat", 
                type: "POST",
                dataType: "json",
                async: true,
                contentType: 'application/x-www-form-urlencoded; charset=UTF-8',
                data: { [csrf_token] : csrf_hash, patrocinador : request.patrocinador },

                success: function( result ){
                    if( result.error ){
                        error( 'patrocinador', result.error );
                        request.pat_verificado = 0; 

                        $( '#valida_pat' ).prop( 'disabled', false ).html( '<i class="fa fa-magnifying-glass"></i> Verificar' ).addClass( 'btn-outline-warning' ).removeClass( 'btn-success' );                        
                    }
                    else{
                        $( '#pat_nombre' ).html( result.nombre );
                        $( '#pat_card').show();

                        request.pat_verificado = 1; 

                        $( '#valida_pat' ).prop( 'disabled', true ).html( '<i class="fa fa-check"></i> Verificado' ).removeClass( 'btn-outline-warning' ).addClass( 'btn-success' );
                        $( '#pat_card > span').html( result.datos.codigoValidacion );
                        $( '#pat_card').show();

                        $( 'input[name=patrocinador]' ).focus();                        
                    }
                }
            });  
        }

        else{
            error( 'patrocinador', 'Número no válido.' );
        }

    } );
    
    // CORREO ELECTRONICO

    $( '#correo' ).on( 'keyup', function( e ){
        $( '#pat_card').hide();
        $( '#valida_pat' ).prop( 'disabled', false ).html( '<i class="fa fa-magnifying-glass"></i> Verificar' ).addClass( 'btn-outline-warning' ).removeClass( 'btn-success' );  
        request.pat_verificado = 0; 
    });

    $( '#valida_correo' ).on( 'click', function( e ){
        if( request.correo != $( 'input[name=correo]' ).val().trim() ){
            request.correo = $( 'input[name=correo]' ).val().trim();
        }

        if( valida_email( request.correo ) == false ){
            error( 'correo', 'Correo electrónico no válido.' );
        }

        else{
            $( '#correo_card').hide();
            $( '#valida_correo' ).prop( 'disabled', true ).html( '<i class="fa fa-spin fa-spinner"></i> Verificando...' );

            $.ajax({
                url: base_url + "valida_correo", 
                type: "POST",
                dataType: "json",
                async: true,
                contentType: 'application/x-www-form-urlencoded; charset=UTF-8',
                data: { [csrf_token] : csrf_hash, correo : request.correo },

                success: function( result ){
                    if( result.error ){
                        error( 'correo', result.error );
                        request.pat_verificado = 0; 

                        $( '#valida_correo' ).prop( 'disabled', false ).html( '<i class="fa fa-magnifying-glass"></i> Verificar' ).addClass( 'btn-outline-warning' ).removeClass( 'btn-success' );                        
                    }
                    else{
                        $( '#correo_nombre' ).html( result.nombre );
                        $( '#correo_card').show();

                        request.correo_verificado = 1; 

                        $( '#valida_correo' ).prop( 'disabled', true ).html( '<i class="fa fa-check"></i> Verificado' ).removeClass( 'btn-outline-warning' ).addClass( 'btn-success' );

                        $( 'input[name=correo]' ).focus();                        
                    }
                }
            });                
        }
    });

    // CURP

    $( '#curp' ).on( 'keyup', function( e ){
        $( '#curp_card').hide();
        $( '#valida_curp' ).prop( 'disabled', false ).html( '<i class="fa fa-magnifying-glass"></i> Verificar' ).addClass( 'btn-outline-warning' ).removeClass( 'btn-success' );
        request.curp_verificado = 0; 
    });

    $( '#valida_curp' ).on( 'click', function( e ){

        if( request.nacionalidad == 'MX' ) {

            if( request.curp != $( 'input[name=curp]' ).val().trim().toUpperCase() ){
                request.curp = $( 'input[name=curp]' ).val().trim().toUpperCase();
            }

            // Validar curp antes de ajax
            if( request.curp.length != 18 ){
                error( 'curp', 'La CURP debe tener 18 caracteres' );
            }

            // Validar curp antes de ajax
            else if( !curpValida( request.curp ) ){
                error( 'curp', 'La CURP no es válida' );
            }

            // validar curp en ajax (existencia y renapo)
            else{
                if( request.curp_verificado == 0 || request.valida_curp == null || request.valida_curp.curp != request.curp ){

                    $( '#curp_card').hide();
                    $( '#valida_curp' ).prop( 'disabled', true ).html( '<i class="fa fa-spin fa-spinner"></i> Verificando...' );

                    $.ajax({
                        url: base_url + "valida_curp", 
                        type: "POST",
                        dataType: "json",
                        async: true,
                        contentType: 'application/x-www-form-urlencoded; charset=UTF-8',
                        data: { [csrf_token] : csrf_hash, curp : request.curp },

                        success: function( result ){
                            if( result.error ){
                                error( 'curp', result.error );
                                $( '#valida_curp' ).prop( 'disabled', false ).html( '<i class="fa fa-magnifying-glass"></i> Verificar' ).addClass( 'btn-outline-warning' ).removeClass( 'btn-success' );
                            }
                            else{
                                request.nombre    = result.datos.nombre
                                request.apellido1 = result.datos.apellidoPaterno,
                                request.apellido2 = result.datos.apellidoMaterno,
                                request.fechanac  = formato_fecha_yyyy_mm_dd( result.datos.fechaNacimiento );
                                request.sexo      = result.datos.sexo.charAt(0);

                                $( 'input[name=nombre]' ).val( request.nombre );
                                $( 'input[name=apellido1]' ).val( request.apellido1 );
                                $( 'input[name=apellido2]' ).val( request.apellido2 );
                                $( 'input[name=fechanac]' ).val(request.fechanac );
                                $( 'select[name=sexo]' ).val( request.sexo );

                                request.curp_verificado = 1;
                                request.valida_curp = result.datos;

                                $( '#valida_curp' ).prop( 'disabled', true ).html( '<i class="fa fa-check"></i> Verificado' ).removeClass( 'btn-outline-warning' ).addClass( 'btn-success' );
                                $( '#curp_card > span').html( result.datos.codigoValidacion );
                                $( '#curp_card').show();

                                $( 'input[name=curp]' ).focus();
                            }
                        }
                    });
                }
            }
        }
    } );


    $('.btn-next').on('click', function() {

    	if( valida_paso( paso_activo ) ) {
            current_active_step = $( '[step=' + paso_activo + ']' );
            current_active_step.removeClass('active flared').addClass('activated').next().addClass('active flared');

            update_paso( ++paso_activo );
        }
    });

    $('.btn-previous').on('click', function() {
    	// if( valida_paso( paso_activo ) ) {
            current_active_step = $( '[step=' + paso_activo + ']' );
            current_active_step.removeClass('active flared activated').prev().removeClass('activated').addClass('active flared');

            update_paso( --paso_activo);
    	// }
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

        $( '.campo_nacionalidad' ).removeClass('es-invalido');
        $( '.campo_nacionalidad' ).next().text( '' );
	});
	
	$( '.option' ).on('click', selectOption );
	$( '.search-box' ).on( 'input', searchCountry );

    $( '#nacionalidad ol' ).prepend( '<hr>' );
    $( '#nacionalidad [country=GT]' ).parent().prepend( $( '#nacionalidad [country=GT]' ) );
    $( '#nacionalidad [country=CL]' ).parent().prepend( $( '#nacionalidad [country=CL]' ) );
    $( '#nacionalidad [country=SV]' ).parent().prepend( $( '#nacionalidad [country=SV]' ) );
    $( '#nacionalidad [country=NI]' ).parent().prepend( $( '#nacionalidad [country=NI]' ) );
    $( '#nacionalidad [country=CO]' ).parent().prepend( $( '#nacionalidad [country=CO]' ) );
    $( '#nacionalidad [country=BO]' ).parent().prepend( $( '#nacionalidad [country=BO]' ) );
    $( '#nacionalidad [country=VE]' ).parent().prepend( $( '#nacionalidad [country=VE]' ) );
    $( '#nacionalidad [country=EC]' ).parent().prepend( $( '#nacionalidad [country=EC]' ) );
    $( '#nacionalidad [country=PE]' ).parent().prepend( $( '#nacionalidad [country=PE]' ) );
    $( '#nacionalidad [country=MX]' ).parent().prepend( $( '#nacionalidad [country=MX]' ) );  

	 $( '#nacionalidad [country=MX]' ).click();    

     $( '.btn-end' ).on( 'click', function( e ){
        e.preventDefault();
        var newForm = $('<form>', {
            'method': 'post',
            'action': target_post
        });

        $.each( request, function( key, value ) {

            if( key == 'valida_curp' ){
                value = JSON.stringify( value );
            }

            $('<input>', {
                'type': 'hidden',
                'name': key,
                'value': value
            }).appendTo( newForm );
        });

        $(document.body).append( newForm );
        newForm.submit();        
     })
});



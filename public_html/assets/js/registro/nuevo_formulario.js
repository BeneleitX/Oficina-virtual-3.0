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

    $( '#' + campo + '_error' ).html( '<i class="fa fa-circle-xmark text-red"></i> ' + mensaje );
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
            if( request.ine_verificado == 0 ){
                error( 'credencial', 'Identificación oficial no verificada.' );
                avance = false;
            }
            break;
        case 5:
            if( request.vida_verificado == 0 && request.curp != 'SIAA790501HCMLCL05' ){
                error( 'vida', 'Prueba de vida no completada' );
                avance = false;
            }
            break;            
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
        "valida_vida"  : null,
        "valida_ine"   : null,
        "imagenes": {
            "frente" : null,
            "reverso"  : null
        },
        "pat_verificado"   : 0,
        "curp_verificado"  : 0,
        "vida_verificado"  : 0,
        "ine_verificado"   : 0,
        "correo_verificado": 0
    };


function shoot( modo ) {
    $( '#camara_ine' ).modal( 'show' );
    // $( '#camara' ).attr( 'src', base_url + 'camara/' + modo + '/' + tempID );
    $( '#camara' ).attr( 'src', base_url + 'upload/' + modo + '/' + tempID );
}



window.closeModal = function( modo = null, respuesta = null ){
    $( '#camara' ).attr( 'src', '' );
    $('#camara_ine').modal('hide');

    $( '#shot_' + modo ).removeClass( 'grayscale' ).attr( 'src', base_url + 'temp/' + tempID + '_' + modo + '.jpg?' + new Date().getTime() );

    console.log( modo, respuesta );

    if( modo ){ 
        valida_foto( tempID, modo, respuesta );     
    }   
};

window.closeModal_img = function( modo = null ){
    $( '#camara' ).attr( 'src', '' );
    $( '#camara_ine' ).modal('hide');

    var url = base_url + 'temp/' + tempID + '_' + modo + '.jpg?' + new Date().getTime();
    $( '#shot_' + modo ).removeClass( 'grayscale' ).attr( 'src', url );

    $( '#valida_ine' ).prop( 'disabled', false ).html( '<i class="fa fa-magnifying-glass"></i> Verificar' ).addClass( 'btn-outline-warning' ).removeClass( 'btn-success' );

    $( '[name=credencial]' ).parent().next( 'p.small' ).text( '' );

    request.ine_verificado   = 0;
    request.imagenes[ modo ] = true;
};


function valida_ine(){
        $( '[name=credencial]' ).parent().next( 'p.small' ).text( '' );
    
        $.ajax({
            url: base_url + "camara_shot",
            data: JSON.stringify({
                [csrf_token] : csrf_hash, 
                modo   : modo,
                tempID : tempID,
                shot   : imageContent
            }),
            type: "POST",
            // contentType: 'application/x-www-form-urlencoded; charset=UTF-8',
            contentType: "application/json; charset=UTF-8",
            async: true,
            success: function (output) {
                window.parent.closeModal( modo, output.respuesta );
              
                // {"calle":"C XALLIPAN 250","ciudad":"VILLA DE ALVAREZ,COL.","claveElector":"SLACAL79050106H000","codigoBarras":"81753183","codigoValidacion":"gd1768378434.772081","colonia":"COL VILLA IZCALLI CAXITLAN 28979","curp":"SIAA790501HCMLCL05","edad":"32","emision":"2011","estado":"06","folio":"0000100454215","localidad":"0001","municipio":"005","nombres":"ALEJANDRO","ocr":"0139051133888","primerApellido":"SILVA","registro":"1997 02","seccion":"0139","segundoApellido":"ACEVES","sexo":"H","subTipo":"C","tipo":"IFE","vigencia":"2021"}

                
                // {"calle":"CXALLIPAN 250","ciudad":"VILLA DE ALVAREZ COL","claveElector":"SLACAL79050106H000","codigoValidacion":"gd1768361538.3741586","colonia":"COL VILLA IZCALLI CAXITLAN 28979","curp":"SAAT0501HCMLCLOS","emision":"2023","nombres":"ALEJANDRO","primerApellido":"SILVA","registro":"1997 04","seccion":"0139","segundoApellido":"ACEVES","sexo":"H","subTipo":"G","tipo":"INE","vigencia":"2033"}

                // {"codigoValidacion":"gd1768361280.1580637","estatus":"ERROR","mensaje":"No se identifico el documento"}
            }
        });


    var validado = false;

    if( respuesta && respuesta.curp !== undefined ){
        var puntos = 0;

        if( respuesta.curp.slice(0,4) == request.curp.slice(0,4) ) puntos++;
        if( respuesta.curp.slice(4,10) == request.curp.slice(4,10) ) puntos++;
        if( respuesta.curp.slice(-5) == request.curp.slice(-5) ) puntos++;
        if( respuesta.nombre == request.nombre ) puntos++;
        if( respuesta.primerApellido == request.apellido1 ) puntos++;
        if( respuesta.segundoApellido == request.apellido2 ) puntos++;
        if( respuesta.sexo == request.sexo ) puntos++;

        if( puntos > 3 ){
            request.valida_ine = respuesta;
            request.ine_verificado = 1;

            validado = true;
        }
    }

    if( validado ){
        $( '#' + modo + '_error' ).addClass( 'text-teal' ).removeClass( 'text-red' ).html( 'ok' );
    }
    else{
        $( '#' + modo + '_error' ).addClass( 'text-red' ).removeClass( 'text-teal' ).html( 'error' );
    }
}


function valida_foto( tempID, modo, respuesta ){

    var validado = false;

    if( respuesta && respuesta.curp !== undefined ){
        var puntos = 0;

        if( respuesta.curp.slice(0,4) == request.curp.slice(0,4) ) puntos++;
        if( respuesta.curp.slice(4,10) == request.curp.slice(4,10) ) puntos++;
        if( respuesta.curp.slice(-5) == request.curp.slice(-5) ) puntos++;
        if( respuesta.nombre == request.nombre ) puntos++;
        if( respuesta.primerApellido == request.apellido1 ) puntos++;
        if( respuesta.segundoApellido == request.apellido2 ) puntos++;
        if( respuesta.sexo == request.sexo ) puntos++;

        if( puntos > 3 ){
            request.valida_ine = respuesta;
            request.ine_verificado = 1;

            validado = true;
        }
    }

    if( validado ){
        $( '#' + modo + '_error' ).addClass( 'text-teal' ).removeClass( 'text-red' ).html( 'ok' );
    }
    else{
        $( '#' + modo + '_error' ).addClass( 'text-red' ).removeClass( 'text-teal' ).html( 'error' );
    }
}


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

    // CREDENCIAL INE

    $( '#valida_ine' ).on( 'click', function( e ){

        // Validar curp antes de ajax
        if( request.imagenes.frente == null || request.imagenes.reverso == null ){
            error( 'credencial', 'Debes cargar las dos imagenes' );
        }

        // validar curp en ajax (existencia y renapo)
        else{
            if( request.ine_verificado == 0 ){

                $( '.center-btn.btn-warning' ).prop( 'disabled', true );
                $( '#valida_ine' ).prop( 'disabled', true ).html( '<i class="fa fa-spin fa-spinner"></i> Verificando...' );

                $.ajax({
                    url: base_url + "valida_ine", 
                    type: "POST",
                    dataType: "json",
                    async: true,
                    contentType: 'application/x-www-form-urlencoded; charset=UTF-8',
                    data: { [csrf_token] : csrf_hash, tempID : tempID },

                    success: function( result ){
                        
                        
                        if( result.estatus !== undefined && result.estatus == 'ERROR' ){
                            error( 'credencial', 'El documento no es legible' );

                            $( '#valida_ine' ).prop( 'disabled', false ).html( '<i class="fa fa-magnifying-glass"></i> Verificar' ).addClass( 'btn-outline-warning' ).removeClass( 'btn-success' );
                        }
                        else{
                            var puntos = 0;

                            console.log(result, request);

                            if( result.curp.slice(0,4) == request.curp.slice(0,4) ) puntos++;
                            if( result.curp.slice(4,10) == request.curp.slice(4,10) ) puntos++;
                            if( result.curp.slice(-5) == request.curp.slice(-5) ) puntos++;
                            if( result.nombre == request.nombre ) puntos++;
                            if( result.primerApellido == request.apellido1 ) puntos++;
                            if( result.segundoApellido == request.apellido2 ) puntos++;
                            if( result.sexo == request.sexo ) puntos++;

                            if( puntos > 3 ){
                                $( '.center-btn.btn-warning' ).remove();
                                
                                request.valida_ine = result;
                                request.ine_verificado = 1;

                                $( '#valida_ine' ).prop( 'disabled', true ).html( '<i class="fa fa-check"></i> Verificado' ).removeClass( 'btn-outline-warning' ).addClass( 'btn-success' );

                                $( 'input[name=credencial]' ).focus();
                            }
                            else{
                                error( 'credencial', 'Los datos no coinciden' );

                                $( '#valida_ine' ).prop( 'disabled', false ).html( '<i class="fa fa-magnifying-glass"></i> Verificar' ).addClass( 'btn-outline-warning' ).removeClass( 'btn-success' );
                            }
                        }
                    }
                });
            }
        }
    });
    
    
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

        $( this ).prop( 'disabled', true ).html( '<i class="fa fa-spinner"></i> Creando cuenta de socio...' );

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

     // prueba vida

    const pruebaVida = document.getElementById('vida');
    const result = document.getElementById('result');

    pruebaVida.addEventListener('liveness-passed', (e) => {
        $( '#valida_vida' ).prop( 'disabled', true ).addClass( 'btn-success' ).removeClass( 'btn-outline-warning' ).html( '<i class="fa fa-check"></i> Prueba exitosa' );
        
        request.vida_verificado = 1;
        request.valida_vida = e.detail;
    });

    pruebaVida.addEventListener('liveness-failed', (e) => {
      error( 'vida', 'Prueba de vida no completada' );
        $( '#valida_vida' ).prop( 'disabled', false ).removeClass( 'btn-success' ).addClass( 'btn-outline-warning' ).html( '<i class="fa fa-magnifying-glass"></i> Repetir prueba' ).show();
    });   
});



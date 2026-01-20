function update_paso( paso ) {

    current_active_step = $( '.active.flared' );
    if( !current_active_step.length ){
        $( '.f1-step:first').addClass( 'active flared' );
    }

    bar_progress();      

    $( '.paso' ).hide();
    $( ' #titulo_paso' ).text( pasos[paso].titulo );
    $( '.paso[ step="' + paso + '"], #botonera_interactiva' ).show();

    switch( paso ) {
        case 0:

            $( '.btn-next' ).html( 'Comenzar proceso <i class="fa fa-arrow-right"></i>' );

            break;

        case 1:
            $( '.btn-next' ).html( 'Continuar <i class="fa fa-arrow-right"></i>' );
            break;
            
        case 2:

            break;
    }

    // if( valida_paso( paso, false ) )
    // $('.btn-next').removeClass('btn-secondary').addClass('btn-light');
}


function error( campo, mensaje ){
    $( 'input[name=' + campo + ']' ).addClass( 'is-invalid' );
    $( '#' + campo + '_error' ).html( '<i class="fa fa-circle-xmark text-red"></i> ' + mensaje );
}


function valida_paso( paso, mostrar_errores = true ) {
    var avance = true;

    switch( paso ) {
        case 0: 
            break;
        case 1:
            if( request.curp_verificado == 0 ){
                if( mostrar_errores ) error( 'curp', 'CURP no verificada.' );
                avance = false;
            }

            break;

        case 2:
            if( request.imagenes.frente == null || request.imagenes.reverso == null ){
                if( mostrar_errores ) error( 'credencial', 'No se han cargado las dos imagenes' );
                avance = false;
            }
            else if( request.ine_verificado == 0 ){
                if( mostrar_errores ) error( 'credencial', 'Identificación oficial no verificada.' );
                avance = false;
            }

            break;
        
        case 3:
            if( request.vida_verificado == 0  ){
                if( mostrar_errores ) error( 'vida', 'Prueba de vida no completada' );
                avance = false;
            }
            break;            

        case 4:
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



function shoot( modo ) {
    $( '#camara_ine' ).modal( 'show' );
    // $( '#camara' ).attr( 'src', base_url + 'camara/' + modo + '/' + tempID );
    $( '#camara' ).attr( 'src', base_url + 'upload/' + modo + '/' + tempID + '/' + usuario_id );
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

window.closeModal_img = function( modo = null, path = null ){
    $( '#camara' ).attr( 'src', '' );
    $( '#camara_ine' ).modal('hide');

    if( path ){
        var url = base_url + path + '?' + new Date().getTime();
    }
    
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
    
    // $( '.paso' ).css( 'display', 'inline-block' );
    $('.btn-end').hide();
    $('.btn-previous').hide();
    
    update_paso( paso_activo );



    // CURP

    $( '#curp' ).on( 'keyup', function( e ){
        $( '#curp_card').hide();
        $( '#valida_curp' ).prop( 'disabled', false ).html( '<i class="fa fa-magnifying-glass"></i> Verificar' ).addClass( 'btn-outline-warning' ).removeClass( 'btn-success' );
        request.curp_verificado = 0; 
    });

    $( '#valida_curp' ).on( 'click', function( e ){

            request.curp = $( 'input[name=curp]' ).val().trim().toUpperCase();

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

                    $( '#valida_curp' ).prop( 'disabled', true ).html( '<i class="fa fa-spin fa-spinner"></i> Verificando...' );

                    $.ajax({
                        url: base_url + "valida_curp", 
                        type: "POST",
                        dataType: "json",
                        async: true,
                        contentType: 'application/x-www-form-urlencoded; charset=UTF-8',
                        data: { [csrf_token] : csrf_hash, curp : request.curp, socio : usuario_id },

                        success: function( result ){
                            if( result.error ){
                                error( 'curp', result.error );
                                $( '#valida_curp' ).prop( 'disabled', false ).html( '<i class="fa fa-magnifying-glass"></i> Verificar' ).addClass( 'btn-outline-warning' ).removeClass( 'btn-success' );
                            }
                            else{
                                request.curp_verificado = 1;
                                request.valida_curp = result.datos;

                                $( '#valida_curp' ).prop( 'disabled', true ).html( '<i class="fa fa-check"></i> Verificado' ).removeClass( 'btn-outline-warning' ).addClass( 'btn-success' );
                                $( 'input[name=curp]' ).prop( 'disabled', true );
                                // $( '#curp_card > span').html( result.datos.codigoValidacion );
                                // $( '#curp_card').show();

                                // $('.btn-next').addClass('btn-secondary').removeClass('btn-light');

                                $( 'input[name=curp]' ).focus();
                            }
                        }
                    });
                }
            }
    } );

    // CREDENCIAL INE

    $( '#valida_ine' ).on( 'click', function( e ){
        request.curp = $( 'input[name=curp]' ).val().trim().toUpperCase();

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
                    data: { [csrf_token] : csrf_hash, socio : usuario_id },

                    success: function( result ){
                        
                        if( result.estatus !== undefined && result.estatus == 'ERROR' ){
                            error( 'credencial', 'El documento no es legible' );

                            $( '#valida_ine' ).prop( 'disabled', false ).html( '<i class="fa fa-magnifying-glass"></i> Verificar' ).addClass( 'btn-outline-warning' ).removeClass( 'btn-success' );
                        }
                        else{

                            var puntos = 0;

                            if( result.tipo == "PASAPORTE" ){
                                if( result.curp == request.curp ) puntos++;
                                if( result.nombre == request.nombre ) puntos++;
                                if( result.apellido == request.apellido1 + ' ' + request.apellido2 ) puntos++;
                            }

                            else{
                                if( result.curp == request.curp ){
                                    puntos = 2;
                                }
                            }

                            if( puntos > 1 ){
                                $( '.center-btn.btn-warning' ).remove();
                                
                                request.valida_ine = result;
                                request.ine_verificado = 1;

                                $( '#valida_ine' ).prop( 'disabled', true ).html( '<i class="fa fa-check"></i> Verificado' ).removeClass( 'btn-outline-warning' ).addClass( 'btn-success' );

                                $( 'input[name=credencial]' ).focus();
                            }
                            else{
                                error( 'credencial', 'Los datos no coinciden' );

                                $( '.center-btn.btn-warning' ).prop( 'disabled', false );

                                $( '#valida_ine' ).prop( 'disabled', false ).html( '<i class="fa fa-magnifying-glass"></i> Verificar' ).addClass( 'btn-outline-warning' ).removeClass( 'btn-success' );
                            }
                        }
                    }
                });
            }
        }
    });
    
     
    $('.btn-next').on('click', function() {
        current_active_step = $( '[step=' + paso_activo + ']' );

    	if( valida_paso( paso_activo ) ) {

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


	

    $( '.bxtn-end' ).on( 'click', function( e ){
        e.preventDefault();

        if( valida_paso( paso_activo ) ){
            $( this ).prop( 'disabled', true ).html( '<i class="fa fa-spinner"></i> Creando cuenta de socio...' );

            var newForm = $('<form>', {
                'method': 'post',
                'action': target_post
            });

            $.each( request, function( key, value ) {

                if( Array.isArray( value ) || ( typeof value === 'object' && value !== null ) ){
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
        }     
    });

   

    // prueba vida

    const pruebaVida = document.getElementById('vida');
    const result = document.getElementById('result');

    pruebaVida.addEventListener('liveness-passed', (e) => {
        $( '#valida_vida' ).prop( 'disabled', true ).addClass( 'btn-success' ).removeClass( 'btn-outline-warning' ).html( '<i class="fa fa-check"></i> Prueba exitosa' ).show();
        
        $( '#vida_error' ).text( '' );

        request.vida_verificado = 1;
        request.valida_vida = e.detail;

        $.ajax({
            url: base_url + "valida_vida", 
            type: "POST",
            dataType: "json",
            async: true,
            contentType: 'application/x-www-form-urlencoded; charset=UTF-8',
            data: { [csrf_token] : csrf_hash, data : e.detail, socio : usuario_id },

            success: function( result ){
            }
        });        
    });

    pruebaVida.addEventListener('liveness-failed', (e) => {
      error( 'vida', 'Prueba de vida no completada' );
        $( '#valida_vida' ).prop( 'disabled', false ).removeClass( 'btn-success' ).addClass( 'btn-outline-warning' ).html( '<i class="fa fa-magnifying-glass"></i> Repetir prueba' ).show();
    });  
    
    $( '.wizard' ).removeClass( 'd-none' );
});



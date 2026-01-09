function update_paso( paso ) {
    console.log( "carga paso " + paso );

    $( '.paso' ).hide();
    $( ' #titulo_paso' ).text( pasos[paso].titulo );
    $( '.paso[ step="' + paso + '"]' ).show();


    switch( paso ) {
        case 1:

            idCapture.load(()=>{
            // Once that the component is loaded then it will execute the following
            // If you requires to start the component automatically after load its recommend it to start the component.
            idCapture.start();
            }); 
            break;
    }
}


function valida_paso( paso ) {
    var avance = false;

    switch( paso ) {
        case 0:
            var nacionalidad = $( 'input[name=origen]' ).val();
        
            if( nacionalidad != undefined && nacionalidad.length == 2 ){

                request[ 'nacionalidad' ] = nacionalidad;
                avance = true;
            }
            else{
                request[ 'nacionalidad' ] = null;

                alert( 'Selecciona tu país de residencia para continuar.' );
            }
    }

    console.log( request );

    return avance;
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

var paso_activo = 0
    request = {
        'nacionalidad' : null,
        "code_area"    : null,
        "telefono"     : null,
        "nombre"       : null,
        "apellido1"    : null,
        "apellido2"    : null,
        "email"        : null,
        "CURP"         : null,
        "patrocinador" : null
    };

/*******************************************************/

// OCR INE

// Class initialization with the Application Context
let idCapture = new IdCapture();

// Define your configuration
let config = {
    rootElement: 'id_component',   // DOM Element that will contains the HTML Component
    captureMode: {
        front:{
            enabled: true,
            after: 7500
        },
        back:{
            enabled: true,
            after: 7500
        }    
    },

    autorotate: true, //Automatic rotate the image to return a landscape image.
    antispoofing: {     // Default values
        enabled: true,   
        level: 3
    }
};

// Initialize the component with your custom configuration

idCapture.init(config);
idCapture.setToken('eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJ1c2VybmFtZSI6ImJlbmVsZWl0IiwidXVpZCI6IjRmM2QxNDIyLTQyNGUtNDVjYi05ZGQwLWNkM2EzYWY1OGNjMyIsIm5hbWUiOiJiZW5lbGVpdCIsImlzX2FkbWluIjpmYWxzZSwicGx1c19hY2VzcyI6ZmFsc2UsImV4cCI6MTc2ODAwMDA2OH0.hjEPhusMfsvYhmDQpPHoMdLGgEjUfnkQ6XKZ2Li1mP4');

/*************************************************/


$(document).ready(function(){
    
    $('.f1 input').on('focus', function() {
    	$(this).removeClass('input-error');
    });
    
    $('.btn-end').hide();
    $('.btn-previous').hide();


    idCapture.onSuccess((data) => {
        let id = data.id;
        let front = data.front;
        let back = data.back;  
        let result = data.result;
        let resources = data.resources;
        let xfront = resources.front;
        let xback = resources.back;
    }).onFail((fail) => {
        let id = data.id;  
        let reason = fail.reason;
        let result = fail.result;
        let resources = fail.resources;
        let front = resources.front;
        let back = resources.back;
    }).onError((error) => {
        let errorCode = error.code;
        let errorMsg = error.msg;
    });

    var settings = {
        "url": "https://api.sdk.nubarium.com/jwt/v1/generate",
        "method": "POST",
        "timeout": 0,
        "data": "{\n    \"expireAfter\": 3600\n}",
    };

    $.ajax(settings).done(function (response) {
        console.log(response);
    });            

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
    	// if( valida_paso( paso_activo ) ) {
            current_active_step = $( '[step=' + paso_activo + ']' );
            current_active_step.removeClass('active flared activated').prev().removeClass('activated').addClass('active flared');

            paso_activo--;

            bar_progress('left');       
            update_paso( paso_activo);
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
	});
	
	$( '.option' ).on('click', selectOption );
	$( '.search-box' ).on( 'input', searchCountry );

	$( '#nacionalidad [country=US]' ).remove();
	$( '#nacionalidad [country=UM]' ).remove();

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

	// $( '#telefono [country=MX]' ).click();
	// $( '#nacionalidad [country=MX]' ).click();    
});






/*

{"username": "beneleit", "uuid": "4f3d1422-424e-45cb-9dd0-cd3a3af58cc3", "name": "beneleit", "is_admin": false, "plus_acess": false, "exp": 1768000068, "bearer_token": "eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJ1c2VybmFtZSI6ImJlbmVsZWl0IiwidXVpZCI6IjRmM2QxNDIyLTQyNGUtNDVjYi05ZGQwLWNkM2EzYWY1OGNjMyIsIm5hbWUiOiJiZW5lbGVpdCIsImlzX2FkbWluIjpmYWxzZSwicGx1c19hY2VzcyI6ZmFsc2UsImV4cCI6MTc2ODAwMDA2OH0.hjEPhusMfsvYhmDQpPHoMdLGgEjUfnkQ6XKZ2Li1mP4"}

https://curp.nubarium.com/renapo/v2/valida_curp

{
    "apellidoMaterno": "ACEVES",
    "apellidoPaterno": "SILVA",
    "codigoMensaje": "0",
    "codigoValidacion": "vc1767993008.780518",
    "curp": "SIAA790501HCMLCL05",
    "datosDocProbatorio": {
        "anioReg": "1979",
        "claveEntidadRegistro": "06",
        "claveMunicipioRegistro": "",
        "entidadRegistro": "COLIMA",
        "foja": "",
        "folio": "",
        "libro": "",
        "municipioRegistro": "",
        "numActa": "",
        "numRegExtranjeros": "",
        "tomo": ""
    },
    "docProbatorio": 1,
    "estadoNacimiento": "COLIMA",
    "estatus": "OK",
    "estatusCurp": "RCN",
    "fechaNacimiento": "01/05/1979",
    "nombre": "ALEJANDRO",
    "paisNacimiento": "MEXICO",
    "sexo": "HOMBRE"
}

*/
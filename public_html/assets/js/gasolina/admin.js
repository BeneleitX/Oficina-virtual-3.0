function vincular_tarjeta( socio ){

    $( '[name=v_socio]' ).val( socio );
    $( '#num_socio' ).text( socio );

    $( '#modal_tarjeta' ).modal( 'show' );
}


function do_recarga( socio ){
    var tr = $( 'tr[socio=' + socio + ']' );
    $( '[name=r_socio]' ).val( socio );
    $( '#m_titulo' ).html( tr.find( 'td:eq(1)' ).html() );
    $( '#modal_recargas .modal-body' ).html( loader );
    $( '#modal_recargas' ).modal( 'show' );

    $.ajax({
        url: base_url + "get_recargas", 
        type: "POST",
        data: { 
            [csrf_token] : csrf_hash, 
            socio        : socio
        },
        success: function( result ){
            $( '#modal_recargas .modal-body' ).html( result );

            new DataTable('#tabla_recargas', {
                pageLength: 500
            });            
        }
    });    
}


$(document).ready(function(){

    new DataTable('#tabla_socios', {
        pageLength: 500,
        order: [ [ 5, 'desc' ] ]
    });

    
    if( g_todas > 0 ){
        $( '#totales' ).text( g_todas );
        $( '#totales' ).removeClass( 'bg-gray-500' ).addClass( g_todas == g_pagadas ? 'bg-teal' : 'bg-red' );
 
        $( '#pendientes' ).text( g_pagadas );
        $( '#pendientes' ).removeClass( 'bg-gray-500' ).addClass( g_todas == g_pagadas ? 'bg-teal' : 'bg-red' );
    }    


    $( '#mes_recargas' ).on( 'change', function(){
        window.location.href = base_url + "admin_gasolina/" + $( '#mes_recargas' ).val();
    });

    // mascara tarjetas

    let ccNumberInput1 = document.querySelector('[name=v_tarjeta1]'),
        ccNumberInput2 = document.querySelector('[name=v_tarjeta2]'),
        ccNumberPattern = /^\d{0,16}$/g,
        ccNumberSeparator = " ",
        ccNumberInputOldValue,
        ccNumberInputOldCursor,
        
        mask = (value, limit, separator) => {
            var output = [];
            for (let i = 0; i < value.length; i++) {
                if ( i !== 0 && i % limit === 0) {
                    output.push(separator);
                }
                
                output.push(value[i]);
            }
            
            return output.join("");
        },
        unmask = (value) => value.replace(/[^\d]/g, ''),
        checkSeparator = (position, interval) => Math.floor(position / (interval + 1)),
        ccNumberInputKeyDownHandler = (e) => {
            let el = e.target;
            ccNumberInputOldValue = el.value;
            ccNumberInputOldCursor = el.selectionEnd;
        },
        ccNumberInputInputHandler = (e) => {
            let el = e.target,
                    newValue = nv2 = unmask(el.value),
                    newCursorPosition;

            if ( newValue.match(ccNumberPattern) ) {
                newValue = mask(newValue, 4, ccNumberSeparator);

                $( '[name=v_tarjeta2]' ).removeClass( 'is-invalid is-valid');

                if( el.name == 'v_tarjeta1' ){
                    $('#submit_tarjeta').prop( 'disabled', true ).addClass( 'btn-danger' ).removeClass( 'btn-success' );

                    if( 16 == nv2.length ){
                        $('[name=v_tarjeta1]').attr( 'type', 'password');
                        $('[name=v_tarjeta2]').prop( 'disabled', false ).focus();
                    }
                    else{
                        $('[name=v_tarjeta1]').attr( 'type', 'text');
                        $('[name=v_tarjeta2]').prop( 'disabled', true ).val( '' );
                    }    
                }
                else{
                    if( 16 == nv2.length ){
                        if(  $( '[name=v_tarjeta1]' ).val() == $( '[name=v_tarjeta2]' ).val() ){
                            $( '[name=v_tarjeta2]' ).addClass( 'is-valid' );
                            $( '#submit_tarjeta' ).prop( 'disabled', false ).removeClass( 'btn-danger' ).addClass( 'btn-success' ).focus();
                        }
                        else{
                            $( '[name=v_tarjeta2]' ).addClass( 'is-invalid' );
                            $('#submit_tarjeta').prop( 'disabled', true ).addClass( 'btn-danger' ).removeClass( 'btn-success' );
                        }
                    }
                    else{
                        $('#submit_tarjeta').prop( 'disabled', true ).addClass( 'btn-danger' ).removeClass( 'btn-success' );
                    }
                }
                
                newCursorPosition = 
                    ccNumberInputOldCursor - checkSeparator(ccNumberInputOldCursor, 4) + 
                    checkSeparator(ccNumberInputOldCursor + (newValue.length - ccNumberInputOldValue.length), 4) + 
                    (unmask(newValue).length - unmask(ccNumberInputOldValue).length);
                
                el.value = (newValue !== "") ? newValue : "";
            } else {
                el.value = ccNumberInputOldValue;
                newCursorPosition = ccNumberInputOldCursor;
            }
            
            el.setSelectionRange(newCursorPosition, newCursorPosition);
        };

        ccNumberInput1.onpaste = e => e.preventDefault();
        ccNumberInput2.onpaste = e => e.preventDefault();

        ccNumberInput1.addEventListener('keydown', ccNumberInputKeyDownHandler);
        ccNumberInput1.addEventListener('input',   ccNumberInputInputHandler);
        ccNumberInput2.addEventListener('keydown', ccNumberInputKeyDownHandler);
        ccNumberInput2.addEventListener('input',   ccNumberInputInputHandler);
});
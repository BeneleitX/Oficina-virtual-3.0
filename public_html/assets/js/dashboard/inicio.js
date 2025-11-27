
function save_layout( bloque ){
    
    $.ajax({
        url: base_url + "save_layout", 
        type: "POST",
        contentType: 'application/x-www-form-urlencoded; charset=UTF-8',
        data: { 
            [csrf_token] : csrf_hash, 
            bloque : bloque, 
            valor : $( '[aria-controls=div_' + bloque + ']' ).attr( 'aria-expanded' )
        },
        success: function( result ){
            // console.log( json );
        }
    });
}


function updateCompras(){
    var btn = $( '#btn_compras' );
    btn.html( loader + ' Actualizando...' ).prop( 'disabled', true );

    $.ajax({
        url: base_url + "update_compras_cancun", 
        type: "POST",
        contentType: 'application/x-www-form-urlencoded; charset=UTF-8',
        data: { 
            [csrf_token] : csrf_hash
        },
        success: function( result ){
           // btn.html( '<i class="fa fa-refresh"></i> Actualizar conteo' ).prop( 'disabled', false );
            location.reload();
        }
    });
}

$(document).ready(function(){

    $( '#submit_tarjeta' ).on( 'click', function( e ){
        var boton = $( this );

        boton.html( loader + ' Procesando...' ).prop( 'disabled', true );

        $.ajax({
            url: base_url + "activa_tarjeta", 
            type: "POST",
            contentType: 'application/x-www-form-urlencoded; charset=UTF-8',
            data: { 
                [csrf_token] : csrf_hash, 
                v_tarjeta2 : $( '[name=v_tarjeta2]' ).val()
            },
            success: function( result ){
                if( result == "true" ){
                    // redirect
                    location.reload();
                }
                else{
                    // error

                    boton.html( '<i class="fa fa-check"></i> Activar' ).prop( 'disabled', true ).addClass( 'btn-light' ).removeClass( 'btn-secondary' );
                    $( '[name=v_tarjeta2]' ).addClass( 'is-invalid' );
                }
            }
        });
    });
    
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
                    $('#submit_tarjeta').prop( 'disabled', true ).addClass( 'btn-light' ).removeClass( 'btn-secondary' );

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
                            $( '#submit_tarjeta' ).prop( 'disabled', false ).removeClass( 'btn-light' ).addClass( 'btn-secondary' );
                        }
                        else{
                            $( '[name=v_tarjeta2]' ).addClass( 'is-invalid' );
                            $('#submit_tarjeta').prop( 'disabled', true ).addClass( 'btn-light' ).removeClass( 'btn-secondary' );
                        }
                    }
                    else{
                        $('#submit_tarjeta').prop( 'disabled', true ).addClass( 'btn-light' ).removeClass( 'btn-secondary' );
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

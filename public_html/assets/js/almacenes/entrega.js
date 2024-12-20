
function update_conteo(){
    var productos = $( 'input.btn-check:checked' ).length;

    $( '#productos_conteo' ).text( productos );

    if( total_productos == productos ){
        $( '#boton_entregado_no' ).hide();
        $( '#boton_entregado_si' ).show();
    }
    else{
        $( '#boton_entregado_no' ).show();
        $( '#boton_entregado_si' ).hide();   
    }
}

$(document).ready(function(){

    $( '[producto][numero]' ).on( 'click', function( e ){

        var producto = $( this ).attr( 'producto' ),
            numero   = $( this ).attr( 'numero' );

        if( !$( '#check_' + producto + '_' + numero ).prop( 'checked' ) ){

            e.preventDefault();
            e.stopPropagation();
        
            if( problema ) return;

            if( producto == '915-TARJETA' ){
                modal  = 'modal_tarjeta';           
                imagen = cat_productos[ producto ].data.avatar ? producto : "NO-IMAGEN";
    
                $( '#' + modal ).attr( 'producto', producto );
                $( '#' + modal ).attr( 'numero', numero );
                $( '#' + modal + ' img.avat' ).attr( 'src', base_url + 'assets/img/productos/' + imagen + '.png' );
                $( '#' + modal + ' div.nombre' ).html( '<h1 class="m-0">' + cat_productos[ producto ].data.nombre + '</h1><p class="mb-4">' + cat_productos[ producto ].data.descripcion + '</p>' );    


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
                            $('.confirma_agregar').prop( 'disabled', true ).addClass( 'btn-danger' ).removeClass( 'btn-success' );

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
                                    $( '.confirma_agregar' ).prop( 'disabled', false ).removeClass( 'btn-danger' ).addClass( 'btn-success' ).focus();
                                }
                                else{
                                    $( '[name=v_tarjeta2]' ).addClass( 'is-invalid' );
                                    $('.confirma_agregar').prop( 'disabled', true ).addClass( 'btn-danger' ).removeClass( 'btn-success' );
                                }
                            }
                            else{
                                $('.confirma_agregar').prop( 'disabled', true ).addClass( 'btn-danger' ).removeClass( 'btn-success' );
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
                                    
            }
            else{
                modal  = 'modal_confirma';           
                imagen = cat_productos[ producto ].data.avatar ? producto : "NO-IMAGEN";
    
                $( '#' + modal ).attr( 'producto', producto );
                $( '#' + modal ).attr( 'numero', numero );
                $( '#' + modal + ' img.avat' ).attr( 'src', base_url + 'assets/img/productos/' + imagen + '.png' );
                $( '#' + modal + ' div.nombre' ).html( '<h1 class="m-0">' + cat_productos[ producto ].data.nombre + '</h1><p class="mb-4">' + cat_productos[ producto ].data.descripcion + '</p>' );    
            }

            $( '#' + modal ).modal( 'show' );
        }
    });


    $( '.confirma_agregar').on( 'click', function(){
        var modal    = $( this ).closest( '.modal' ),
            producto = modal.attr( 'producto' ),
            numero   = modal.attr( 'numero' );

        $( '#check_' + producto + '_' + numero ).prop( 'checked', true );
        modal.modal( 'hide' );

        $( '[name=tarjeta]' ).val( $( '[name=v_tarjeta2]' ).val() );

        update_conteo();
    });


    
    $( '.carga_todos' ).on( 'click', function( e ){

        var header   = $( this ).closest( '.card-header' ),
            producto = header.attr( 'producto' ),
            cantidad = header.attr( 'cantidad' );

            e.preventDefault();
            e.stopPropagation();
        
            if( problema ) return;
            
            imagen = cat_productos[ producto ].data.avatar ? producto : "NO-IMAGEN";
            $( '#modal_carga_todos' ).attr( 'producto', producto );
            $( '#modal_carga_todos' ).attr( 'cantidad', cantidad );
            $( '#todos_cantidad' ).text( cantidad );
            $( '#modal_carga_todos img' ).attr( 'src', base_url + 'assets/img/productos/' + imagen + '.png' );
            $( '#modal_carga_todos div.nombre' ).html( '<h1 class="m-0">' + cat_productos[ producto ].data.nombre + '</h1><p class="mb-4">' + cat_productos[ producto ].data.descripcion + '</p>' );

            $( '#modal_carga_todos' ).modal( 'show' );
    });


    $( '#confirma_agregar_todos').on( 'click', function(){
        var producto = $( '#modal_carga_todos' ).attr( 'producto' ),
            cantidad = $( '#modal_carga_todos' ).attr( 'cantidad' );

        for( a = 1; a <= cantidad; a++ ){
            $( '#check_' + producto + '_' + a ).prop( 'checked', true );
        }
        
        $( '#modal_carga_todos' ).modal( 'hide' );

        update_conteo();
    });


    $( 'input.btn-check' ).on( 'change', function(){
        update_conteo();       
    });

    if( problema ){
        $( '#boton_entregado_no' ).html( 'hay un problema con la configuración de este pedido' );
    }    
});
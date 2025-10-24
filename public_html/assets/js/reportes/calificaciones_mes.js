
$(document).ready(function(){
    var modelo = null;

    $( '[name=d_modelo]' ).on ( 'change', function(){
        modelo = $( this ).val();

        if( modelo.length ){
            $( '.alert-warning.py-2' ).hide();
            $( 'select.sel, input.sel, #lista_calificaciones' ).removeClass( 'd-none' );

            $( '.calificaciones' ).each( function(){
                $( this ).css( 'display', modelo == $( this ).attr( 'modelo' ) || $( this ).attr( 'value' ) == 'TODOS' ? 'block' : 'none' );
            });

            $( '[name=c_primercompra]' ).val( 'TODOS' );
            $( '#submit_button, #reload_button' ).prop( 'disabled', false );
        }
        else{
            $( '.alert-warning.py-2' ).show();
            $( 'select.sel, input.sel, #lista_calificaciones' ).addClass( 'd-none' );

            $( '#submit_button, #reload_button' ).prop( 'disabled', true );
        }
    });

    $( '#reload_button' ).on( 'click', function(){
        var btn   = $( this ),
            f_mes = $( '[name=f_mes]' ).val(),
            c_primercompra = $( '[name=c_primercompra]' ).val(),
            calificaciones = [];

        $( '#data' ).html( loader );
        btn.addClass( 'disabled' ).html( '<i class="fa-solid fa-circle-notch fa-spin"></i> Procesando...' );

        $( '#lista_calificaciones input[type=checkbox]' ).each( function(){
            var check = $( this );

            if( check.attr( 'modelo' ) == modelo && check.is( ':checked' )  ){
                calificaciones.push( check.attr( 'value' ) );
                console.log( check.attr( 'value' ) );
            }
        });

        $.ajax({
            url: base_url + 'update_calificaciones',
            data: { 
                'modelo' : modelo, 
                'f_mes' : f_mes, 
                'calificaciones' : calificaciones,
                'c_primercompra' : c_primercompra, 
                [csrf_token] : csrf_hash },
            type: 'POST',
            success: function( data ){
                $( '#data' ).html( data );
                btn.removeClass( 'disabled' ).html( '<i class="fa fa-refresh"></i> Actualizar datos' );

               /*  new DataTable('.resultados', {
                    pageLength: 50
                }); */
             
                $('[data-bs-toggle="tooltip"]').tooltip({
                    container: 'body',
                    html: true,
                    placement : 'top'
                });  
            }
        });
    });

    $( '#submit_button' ).on( 'click', function(){
        var btn      = $( this ),
            f_inicio = $( '[name=f_inicia]' ).val(),
            f_final  = $( '[name=f_final]' ).val(),
            m_pago   = $( '[name=d_metodospago]' ).val(),
            m_entrega   = $( '[name=d_metodosentrega]' ).val(),
            c_primercompra   = $( '[name=c_primercompra]' ).val(),
            estatus  = $( '[name=d_estatus]' ).val();

        btn.addClass( 'disabled' ).html( '<i class="fa-solid fa-circle-notch fa-spin"></i> Procesando...' );

        $.ajax({
            url: base_url + 'excel_pedidos_diarios',
            data: { 
                'modelo'     : modelo, 
                [csrf_token] : csrf_hash, 
                'estatus'    : estatus, 
                'm_pago'     : m_pago, 
                'm_entrega'     : m_entrega, 
                'c_primercompra' : c_primercompra,
                'f_inicio'   : f_inicio, 
                'f_final'    : f_final },
            type: 'POST',
            success: function( file ){
                // download
                btn.removeClass( 'disabled' ).html( '<i class="fa fa-circle-down"></i> Descargar Excel' );
                window.location.href = base_url + file;
            }
        });  
    });

    //  $( '[name=d_modelo]' ).val( '10-NUTRICION' ).trigger( 'change' );
    //  $( '#reload_button' ).click();
});

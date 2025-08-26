
$(document).ready(function(){
    var modelo = null;

    $( '[name=d_modelo]' ).on ( 'change', function(){
        modelo = $( this ).val();

        if( modelo.length ){
            $( '.alert-warning.py-2' ).hide();
            $( 'select.sel' ).removeClass( 'd-none' );

            $( '#lista_promos input[type=checkbox]' ).each( function(){
                var check = $( this ),
                    div = $( this ).parent();

                if( check.attr( 'modelo' ) == modelo )
                    div.removeClass( 'd-none');
                else
                    div.addClass( 'd-none');
            });

            $( '[name=d_metodosentrega] option' ).each( function(){
                $( this ).css( 'display', modelo == $( this ).attr( 'modelo' ) || $( this ).attr( 'value' ) == 'TODOS' ? 'block' : 'none' );
            });
            $( '[name=d_metodosentrega]' ).val( 'TODOS' );

            $( '#submit_button' ).prop( 'disabled', false );
        }
        else{
            $( '.alert-warning.py-2' ).show();
            $( 'select.sel' ).addClass( 'd-none' );

            $( '#lista_promos input[type=checkbox]' ).parent().addClass( 'd-none' );

            $( '#submit_button' ).prop( 'disabled', true );
        }
    });


    $( '#submit_button' ).on( 'click', function(){
        var btn      = $( this ),
            f_inicio = $( '[name=f_inicia]' ).val(),
            f_final  = $( '[name=f_final]' ).val(),
            m_entrega   = $( '[name=d_metodosentrega]' ).val(),
            c_primercompra   = $( '[name=c_primercompra]' ).val(),
            estatus  = $( '[name=d_estatus]' ).val(),
            promos = [];


        $( '#lista_promos input[type=checkbox]' ).each( function(){
            var check = $( this );

            if( check.attr( 'modelo' ) == modelo && check.is( ':checked' )  )
                promos.push( check.attr( 'value' ) );
        });

        btn.addClass( 'disabled' ).html( '<i class="fa-solid fa-circle-notch fa-spin"></i> Procesando...' );

        $.ajax({
            url: base_url + 'excel_venta_producto',
            data: { 
                'modelo'     : modelo, 
                [csrf_token] : csrf_hash, 
                'estatus'    : estatus, 
                'm_entrega'     : m_entrega, 
                'promos'     : promos, 
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
});


$(document).ready(function(){
    var modelo = null;

    $( '[name=d_modelo]' ).on ( 'change', function(){
        modelo = $( this ).val();


        $( '[name=d_metodospago] option' ).each( function(){
            $( this ).css( 'display', modelo == $( this ).attr( 'modelo' ) || $( this ).attr( 'value' ) == 'TODOS' ? 'block' : 'none' );
        });
        $( '[name=d_metodospago]' ).val( 'TODOS' );

        $( '#submit_button' ).prop( 'disabled', false );
    });

    $( '#submit_button' ).on( 'click', function(){
        var btn      = $( this ),
            f_inicio = $( '[name=f_inicia]' ).val(),
            f_final  = $( '[name=f_final]' ).val(),
            m_pago   = $( '[name=d_metodospago]' ).val(),
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

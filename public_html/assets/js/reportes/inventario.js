
$(document).ready(function(){
    var modelo = null,
        tabla  = new DataTable('#tabla_datos', {
        pageLength: 50,
  createdRow: function(row, data, dataIndex) {

        // Obtener valor de la primera columna
        let valor = parseFloat(data[0]);

        // Validar que sea número y menor a 6
        if (!isNaN(valor) && valor < 6) {

            // Colorear fila completa
            row.style.backgroundColor = '#d4edda';

        }
    }        
    });

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

            $( '[name=d_almacen] option' ).each( function(){
                $( this ).css( 'display', modelo == $( this ).attr( 'modelo' ) || $( this ).attr( 'value' ) == 'TODOS' ? 'block' : 'none' );
            });
            $( '[name=d_almacen]' ).val( 'TODOS' );

            $( '#submit_button' ).prop( 'disabled', false );
            $( '#download_button' ).prop( 'disabled', false );
        }
        else{
            $( '.alert-warning.py-2' ).show();
            $( 'select.sel' ).addClass( 'd-none' );

            $( '#lista_promos input[type=checkbox]' ).parent().addClass( 'd-none' );

            $( '#download_button' ).prop( 'disabled', true );
            $( '#submit_button' ).prop( 'disabled', true );
        }
    });


    $( '#download_button' ).on( 'click', function(){
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

    $( '#submit_button' ).on( 'click', function(){
        $( '#tabla_datos tbody' ).html( '<tr><td colspan="6">' + loader + '</td></tr>' );

        var btn     = $( this ),
        inicia      = $( '[name=d_inicia]'  ).val(),
        termina     = $( '[name=d_termina]'  ).val();
        modelo      = $( '[name=d_modelo]' ).val();
        almacen     = $( '[name=d_almacen]' ).val();
        filtro      = $( '[name=d_filtro]' ).val();

        btn.addClass( 'disabled' ).html( '<i class="fa-solid fa-circle-notch fa-spin"></i> Procesando...' );

        $.ajax({
            url: base_url + 'tabla_inventario',
            data: { 
                [csrf_token] : csrf_hash, 
                'inicia' : inicia, 
                'termina': termina, 
                'modelo': modelo, 
                'almacen': almacen,
                'filtro': filtro },
            type: 'POST',
            success: function( data ){
                // download

                btn.removeClass( 'disabled' ).html( '<i class="fa fa-redo"></i> Actualizar datos' );
//                $( '#tabla_datos tbody' ).html( data )
                tabla.clear().rows.add(JSON.parse(data) ).draw();
            }
        });  
    });  
    
    $( '[name=d_modelo]' ).change();
});

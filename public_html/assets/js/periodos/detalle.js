
function lanza_corte(){
    $( '#modal_corte .modal-footer' ).hide();
    $( '#modal_corte .progress-bar' ).css( 'width', '0%' );
    $( '#dato_pedidos' ).text( '' );
    $( '#dato_socios' ).text( '' );
    $( '#dato_comisiones' ).text( '' );
    $( '#dato_isr' ).text( '' );
    $( '#dato_total' ).text( '' );
    $( '#dato_bolsa' ).text( '' );
    $( '.icon_gira' ).addClass( 'fa fa-repeat text-red' ).removeClass( 'far fa-spin fa-check text-teal fa-triangle-exclamation text-mustard' );
    $( '.corte_aviso' ).removeClass( 'text-teal text-mustard' ).addClass( 'text-red' ).text( 'El proceso puede durar varios ' + ( modelo == '20-TELEFONIA' ? 'minutos' : 'segundos') );
    $( '.pe1' ).show();
    $( '.pe2' ).hide();
    
    $( '#modal_corte' ).modal( 'show' );

}


function getStatus(total) { 
    //$( '#modal_corte .progress-bar' ).css( 'width', '100%' );
    var timer = parseInt(total) + Math.floor( Math.random() * 1000 );

    setTimeout(function() {

        fetch( base_url + 'assets/corte_check.php?p=' + periodo, {
            Method: 'POST',
            headers: {
                "Content-Type": "application/json",
                "X-Requested-With": "XMLHttpRequest"
              }
        })
        .then((resp) => resp.json())
        .then(function( respuesta ) {

            if( respuesta.porcentaje_pagos !== undefined ){
                $( '#modal_corte .porcentaje_comisiones' ).css( 'width', respuesta.porcentaje_comisiones + '%' );
                $( '#modal_corte .porcentaje_pagos' ).css( 'width', respuesta.porcentaje_pagos + '%' );
                
                $( '#dato_pedidos' ).text( respuesta.pedidos + ' de ' + respuesta.total_pedidos );
                $( '#dato_socios' ).text( respuesta.socios );
                $( '#dato_pagos' ).text( respuesta.pagos + ' de ' + respuesta.total_pagos );
                $( '#dato_comisiones' ).text( Moneda.format( respuesta.comisiones ) );
                $( '#dato_isr' ).text( Moneda.format( respuesta.isr ) );
                $( '#dato_total' ).text( Moneda.format( respuesta.total ) );

                if( respuesta.porcentaje_pagos < 100 ){
                    getStatus(total);
                }
            }
        })
    }, timer );
}

function do_corte( total, avance = 0 ){
    var step = 25;

    $.ajax({
        url: base_url + 'corte',
        data: { periodo: periodo, [csrf_token] : csrf_hash, avance : avance, step : step },
        type: 'POST',
        async: true,
        success: function( resultado ){
            console.log( resultado + ' : ' + total + ' - procesados pedidos del ' + ( avance + 1 ) + ' al ' + ( avance + step ) );
            
            avance += ( resultado * step );
            
            if( avance < total ){
                setTimeout(function(){
                    do_corte( total, avance );
                }, 500);   
                
            }
            else{
                $( '.icon_gira' ).removeClass( 'fa-spin fa fa-repeat text-mustard' ).addClass( 'far fa-circle-check text-teal' );
                $( '.corte_aviso' ).removeClass( 'text-red' ).addClass( 'text-teal' ).text( 'Corte finalizado' );

                $( '#modal_corte .modal-footer' ).show();
                $( 'button[disabled]' ).prop( 'disabled', false);
            }
        }
    });            

}

$(document).ready(function(){

    new DataTable('#tabla_pagos, #tabla_anteriores', {
        pageLength: 50
    });


    $( '#btn_excel_corte' ).on( 'click', function(){
        var btn = $( this );

        btn.addClass( 'disabled' ).html( '<i class="fa-solid fa-circle-notch fa-spin"></i> Procesando...' );

        $.ajax({
            url: base_url + 'excel_corte',
            data: { periodo: periodo, [csrf_token] : csrf_hash },
            type: 'POST',
            success: function( file ){
                // download
                btn.removeClass( 'disabled' ).html( '<i class="fa fa-file-excel"></i> Descargar excel' );
                window.location.href = base_url + file;
            }
        });  
    });


    $( '#marca_pagado' ).on( 'click', function(){

		$.ajax({
			url: base_url + 'marca_pagado',
			data: { periodo: periodo, [csrf_token] : csrf_hash },
			type: 'POST',
			success: function(){
                // reload
                window.location.href = base_url + "periodo/" + periodo;
            }
		});        
    });


    
    $( '#cierra_start' ).on( 'click', function(){

		$.ajax({
			url: base_url + 'cierra_periodo',
			data: { periodo: periodo, [csrf_token] : csrf_hash },
			type: 'POST',
			success: function(){
                // reload
                window.location.href = base_url + "periodo/" + periodo;
            }
		});        
    });


    $( '#abre_start' ).on( 'click', function(){

		$.ajax({
			url: base_url + 'abre_periodo',
			data: { periodo: periodo, [csrf_token] : csrf_hash },
			type: 'POST',
			success: function(){
                // reload
                window.location.href = base_url + "periodo/" + periodo;
            }
		});        
    });    


    $( '#corte_start' ).on( 'click', function(){
        $( '.icon_gira' ).removeClass( 'text-red' ).addClass( 'fa-spin text-mustard' );

        $( '.pe1' ).hide();
        $( '.pe2' ).show();

        var pedidos = 0;

        $.ajax({
            type: 'POST',
			url: base_url + 'reset_corte',
            dataType: "json",
            async: true,
			data: { periodo: periodo, [csrf_token] : csrf_hash },
            success: function(r){ console.log(r);
                pedidos = r.pedidos;

                getStatus(pedidos);        
                do_corte( pedidos );                
            }
		});


    });
});
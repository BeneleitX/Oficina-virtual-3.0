
function lanza_corte(){
    $( '#modal_corte .modal-footer' ).hide();
    $( '#modal_corte .progress-bar' ).css( 'width', '0%' );
    $( '#dato_pedidos' ).text( '' );
    $( '#dato_socios' ).text( '' );
    $( '#dato_comisiones' ).text( '' );
    $( '#dato_isr' ).text( '' );
    $( '#dato_deposito' ).text( '' );
    $( '#dato_bolsa' ).text( '' );
    $( '.icon_gira' ).addClass( 'fa-repeat text-red' ).removeClass( 'fa-spin fa-check text-teal fa-triangle-exclamation text-mustard' );
    $( '.corte_aviso' ).removeClass( 'text-teal tetx-mustard' ).addClass( 'text-red' ).text( 'El proceso puede durar varios segundos' );
    $( '.pe1' ).show();
    $( '.pe2' ).hide();
    
    $( '#modal_corte' ).modal( 'show' );

}


function getStatus() { 
    $( '#modal_corte .progress-bar' ).css( 'width', '100%' );
   
    setTimeout(function() {

        fetch(base_url + 'assets/corte_check.php', {
            Method: 'POST',
            body: JSON.stringify({ periodo: periodo }),
            headers: {
                "Content-Type": "application/json",
                "X-Requested-With": "XMLHttpRequest"
              }
        })
        .then((resp) => resp.json())
        .then(function( respuesta ) {
            $( '#modal_corte .progress-bar' ).css( 'width', respuesta.porcentaje + '%' );
            
            $( '#dato_pedidos' ).text( respuesta.pedidos );
            $( '#dato_socios' ).text( respuesta.socios );
            $( '#dato_comisiones' ).text( Moneda.format( respuesta.comisiones ) );
            $( '#dato_isr' ).text( Moneda.format( respuesta.isr ) );
            $( '#dato_deposito' ).text( Moneda.format( respuesta.total ) );
            $( '#dato_bolsa' ).text( Moneda.format( respuesta.bolsa ) );

            if (respuesta.porcentaje < 100) {
                getStatus();
            }
        })
    }, Math.floor( Math.random() * 1000 ) );
}


$(document).ready(function(){

    new DataTable('#tabla_pagos, #tabla_anteriores', {
        pageLength: 50
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
        $( '.icon_gira' ).addClass( 'fa-spin' );

        $( '.pe1' ).hide();
        $( '.pe2' ).show();

		$.ajax({
			url: base_url + 'corte',
			data: { periodo: periodo, [csrf_token] : csrf_hash },
			type: 'POST',
			dataType: "json",
			async: true,
			success: function( respuesta ){
                if( respuesta.error !== undefined ){
                    $( '.icon_gira' ).removeClass( 'fa-spin fa-repeat text-red' ).addClass( 'fa-triangle-exclamation text-mustard' );
                    $( '.corte_aviso' ).removeClass( 'text-red' ).addClass( 'text-mustard' ).text( 'Ya existe otro corte en proceso' );
                    $( '#modal_corte .progress-bar' ).removeClass( 'bg-teal' ).addClass( 'bg-gray-500' );
                }
                else{
                    $( '.icon_gira' ).removeClass( 'fa-spin fa-repeat text-red' ).addClass( 'fa-check text-teal' );
                    $( '.corte_aviso' ).removeClass( 'text-red' ).addClass( 'text-teal' ).text( 'Corte finalizado' );
                }

                $( '#dato_pedidos' ).text( respuesta.pedidos );
                $( '#dato_socios' ).text( respuesta.socios );
                $( '#dato_comisiones' ).text( Moneda.format( respuesta.comisiones ) );
                $( '#dato_isr' ).text( Moneda.format( respuesta.isr ) );
                $( '#dato_deposito' ).text( Moneda.format( respuesta.total ) );
                $( '#dato_bolsa' ).text( Moneda.format( respuesta.bolsa ) );

                $( '#modal_corte .modal-footer' ).show();
                $( 'button[disabled]' ).prop( 'disabled', false);
            }
		});

        getStatus();
        
    });
});
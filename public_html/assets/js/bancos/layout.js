function modal_analiza(){
    $( 'input.upload').click();

    $( '#modal_analiza .modal-body' ).html( '<i class="fa-solid text-teal display-1 my-4 fa-circle-notch fa-spin"></i><h5 class="mb-3">Analizando archivo</h5>' );
}



$(document).ready(function(){

    var table = new DataTable('#tabla_pedidos', {
        pageLength: 50,
		columnDefs: [{ className: "dt-nowrap", "targets": [3] } ]
    });


    $( 'input.upload').on( 'change', function(){
		var formData = new FormData();

		formData.append( 'archivo', $( 'input.upload' )[0].files[0] ); 
		formData.append( [csrf_token] , csrf_hash ),

        $( '#modal_analiza' ).modal( 'show' );

		$.ajax({
			url: base_url + 'analiza_layout',
			data: formData,
			type: 'POST',
			dataType: "json",
			async: true,
			contentType: false, // NEEDED, DON'T OMIT THIS (requires jQuery 1.6+)
			processData: false, // NEEDED, DON'T OMIT THIS
			success: function( respuesta ){

					var pagos = [],
						n = 1;

					$( '#modal_analiza .modal-body' ).html( '<img class="w-50" src="' + respuesta.logo_banco + '"><div class="card mb-3"><div class="card-header bg-teal"><h5 class="text-white m-0">Resultados</h5></div><table class="table mb-0 w-100"><tr><td class="text-start">Filas analizadas</td><td class="text-end">' + respuesta.conteo.lineas + '</td></tr><tr><td class="text-start">Pagos válidos</td><td class="text-end">' + respuesta.conteo.pagos + '</td></tr><tr><td class="text-start">Ya ingresados</td><td class="text-end">' + ( respuesta.conteo.pagos - respuesta.conteo.pagados ) + '</td></tr><tr><td class="text-start">Pagos aplicados</td><td class="text-end">' + respuesta.conteo.pagados + '</td></tr></table></div><p><button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar ventana</button></p>' );
				
					$.each( respuesta.pagos, function( ref, data ){
						var p = [
							n++,
							'<img style="width:50px; border-radius:5px" src="' + respuesta.logo_banco + '">',
							'<span class="badge bg-marine">' + data.referencia + '</span>',
							data.socio ?? '<span class="text-red"><i class="fa fa-warning"></i> Pedido no encontrado</span>',
							data.fecha,
							Moneda.format( data.cantidad ),
							data.folio,
							data.accion ?? 'ninguna'
						];

						pagos.push( p );
					});
				
					table.rows
					.add( pagos )
					.draw();
			}
		});
	});

});
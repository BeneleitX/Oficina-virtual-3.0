$(document).ready(function(){

    new DataTable('#tabla_pedidos', {
        pageLength: 50
    });


    $( '.detalle_producto' ).on( 'click', function(){
        var producto = $( this ).attr( 'producto' );

        $( '#detalle_producto .modal-body' ).html( loader );
        $( '#detalle_producto .modal-header > h5' ).html( cat_productos[ producto ].data.nombre );

        $( '#detalle_producto' ).modal( 'show' );

        $.ajax({
            url: base_url + "get_data_producto", 
            type: "POST",
            data: { 
                [csrf_token] : csrf_hash, 
                producto     : producto,
                almacen      : almacen
            },
            success: function( result ){
                $( '#detalle_producto .modal-body' ).html( result );
            }
        });
    });
});

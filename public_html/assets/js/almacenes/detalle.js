
function carga_stock( producto, avatar ){

    
    $( '#stock_producto' ).val( producto );
    $( '#producto_head').text( cat_productos[ producto ].data.nombre);
    $( '#stock_avatar').attr( 'src', base_url + 'assets/img/productos/' + ( avatar ? producto : 'NO-IMAGEN' ) + '.png' );
    $( '#stock_modal' ).modal( 'show' );
}

$(document).ready(function(){

    new DataTable('#tabla_pedidos', {
        pageLength: 50
    });

    const myPopoverTrigger = document.getElementsByClassName('pover');

    for (var i = 0 ; i < myPopoverTrigger.length; i++) {
        myPopoverTrigger[i].addEventListener('shown.bs.popover', ( a ) => {

            $( '.popover-body a').on( 'click', function(){
                var producto = $( a.target ).attr( 'producto' ),
                    avatar   = $( a.target ).attr( 'avatar' );

                carga_stock( producto, avatar );
            });
        });
    }
});

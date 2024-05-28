 
function update_conteo(){
    var productos = problema ? 0 : $( 'input.btn-check:checked' ).length;

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

            imagen = cat_productos[ producto ].data.avatar ? producto : "NO-IMAGEN";
            $( '#modal_confirma' ).attr( 'producto', producto );
            $( '#modal_confirma' ).attr( 'numero', numero );
            $( '#modal_confirma img' ).attr( 'src', base_url + 'assets/img/productos/' + imagen + '.png' );
            $( '#modal_confirma div.nombre' ).html( '<h1 class="m-0">' + cat_productos[ producto ].data.nombre + '</h1><p class="mb-4">' + cat_productos[ producto ].data.descripcion + '</p>' );

            $( '#modal_confirma' ).modal( 'show' );
        }
    });

    $( '#confirma_agregar').on( 'click', function(){
        var producto = $( '#modal_confirma' ).attr( 'producto' ),
            numero   = $( '#modal_confirma' ).attr( 'numero' );

        $( '#check_' + producto + '_' + numero ).prop( 'checked', true );
        $( '#modal_confirma' ).modal( 'hide' );

        update_conteo();
    });

    $( 'input.btn-check' ).on( 'change', function(){
        update_conteo();       
    });

    if( problema ){
        $( '#boton_entregado_no' ).html( 'hay un problema con la configuración de este pedido' );
    }
});
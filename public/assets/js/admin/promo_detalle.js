function modal_productos(){
    var elegibles = [];

    $( '#modal_productos div[producto]' ).hide();

    $( '#elegibles input:checked').each(function(){
        elegibles.push( $( this ).val() );
        $( '#modal_productos div[producto=' + $( this ).val() + ']' ).show();
    });

    $( '#modal_productos' ).modal( 'show' );
}


function agrega_producto( producto ){
    p = cat_productos[ producto ];

    $( '#precargados' ).append( '<div class="col-6 text-center col-lg-2 small mb-2" producto="' + p.codigo + '"><label data-bs-toggle="tooltip" title="Click para quitar" style="padding:0;" class="btn col-12 btn-primary">' + p.data.nombre.toUpperCase() + '</label></div>' );

    $( '#precargados div[producto] label' ).on( 'mouseover', function(){
        $( this ).removeClass( 'btn-primary' ).addClass( 'btn-danger' );
    }).on( 'mouseout', function(){
        $( this ).addClass( 'btn-primary' ).removeClass( 'btn-danger' );
    }).on( 'click', function(){
        $( this ).closest( 'div[producto]' ).remove();
    } );

    boton = $( '#boton_agrega' ).appendTo( '#precargados' );
    $( '#modal_productos' ).modal( 'hide' );

    $('[data-bs-toggle="tooltip"]').tooltip({
        container: 'body',
        html: true,
        placement : 'top'
    });      
}

$(document).ready(function(){

    $( '#guarda_cambios' ).on( 'click', function( e ){
        e.preventDefault();
        e.stopPropagation();

        // preparar json a enviar
        data = 
        {
            "codigo": $( '[name=n_codigo]' ).val(),
            "estatus_codigo": $( '[name=n_estatus]' ).val(),
            "modelo_codigo": modelo,
            "settings": {
                "clase": $( '[name=n_clase]' ).val(),
                "exacto": $( '[name=n_exacto]' ).is( ':checked' ),
                "obligatoria": $( '[name=n_obligatoria]' ).is( ':checked' ),
                "forced": $( '[name=n_forced]' ).is( ':checked' ),
                "nombre": $( '[name=n_nombre]' ).val(),
                "siglas": $( '[name=n_siglas]' ).val(),
                "modelos": [
                    "10-NUTRICION"
                ],
                "paquete": $( '[name=n_paquete]' ).is( ':checked' ),
                "descripcion": $( '[name=n_descripcion]' ).val(),
            },
            "inicia": $( '[name=n_inicia]' ).val(),
            "termina": $( '[name=n_termina]' ).val(),
            "productos": {
                "precarga": [],
                "elegibles": []
            },
            "formulas": {
                "precio": $( '[name=n_precio]' ).val(),
                "activacion": $( '[name=n_activacion]' ).val(),
                "disponible": $( '[name=n_disponible]' ).val()
            }
        };

        $( '#elegibles input:checked').each(function(){
            data.productos.elegibles.push( $( this ).val() );
        });
        $( '#precargados [producto]').each(function(){
            data.productos.precarga.push( $( this ).attr( 'producto' ) );
        });

        // ajax para guardar)
        $.ajax({
            url: base_url + "save_promo", 
            type: "POST",
            contentType: 'application/x-www-form-urlencoded; charset=UTF-8',
            data: { [csrf_token] : csrf_hash, data : data },
            success: function( result ){
                window.location.href = base_url + "promo_detalle/" + data.codigo;
            }
        });
    });

    $( '#boton_agrega button' ).on( 'click', function( e ){
        e.preventDefault();
        modal_productos();
    } );

    $.each( precarga, function( k, p ){
        agrega_producto( p )
    });
});
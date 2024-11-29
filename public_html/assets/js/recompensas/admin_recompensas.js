function entregar_recompensa( r_id ){
    var tr = $( 'tr[redencion=' + r_id + ']' ),
        r_codigo = tr.attr( 'recompensa' ),
        r = cat_recompensas[ r_codigo ], 
        clon = tr.find( 'td:eq(1)' ).clone().html();

    $( '.img_recompensa' ).attr( 'src', base_url + 'assets/img/recompensas/' + r.codigo + '.png' );
    $( '.recompensa_nombre' ).text( r.nombre );
    $( 'input[name=redencion]' ).val( r_id );
    $( '#socio_data' ).html( clon );
    $( '#entregar_recompensa' ).modal( 'show' );
}


$(document).ready(function(){

    new DataTable('.tabla_redenciones', {
        pageLength: 50
    });
});
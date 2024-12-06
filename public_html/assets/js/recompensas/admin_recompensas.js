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

    $( '#descarga_premios' ).on( 'click', function(){
        var btn = $( this );

        btn.addClass( 'disabled' ).html( '<i class="fa-solid fa-circle-notch fa-spin"></i> Procesando...' );

        $.ajax({
            url: base_url + 'excel_premios',
            data: { [csrf_token] : csrf_hash },
            type: 'POST',
            success: function( file ){
                // download
                btn.removeClass( 'disabled' ).html( '<i class="fa fa-file-excel"></i><span class="d-none d-lg-inline"> Descargar Excel</span>' );
                window.location.href = base_url + file;
            }
        });  
    });    
});
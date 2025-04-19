


$(document).ready(function(){

    new DataTable('#tabla_participantes', {
        pageLength: 1000,
        order: [ [ 6, 'asc' ] ]
    });


    $( '#descarga_semillero' ).on( 'click', function(){
        var btn = $( this );

        btn.addClass( 'disabled' ).html( '<i class="fa-solid fa-circle-notch fa-spin"></i> Procesando...' );

        $.ajax({
            url: base_url + 'excel_semillero',
            data: { 'evento' : evento, [csrf_token] : csrf_hash },
            type: 'POST',
            success: function( file ){
                // download
                btn.removeClass( 'disabled' ).html( '<i class="fa fa-file-excel"></i><span class="d-none d-lg-inline"> Descargar Excel</span>' );
                window.location.href = base_url + file;
            },
            error: function(){
                btn.removeClass( 'disabled' ).html( '<i class="fa fa-file-excel"></i><span class="d-none d-lg-inline"> Descargar Excel</span>' );
            }
        });  
    });
});
function excel_pines(){
    var btn = $( this );

    btn.addClass( 'disabled' ).html( '<i class="fa-solid fa-circle-notch fa-spin"></i> Procesando...' );

    $.ajax({
        url: base_url + 'excel_pines_pendientes',
        data: { [csrf_token] : csrf_hash },
        type: 'POST',
        success: function( file ){
            // download
            btn.removeClass( 'disabled' ).html( '<i class="fa fa-file-excel"></i> Descargar pendientes' );
            window.location.href = base_url + file;
        }
    }); 
}


$(document).ready(function(){

    new DataTable('#tabla_rangos', {
        pageLength: 50
    });

});
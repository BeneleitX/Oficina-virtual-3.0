
function excel_rangos( mes ){
    var btn = $( this );
console.log( mes );
    btn.addClass( 'disabled' ).html( '<i class="fa-solid fa-circle-notch fa-spin"></i> Procesando...' );

    $.ajax({
        url: base_url + 'excel_rangos',
        data: { 'mes' : mes, [csrf_token] : csrf_hash },
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
}



$(document).ready(function(){

    new DataTable('#tabla_solicitudes', {
        pageLength: 50, 
        order: [ [ 6, 'desc' ] ],
    });

    $( '#mes_retiros' ).on( 'change', function(){
        window.location.href = base_url + "bono_liderazgo/" + $( '#mes_retiros' ).val();
    });

});
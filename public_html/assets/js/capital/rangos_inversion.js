function detalle_bono( mes ){
    var tr = $( 'tr[mes=' + mes + ']' ),
        cantidad = tr.attr( 'cantidad' );

    $( '#detalle_bono .modal-body' ).html( loader + ' <i class="fa fa-warning text-red"></i> Este proceso puede durar varios segundos dependiendo el tamaño de red' );
    $( '#detalle_bono' ).modal( 'show' );

    console.log( cantidad );
    
    $.ajax({
        url: base_url + "get_bono_liderazgo", 
        type: "POST",
        data: { 
            [csrf_token] : csrf_hash, 
            socio        : usuario.id,
            mes          : mes,
            cantidad     : cantidad
        },
        success: function( result ){
            $( '#detalle_bono .modal-body' ).html( result );
        }
    });    
}



$(document).ready(function(){

    
});

$(document).ready(function(){

    // new DataTable('#tabla_variables', {
    //    pageLength: 50
    //});

    $( 'tr[variable] .form-control' ).on( 'change', function(){
        var control  = $( this ),
            tr       = control.closest( 'tr' ),
            variable = tr.attr( 'variable' ),
            valor    = control.val();

        $.ajax({
            url: base_url + "save_variable", 
            type: "POST",
            contentType: 'application/x-www-form-urlencoded; charset=UTF-8',
            data: { [csrf_token] : csrf_hash, variable : variable, valor: valor },
            success: function( result ){
                
            }
        });
    } );

});

function reclama_recompensa( recompensa ){
    r = cat_recompensas[ recompensa ];

    $( '.img_recompensa' ).attr( 'src', base_url + 'assets/img/recompensas/' + r.codigo + '.png' );
    $( '.cantidad_estrellas' ).text( r.estrellas );
    $( '.saldo_estrellas' ).text( total_estrellas - r.estrellas );
    $( '.recompensa_nombre' ).text( r.nombre );
    $( '.boton_reclama' ).attr( 'href' , base_url + '/reclama_recompensa/' + r.codigo );
    $( '#reclama_recompensa' ).modal( 'show' );
}


$(document).ready(function(){

    new DataTable('.tabla_estrellas', {
        pageLength: 50
    });

    $('#ciclo_1').sortable({
        group: 'list',
        animation: 200,
        ghostClass: 'ghost',
        onSort: reportActivity,
    });

    function reportActivity() {
        var sort1 = [];
        
        $('#ciclo_1 [recompensa_orden]').each( function(){
            sort1.push( $( this ).attr( 'recompensa_orden' ) );
        });

         $.ajax({
			url: base_url + "guarda_recompensas", 
			type: "POST",
			data: { [csrf_token] : csrf_hash, orden : sort1, ciclo: 1 }
		}); 
    };
});
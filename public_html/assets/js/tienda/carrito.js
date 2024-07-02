$(document).ready(function(){

	function delay(fn, ms) {
		let timer = 0
		return function(...args) {
		  clearTimeout(timer)
		  timer = setTimeout(fn.bind(this, ...args), ms || 0)
		}
	  }


	$( '#busca_producto' ).keyup(delay(function (e) {

        var busqueda = $( this ).val();
        $('[producto]').show();
        
        $('[producto]').each( function(){
            var texto = $( this ).text();
            if( !( texto.toLowerCase().indexOf( busqueda.toLowerCase() ) >= 0 ) )
                $( this ).hide();
        } ); 

    }, 400));

});

$(document).ready(function(){

	$( '[name=patrocinador]' ).keyup(delay(function (e) {
		var pat = parseInt( $( '[name=patrocinador]' ).val() );

		$( '.verificado, .noverifica' ).empty();
		$( '[name=patrocinador]' ).removeClass( 'is-invalid' );

		if( pat ){
			$.ajax({
				url: base_url + "valida_patrocinador", 
				type: "POST",
				dataType: "json",
				contentType: 'application/x-www-form-urlencoded; charset=UTF-8',
				data: { [csrf_token] : csrf_hash, id : pat, avatar_size: 40 },
				success: function( result ){

					
					if( result.error ){
						// $( '.noverifica' ).html( result.error );
						// $( '[name=patrocinador]' ).addClass( 'is-invalid' );
					}
					else{
						// se debe de meter un true a un campo hidden 

						$( '.verificado' ).html( '<table><tr><td style="padding-right:10px">' + result.avatar+ '</td><td class="lh-1">' + result.nombre + '<br><span class="small text-green"><i class="fa fa-check"></i> Verificado</span></td></tr></table>' );
					}
				}
			});
		}
	}, 600));
});

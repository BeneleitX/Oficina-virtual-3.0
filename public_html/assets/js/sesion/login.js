
function reload_captcha(){

    const interval_id = window.setInterval(function(){}, Number.MAX_SAFE_INTEGER);

    for (let i = 1; i < interval_id; i++) {
        window.clearInterval(i);
    }

    $( '.circlebar' ).empty().append( '<div id="contador"></div>' );

    new Circlebar({
        element: "#contador",
        startValue: 0,
        maxValue: 30,
        dialWidth: 3,
        counter: 1000,
        size: "38px",
        type: "timer"
    });

    $('#captcha').attr('src', base_url + 'captcha.php?white=' + ( white ? 1 : 0) + '&t=' + Math.random()*1000 );
}


$(document).ready(function(){

    reload_captcha();

    $("#login_bsumbit").submit( function()
    {
        var socio = $.trim( $( '[name=socio_id]').val() );
        $( '[name=socio_id]').val( socio );

        $( 'input' ).prop( 'readonly', true ).css( 'background', '#ccc');

        const interval_id = window.setInterval(function(){}, Number.MAX_SAFE_INTEGER);

        for (let i = 1; i < interval_id; i++) {
            window.clearInterval(i);
        }

        return true; // ensure form still submits
    });


    $( '#pass_reveal' ).on( 'click', function( e ){
        var input = $( 'input[name=socio_password]' );

        if( input.prop( 'type' ) == 'password' ) {
            input.prop( 'type',  'text');
            $( this ).html( '<i class="fa fa-eye text-gray-600"></i>' );
        }
        else{
            input.prop( 'type',  'password');
            $( this ).html( '<i class="fa fa-eye-slash text-gray-600"></i>' );
        }
        
    })
});

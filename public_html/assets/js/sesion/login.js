function reload_captcha(){
//    var url = base_url + 'captcha?' + Math.random()*1000;
    var url = base_url + 'captcha.php?' + Math.random()*1000;
    
    $('#captcha').attr('src', url );
}

$(document).ready(function(){

    reload_captcha();

    $("#login_bsumbit").submit( function()
    {
        var socio = $.trim( $( '[name=socio_id]').val() );
        $( '[name=socio_id]').val( socio );

        return true; // ensure form still submits
    });
});

$(document).ready(function(){
    $(function()
    {
        $("#login_bsumbit").submit(function()
        {
            $( '[name=socio_id]').val( $.trim( $( '[name=socio_id]').val() ) );
    
            return true; // ensure form still submits
        });
    });
});

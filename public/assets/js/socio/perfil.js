
var cropper;

$(document).ready(function(){

    $( '#imagen_avatar' ).on( 'mouseover', function(){
        $( '#cambia_avatar' ).show();
    }).on( 'mouseout', function(){
        $( '#cambia_avatar' ).hide();
    });
 
    $( '#cambia_avatar' ).on( 'mouseover', function(){
        $( '#cambia_avatar' ).show();
    }).on( 'click', function(){
        $('#profileImageModal').modal( 'show' );    
    });

});    
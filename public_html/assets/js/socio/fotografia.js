

$(document).ready(function(){

    $('.vanilla-rotate').on('click', function(ev) {
        $uploadCrop.croppie( 'rotate',  parseInt($(this).data('deg') ) );
    });

    var $uploadCrop;

    function readFile(input) {
        if (input.files && input.files[0]) {
            var reader = new FileReader();
            
            reader.onload = function (e) {
                $('.upload-demo').addClass('ready');
                $uploadCrop.croppie('bind', {
                    url: e.target.result
                });
            }
            
            reader.readAsDataURL(input.files[0]);
        }
    }

    $uploadCrop = $('#upload-demo').croppie({
        viewport: {
            width: 200,
            height: 200,
            type: 'circle'
            
        },
        enableExif: true,
        enableOrientationboolean: true,
        enableOrientation: true
    });

    $('#foto_upload').on('change', function () { readFile(this); });
    $('.upload-result').on('click', function (ev) {

        $uploadCrop.croppie('result', {
            type: 'canvas',
            size: { 'width' : 400, 'height' : 400},
            format : 'jpeg',
            circle : false
        }).then(function (resp) {


            $.ajax({
                url: base_url + "guarda_avatar",
                type: "POST",
                data: {

                    [csrf_token] : csrf_hash,
                    "image": resp
                },
                success: function(data) {
                    window.location.href = base_url + "perfil";
                }
            });
        });
    });
});    

function popupResult(result) {
    var html;
    if (result.html) {
        html = result.html;
    }
    if (result.src) {
        html = '<img src="' + result.src + '" />';
    }
    $( '#upload-demo' ).append( html );

}
// CAMARA PARA INE

    const CoreAPIEndpoint = "https://api.idanalyzer.com";

    const documentcanvas = document.getElementById('documentcanvas');
    const documentctx = documentcanvas.getContext('2d');
    let currentStream;
    let currentDeviceID;
    let cameraLoading = false;

    String.prototype.escape = function() {
        var tagsToReplace = {
            '&': '&amp;',
            '<': '&lt;',
            '>': '&gt;'
        };
        return this.replace(/[&<>]/g, function(tag) {
            return tagsToReplace[tag] || tag;
        });
    };

    function setVideoAttr(){
        // Workaround iOS Safari bug when switching camera
        $('#cameraDisplay')[0].setAttribute('autoplay', '');
        $('#cameraDisplay')[0].setAttribute('muted', '');
        $('#cameraDisplay')[0].setAttribute('playsinline', '');
    }

    function stopCamera( finaliza = false ) {
        if (typeof currentStream !== 'undefined' && currentStream !== false) {
            // Workaround Android 11 Chrome camera freeze when switching camera
            $('#cameraDisplay')[0].srcObject = null;
            currentStream.getTracks().forEach(track => {
                track.stop();
            });
            currentStream = false;
        }

        if( finaliza ){
            window.parent.closeModal();
        }
    }

    function getCameraDevices(){

        if ('mediaDevices' in navigator) {
            navigator.mediaDevices.getUserMedia({
                video: true,
                audio: false
            }).then((stream) => {
                currentStream = stream;
                const getCameraSelection = async () => {
                    const devices = await navigator.mediaDevices.enumerateDevices();
                    const videoDevices = devices.filter(device => device.kind === 'videoinput');
                    const options = videoDevices.map(videoDevice => {
                        $("#cameraList").append(`<option value="${videoDevice.deviceId}">${videoDevice.label}</option>`);
                    });
                    stopCamera();
                    if ($("#cameraList option").length === 0) {
                        alert("Sorry, your device does not have a camera.");
                    } else{
                        $('#btnStartCamera').prop("disabled", true);
                        startCamera();
                    }
                };
                getCameraSelection();
            }).catch((error) => {
                alert("Failed to get camera list!");
            });

        } else {
            alert("Browser does not support mediaDevices API.");
        }
    }
    function startCamera() {
        // get device id from selected item
        if(currentDeviceID === $("#cameraList").val()) return;
        currentDeviceID = $("#cameraList").val();
        // start camera stream with device id
        startCameraStream(currentDeviceID);
    }

    function startCameraStream(deviceID) {
        if ('mediaDevices' in navigator && navigator.mediaDevices.getUserMedia) {
            if (cameraLoading === true) return;
            cameraLoading = true;
            console.log("Loading camera: "+deviceID);
            // build a constraint
            let constraints = {
                video: {
                    width: {ideal: 1920},
                    height: {ideal: 1080},

                    deviceId: {
                        exact: deviceID
                    }
                }
            };
            // stop current stream if there is one
            stopCamera();

            setVideoAttr();
            // delay the stream for a bit to prevent browser bugging out when switching camera
            setTimeout(function () {
                navigator.mediaDevices.getUserMedia(constraints).then(function (mediastream) {
                    currentStream = mediastream;
                    $('#cameraDisplay')[0].srcObject = mediastream;
                    cameraLoading = false;
                    $('#btnCapture').prop("disabled", false);

                }).catch(function (err) {
                    console.log(err);
                    cameraLoading = false;
                    alert("Camera Error!");
                });

            }, 100);
        }
    }

    function captureImage(){

        // Copy the video frame to canvas
        documentcanvas.width = $('#cameraDisplay')[0].videoWidth;
        documentcanvas.height = $('#cameraDisplay')[0].videoHeight;
        documentctx.drawImage($('#cameraDisplay')[0], 0, 0, documentcanvas.width, documentcanvas.height, 0, 0, documentcanvas.width, documentcanvas.height);

        // convert canvas content to base64 image
        let imageBase64 = documentcanvas.toDataURL("image/jpeg");

        // send the base64 content to Core API
        CoreAPIScan(imageBase64);
    }
    function CoreAPIScan(imageContent) {

        $("#btnCapture").prop( 'disabled', true).html( '<i class="fa fa-spin fa-spinner"></i> Procesando...' );

        $.ajax({
            url: base_url + "camara_shot",
            data: JSON.stringify({
                [csrf_token] : csrf_hash, 
                modo   : modo,
                tempID : tempID,
                shot   : imageContent
            }),
            type: "POST",
            // contentType: 'application/x-www-form-urlencoded; charset=UTF-8',
            contentType: "application/json; charset=UTF-8",
            async: true,
            success: function (output) {
                window.parent.closeModal( modo );
            }
        });
    }


    
$(document).ready(function(){
    getCameraDevices();
});

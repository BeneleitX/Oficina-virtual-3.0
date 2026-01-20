
$(document).ready(function(){
    const url = 'https://' + usuario_id + '.beneleit.mx',
          qrCode = new QRCodeStyling(
{"type":"canvas","shape":"square","width":250,"height":250,"data": url,"margin":0,"qrOptions":{"typeNumber":"0","mode":"Byte","errorCorrectionLevel":"Q"},"imageOptions":{"saveAsBlob":true,"hideBackgroundDots":true,"imageSize":0.4,"margin":0},"dotsOptions":{"type":"rounded","color":"#009779","roundSize":true,"gradient":null},"backgroundOptions":{"round":0,"color":"#1a2542"},"image":"","dotsOptionsHelper":{"colorType":{"single":true,"gradient":false},"gradient":{"linear":true,"radial":false,"color1":"#6a1a4c","color2":"#6a1a4c","rotation":"0"}},"cornersSquareOptions":{"type":"extra-rounded","color":"#009779"},"cornersSquareOptionsHelper":{"colorType":{"single":true,"gradient":false},"gradient":{"linear":true,"radial":false,"color1":"#000000","color2":"#000000","rotation":"0"}},"cornersDotOptions":{"type":"","color":"#009779"},"cornersDotOptionsHelper":{"colorType":{"single":true,"gradient":false},"gradient":{"linear":true,"radial":false,"color1":"#000000","color2":"#000000","rotation":"0"}},"backgroundOptionsHelper":{"colorType":{"single":true,"gradient":false},"gradient":{"linear":true,"radial":false,"color1":"#009779","color2":"#009779","rotation":"0"}}}        
    );

    qrCode.append(document.getElementById("qrcode"));
    // qrCode.download({ name: "qr", extension: "svg" });  

});
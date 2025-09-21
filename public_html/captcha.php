<?php

function hextorgb ($hexstring){
    $integar = hexdec($hexstring);
    return array(
    "red" => 0xFF & ($integar >> 0x10),
    "green" => 0xFF & ($integar >> 0x8),
    "blue" => 0xFF & $integar
    );
}

session_start();

$captcha = "";
$captcha_b = "";
$letra = "";
$captchaHeight = 40;
$captchaWidth = 240;
$totalCharacters = 6;
$possibleLetters = "1234567890";
$captchaFont = "assets/captcha.ttf";
$randomDots = 20;
$randomLines = 10;
$textColor = "47c24c";
$noiseColor = "009779";
$character = 0;

while ($character < $totalCharacters) {
    $letra = substr($possibleLetters, mt_rand(0, strlen($possibleLetters)-1), 1);
    $captcha .= $letra;
    $captcha_b .= "    ".$letra;
    $character++;
}

setcookie( "captcha", md5( $captcha ), time() + 32, "/");

$captchaFontSize = $captchaHeight * 0.65;
$captchaImage = @imagecreate($captchaWidth,$captchaHeight);
$backgroundColor = imagecolorallocate($captchaImage,26,37,66); // color fondo
$arrayTextColor = hextorgb($textColor);
$textColor = imagecolorallocate($captchaImage,$arrayTextColor['red'],$arrayTextColor['green'],$arrayTextColor['blue']);
$arrayNoiseColor = hextorgb($noiseColor);
$imageNoiseColor = imagecolorallocate($captchaImage,$arrayNoiseColor['red'],$arrayNoiseColor['green'],$arrayNoiseColor['blue']);

for( $captchaDotsCount=0; $captchaDotsCount<$randomDots; $captchaDotsCount++ ) {
    imagefilledellipse($captchaImage,mt_rand(0,$captchaWidth),mt_rand(0,$captchaHeight),2,3,$imageNoiseColor);
}

for( $captchaLinesCount=0; $captchaLinesCount<$randomLines; $captchaLinesCount++ ) {
    imageline($captchaImage,mt_rand(0,$captchaWidth),mt_rand(0,$captchaHeight),mt_rand(0,$captchaWidth),mt_rand(0,$captchaHeight),$imageNoiseColor);
}
$text_box = imagettfbbox($captchaFontSize,0,$captchaFont,$captcha_b);

$x = ($captchaWidth - $text_box[4])/2;
$y = ($captchaHeight - $text_box[5])/2;



imagettftext($captchaImage,$captchaFontSize,0,$x,$y,$textColor,$captchaFont,$captcha_b);
header('Content-Type: image/jpeg');
imagejpeg($captchaImage);
imagedestroy($captchaImage);



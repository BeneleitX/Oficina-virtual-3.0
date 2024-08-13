<?php

function AESencriptar( $AES, $xml )
{
    $iv = openssl_random_pseudo_bytes(openssl_cipher_iv_length('aes-128-cbc'));
    $cipherText = openssl_encrypt ( $xml, 'AES-128-CBC', hex2bin( $AES[ "key128" ]), 1, $iv);
    return base64_encode($iv.$cipherText);
}


function AESdesencriptar($encodedInitialData)
{
    $encodedInitialData =  base64_decode($encodedInitialData);
    $iv = substr($encodedInitialData,0,16);
    $encodedInitialData = substr($encodedInitialData,16);
    return openssl_decrypt($encodedInitialData, 'AES-128-CBC', hex2bin($this->key128), 1, $iv);
}

function getCadenaXML( $pedido, $socio ){

    $sandbox = [
        "empresa"  => "SNBX",
        "sucursal" => "01SNBXBRNCH",
        "usuario"  => "SNBXUSR01",
        "password" => "SECRETO",
        "key128"   => "5dcc67393750523cd165f17e1efadd21",
        "cadena"   => "SNDBX123",
        "url"      => "https://wppsandbox.mit.com.mx/gen"
    ];

    $beneleit = [
        "empresa"  => "6422",
        "sucursal" => "0003",
        "usuario"  => "6422SIUS0",
        "password" => "9ON9SF7YYU",
        "key128"   => "E2DC8AFBF99F6E298C813F4BD572D688",
        "cadena"   => "9265655162",
        "url"      => "https://bc.mitec.com.mx/p/gen"
    ];    

    // Elegir ambiente
    $AES = $sandbox;

    $subtotal = $pedido[ "data" ][ "total" ] + $pedido[ "data" ][ "comisionentrega" ] - $pedido[ "data" ][ "saldo" ];
    $comisionbanco = $subtotal * 2 / 100;
    $total = $subtotal + $comisionbanco;

    $xml = "<?xml version=\"1.0\" encoding=\"UTF-8\"?>
            <P>
                <business>
                    <id_company>{$AES[ "empresa" ]}</id_company>
                    <id_branch>{$AES[ "sucursal" ]}</id_branch>
                    <user>{$AES[ "usuario" ]}</user>
                    <pwd>{$AES[ "password" ]}</pwd>
                </business>
                <url>
                    <reference>{$pedido[ "referencia" ]}</reference>
                    <amount>{$total}</amount>
                    <moneda>MXN</moneda>
                    <canal>W</canal>
                    <omitir_notif_default>0</omitir_notif_default>
                    <st_correo>0</st_correo>
                    <mail_cliente>{$socio->correo}</mail_cliente>
                </url>
            </P>";

    $cifrado = AESencriptar( $AES, $xml );


    $encodedString = urlencode('<pgs><data0>{$AES[ "cadena" ]}</data0><data>{$cifrado}</data></pgs>');
    $request = new \HttpRequest();
    $request->setUrl( $AES[ "url" ] );
    $request->setMethod(HTTP_METH_POST);
    
    $request->setHeaders(array(
      'cache-control' => 'no-cache',
      'content-type' => 'application/x-www-form-urlencoded'
    ));
    
    $request->setContentType('application/x-www-form-urlencoded');
    $request->setPostFields(array(
      'xml' => encodedString
    ));
    
    try {
      $response = $request->send();
    
      echo $response->getBody();
    } catch (HttpException $ex) {
      echo $ex;
    }    
}
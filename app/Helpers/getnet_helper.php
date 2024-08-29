<?php

function AESencriptar($plaintext, $key128){
    $iv = openssl_random_pseudo_bytes(openssl_cipher_iv_length('aes-128-cbc'));
    $cipherText = openssl_encrypt ( $plaintext, 'AES-128-CBC', hex2bin($key128), 1, $iv);
    return base64_encode($iv.$cipherText);
  }


function AESdesencriptar($encodedInitialData, $key128){
    $encodedInitialData =  base64_decode($encodedInitialData);
    $iv = substr($encodedInitialData,0,16);
    $encodedInitialData = substr($encodedInitialData,16);
    $decrypted = openssl_decrypt($encodedInitialData, 'AES-128-CBC', hex2bin($key128), 1, $iv);
    return $decrypted;
  }

function getCadenaXML( $pedido, $socio ){
    $getnet = VARIABLES[ "getnet" ][ "valor" ];

    // Elegir ambiente
    $AES = $getnet[ "ambientes" ][ $getnet[ "ambiente" ] ];

    $subtotal = $pedido[ "data" ][ "total" ] + $pedido[ "data" ][ "comisionentrega" ] - $pedido[ "data" ][ "saldo" ];
    $comisionbanco = $subtotal * 2 / 100;
    $total = 2; //$subtotal + $comisionbanco;

    $xml = "<?xml version=\"1.0\" encoding=\"UTF-8\"?><P><business><id_company>{$AES[ "empresa" ]}</id_company><id_branch>{$AES[ "sucursal" ]}</id_branch><user>{$AES[ "usuario" ]}</user><pwd>{$AES[ "password" ]}</pwd></business><url><reference>{$pedido[ "referencia" ]}</reference><amount>{$total}</amount><moneda>MXN</moneda><canal>W</canal><omitir_notif_default>0</omitir_notif_default><st_correo>0</st_correo><mail_cliente>{$socio->correo}</mail_cliente><version>IntegraWPP</version></url></P>";

    $cifrado = AESencriptar( $xml, $AES[ "key128" ] );
    $encodedString = "<pgs><data0>{$AES[ "cadena" ]}</data0><data>{$cifrado}</data></pgs>";
    
    $curl = curl_init();
    curl_setopt($curl, CURLOPT_URL, $AES[ "url" ] );
    curl_setopt($curl, CURLOPT_POST, true );
    curl_setopt($curl, CURLOPT_POSTFIELDS, http_build_query( [ "xml" => $encodedString ] ) );
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, true); 
    $respuesta = curl_exec( $curl );
    curl_close($curl);

    return simplexml_load_string( AESdesencriptar( $respuesta, $AES[ "key128" ] ) )->nb_url;
}
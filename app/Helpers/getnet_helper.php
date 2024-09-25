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

    // Elegir ambiente
    $getnet   = VARIABLES[ "getnet" ][ "valor" ];
    $AES      = $getnet[ "ambientes" ][ $getnet[ "ambiente" ] ];
    $usuario  = model( "UsuarioModel" )->find( $pedido[ "usuario_id" ] );
    $subtotal = $pedido[ "data" ][ "total" ] + $pedido[ "data" ][ "comisionentrega" ] - $usuario->saldo( $pedido[ "modelo_codigo" ] );
    $comisionbanco = ceil( $subtotal * 2 / 100 );
    $total    = $subtotal + $comisionbanco;

    $xml = "<?xml version=\"1.0\" encoding=\"UTF-8\"?><P><business><id_company>{$AES[ "empresa" ]}</id_company><id_branch>{$AES[ "sucursal" ]}</id_branch><user>{$AES[ "usuario" ]}</user><pwd>{$AES[ "password" ]}</pwd></business><url><reference>{$pedido[ "referencia" ]}</reference><amount>{$total}</amount><moneda>MXN</moneda><canal>W</canal><omitir_notif_default>0</omitir_notif_default><st_correo>0</st_correo><mail_cliente>".trim( $socio->correo )."</mail_cliente><version>IntegraWPP</version></url></P>";

    return $xml;
}

function getCadenaURL( $xml ){
  // Elegir ambiente
  $getnet = VARIABLES[ "getnet" ][ "valor" ];
  $AES = $getnet[ "ambientes" ][ $getnet[ "ambiente" ] ];
  $cifrado = AESencriptar( $xml, $AES[ "key128" ] );
  $encodedString = "<pgs><data0>{$AES[ "cadena" ]}</data0><data>{$cifrado}</data></pgs>";


  $curl = curl_init();
  curl_setopt($curl, CURLOPT_URL, $AES[ "url" ] );
  curl_setopt($curl, CURLOPT_POST, true );
  curl_setopt($curl, CURLOPT_POSTFIELDS, http_build_query( [ "xml" => $encodedString ] ) );
  curl_setopt($curl, CURLOPT_RETURNTRANSFER, true); 
  $respuesta = curl_exec( $curl );
  curl_close($curl);
  $des = AESdesencriptar( $respuesta, $AES[ "key128" ] );

  return simplexml_load_string( $des )->nb_url ?? base_url( "no_internet" );
}



function xml2array ( $xmlObject, $out = array () )
{
    foreach ( (array) $xmlObject as $index => $node )
        $out[$index] = ( is_object ( $node ) ) ? xml2array ( $node ) : $node;

    return $out;
}
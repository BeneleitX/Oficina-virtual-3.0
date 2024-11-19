<?php

function getCadenaCheckoutID( $xml ){
  
    // Elegir ambiente
  
    $conekta  = VARIABLES[ "conekta" ][ "valor" ];
    $ambiente = $getnet[ "ambientes" ][ $getnet[ "ambiente" ] ];



    $curl = curl_init();
    curl_setopt($curl, CURLOPT_URL, "https://api.conekta.io/orders" );
    curl_setopt($curl, CURLOPT_POST, true );
    curl_setopt($curl, CURLOPT_POSTFIELDS, http_build_query( [ "xml" => $encodedString ] ) );
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, true); 
    $respuesta = curl_exec( $curl );
    curl_close($curl);
    
    return $respuesta;
}

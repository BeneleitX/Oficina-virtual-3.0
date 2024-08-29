<?php
namespace App\Controllers;

use App\Entities\Usuario;

class Gateway extends BaseController
{

    public function GetnetGatewayResponse(){
        helper( "getnet_helper" );

        // Elegir ambiente
        $getnet = VARIABLES[ "getnet" ][ "valor" ];
        $AES = $getnet[ "ambientes" ][ $getnet[ "ambiente" ] ];

        $respuesta = $this->request->getPost( "strResponse" );

        // test
        $respuesta = "2fhWxW5eV6MDsEWYBXBaYZbc1iHV5m1uLCuz4IHY9qdajxjJ%2BNjbvkn2%2FzliqRaAFOpZODBfAu2U%2BoSjsxi1%2FQ5qnyuRHLekSfE%2FjBC9Tf9EWcAIgT0a5YuwszNFYjI%2BaEwWQJ4ry9msZj1cdDE12ZYbAaAfbtT2bR2RcOMp%2BM71F0RuOd8j%2Fz6%2FFhd7mr%2BvM8UKGVIY0V%2F3OB%2BXQQBXH8vBCE%2B5x7hQKY6DoL%2Fyf4Op4hh1jo%2B4I6S4f%2BMJQPGfBi6YzTFMOlkhhg8zj%2FIyhtUDfq8T3q%2FDP8GcOl9h7tpVWN99fOFazxzvPUMw4sE%2BU8B9FmGTSd87O5VT1ndn7ffkrCBVeekIJnzjfkxNkYzYf02mw9GtbgSA2fC47olckm27uV9TqEHSgoJ5f%2BGdJAKMfPpjwNBHtkcbu1URTv5Svq1jHHyiImMQtU5GGya5qqzEqoSKPDPtvN9NY992FhDp4cXjfiz0ydjHwaXJquYot4dhX5c3gJOzinvpGHQxOfq9Xezg2mNTruwZUf2Up0mmIm27W%2B2HjyDvChLK2CSzQVFErZuqcHpi1urY6o%2FBS3vGVUcygaFgJ6aDT%2B6W0UeGfGE8p7%2B2xFM19GSJhsSt2f0e%2BjlcjjWGRkEO24jsu0ZOgHjHUnJlhAle2O0MIzUAWAo1cjNTkqk6vs%2BdVJIMIJLd1fB1oIXYA05%2BlkcqdlTPdKKgMRzL63yd1uepPBO4U0RVtJ66yru34gJccY8C91xZFNqUh1ApnYq1UM33T8XrlVi%2F%2B1FcjwIV%2FBHXjf9g6TJyrkvxi8CFj5gRIJGU1vcThnNLTSbGBTIAQvzlKEArf4t3F%2FhkJmLifnKwKT99vp5TkwFWVXaNrPWBVeJkvLkuBtxApAkuvbkZxcCQ2k%2FNYnyeajJrO8pmxdSWDswVMjXyylpaJX5BdegYbuXJG91xpupwXF2ZfxjFztzQGhAUUZnGTvQs%2FRcjooEC6qAb5nQgk1U7sOwacTLocmW6ccpTZNFwopmKGzp7E54Nklfcq3Z0SMe3JmH9mkUmS1%2B0aXrwIB85lRKrTOEThjO%2BVmfgpp0M8neZBZ10r9KVC9H6eW9eWvGtcVe7qiWDEQbHmBP5efZ4RZcCvUZ2JVKmyU68ec9EsW8Cbhx28uLk";

        $xml = simplexml_load_string( AESdesencriptar( $respuesta, $AES[ "key128" ] ) );

        dd($xml);
    }


    public function GetnetRedirect(){
        $respuesta = $this->request->getGet();

        $this->data["respuesta"] = $respuesta;
        $this->data[ "navbar" ]  = true;

        echo template( "gateway/getnet", $this->data );

    }

}

/*

PAGO EXITOSO
[operacion] => 100000551635
[nbResponse] => Aprobado
[referencia] => MIFACTURA001

RECHAZADO POR EMISOR
[nbResponse] => Rechazado
[nb_error] => La transaccion ya fue aprobada el 29/08/24 9:51:55
[referencia] => MIFACTURA001

RECHAZADO POR REGLA
[nbResponse] => Rechazado
[nb_error] => La tarjeta no pudo ser autenticada en 3DS.
[referencia] => MIFACTURA001

RECHAZADO PRO BANCO EMISOR
[nbResponse] => Rechazado
[nb_error] => FONDOS INSUFICIENTES
[referencia] => MIFACTURA001

*/
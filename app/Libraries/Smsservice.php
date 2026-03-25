<?php

namespace App\Libraries;

class Smsservice
{
    private $endpoint = "https://core.newww.mx/api/operadores/sms/envio/";
    private $token = "85bc-44e62007dd4c";

    public function enviar($mensaje, $numeros, $referencia = null, $mascara = "")
    {
        $client = \Config\Services::curlrequest();

        $response = $client->post($this->endpoint, [
            'headers' => [
                'Authorization' => 'Bearer ' . $this->token,
                'accept' => '*/*'
            ],
            'form_params' => [
                'mensaje' => $mensaje,
                'numeros_telefono' => json_encode($numeros),
                'clave_mascara' => $mascara,
                'referencia_operacion' => $referencia
            ]
        ]);

        return json_decode($response->getBody(), true);
    }
}
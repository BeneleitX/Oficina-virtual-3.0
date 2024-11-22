<?php
namespace App\Controllers;

use App\Entities\Usuario;

class Gateway extends BaseController
{
    public function GetnetRedirect(){
        $respuesta = $this->request->getGet();

        $this->data["respuesta"] = $respuesta;
        $this->data[ "navbar" ]  = false;

        echo template( "gateway/getnet", $this->data );

    }

    public function ConektaRedirect(){
        $respuesta = $this->request->getGet();

        $this->data["respuesta"] = $respuesta;
        $this->data["ok"] = true;
        $this->data[ "navbar" ]  = false;
        $this->data[ "referencia" ]  = "0000";

        echo template( "gateway/conekta", $this->data );

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
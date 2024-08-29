<?php
namespace App\Controllers;

use App\Entities\Usuario;

class Gateway extends BaseController
{


    public function GetnetGatewayResponse(){
        $respuesta = $this->request->getPost( "strResponse" );
        $xml = simplexml_load_string( AESdesencriptar( $respuesta, $AES[ "key128" ] ) )->nb_url;

        echo $xml;
    }


    public function GetnetRedirect(){
        echo "OK";
        return;
    }

}

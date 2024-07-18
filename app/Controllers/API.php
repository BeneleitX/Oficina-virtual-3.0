<?php

namespace App\Controllers;

use CodeIgniter\API\ResponseTrait;

class Bitacora extends BaseController
{
    use ResponseTrait;

    public function __construct (){
        $api_key = $this->config->item('rest_api_key');

        if(!$_POST){
            return $this->fail( $errors, 400 );
        }
        else{
            if( $api_key !== $this->request->getPost( "API_key" ) ){
                return $this->failUnauthorized( "Token no válido" );
            }           
        }
    }

    public function validate_login(){
        $usuario_id = intval( $this->config->item('socio_id') );
        $password   = intval( $this->config->item('password') );

        $u = model()->find( $usuario_id );

        if( !$u ){
            $this->fail($errors, 400);
        }
        
        $data = [
            "socio": ""
        ];

        $this->respond($data, 200);
    }
}

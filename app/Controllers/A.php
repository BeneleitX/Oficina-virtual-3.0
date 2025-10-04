<?php namespace App\Controllers;


class A extends BaseController
{

    public function Landing( $u ){

        $request = base64_decode( urldecode( $u ) );
        $socio = model( "UsuarioModel" )->where( "password = '{$request}'" )->first();
        echo "<p>Landing de ".$socio->nombre(2)."</p>";
    }
}

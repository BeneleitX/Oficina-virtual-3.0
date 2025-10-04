<?php namespace App\Controllers;


class A extends BaseController
{
    public function Landing( $u ){


        $request = base64_decode( urldecode( $u ) );
        $socio = model( "UsuarioModel" )->where( "password = '{$request}'" )->first();
        echo "<p>TEST Landing page (".$socio->nombre(2).")</p><ul><li>Link para registro directo</li><li>Link para tienda en línea directa</li></ul>";
    }
}

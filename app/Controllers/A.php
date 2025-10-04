<?php namespace App\Controllers;


class A extends BaseController
{
    public function Landing( $u ){


        $request = base64_decode( urldecode( $u ) );
        $socio = model( "UsuarioModel" )->where( "password = '{$request}'" )->first();
        echo "<p>Landing page (".$socio->nombre(2).")</p><ul><li>Registro directo</li><li>Tienda en línea</li></ul>";
    }
}

<?php namespace App\Controllers;


class A extends BaseController
{
/**
 * Landing page, para pruebas de registro directo y tienda en línea directa
 *
 * @param string $u base64 encoded password
 * @return void
 */
    public function Landing( $u, $numero = null ){
        if( $numero != null ){
            $socio = model( "UsuarioModel" )->find( $numero );
        }
        else{
            $request = base64_decode( urldecode( $u ) );
            $socio = model( "UsuarioModel" )->where( "password = '{$request}'" )->first();
        }

        
        echo "<p>TEST Landing page (".$socio->nombre(2).")</p><ul><li>Link para registro directo</li><li>Link para tienda en línea directa</li></ul>";
    }
}

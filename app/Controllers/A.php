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

        // return redirect()->to( "inicio" );

        if( $numero != null ){
            $socio = model( "UsuarioModel" )->find( $numero );
        }
        else{
            $request = base64_decode( urldecode( $u ) );
            $socio = model( "UsuarioModel" )->where( "password = '{$request}'" )->first();
        }

        $this->data[ "socio" ] = $socio;
        echo template( "a/landing", $this->data );

    }
}

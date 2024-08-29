<?php
namespace App\Controllers;

use App\Entities\Usuario;

class Gateway extends BaseController
{

    public function GetnetGatewayResponse(){
        helper( "getnet_helper" );

        // Elegir ambiente
        $getnet = VARIABLES[ "getnet" ][ "valor" ];
        $AES = $getnet[ "ambientes" ][ "sandbox" ];

        $respuesta = $this->request->getPost( "strResponse" );
        
        /* test exito     
        [reference] => MIFACTURA001
        [response] => approved
        [foliocpagos] => 100000000007
        [time] => 9:51:55
        [date] => 29/08/24
        [amount] => 2500.00
        [cc_mask] => 545454XXXXXX1234
        */
        $respuesta = "DwANrZGeJ2SYkemnSKEr5L%2BEUAADf3ZRApy%2B8rA9%2FeV7snCbbCprpMpT84vdB3lDMKBfDzg6mBe4mD6xyG1h%2BHFCuUdTXntkqzNgEUISF2WuiWdjD9byoBcOp783oJ5c2E%2FA8hGUbeDrBLbypghf73ShzM8IaXi8FfTrc0uGNfIOeZ7iVnJCoAqCZpTjP%2FhyhdwBTCyH7IOwRmvmi174GJJDhRGA%2F%2Bhrx01mhqJb%2Bcr9EtJcqZugy7IZ9dPfLTG5XlDu4xaKPg3%2B3PFPInMbVAIHAwpmEUoLMR%2BB4IBDifrEIfarzT8v2i5zMH%2BgDBmvimD7AAc06eZFsWsLxNmPDk3iGEUCIZ9pUgLwbgVRbfknAdLaw86IeTOjMCjKL%2F4RP4RsF%2FHN%2BV5Q4wXMSid5Kc0h3L%2Fta%2FXFesHcgnZh9yP8t87roShe4UiOJNK8wrRbuMWtI7zf0ltwAACK7T3EAGdsJV7vkByHualMnLfcO%2BjAjSq2vPyfj0y%2B4f%2BQBE8HXvs%2BFlq7dxfNwX0LzhbwZ1bPzoUm%2FeSF4D%2FM5j%2B6fnyZRE0wRn%2BdOWP4jxq06LJSWddzWw6gx5pT%2F3rYTLF%2BLAYSmKsUxdQY39AAkixigQw3NCVqhZG7jWHfF%2Fhj%2FE4BdePWCrssYIAMNZP87kl5SvWlLw0087MR1DyDNIZLhXNZvcRAhtDtsCvuNDgMRcbHqCqMfwJkQ63XNgWV9l55rsSe9vkOezqMwyGrAZcqUqTDnfMTVUCw62SC6KlZy1wGIlfsD9JuZQmFTNByouhxX7nTp3tJhWuKdRlkUChg9SJ2ghLBPnVrtbjx6raE%2BI9oz0KilwHSrWSoyPlXiWR%2FgryxHzvJgpGqn6DWXEBp5NkYqWz5x5TryPnv3IXHOtjG2iHI4EHVit5iTK4Yp%2BZhkP5mmulrLdqPnhNaOUV4iSF1k6QgZm9ExhdCq9PwhGlMz9HH12xRDKpGm6T62MsT0uSx02Y182IbnlPLdlZujAXUb1RQyb%2BIWHcu52hWRcSIcOK7hAXMBkK%2F7KUGiInlXD3WIDAyExo7lFhQcru3dDOZj5WQHXRgVnQIotG2Wn%2BjHW8nrvdhBVU%2BjjjqI3T4sfsob7SK6PgaN%2BZORtGH%2FxcdAGLXv2Ty9sfSfqLwx%2Frk";

        /* test denegado emisor
        [reference] => MIFACTURA001
        [response] => denied
        [foliocpagos] => 100000000008
        */
        $respuestax = "%2F7TFRPZrOy4GTxK9Vd0ACypKPRMGz1yA5SDM40ASXZS5mKccm78AaHg9XIqDPhEeYjtZ%2BdmzDJzbF%2F0YDg2av6Ac5Rz5%2Bh4rEcIws2pa9hz7Fza3GW7gm7XAlaUd77pt48ot2NmvgbjkkzmdwUVy4FX%2FbwGjX478MsV%2F6yrAb1D0Mo95IyqxstHphhprG5KOgcrZPfayYUUQvRmgbqLWscjgaqxzWZjSFHRwlIVgqMSYU5cYGyVp8eIrm%2BZ13pD%2BDnHdT%2Bcf4O%2F%2BctxPrcVJh%2BECuyk1gVgRwALQKQQ%2BRQZY0cAJhTJGk1KT3ys2q577NTY0CSVjl36%2FJ8o3JL5CGfH%2FRViUe2tOfIDXEwIeh4EwEgw5tjdj8rAi1fJLAlPrzeOfqLUOA9axC981g2Cn8AtcMKornLNa0CwOBH5Z2ETnRxXVrm2B7gY2wr6ROlfI8HFNgT0xXkr9jU%2FmVBLVr94MmB2WWGy2NpCp5Zf3BCS35SuvehP4xHYyRET6vEWzBIzUh%2FAENtPahAEVSPUR4Bjvpe8p%2FBtp7VcbYIe4YbwLzY%2BCuC7bEBqJ6YEYw5%2B1E9UWnwkSYBdNgVIHEJwvhg%3D%3D";

        /* test denegado 3ds 
        [reference] => MIFACTURA001
        [response] => denied
        [nb_error] => La tarjeta no pudo ser autenticada en 3DS.
        */
        $respuestax = "V4SvGwYsemaq4sA2TjWl5S8gQgGEK10LufEvHCc71RlQhvgoPZUVM8BbyUqOefN4%2FmDxHWynujOlsdRScGPr7Q%2BfZ7kr17iz8z5Gad6jApBSWVnjUvcxW4lNzjAb8DxtsWL%2FzZ4m%2FcYnkWTlAV2J1Wwa52RwVLTifkPd0rcNIOar%2BzoTE8Z%2B0tyN4Nqm0aCGrN8MPw9pxgzwaFleL%2BWTFqf5kZ0YUmRgfVSwCpQZUXYgAvzjquDclbg7rt65gZMg92WGCbJJUNnavAe1mdlfD9YyU2wI4zYAVTqmPUykSql%2B9LT0u02l4aFykdn2124Orib1zFVjAco9sl%2Fgw1CZHx8cPepC0xZcM%2BrkjfsBQOT%2B3oBNg9brNHTqcIN5kmc%2FeH8ryRxrUgfLimVj56fuFqUYJP3xYlF13MoD2lROnheSlTHksQFSYoNCk0Ei4wQVE0m%2FtFBbEVC%2BAlrtDC7atoZY7xmvUaZqs4RxSDha0j50oqOFwFMsLhbIi%2BdPxQF%2BrH3n3I9yltkjloUW3IALjpgZgzSDv8gojIBpcp0hgx8%2BXAO7CosL0sA33RdkxaJTqJYB46OUy%2FIKmJszUKYWKw%3D%3D";

        /* test error
        [reference] => MIFACTURA001
        [response] => error
        */
        $respuestax = "tl0rSbtPArdDIVVeFxeSAUIKK9mvFtYaH1AgWTRjwFR3cakeopJFWZg55YVKweM5ZSgAqAHPVfZhJlBvPl22CuHqwttxh7xxZsjcCMhkbkib12d5nn6VgpMgCdfvLNedPGsINVHnL1tVtQmSy4XBrNjd7InEBYrA7xPL83ep8nF5mWJGSletXOHXwmdXWCdvbFanaOWmDl1T8ctQZJKtrIVLjoAXBEx8daPx4VaFZkoT1BEdbaVsbxS2ZEvFu8PPR1QVuviuNpBL5%2FfeTwCmtZxdjUGH4Fa1J4MogPHHsQdcnFme7PaFeNwmrOfAKqkbtlUyVdnIDgfnjVaNAE40R2%2BxV%2B7Hp%2Fy0S6eYIdaK0Tym6MgYN5luZCQlMWw%2BhY8FQ%2FYSAUteKQU3XdRt3AGkjOJiBcFXE67X%2Bjh3VE%2FW5lY%2BaXi2OeWYDLGVL21kyMw3SRC%2B5A%2BSX6y6FA0SuGJfWgmsSDS6Ykyz%2BTqbTiRJt%2BlHOh5n0iLsGWgwfS%2Fd1Yiw";


        $respuesta = xml2array( simplexml_load_string( AESdesencriptar( rawurldecode( $respuesta ), $AES[ "key128" ] ) ) );

        if( $respuesta["response" ] == "approved" ){

            // test
            $respuesta[ "reference" ] = "10828796";
            $d = array_reverse(explode( "/", $respuesta[ "date" ] ) );
            $d[0] += 2000;
            $respuesta[ "date" ] = date( "Y-m-d", strtotime( implode( "/", $d ) ) );
    
            $p = model( "PedidoModel" )->find( substr( $respuesta[ "reference" ], 0, -1 ) );

            $p[ "estatus_codigo" ] = "420-PAGADO";
            $p[ "metodopago_codigo" ] = "21-GETNET";
            $p[ "fechas" ][ "pagado" ]   = $respuesta[ "date" ];     
            $p[ "fechas" ][ "califica" ] = $respuesta[ "date" ]; 
            
            model( "PedidoModel" )->save( $p );

            $u = model( "UsuarioModel" )->find( $p[ "usuario_id" ] );

            $data = $u->data;                                    
            $historial = $u->historial; 

            foreach( $p[ "PTS" ] as $promo => $pts ){
                if( !is_object( $historial->modelos->{$p[ "modelo_codigo" ]}->primercompra ) ){
                    $historial->modelos->{$p[ "modelo_codigo" ]}->primercompra = json_decode( '{}' );
                }

                if( !isset( $historial->modelos->{$p[ "modelo_codigo" ]}->primercompra->{$promo} ) ){
                    $historial->modelos->{$p[ "modelo_codigo" ]}->primercompra->{$promo} = substr( $p[ "fechas" ][ "califica" ], 0, 10 );
                }
            } 

            $historial->modelos->{$p[ "modelo_codigo" ]}->ultimacompra = $p[ "fechas" ][ "califica" ];

            $u->data = $data;
            $u->historial = $historial;

            model( "UsuarioModel" )->save( $u );  

            $db = db_connect();
            $db->query( "select f_update_PTS( {$u->id}, '{$p[ "modelo_codigo" ]}', '".date( "Ym", strtotime( $respuesta[ "date" ] ) )."' )" );  
            $db->query( "select f_get_estatus( {$u->id}, 0 )" );
            $db->query( "select f_reparte_comisiones( {$p[ "id" ]}, 0 )" );    

            model( "FondeoModel" )->ignore( true )->save( [
                "operacion" => $respuesta[ "foliocpagos" ],
                "fecha" => $respuesta[ "date" ]." ".$respuesta[ "time" ],
                "estatus_codigo" => "620-RECIBIDO",
                "metodopago_codigo" => "21-GETNET",
                "usuario_id" => $u->id,
                "referencia" => $p[ "referencia" ],
                "cantidad" => $respuesta[ "amount" ],
                "extras" => $respuesta
            ] );
        }

        // BITACORA Pago rechazado
        bitacora( $respuesta["response" ] == "approved" ? 58 : 59, $this->data[ "usuario" ]->id, [ 
            "referencia"  => $respuesta[ "reference" ],
            "estatus"     => $respuesta["response" ],
            "metodopago"  => "21-GETNET"
        ] );                
        
        echo "BENELEIT OK";
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
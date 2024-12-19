<?php 
require '../vendor/autoload.php';

use Conekta\Api\OrdersApi;
use Conekta\Model\OrderRequest;
use Conekta\Configuration;
use Conekta\ApiException;

$ambiente = VARIABLES[ "conekta" ][ "valor" ];
$conekta  = $ambiente[ "ambientes" ][ $ambiente[ "ambiente" ] ];
$client   = new \GuzzleHttp\Client();

$subtotal = $pedido[ "data" ][ "total" ] + $pedido[ "data" ][ "comisionentrega" ] - $usuario->saldo( $pedido[ "modelo_codigo" ] );
$comisionbanco = ceil( $subtotal * 2 / 100 );
$total    = $subtotal + $comisionbanco;

$json     = [
    "body" => json_encode( [
        "customer_info" => [
            "name"  => limpia_acentos( $socio->nombre( 2, false, true ) ),
            "email" => trim( $socio->correo ),
            "phone" => $socio->telefono
        ],
        "line_items" => [
            [
                "unit_price" => $total * 100,
                "name"       => $pedido[ "referencia" ],
                "quantity"   => 1
            ]
        ],
        "metadata" => [
            "referencia" => $pedido[ "referencia" ]
        ],        
        "checkout" => [
            "type" => "Integration",
            "allowed_payment_methods" => [ "card", "cash" ],
            "expires_at"       => ( time() + ( 60 * 60 * 24 * 15 ) ) , 
            "redirection_time" => 0,
            "success_url"      => "https://app.beneleit.mx/ConektaRedirect"
        ],
        "currency" => "MXN"
    ]),
    "headers" => [
        "accept"        => "application/vnd.conekta-v2.1.0+json",
        "authorization" => "Bearer ".$conekta[ "private_key" ],
        "content-type"  => "application/json",
    ],
]; 

$response = $client->request('POST', 'https://api.conekta.io/orders', $json );
$stream   = json_decode( $response->getBody() );

$pedido[ "data" ][ "conekta" ][ "order" ] = $stream->id;
$pedido[ "data" ][ "conekta" ][ "checkout" ] = $stream->checkout->id;

model( "PedidoModel" )->save( $pedido );

?>

<p class="text-center">
	<a href="<?php echo base_url( "pedido/{$pedido[ "referencia" ]}" ); ?>" class="btn btn-danger"><i class="fa fa-undo"></i> Regresar al pedido</a>
</p>

<script crossorigin src="https://pay.conekta.com/v1.0/js/conekta-checkout.min.js"></script>

<div id="conekta_component" style="height: 714px"></div>
<script>
    const options = {
        backgroundMode: 'lightMode', //lightMode o darkMode
        colorPrimary:   '#009779', //botones y bordes
        colorText: 	    '#1A2542', // títulos
        colorLabel: 	'#1A2542', // input labels
        inputType: 		'flatMode', // minimalMode o flatMode
    };
    const config = {
        locale: 	  	   'es',
        targetIFrame: 	   '#conekta_component',
        publicKey:    	   '<?php echo $conekta[ "public_key" ]; ?>',
        checkoutRequestId: '<?php echo $stream->checkout->id; ?>',
    };

    const callbacks = {
        onGetInfoSuccess:  function( lTime ){ console.log('loading time en milisegundos', lTime.initLoadTime ); },
        onFinalizePayment: function( order ){ console.log('success: ', JSON.stringify(order)); },
        onErrorPayment:    function( error ){ console.log('error en pago: ', error); },
    };

    window.ConektaCheckoutComponents.Integration({config, callbacks, options });
</script>

<?php 
require '../vendor/autoload.php';

use Conekta\Api\OrdersApi;
use Conekta\Model\OrderRequest;
use Conekta\Configuration;
use Conekta\ApiException;

$conekta  = VARIABLES[ "conekta" ][ "valor" ][ "ambientes" ][ "sandbox" ];
$client   = new \GuzzleHttp\Client();


$response = $client->request('POST', 'https://api.conekta.io/orders', [
    "body" => json_encode( [
        "customer_info" => [
            "name"  => $socio->nombre( 2 ),
            "email" => $socio->correo,
            "phone" => $socio->telefono
        ],
        "line_items" => [
            [
                "unit_price" => 23000,
                "name"       => $pedido[ "referencia" ],
                "quantity"   => 1
            ]
        ],
        "checkout" => [
            "type" => "Integration",
            "allowed_payment_methods" => [ "card" ],
            "expires_at"       => ( time() + ( 60 * 60 * 24 * 15 ) ) , 
            "redirection_time" => 5,
            "success_url"      => "https://app.beneleit.mx/ConektaRedirect"
        ],
        "currency" => "MXN"
    ]),
    "headers" => [
        "accept"        => "application/vnd.conekta-v2.1.0+json",
        "authorization" => "Bearer ".$conekta[ "private_key" ],
        "content-type"  => "application/json",
    ],
]);

$stream = json_decode( $response->getBody() );

$pedido[ "data" ][ "conekta" ][ "id" ] = $stream->id;

dd($pedido, $socio, $stream );
?>

<p class="text-center">
	<a href="<?php echo base_url( "pedido/{$pedido[ "referencia" ]}" ); ?>" class="btn btn-danger"><i class="fa fa-undo"></i> Regresar al pedido</a>
</p>

<script crossorigin src="https://pay.conekta.com/v1.0/js/conekta-checkout.min.js"></script>

<div id="example" style="height: 714px"></div>
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
        targetIFrame: 	   '#example',
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

<?php 

require '../vendor/autoload.php';

use Conekta\Api\OrdersApi;
use Conekta\Configuration;
use Conekta\Model\OrderRequest;
use Conekta\Api\CustomersApi;
use Conekta\ApiException;
use Conekta\Model\Customer;
use Conekta\Model\CustomerFiscalEntitiesRequest;
use Conekta\Model\CustomerUpdateFiscalEntitiesRequest;
use Conekta\Model\UpdateCustomer;
use Conekta\ObjectSerializer;


$conekta  = VARIABLES[ "conekta" ][ "valor" ][ "ambientes" ][ "sandbox" ];
$config   = Configuration::getDefaultConfiguration()->setAccessToken( $conekta[ "api_key" ] );
$Order    = new OrdersApi(null, $config);
$Customer = new CustomersApi(null, $config);

$validCustomer = [
    'name' => "Payment Link Name",
    'email' => "juan.perez@dominio.com"
];

// $c = Customer::create($validCustomer);


// Configure Bearer authorization: bearerAuth
$config = Conekta\Configuration::getDefaultConfiguration()->setAccessToken( $conekta[ "api_key" ] );

$apiInstance = new Conekta\Api\OrdersApi(
    // If you want use custom http client, pass your client which implements `GuzzleHttp\ClientInterface`.
    // This is optional, `GuzzleHttp\Client` will be used as default.
    new GuzzleHttp\Client(),
    $config
);
$order_request = new \Conekta\Model\OrderRequest([
    'line_items' => [
        [
            'name' => 'Nombre del Producto o Servicio',
            'unit_price' => 23000,
            'quantity' => 1
        ]
    ],
    'currency' => 'MXN',
    'customer_info' => [
       'name' => 'Jorge Martinez',
        'email' => 'jorge.martinez@conekta.com',
        'phone' => '+5218181818181'
    ],
    'metadata' => [
        'datos_extra' => '12334'
    ],
    'charges' => [
        [
            'payment_method' => [
                'type' => 'oxxo_cash',
                "expires_at" => time() + 10000
            ]
        ]
    ]
]); // \Conekta\Model\OrderRequest | requested field for order

try {
    $result = $apiInstance->createOrder($order_request);
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling OrdersApi->createOrder: ', $e->getMessage(), PHP_EOL;
}

?>

<p class="text-center"><a href="<?php echo base_url( "pedido/{$pedido[ "referencia" ]}" ); ?>" class="btn btn-danger"><i class="fa fa-undo"></i> Regresar al pedido</a></p>


  
          
<script>



$(document).ready(function()
{ 
});




</script>
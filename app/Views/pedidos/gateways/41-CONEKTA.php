<?php

/* require '../vendor/autoload.php';
use Conekta\Api\OrdersApi;
use Conekta\Configuration;
use Conekta\Model\OrderRequest;


// Configure Bearer authorization: bearerAuth
$config = Configuration::getDefaultConfiguration()->setAccessToken(getenv("CONEKTA_API_KEY"));
$apiInstance = new OrdersApi(null, $config);

$rq = new OrderRequest([
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
                'type' => 'card',
                'number' => '4242424242424242',
                'name' => 'Jorge Martinez',
                'exp_month' => '12',
                'exp_year' => '2025',
                'cvc' => '198'
            ]
        ]
    ]
]);

try {
    $result = $apiInstance->createOrder($rq);
    $json_string = json_encode($result, JSON_PRETTY_PRINT);
    print_r($json_string);
} catch (Exception $e) {
    dd($e);
} */



// privada
// key_tEF8xT2JfZnrzr0HfQrWjbX

// publica
// key_FViIiNDBgN4w1xKQMaI3Q3Z

// 4242424242424242	tok_test_visa_4242
// 4915669353237603	tok_test_banorte_debit

// william.rivero@conekta.com


$url = '{POST_REST_ENDPOINT}';
$curl = curl_init();
$fields = array(
    'field_name_1' => 'Value 1',
    'field_name_2' => 'Value 2',
    'field_name_3' => 'Value 3'
);
$fields_string = http_build_query($fields);
curl_setopt($curl, CURLOPT_URL, $url);
curl_setopt($curl, CURLOPT_POST, TRUE);
curl_setopt($curl, CURLOPT_POSTFIELDS, $fields_string);
$data = curl_exec($curl);
curl_close($curl);

?>
<script
      crossorigin
      src="https://pay.conekta.com/v1.0/js/conekta-checkout.min.js"
    ></script>

<div id="example" style="height: 714px"></div>
<script type="text/javascript">
  const options = {
    backgroundMode: 'lightMode', //lightMode o darkMode
    colorPrimary: '#081133', //botones y bordes
    colorText: '#585987', // títulos
    colorLabel: '#585987', // input labels
    inputType: 'minimalMode', // minimalMode o flatMode
  };
  const config = {
    locale: 'es',
    publicKey: '<?php echo getenv( "CONEKTA_API_PUBLIC_KEY" ); ?>',
    targetIFrame: '#example',
    checkoutRequestId: '123456',
  };

  const callbacks = {
    // Evento que notifica cuando finalizó la carga del component/tokenizer
    onGetInfoSuccess: function (loadingTime) {
      console.log('loading time en milisegundos', loadingTime.initLoadTime);
    },
    // Evento que notifica cuando finalizó el pago correctamente
    onFinalizePayment: function (order) {
      console.log('success: ', JSON.stringify(order));
    },
    // Evento que notifica cuando finalizó la carga del component/tokenizer
    onErrorPayment: function (error) {
      console.log('error en pago: ', error);
    },
  };
  window.ConektaCheckoutComponents.Integration({
    config,
    callbacks,
    options
  });
</script>
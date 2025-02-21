<?php 

require '../vendor/autoload.php';

$client = new \CoinGate\Client('FtGVgmPj3QyJeofNhFczo3wwebAkZ4rigHrEhMA2', true);
$client->setEnvironment('sandbox');
$socio = model( "UsuarioModel" )->find( $pedido[ "usuario_id" ] );
$producto = model( "ProductoModel" )->find( array_keys( $pedido[ "promociones"][ "510-SEMILLA" ][ "productos" ] ) )[0];

/* $params = [
    'order_id'          => $pedido[ "referencia" ],
    'purchaser_email'   => $socio->correo,
    'price_amount'      => $pedido[ "data" ][ "total" ],
    'price_currency'    => 'USD',
    'receive_currency'  => 'USDT',
//    'callback_url'      => "https://scabbia.requestcatcher.com/", 
    'callback_url'      => "https://api.beneleit.mx/CoingateCallback",
    'cancel_url'        => base_url()."CoingateFinish",
    'success_url'       => base_url()."CoingateFinish",
    'title'             => "Capital24 ".$pedido[ "referencia" ],
    'description'       => $pedido[ "promociones"][ "510-SEMILLA" ][ "productos" ][ $producto[ 0 ] ][ "nombre"],
    "shopper"           => [
        "type"          => "personal",
        "email"         => $socio->correo,
        "country"       => "MX",
        "language"      => "es",
        "phone"         => $socio->telefono,
        "first_name"    => $socio->data->nombre,
        "last_name"     => implode( " ", $socio->data->apellidos ),
        "date_of_birth" => $socio->fechanac,
        "residence_country" => "MX",
    ],  
];
 */


 require '../vendor/autoload.php';
// usuario 8ac3384cea36a23b16c24fd150ecd07d0277adde
// hLuaAQQxMJJZ9SKi5sxlbO2GV7qNAcuPxZ6z7Zbr0aD3u1aoa9j1yoP3E1vqGqDnilNOsxU31HJfKZDK92Ilx03y8NtALUF9UIhOyBcedNjtKju3YZ84aG21bi8PRlg6
 const PAYOUT_KEY = 'hLuaAQQxMJJZ9SKi5sxlbO2GV7qNAcuPxZ6z7Zbr0aD3u1aoa9j1yoP3E1vqGqDnilNOsxU31HJfKZDK92Ilx03y8NtALUF9UIhOyBcedNjtKju3YZ84aG21bi8PRlg6';
 const MERCHANT_UUID = 'ce9af27a-f2a3-4138-9ebc-d90f1deef5df';
 $payout = \Cryptomus\Api\Client::payout(PAYOUT_KEY, MERCHANT_UUID);
 
  $data = [
    'amount' => '15',
    'currency' => 'USD',
    'network' => 'TRON',
    'order_id' => '555321',
    'address' => 'TXguLRFtrAFrEDA17WuPfrxB84jVzJcNNV',
    'is_subtract' => '1',
    'url_callback' => 'https://example.com/callback'
];

$result = $payout->create($data);

 return;

$client = new \GuzzleHttp\Client();

$response = $client->request('GET', 'https://api.tatum.io/v3/tron/transaction/d6634372a905ae96435533b6b8ae123c295586f1f2a34b0bfce76232fe7d393e', [
  'headers' => [
    "accept"        => "application/vnd.conekta-v2.1.0+json",
    "content-type"  => "application/json",
    'x-api-key' => 't-67ac41a55507b6d1c074de74-cda8601f052742e186eeb588',
  ],
]);

echo "<pre>".print_r( json_decode( $response->getBody() ), 1 )." </pre>";
return;
?>

<p class="text-center mb-4">
	<a href="<?php echo base_url( "pedido/{$pedido[ "referencia" ]}" ); ?>" class="btn btn-danger"><i class="fa fa-undo"></i> Regresar al pedido</a>
</p>

<div class="row">
    <div class="col-lg-6 offset-lg-3">


        <div class="card mt-4">
            <div id="loader" class="card-body text-center">
                <img src="<?php echo base_url(); ?>assets/img/loader.gif" class="img-fluid px-5">
            </div>

            <div id="finaliza" class="card-body text-center my-5" style="display: none;">
                <p style="font-size:100px"><i class="fa fa-circle-check text-teal"></i></p>
                <h5>TxHash correcto<br>Se ha registrado la inversión</h5>
            </div>

            <div id="principal" style="display: none;">
                <div class="card-header"><h5 class="text-teal m-0 py-2">Registro de capital semilla</h5></div>

                <div class="card-body text-start">
                    <table class="w-100 mb-4"><tr>
                        <td class="w-25"><img src="<?php echo base_url()."assets/img/productos/{$producto->codigo}.png"; ?>" class="img-fluid"></td>
                        <td class="w-75">
                            <h1 class="m-0"><?php echo $producto->data->nombre; ?></h1>
                            <p><?php echo $producto->data->descripcion; ?></p>
                            <div class="row">
                                <div class="col-4"><input readonly class="form-control text-end" value="<?php echo $pedido[ "data" ][ "total" ]; ?>"></div>
                                <div class="col-6 pt-2">USD</div>
                            </div>
                        </td>
                    </tr></table>

                    <div class="alert alert-warning">
                    El TxHash o hash de transacción es el identificador que genera la transacción de tu inversión. También se le conoce como ID de transacción (TxID). 
                    </div>

                    <div class="text-center my-4">
                        <h5>Pega aquí tu TxHash</h5>
                        <input type="text" class="form-control text-center" name="_txhash">
                        <p id="error" class="text-red" style="display:none"></p>
                    </div>

                    <p class="text-center"><button onclick="check_hash()" type="button" class="btn btn-secondary"><i class="fa fa-check"></i> Registrar inversión</button></p>
                </div>    
            </div>
        </div>
    </div>
</div>

<script>
    function check_hash(){
        var pedido = <?php echo $pedido[ "id" ]; ?>,
            hash   = $( '[name=_txhash]' ).val();

        $( '#loader' ).slideDown();
        $( '#principal' ).slideUp();

        $.ajax({
            url: base_url + "txhash", 
            type: "POST",
            dataType: "json",
            contentType: 'application/x-www-form-urlencoded; charset=UTF-8',
            data: { [csrf_token] : csrf_hash, pedido : pedido, hash : hash },
            success: function( result ){
                setTimeout(function() {
                    if( result.error ){
                        $( '#error' ).text( result.error ).show();
                        $( '#loader' ).slideUp();
                        $( '#principal' ).slideDown();
                    }
                    else if( result.success ){
                        $( '#loader' ).slideUp();
                        $( '#finaliza' ).slideDown();
                    }
                }, 1000);
            }
        });        
    }


    setTimeout(function() {
        $( '#loader' ).slideUp();
        $( '#principal' ).slideDown();
    }, 2000);

    $( '[name=_txhash]' ).on( 'change', function(){
        $( '#error' ).text().hide();
    });
</script>


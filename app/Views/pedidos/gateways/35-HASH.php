<?php 

require '../vendor/autoload.php';

$client = new \CoinGate\Client('FtGVgmPj3QyJeofNhFczo3wwebAkZ4rigHrEhMA2', true);
$client->setEnvironment('sandbox');
$socio = model( "UsuarioModel" )->find( $pedido[ "usuario_id" ] );
$producto = array_keys( $pedido[ "promociones"][ "510-SEMILLA" ][ "productos" ] );

$params = [
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



?>

<p class="text-center">
	<a href="<?php echo base_url( "pedido/{$pedido[ "referencia" ]}" ); ?>" class="btn btn-danger"><i class="fa fa-undo"></i> Regresar al pedido</a>
</p>

<div class="row">
    <div class="col-lg-6 offset-lg-3">
        <div class="card mt-4">
            <div class="card-body text-center">
                <img src="<?php echo base_url(); ?>assets/img/loader.gif" class="img-fluid p-5">
            </div>
        </div>
    </div>
</div>

<script>

</script>


<?php 

/* -H "Accept: application/vnd.conekta-v2.1.0+json" \
-H "Content-type: application/json" \
-u key_YOUR_PRIVATE_API_KEY: \
-X POST -d '{
"line_items": [{ 
     "name": "Nombre del Producto o Servicio",
     "unit_price": 23000,
     "quantity": 1
   }],
   "currency": "MXN",
   "customer_info": {
     "name": "Jorge Martínez",
     "email": "jorge.martinez@conekta.com",
     "phone": "+5218181818181"
   },
    "metadata":{
     "datos_extra": "1234"
   },
   "checkout": {
     		"type": "Integration",
        "allowed_payment_methods": ["card", "bank_transfer, "cash"] //Habilita todos los metodos de pago
     }
}’https://api.conekta.io/orders */



?>

<p class="text-center"><a href="<?php echo base_url( "pedido/{$pedido[ "referencia" ]}" ); ?>" class="btn btn-danger"><i class="fa fa-undo"></i> Regresar al pedido</a></p>


<html>
  <head>
    <meta charset="utf-8" />
    <title>Checkout</title>
    <script
      crossorigin
      src="https://pay.conekta.com/v1.0/js/conekta-checkout.min.js"
    ></script>
    <!-- En este archivo esta la config del componente -->
  </head>
  <body>
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
        publicKey: 'key_FViIiNDBgN4w1xKQMaI3Q3Z',
        targetIFrame: '#example',
        checkoutRequestId: '{{checkoutRequestId}}',
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
  </body>
</html>


<script>
    $(document).ready(function()
    { 
    });
</script>

<?php
$socio = model( "UsuarioModel" )->find( $pedido[ "usuario_id" ] );
$producto = model( "ProductoModel" )->find( array_keys( $pedido[ "promociones"][ "510-SEMILLA" ][ "productos" ] ) )[0];

$saldo = $socio->saldo( $modelo );
$total = $pedido[ "data" ][ "total" ] - $saldo;

if( $total < 0 ){
    $total = 0;
}
?>

<p class="text-center mb-4">
	<a href="<?php echo base_url( "pedido/{$pedido[ "referencia" ]}" ); ?>" class="btn btn-danger"><i class="fa fa-undo"></i> Regresar al pedido</a>
</p>



        <div class="card mt-4">
            <div id="loader" class="card-body text-center">
                <img src="<?php echo base_url(); ?>assets/img/loader.gif" style="width:150px; height:150px; opacity:0.4" class="m-5">
            </div>

            <div id="finaliza" class="card-body text-center mb-5" style="display: none;">
                <p style="font-size:100px"><i class="fa fa-circle-check text-teal"></i></p>
                <h1>¡Felicidades!</h1>
                <h5>Se ha creado con éxito la inversión <span class="badge bg-<?php echo $producto->data->color; ?>"><?php echo $pedido[ "referencia" ]; ?></span></h5>

                <span class="badge bg-gray-300 text-marine" style="font-size:monospace" id="txhash"></span>
                <div class="row"><div class="col-lg-4 offset-lg-4">
                    <table class="small my-3 text-center w-100">
                        <tr><td nowrap class="text-center w-50">Fecha pago: <strong id="fecha_1"></strong>
                        <td nowrap class="text-center w-50">Fecha inicio: <strong id="fecha_2"></strong></tr>
                    </table>

                    <div class="card"><div class="card-body">
                        <table class="">
                            <tr>
                                <td class="w-25"><img src="<?php echo base_url()."assets/img/productos/{$producto->codigo}.png"; ?>" class="img-fluid"></td>
                                <td class="w-75 pt-3 text-start">
                                    <h5 class="text-<?php echo $producto->data->color; ?>"><?php echo $producto->data->descripcion; ?></h5>
                                    <h2><img src="https://static.tronscan.org/production/logo/usdtlogo.png" style="width:22px"> <span id="cantidad_final"></span></h2>
                                    
                                </td>
                            </tr>
                        </table>
                    </div></div>
                </div></div>

                <a href="<?php echo base_url( "capital" ); ?>" class="mt-3 btn btn-primary"><i class="fa fa-magnifying-glass"></i> Ver detalles</a>
            </div>

            <div id="principal" style="display: none;">
                <div class="card-header"><h5 class="text-teal m-0 py-2">Transferencia de capital semilla</h5></div>

                <div class="card-body text-start">
                    <div class="row">
                        <div class="col-lg-6">
                            <table class="w-100 mb-3"><tr>
                                <td><img src="<?php echo base_url(); ?>assets/img/wallet.png" style="width:150px;margin-right:10px"></td>
                                <td class="w-100">
                                    <div class="card" style="overflow:hidden">

                                        <table class="table m-0">
                                            <tr>
                                                <td>Socio</td>
                                                <td><?php echo $socio->id( "50-INVERSION" ); ?></td>
                                            </tr>
                                            <tr>
                                                <td>Cantidad a transferir</td>
                                                <td><strong>$<?php echo number_format( $total, 2 ); ?> USDT</strong></td>
                                            </tr>                                            
                                            <tr>
                                                <td>Red</td>
                                                <td><strong>TRON (TRC20) Network</strong></td>
                                            </tr>
                                        </table>

                                    </div>
                                </td>
                            </tr></table>


                            <p class="m-0">Destino (address)</p>


                            <h5 class="mb-3"><strong>TAr7YFFgxkRs2zEHGm34dcj8M4TqAv2eGP</strong> <button class="btn btn-light small" onclick="navigator.clipboard.writeText( 'TAr7YFFgxkRs2zEHGm34dcj8M4TqAv2eGP')"><i class="fa fa-copy"></i></button></h5>


                            <div class="alert alert-warning small">
                            <ul class="m-0">
                                <li>Una vez finalizada la transacción, deberás colocar el TxHash en el campo de la derecha</li>
                                <li>El TxHash o hash de transacción es el identificador que genera la transacción de tu inversión. También se le conoce como ID de transacción (TxID)</li>
                            
                            </div>

                        </div>
                        <div class="col-lg-6">
                            <table class="w-100 mb-4">
                                <tr>
                                    <td class="w-25"><img src="<?php echo base_url()."assets/img/productos/{$producto->codigo}.png"; ?>" class="img-fluid"></td>
                                    <td class="w-75 pt-3">
                                        <h1 class="m-0"><?php echo $producto->data->nombre; ?></h1>
                                        <p class="text-<?php echo $producto->data->color; ?>"><?php echo $producto->data->descripcion; ?></p>
                                        <h5 class="m-0"><span class="badge bg-<?php echo $saldo ? "mustard" : "gray-400"; ?>">Saldo a favor: $<?php echo number_format( $saldo, 2 ); ?></span></h5>
                                        <h5>Total a depositar: $<?php echo number_format( $total, 2 ); ?></h5>
                                        <p class="small text-marine">Cantidades en $USD <img src="https://static.tronscan.org/production/logo/usdtlogo.png" style="width:24px"></p>
                                    </td>
                                </tr>
                            </table>

                            <div class="text-center my-4">
                                <?php 
                                if( $saldo >= $pedido[ "data" ][ "total" ]  ){
                                ?>
                                    <input type="hidden" name="_txhash" value="saldo">
                                    <div class="alert alert-success">
                                        <h3>¡Felicidades!</h3>
                                        <p>Tu saldo a favor cubre la totalidad del costo de tu pedido. Haz click en el siguiente botón para finalizar tu pago y activar tu inversión</p>
                                    </div>
                                <?php
                                }
                                else{
                                ?>
                                <h3>Pega aquí tu TxHash:</h3>
                                <input type="text" class="form-control text-center border-3 border-teal" name="_txhash">
                                <pre id="error" class="mt-2 alert alert-danger" style="display:none"></pre>

                                <?php 
                                }
                                ?>
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
                        $( '#error' ).html( result.error ).show();
                        $( '#loader' ).slideUp();
                        $( '#principal' ).slideDown();
                    }
                    else if( result.success ){

                        $( '#cantidad_final' ).text( Moneda.format( result.success.cantidad ) );
                        $( '#fecha_1' ).text( result.success.fecha_1 );
                        $( '#fecha_2' ).text( result.success.fecha_2 );
                        $( '#txhash' ).text( result.success.extras.TxHash );
                        $( '#loader' ).slideUp();
                        $( '#finaliza' ).slideDown();
                      
                        ( function call_confetti() {
                            confetti({
                                spread: 360,
                                ticks: 50,
                                gravity: 0,
                                decay: 0.94,
                                startVelocity: 10,
                                particleCount: randomInRange(10, 300), 
                                origin: { x: randomInRange(0.2, 0.8), y: randomInRange(0,0.5)} 
                            });
                        
                            timeout = setTimeout(call_confetti, randomInRange(10, 500));
                        }() );                         
                    }
                }, 400);
            }
        });     
    }


    setTimeout(function() {
        $( '#loader' ).slideUp();
        $( '#principal' ).slideDown();
    }, 1000);

    $( '[name=_txhash]' ).on( 'change', function(){
        $( '#error' ).text( '' ).hide();
    });

/*     $( '#loader' ).slideUp();
    $( '#principal' ).slideUp();
    $( '#finaliza' ).slideDown(); */


</script>


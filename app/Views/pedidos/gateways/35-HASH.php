<?php
$socio = model( "UsuarioModel" )->find( $pedido[ "usuario_id" ] );
$producto = model( "ProductoModel" )->find( array_keys( $pedido[ "promociones"][ "510-SEMILLA" ][ "productos" ] ) )[0];

$saldo = $socio->saldo( $modelo );
$total = $pedido[ "data" ][ "total" ] - $saldo;
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
                <h5>TxHash correcto<br>Se ha registrado la inversión <span class="badge bg-marine"><?php echo $pedido[ "id" ]; ?></span></h5>
            </div>

            <div id="principal" style="display: none;">
                <div class="card-header"><h5 class="text-teal m-0 py-2">Registro de capital semilla</h5></div>

                <div class="card-body text-start">
                    <table class="w-100 mb-4"><tr>
                        <td class="w-25"><img src="<?php echo base_url()."assets/img/productos/{$producto->codigo}.png"; ?>" class="img-fluid"></td>
                        <td class="w-75 pt-3">
                            <h1 class="m-0"><?php echo $producto->data->nombre; ?></h1>
                            <p class="text-<?php echo $producto->data->color; ?>"><?php echo $producto->data->descripcion; ?></p>
                            <h5>Saldo a favor: $<?php echo number_format( $saldo, 2 ); ?><??>
                            <br>Total a depositar: $<?php echo number_format( $total, 2 ); ?></h5>
                            <p class="small text-marine">Cantidades en $USD <img src="https://static.tronscan.org/production/logo/usdtlogo.png" style="width:24px"></p>
                        </td>
                    </tr></table>

                    <div class="alert alert-warning">
                    El TxHash o hash de transacción es el identificador que genera la transacción de tu inversión. También se le conoce como ID de transacción (TxID). 
                    
                    </div>

                    <div class="text-center my-4">
                        <h5>Pega aquí tu TxHash</h5>
                        <input type="text" class="form-control text-center" name="_txhash">
                        <pre id="error" class="mt-2 alert alert-danger" style="display:none"></pre>
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
        $( '#error' ).text( '' ).hide();
    });
</script>


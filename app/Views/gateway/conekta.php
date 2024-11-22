<div class="row">
    <div class="offset-md-3 col-md-8 offset-lg-3 col-lg-6 text-center py-5">
        <h1 class="mt-1 mb-0">
            <?php
                echo $ok ? 
                "<p><i class=\"fa fa-circle-check text-teal\" style=\"font-size:300px\"></i></p>¡Listo!" : 
                "<p><i class=\"fa fa-circle-xmark text-red\" style=\"font-size:300px\"></i></p>Pago rechazado";
            ?>
        </h1>

        <div class="card my-5">
            <div class="card-header text-center">Mensaje del banco</div>
            <div class="card-body py-5"><?php echo $ok ? "Cobro realizado con éxito" : "Error" ?></div>
        </div>


        <p><button onclick="top.location.href = '<?php echo base_url( "pedido/".$referencia ); ?>';" class="btn btn-secondary"><i class="fa fa-undo"></i> Regresar al pedido</a></button>

    </div>
</div>
<?php

d( $respuesta );

?>
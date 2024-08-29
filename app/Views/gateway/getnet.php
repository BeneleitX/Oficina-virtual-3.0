<div class="row">
    <div class="offset-md-3 col-md-8 offset-lg-3 col-lg-6 text-center py-5">
        <h1 class="mt-1 mb-0">
            <?php
                echo $respuesta[ "nbResponse" ] == "Aprobado" ? 
                "<p><i class=\"fa fa-check text-teal\" style=\"font-size:300px\"></i></p>¡Listo!" : 
                "<p><i class=\"fa fa-xmark text-red\" style=\"font-size:300px\"></i></p>Pago rechazado";
            ?>
        </h1>

        <div class="card my-5">
            <div class="card-header text-center">Mensaje del banco</div>
            <div class="card-body py-5"><?php echo $respuesta[ "nbResponse" ] == "Aprobado" ? "Cobro realizado con éxito" : $respuesta[ "nb_error" ]; ?></div>
        </div>


        <p><a href="<?php echo base_url( "pedido/{$respuesta[ "referencia" ]}" ); ?>" class="btn btn-secondary"><i class="fa fa-undo"></i> Regresar al pedido</a></p>

    </div>
</div>

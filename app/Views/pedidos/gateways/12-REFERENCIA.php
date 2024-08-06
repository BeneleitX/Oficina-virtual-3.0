<div class="alert alert-info">
    <div class="row">
        <div class="col-lg-4">
            <h5>1. Elige tu banco</h5>
            <p>Este método de pago sólo aplica en los bancos BBVA (Convenio CIE 1589881) y Banco AZTECA y se ingresa como PAGO DE SERVICIOS.</p>
            <img src="<?php echo base_url(); ?>assets/img/referenciado.png" class="w-50 rounded">
        </div>
        <div class="col-lg-4">
            <h5>2. Elige tu forma de depósito</h5>
            <p>Puedes realizar el pago desde tu celular usando la App de tu banco en línea, en practicajas o ventanilla en sucursal.</p>
        </div>
        <div class="col-lg-4">
            <h5>3. Paga puntualmente</h5>
            <p>Los fondos pueden tardar hasta 24 horas en acreditarse, sin embargo para efectos de cálculo de comisiones y calificación de socio, la compra se registra en el sistema con la fecha y hora exacta del pago recibido por el banco.</p>
        </div>
    </div>
</div>


<div class="card mb-3">
    <div class="card-body text-center">
        <div class="row">
            <div class="col-lg-4 offset-lg-2 display-4 mb-3">
            <h5>Referencia para pago de servicios</h5>
                    <span class="py-3 badge bg-pink col-12"><?php echo getReferencia( $pedido[ "id" ] ); ?></span>
            </div>
            <div class="col-lg-4 display-4">
                <h5>Cantidad a depositar</h5>
                <span class="py-3 badge bg-marine col-12">$<?php echo number_format( $cantidad, 2 ); ?></span>
            </div>
        </div>
    </div>
</div>

<a href="<?php echo base_url( "tienda/".$modelo ); ?>"><i class="fa fa-undo"></i> Regresar al pedido <?php echo $pedido[ "referencia" ]; ?></a><br>
<a href="<?php echo base_url( "historial/".$modelo ); ?>"><i class="fa fa-store"></i> ir a mis pedidos</a>

<div class="alert alert-danger mt-5">
    Marcar como pagado (solo pruebas) <button onclick="fondeo( '<?php echo $pedido[ "modelo_codigo" ]."', '".$metodopago[ "codigo" ]."', ".$cantidad; ?> );" class="btn btn-danger">PAGAR PEDIDO <?php echo $pedido[ "referencia" ]; ?></button>
</div>
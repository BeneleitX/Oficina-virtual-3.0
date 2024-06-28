<h4 class="mt-1 mb-3"><?php echo $titulo; ?>: <?php echo $metodopago[ "nombre" ]; ?></h4>

<?php echo pills( "tienda", $modelo ); ?>

<?php echo view( "tienda/gateways/".$pedido[ "metodopago_codigo"] ); ?>

<script>
	var modelo 			= '<?php echo $modelo; ?>',
        usuario 		= <?php echo json_encode( $socio ) ?>,
		pedido  		= <?php echo json_encode( $pedido ); ?>;
</script>

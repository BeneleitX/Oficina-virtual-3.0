<h4 class="mt-1 mb-3"><?php echo $titulo; ?>: <?php echo $metodopago[ "nombre" ]; ?></h4>

<ul class="nav nav-pills mb-4">
    <?php 
    foreach( MODELOS as $m ){
        if( $m[ "settings" ][ "efectivo" ] ){
            echo "\n<li class=\"nav-item\"><a class=\"nav-link ".( $modelo == $m[ "codigo" ] ? "active" : "")."\" aria-current=\"page\" href=\"".base_url( "tienda/".$m[ "codigo" ] )."\"><i class=\"fa fa-{$m[ "settings" ][ "icono" ]}\"></i> {$m[ "nombre" ]}</a></li>";
        }
    }
    ?>
</ul>

<?php echo view( "tienda/gateways/".$pedido[ "metodopago_codigo"] ); ?>

<script>
	var modelo 			= '<?php echo $modelo; ?>',
        usuario 		= <?php echo json_encode( $socio ) ?>,
		pedido  		= <?php echo json_encode( $pedido ); ?>;
</script>

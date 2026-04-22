<div class="alert alert-success">
    Registrando salida de productos
</div>


<script>
$(document).ready(function(){
    fondeo( '<?php echo $pedido[ "id" ]; ?>', '<?php echo $metodopago[ "codigo" ]; ?>', 0, 1 );
});
   
</script>
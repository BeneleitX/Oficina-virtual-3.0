<div class="alert alert-success">
    Pagando pedido con saldo a favor
</div>


<script>
$(document).ready(function(){
    fondeo( '<?php echo $pedido[ "id" ]; ?>', '<?php echo $metodopago[ "codigo" ]; ?>', 0 );
});
   
</script>
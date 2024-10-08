<?php 
helper( "getnet_helper" );

$xml  = getCadenaXML( $pedido, $socio );
$xurl = getCadenaURL( $xml );



?>

<p class="text-center"><a href="<?php echo base_url( "pedido/{$pedido[ "referencia" ]}" ); ?>" class="btn btn-danger"><i class="fa fa-undo"></i> Regresar al pedido</a></p>

<iframe 
    src="<?php echo $xurl; ?>"
    width="100%" 
    height="750px" 
    frameborder="0" 
    scrolling="no"
    seamless="seamless"
    id="getnet"
  ></iframe> 
  
          
<script>



$(document).ready(function()
{ 
});




</script>
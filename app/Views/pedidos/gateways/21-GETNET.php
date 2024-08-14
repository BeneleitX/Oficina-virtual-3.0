<?php 
helper( "getnet_helper" );
$xurl = getCadenaXML( $pedido, $socio );
?>


<iframe 
    src="<?php echo $xurl; ?>"
    width="100%" 
    height="880px" 
    frameborder="0" 
    scrolling="no"
    seamless="seamless"
  ></iframe> 
          
<script>

$(document).ready(function()
{ 
});
</script>
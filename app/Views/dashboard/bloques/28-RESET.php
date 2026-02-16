<?php

$imagen = "assets/img/eventos/reset.png";
$hash   = filemtime( $imagen );


?>

<img src="<?php echo base_url().$imagen."?".$hash; ?>" class="img-fluid">



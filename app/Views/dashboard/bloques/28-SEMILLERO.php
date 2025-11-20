<?php

$evento = "917-SEMILLERO-17";
$imagen = "assets/img/eventos/{$evento}.png";
$hash   = filemtime( $imagen );

$sql  = "SELECT 
        count(*) as inscrito
        from t_pedidos p 
        where 
        p.usuario_id = {$usuario->id} and 
        SUBSTRING( p.estatus_codigo,1,3) > 400
        and p.promociones->>'$.\"910-EVENTOS\".productos.\"{$evento}\".cantidad' > 0";

$db = db_connect();
$inscrito = $db->query( $sql )->getRow()->inscrito;
?>

<a href="<?php echo base_url(); ?>tienda/90-SEMILLERO"><img src="<?php echo base_url().$imagen."?".$hash; ?>" class="img-fluid"></a>

<?php if( $inscrito ){ ?>
<div class="bg-black p-2">
    <h5 class="text-center text-white"><i class="fa fa-circle-check text-teal"></i> ¡Ya estas inscrito!</h5>
</div>
<?php } ?>



<h4 class="mt-1 mb-3"><?php echo $titulo; ?></h4>

<div class="row">

<?php 

$menu = [
    [ "success", "historial/".getModeloPrincipal(), "receipt", "Mis pedidos", sizeof( $pedidos ) ],
    [ "success", "tienda/".getModeloPrincipal(), "store", "Tienda en línea" ],
];

foreach( $menu as $opcion ){
    echo "\n<div class=\"col-6 col-md-4 col-lg-3 col-xl-2 mb-4\"><a class=\"btn position-relative btn-outline-{$opcion[0]} col-12 ".($opcion[0] == "secondary" ? "disabled" : "" )."\"  href=\"".base_url( $opcion[1] )."\"><i class=\"fa fa-{$opcion[2]} m-3\" style=\"font-size:50px\"></i><p>{$opcion[3]}</p><div class=\"contador text-center\" style=\"line-height:8px\">".( isset( $opcion[4] ) && $opcion[4] > 0 ? "<span class=\"badge rounded-pill bg-marine\">{$opcion[4]}</span><br>" : "" ).( str_contains( $opcion[1], "/" ) ? "<i class=\"fa fa-circle elipsis text-mustard\"></i><i class=\"fa fa-circle text-pink elipsis\"></i><i class=\"fa fa-circle text-light-blue elipsis\"></i>" : "")."</div></a></div>";
}

?>
</div>

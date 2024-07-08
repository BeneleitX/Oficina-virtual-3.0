<link href="<?php echo base_url(); ?>assets/css/datatables.css" rel="stylesheet"/>
<script src="<?php echo base_url(); ?>assets/js/datatables.js" type="text/javascript"></script>
<script src="<?php echo base_url(); ?>assets/js/datatables_bs5.js" type="text/javascript"></script>

<h4 class="mt-1 mb-0"><?php echo $titulo; ?></h4>
<p><a href="<?php echo base_url( "admin" ); ?>"><i class="fa fa-undo"></i> Regresar a configuración</a></p>

<div class="row">
    <div class="col-lg-9">
        <?php echo pills( "pines", $modelo ); ?>
    </div>
    <div class="col-lg-3">
        <button class="btn mt-3 col-12 btn-primary" onclick="excel_pines()"><i class="fa fa-file-excel"></i> Descargar pendientes</button>
    </div>
</div>

<div class="row">
<?php 
    foreach( $rangos as $r ){

        $activos = isset( $socios[ $r[ "codigo" ] ][ "activos"] ) ? $socios[ $r[ "codigo" ] ][ "activos"] : 0;
        $inactivos = isset( $socios[ $r[ "codigo" ] ][ "inactivos"] ) ? $socios[ $r[ "codigo" ] ][ "inactivos"] : 0;

        echo "\n<div class=\"col-3\"><div class=\"card mb-2\" style=\"overflow:hidden; position:relative\">
        <div style=\"position:absolute; top:20px; right:15px\"><span class=\"badge fs-6 bg-marine\">{$activos}</span></div>
        <table class=\"bg-white w-100\"><tr><td><img src=\"".base_url()."assets/img/rangos/{$r[ "codigo" ]}.jpg\" style=\"width:100px\" class=\"m-2\"></td><td class=\"w-100\" style=\"line-height:1\"><h5 class=\"m-0\">{$r[ "nombre" ]}</h5><span class=\"small text-teal\">".number_format( $r[ "cantidades" ][0], 2 )." - ".number_format( $r[ "cantidades" ][1], 2 )."</span><p class=\"mt-3 mb-0\">Por entregar: {$activos}</p></td></table>
        </div></div>";
    }
?>
</div>

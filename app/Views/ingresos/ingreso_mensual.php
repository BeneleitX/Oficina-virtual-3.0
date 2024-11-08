<link href="<?php echo base_url(); ?>assets/css/datatables.css" rel="stylesheet"/>
<link href="<?php echo base_url(); ?>assets/css/responsive.css" rel="stylesheet"/>
<script src="<?php echo base_url(); ?>assets/js/datatables.js" type="text/javascript"></script>
<script src="<?php echo base_url(); ?>assets/js/datatables_bs5.js" type="text/javascript"></script>
<script src="<?php echo base_url(); ?>assets/js/responsive.js" type="text/javascript"></script>

<h4 class="mt-1 mb-0"><?php echo $titulo; ?></h4>
<p class="mb-3">Hoy es <?php echo dia( date("N") )." ".date("d")." de ".mes( date("m") ).", ".date("Y") ?></p>

<div class="row mb-4">
    <div class="col-lg-8">
        <?php echo pills( "ingreso_mensual", $modelo ); ?>
    </div>
    <div class="col-lg-4 text-end">
        <button class="btn btn-success d-none"><i class="fa fa-file-excel"></i> Descargar Excel</button>

        <div class="row">
        <div class="col-4">
                <a href="<?php echo base_url()."balance/{$modelo}/".codigo_periodo( $modelo ); ?>" class="btn btn-outline-secondary"> Detalle SEMANAL</a>
            </div>
            <div class="col-4">
                <a href="<?php echo base_url()."ingreso_mensual/{$modelo}"; ?>" class="btn btn-secondary"> Ingreso MENSUAL</a>
            </div>
            <div class="col-4">
                <a href="<?php echo base_url()."depositos/{$modelo}"; ?>" class="btn btn-outline-secondary"> Depósitos recibidos</a>
            </div>
        </div>
    </div>
</div>

<?php

$mes = date( "Ym" );

while( $mes >= '202408' ){

    $hoy = strtoupper( mes( substr( $mes, 4, 2 ) ) )." ".substr( $mes, 0, 4 );
    $cantidad = $ingresosxdia[ $hoy ] ?? null;

    if( isset( $ingreso[ $mes ] ) ){
    ?>
    <div class="card mb-3 col-lg-6">
        <div class="card-header bg-marine"><h5 class="m-0 text-white"><?php echo $hoy; ?></h5></div>

            <table class="mb-0 table table-striped bg-white tabla_comisiones" id="t_<?php echo date("Y-m-d"); ?>">
                <tbody>
                    <?php 
                        $suma = 0;
                        foreach( $ingreso[ $mes ] as $k => $c ){

                                $esquema = ESQUEMAS[ $k ][ "settings" ][ "titulo" ];
                                $suma += $c;

                                echo "\n<tr\">
                                    <td width=\"20%\" nowrap><span class=\"badge bg-".MODELOS[ $modelo ][ "settings" ][ "color" ]."\"><i class=\"fa fa-".MODELOS[ $modelo ][ "settings" ][ "icono" ]."\"></i> ".MODELOS[ $modelo ][ "nombre" ]."</span> {$esquema}</td>
                                    <td width=\"10%\" class=\"text-end\"><strong>$".number_format( $c, 2 )."</strong></td>

                                </tr>";
                        }
                    ?>
                
                </tbody>

            </table>
        <div class="card-footer text-end bg-gray-600">
        <h5 class="m-0 text-white">$<?php echo number_format( $suma, 2 ); ?></h5>
        </div>
    </div>
<?php } 


    $mes = date( "Ym", strtotime( substr( $mes, 0, 4 )."-".substr( $mes, 4, 2 )."-01 - 1 month" ) );
}
?>



<script>
    var modelo = '<?php echo $modelo; ?>';
</script>

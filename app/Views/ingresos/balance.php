<script src="<?php echo base_url(); ?>assets/js/heatmap.js" type="text/javascript"></script>
<link href="<?php echo base_url(); ?>assets/css/datatables.css" rel="stylesheet"/>
<script src="<?php echo base_url(); ?>assets/js/datatables.js" type="text/javascript"></script>
<script src="<?php echo base_url(); ?>assets/js/datatables_bs5.js" type="text/javascript"></script>

<h4 class="mt-1 mb-0"><?php echo $titulo; ?></h4>
<p class="mb-3">Hoy es <?php echo dia( date("N") )." ".date("d")." de ".mes( date("m") ).", ".date("Y") ?></p>

<div class="row">
    <div class="col-lg-8">
        <?php echo pills( "balance", $modelo, "codigo_periodo" ); ?>
    </div>
    <div class="col-lg-4 text-end">
        <button class="btn btn-success"><i class="fa fa-file-excel"></i> Descargar Excel</button>
    </div>
</div>

    
<div id="heatmap" class="mb-5">
<?php 
    $ingresosxdia = $socio->getIngresosPorDia( $modelo );

    $inicia = date( "Y-m-d", strtotime( date( "Y-m-d", strtotime( $socio->historial->registro." + 1 day" ) )." last Monday" ) );


    while( $inicia <= date( "Y-m-d") ){
        $fecha = $inicia;
        $mes = substr( $fecha, 5, 2 );
        $semana = date("W",  strtotime( $fecha ) );

        $selected = ( $periodo[ "codigo" ] == codigo_periodo( $modelo, $inicia ) ) ? "selected" : "";

        echo "<div class=\"heatmap_columna {$selected}\" periodo=\"".codigo_periodo( $modelo, $inicia )."\"><p class=\"m-2\">{$semana}</p>";
        
        for( $d = 0; $d < 7; $d++ ){
            $fecha_next = date( "Y-m-d", strtotime( $fecha." + 1 day" ) );
            $cantidad = $ingresosxdia[ $fecha ] ?? null;

            echo "<div class=\"heatmap_dia ".( $mes != substr( $fecha, 5, 2 ) ? "" : "" )."\" data-bs-toggle=\"tooltip\" title=\"{$fecha} : $".number_format( $cantidad, 2 )."\" semana=\"{$semana}\" cantidad=\"{$cantidad}\"></div>";
            
            $fecha = $fecha_next;
        }

        $inicia = date( "Y-m-d", strtotime( $inicia." + 1 week" ) );
        echo "</div>";
    }
?>
</div>


<?php
$fecha = $periodo[ "inicia" ];

for( $d = 0; $d < 7; $d++ ){ 
    $hoy = date( "Y-m-d", strtotime( $fecha." + {$d} day" ) );
    $cantidad = $ingresosxdia[ $hoy ] ?? null;

    if( $cantidad ){
    ?>
    <div class="card mb-3">
        <div class="card-header bg-marine"><h5 class="m-0 text-white"><?php echo date( "d-m-Y", strtotime( $hoy ) ); ?></h5></div>
        <div class="card-body">
            <table class="mb-0 table table-striped bg-white tabla_comisiones" id="t_<?php echo date("Y-m-d"); ?>">
                <thead>
                    <tr>
                        <th class="text-center">Compra</th>
                        <th class="text-center">Fecha</th>
                        <th class="text-start">Nivel</th>
                        <th>Esquema</th>
                        <th>Cantidad</th>
                        <th>Socio</th>
                    </tr>
                </thead>

                <tbody>
                    <?php 
                        $socios = [];
                        foreach( $comisiones as $c ){

                            if( $c->fecha == $hoy ){
                                if( !isset( $socios[ $c->usuario_id ] ) ){
                                    $socios[ $c->usuario_id ] = model( "UsuarioModel" )->find( $c->usuario_id );
                                }

                                $socios[ $c->usuario_id ] = model( "UsuarioModel" )->find( $c->usuario_id );
                                echo "\n<tr\">
                                    <td width=\"10%\" class=\"text-center\"><a href=\"".base_url()."/pedido/{$c->referencia}\"><span class=\"badge bg-marine\">{$c->referencia}</span></a></td>
                                    <td width=\"20%\" class=\"text-center\">{$c->fecha}</td>
                                    <td width=\"10%\" class=\"text-start\"><strong>{$c->nivel}</strong> ".($c->compresion ? "<span class=\"badge border border-red text-red\">Compresion</span>" : "")."</td>
                                    <td width=\"20%\"><span class=\"badge bg-".MODELOS[ $modelo ][ "settings" ][ "color" ]."\"><i class=\"fa fa-".MODELOS[ $modelo ][ "settings" ][ "icono" ]."\"></i> ".MODELOS[ $modelo ][ "nombre" ]."</span> ".ESQUEMAS[ $c->esquema_codigo ][ "settings" ][ "titulo" ]."</td>
                                    <td width=\"10%\" class=\"text-end\"><strong>$".number_format( $c->cantidad, 2 )."</strong></td>
                                    <td nowrap>".$socios[ $c->usuario_id ]->avatar( 24 )." ".$socios[ $c->usuario_id ]->id( $modelo )."<span class=\"d-none d-lg-inline\"> ".$socios[ $c->usuario_id ]->nombre( 2 )."</span></td>
                                </tr>";
                            }
                        }
                    ?>
                
                </tbody>
            </table>
        </div>
    </div>
<?php } } ?>

<script>
    var modelo = '<?php echo $modelo; ?>';
</script>
<script src="<?php echo base_url(); ?>assets/js/heatmap.js" type="text/javascript"></script>
<link href="<?php echo base_url(); ?>assets/css/datatables.css" rel="stylesheet"/>
<link href="<?php echo base_url(); ?>assets/css/responsive.css" rel="stylesheet"/>
<script src="<?php echo base_url(); ?>assets/js/datatables.js" type="text/javascript"></script>
<script src="<?php echo base_url(); ?>assets/js/datatables_bs5.js" type="text/javascript"></script>
<script src="<?php echo base_url(); ?>assets/js/responsive.js" type="text/javascript"></script>

<h4 class="mt-1 mb-0"><?php echo $titulo; ?></h4>
<p class="mb-3">Hoy es <?php echo dia( date("N") )." ".date("d")." de ".mes( date("m") ).", ".date("Y") ?></p>

<div class="row mb-4">
    <div class="col-lg-8">
        <?php echo pills( "balance", $modelo, "codigo_periodo" ); ?>
    </div>
    <div class="col-lg-4 text-end">
        <button class="btn btn-success d-none"><i class="fa fa-file-excel"></i> Descargar Excel</button>

        <div class="row">
        <div class="col-4">
                <a href="<?php echo base_url()."balance/{$modelo}/". $periodo[ "codigo" ]; ?>" class="btn btn-secondary"> Detalle SEMANAL</a>
            </div>
            <div class="col-4">
            <a href="<?php echo base_url()."ingreso_mensual/{$modelo}"; ?>" class="btn btn-outline-secondary"> Ingreso MENSUAL</a>
            </div>
            <div class="col-4">
            <a href="<?php echo base_url()."depositos/{$modelo}"; ?>" class="btn btn-outline-secondary"> Depósitos recibidos</a>
            </div>
        </div>
    </div>
</div>

<div class="card mb-2" style="width:100%; overflow-x: auto;" id="heatmap_container">
    <div id="heatmap" class="card-body">
    <?php 
        $ingresosxdia = $socio->getIngresosPorDia( $modelo, $esq );

        $inicia = date( "Y-m-d", strtotime( date( "Y-m-d", strtotime( MODELOS[ $modelo ][ "settings" ][ "fecha_arranque" ]." + 1 day" ) )." last Monday" ) );
        ; // "2024-08-12"; //

        while( $inicia <= $fecha_max ){
            $fecha = $inicia;
            $mes = substr( $fecha, 5, 2 );
            $semana = date("W",  strtotime( $fecha ) );

            $selected = ( $periodo[ "codigo" ] == codigo_periodo( $modelo, $inicia ) ) ? "selected" : "";

            echo "<div class=\"heatmap_columna {$selected}\" periodo=\"".codigo_periodo( $modelo, $inicia )."\"><p class=\"m-1\">{$semana}</p>";
            
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
</div>
<div class="card mb-5">
    <div class="card-body">
        <div class="row">
            <div class="col-lg-9">
                <?php
                foreach( $esquemas_activos as $e ){
                    $s = ESQUEMAS[ $e[ "esquema" ] ];

                    echo "\n<input ".( in_array( $e[ "esquema" ], $esq ) ? "checked" : "" )." type=\"checkbox\" value=\"{$s[ "codigo" ]}\" class=\"btn-check opciones\" id=\"btn-check-{$s[ "codigo" ]}\" autocomplete=\"off\"><label class=\"btn btn-outline-yesno btn-sm\" for=\"btn-check-{$s[ "codigo" ]}\"><i class=\"fa fa-sack-dollar\"></i> {$s[ "settings" ][ "titulo" ]}</label> ";
                }
                ?>
            </div>
            <div class="col-lg-3 text-end"><a href="" style="display:none" class="btn btn-primary" id="actualiza"><i class="fa fa-rotate"></i> Actualizar datos</a></div>
        </div>
    </div>
</div>
<?php
$hoy = $periodo[ "inicia" ];

// for( $d = 0; $d < 7; $d++ ){ 
while( $hoy <= $periodo[ "termina" ] ){ 
    
    $cantidad = $ingresosxdia[ $hoy ] ?? null;
    
    if( $cantidad ){
    ?>
    <div class="card mb-3">
        <div class="card-header bg-marine">
            <div class="row">
                <div class="col-6"><h5 class="m-0 text-white"><?php echo date( "d-m-Y", strtotime( $hoy ) ); ?></h5></div>
                <div class="col-6 text-end text-white">$<?php echo number_format( $cantidad, 2 ); ?></div>
            </div>
        </div>

            <table class="mb-0 table table-striped bg-white tabla_comisiones" id="t_<?php echo date("Y-m-d"); ?>">
                <thead>
                    <tr>
                        <th class="text-center">Compra</th>
                        <th>Cantidad</th>
                        <th>Socio</th>
                        <th class="text-center">Fecha</th>
                        <th class="text-start">Nivel</th>
                        <th>Esquema</th>
                    </tr>
                </thead>

                <tbody>
                    <?php 
                        $socios = [];
                        $comisiones = $socio->getComisiones( $periodo[ "codigo" ], $esq );

                        foreach( $comisiones as $c ){

                            if( $c->fecha == $hoy ){
                                if( !isset( $socios[ $c->usuario_id ] ) ){
                                    $socios[ $c->usuario_id ] = model( "UsuarioModel" )->find( $c->usuario_id );
                                }
                                
                                $esquema = ESQUEMAS[ $c->esquema_codigo ][ "settings" ][ "titulo" ];
                                $c->promociones = json_decode( $c->promociones, 1 );
                                
                                if( $modelo == "20-TELEFONIA" ){
                                    $esquema = "<span class=\"badge bg-gray-600\">".substr( array_keys( $c->promociones[ "310-TELEFONIA" ][ "productos" ] )[0], 4 )."</span> ".$esquema; 
                                }

                                $socios[ $c->usuario_id ] = model( "UsuarioModel" )->find( $c->usuario_id );
                                echo "\n<tr\">
                                    <td width=\"10%\" class=\"text-center\">".( $usuario->es_admin() ? "<a href=\"".base_url()."/pedido/{$c->referencia}\"><span class=\"badge bg-marine\">{$c->referencia}</span></a>" : "<span class=\"badge bg-marine\">{$c->referencia}</span>" )."</td>

                                    <td width=\"10%\" class=\"text-end\"><strong>$".number_format( $c->cantidad, 2 )."</strong></td>

                                    <td nowrap>".$socios[ $c->usuario_id ]->avatar( 24 )." ".$socios[ $c->usuario_id ]->id( $modelo )."<span class=\"d-none d-lg-inline\"> ".$socios[ $c->usuario_id ]->nombre( 2 )."</span></td>

                                    <td width=\"20%\" class=\"text-center\">{$c->fecha}</td>

                                    <td width=\"10%\" class=\"text-start\"><strong>{$c->nivel}</strong> ".($c->compresion ? "<span class=\"badge border border-red text-red\">C<span class=\" d-none d-lg-inline\">ompresion</span></span>" : "")."</td>

                                    <td width=\"20%\" nowrap><span class=\"badge bg-".MODELOS[ $modelo ][ "settings" ][ "color" ]."\"><i class=\"fa fa-".MODELOS[ $modelo ][ "settings" ][ "icono" ]."\"></i> ".MODELOS[ $modelo ][ "nombre" ]."</span> {$esquema}</td>

                                </tr>";
                            }
                        }
                    ?>
                
                </tbody>
            </table>
     
    </div>
<?php } 
$hoy = date( "Y-m-d", strtotime( $hoy." + 1 day" ) );
} ?>

<script>
    var modelo  = '<?php echo $modelo; ?>',
        periodo = '<?php echo $periodo[ "codigo" ]; ?>';
</script>
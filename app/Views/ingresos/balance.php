<script src="<?php echo base_url(); ?>assets/js/heatmap.js" type="text/javascript"></script>

<h4 class="mt-1 mb-0"><?php echo $titulo; ?></h4>
<p class="mb-3">Hoy es lunes 11 de marzo, 2024</p>

<?php 

echo pills( "balance", $modelo, "codigo_periodo" ); ?>
    
<div id="heatmap" class="mb-5">
<?php 
    $ingresosxdia = $socio->getIngresosPorDia("10-NUTRICION");

    $inicia = date( "Y-m-d", strtotime( date( "Y-m-d", strtotime( $socio->historial->registro." + 1 day" ) )." last Monday" ) );


    while( $inicia < date( "Y-m-d") ){
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
        <div class="card-header bg-teal"><h5 class="m-0 text-white"><?php echo $hoy; ?></h5></div>
<table class="mb-0 table table-striped bg-white tabla_comisiones" id="t_<?php echo date("Y-m-d"); ?>">
    <thead>
        <tr>
            <th class="d-none d-lg-table-cell">Compra</th>
            <th>Cantidad</th>
            <th>Esquema</th>
            <th class="d-none d-lg-table-cell">Nivel</th>
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
                        <td width=\"10%\" class=\"text-center d-none d-lg-table-cell\"><span class=\"badge bg-marine\">{$c->pedido_id}</span></td>
                        <td width=\"10%\" class=\"text-end\">$".number_format( $c->cantidad, 2 )."</td>
                        <td width=\"20%\">".ESQUEMAS[ $c->esquema_codigo ][ "settings" ][ "titulo" ]."</td>
                        <td width=\"10%\" class=\"text-center d-none d-lg-table-cell\">{$c->nivel}</td>
                        <td>".$socios[ $c->usuario_id ]->avatar( 24 )." ".$socios[ $c->usuario_id ]->id( $modelo )."<span class=\"d-none d-lg-inline\"> ".$socios[ $c->usuario_id ]->nombre( 2 )."</span></td>
                    </tr>";
                }
            }
        ?>
     
    </tbody>
</table>
        </div>
<?php } } ?>

<script>
    var modelo = '<?php echo $modelo; ?>';
</script>
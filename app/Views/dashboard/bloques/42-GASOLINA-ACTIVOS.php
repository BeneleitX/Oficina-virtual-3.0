<?php
$mes = date( "Ym" );

if( !isset( $usuario->data->estatus->modelos->{"40-GASOLINAS"} ) ) $usuario->valida_modelo();
$estatus = substr( $usuario->data->estatus->modelos->{"40-GASOLINAS"}, 0, 1);
$calificacion = substr( $cx[ $m["codigo" ] ][ "m_0" ], 0, 1 );

$tarjeta = [
    "compra" => $usuario->historial->modelos->{"40-GASOLINAS"}->primercompra->{"412-TARJETA"} ?? null,
    "numero" => $usuario->data->tarjeta ?? null
];

?>

<div class="card-body">
    <table class="w-100 mb-3">
        <tr>
            <td style="width:100px; position:relative" class="px-3" valign="bottom">
                <img src="<?php echo base_url()."assets/img/gas_{$estatus}.png"; ?>" style="width:100px">
                <span class="badge bg-white text-gray-600 py-0 px-1" style="font-size:30px; position:absolute; top:50px; left: 54px"><?php echo $calificacion; ?></span>
            </td>
            <td>

            <div class="card small">
                <table class="table table-striped table-bordered w-100 m-0 table-sm text-center">
                    <thead>
                        <tr>
                            <th></th>
                            <th>1</th>
                            <th>2</th>
                            <th>3</th>
                            <th>4</th>
                            <th>5</th>
                        </tr>
                    </thead>
                    <tbody>
                    <tr>
                            <td>1</td>
                            <td class="<?php echo $calificacion >= 1 ? "table-success" : ( $calificacion ? "table-danger" : "" ); ?>"></td>
                            <td class="<?php echo $calificacion >= 2 ? "table-success" : ( $calificacion ? "table-danger" : "" ); ?>"></td>
                            <td class="<?php echo $calificacion >= 3 ? "table-success" : ( $calificacion ? "table-danger" : "" ); ?>"></td>
                            <td class="<?php echo $calificacion >= 4 ? "table-success" : ( $calificacion ? "table-danger" : "" ); ?>"></td>
                            <td class="<?php echo $calificacion >= 5 ? "table-success" : ( $calificacion ? "table-danger" : "" ); ?>"></td>
                        </tr>
                        <tr>
                            <td>2</td>
                            <td class="<?php echo $calificacion >= 1 ? "table-success" : ( $calificacion ? "table-danger" : "" ); ?>"></td>
                            <td class="<?php echo $calificacion >= 2 ? "table-success" : ( $calificacion ? "table-danger" : "" ); ?>"></td>
                            <td class="<?php echo $calificacion >= 3 ? "table-success" : ( $calificacion ? "table-danger" : "" ); ?>"></td>
                            <td class="<?php echo $calificacion >= 4 ? "table-success" : ( $calificacion ? "table-danger" : "" ); ?>"></td>
                            <td class="<?php echo $calificacion >= 5 ? "table-success" : ( $calificacion ? "table-danger" : "" ); ?>"></td>
                        </tr>
                        <tr>
                            <td>3</td>
                            <td class="<?php echo $calificacion >= 1 ? "table-success" : ( $calificacion ? "table-danger" : "" ); ?>"></td>
                            <td class="<?php echo $calificacion >= 2 ? "table-success" : ( $calificacion ? "table-danger" : "" ); ?>"></td>
                            <td class="<?php echo $calificacion >= 3 ? "table-success" : ( $calificacion ? "table-danger" : "" ); ?>"></td>
                            <td class="<?php echo $calificacion >= 4 ? "table-success" : ( $calificacion ? "table-danger" : "" ); ?>"></td>
                            <td class="<?php echo $calificacion >= 5 ? "table-success" : ( $calificacion ? "table-danger" : "" ); ?>"></td>
                        </tr>
                        <tr>
                            <td>4</td>
                            <td class="<?php echo $calificacion >= 1 ? "table-success" : ( $calificacion ? "table-danger" : "" ); ?>"></td>
                            <td class="<?php echo $calificacion >= 2 ? "table-success" : ( $calificacion ? "table-danger" : "" ); ?>"></td>
                            <td class="<?php echo $calificacion >= 3 ? "table-success" : ( $calificacion ? "table-danger" : "" ); ?>"></td>
                            <td class="<?php echo $calificacion >= 4 ? "table-success" : ( $calificacion ? "table-danger" : "" ); ?>"></td>
                            <td class="<?php echo $calificacion >= 5 ? "table-success" : ( $calificacion ? "table-danger" : "" ); ?>"></td>
                        </tr>
                    </tbody>
                </table>
            </div>


            </td>
        </tr>
    </table>

    

    <?php
        if( $tarjeta[ "compra" ] ){
            if( $tarjeta[ "numero" ] ){
                echo "\n<table class=\"mb-1\" align=\"center\"><tr><td><strong>TARJETA FISICA</strong></td><td class=\"small\">".estatus( "201-ACTIVO" )."</td></tr></table><div class=\"alert alert-info m-0 text-center fs-3 py-1\"><i class=\"fa fa-credit-card text-white small\"></i> {$tarjeta[ "numero" ]}</div>";
            }
            else{
                echo "\n<table class=\"mb-1\" align=\"center\"><tr><td><strong>TARJETA FISICA</strong></td><td class=\"small\">".estatus( "330-EN-ESPERA" )."</td></tr></table><div class=\"alert alert-warning m-0 text-center\">Tarjeta en proceso de activación</div>";
            }
        }
        else{
            echo "\n<table class=\"mb-1\" align=\"center\"><tr><td><strong>TARJETA FISICA</strong></td><td class=\"small\">".estatus( "126-NO-ADQUIRIDO" )."</td></tr></table><div class=\"alert alert-light m-0 text-center\">No cuentas aun con tu tarjeta física</div>";
        }
        
    ?>

<p class="text-center mt-3 mb-0"><?php echo "Día ".ceil( $transcurridos / 24 / 60 )." de ".$total_dias; ?></p>

<div class="progress" role="progressbar" aria-valuemin="0" aria-valuemax="100" style="height:24px; border-radius:10px">
    <div class="progress-bar bg-teal" style="width: <?php echo $porc_bono; ?>%"><?php echo $porc_bono."%"; ?></div>
</div>     
</div>


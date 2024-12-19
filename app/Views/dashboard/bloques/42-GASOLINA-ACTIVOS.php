<?php
$mes = date( "Ym" );

if( !isset( $usuario->data->estatus->modelos->{"40-GASOLINAS"} ) ) $usuario->valida_modelo();
$estatus = substr( $usuario->data->estatus->modelos->{"40-GASOLINAS"}, 0, 1);
$calificacion = substr( $cx[ $m["codigo" ] ][ "m_0" ], 0, 1 );

$tarjeta = [
    "compra"  => strlen( $usuario->historial->modelos->{"40-GASOLINAS"}->primercompra->{"412-TARJETA"} ?? "" ),
    "numero"  => ( ( $usuario->data->tarjeta->numero ?? null ) ? ( $usuario->data->tarjeta->estatus == "625-ACTIVA" ? $usuario->data->tarjeta->numero : "**** **** **** ****" ) : null ),
    "estatus" => $usuario->data->tarjeta->estatus ?? "126-NO-ADQUIRIDO"
];

?>

<div class="card-body text-center">
    <table class="w-100 mb-3">
        <tr>
            <td style="width:100px; position:relative" class="px-3" valign="bottom">
                <img src="<?php echo base_url()."assets/img/gas_{$estatus}.png"; ?>" style="width:100px">
                <span class="badge bg-white text-gray-600 py-0 px-1" style="font-size:30px; position:absolute; top:60px; left: 54px"><?php echo $calificacion; ?></span>
            </td>
            <td nowrap>
            <div class="small" style="display:inline-block; width: 5%; vertical-align:bottom; writing-mode: tb-rl; transform: rotate(-180deg);">&nbsp; Niveles en tu red</div>
            <div style="display:inline-block; width:95%" class="card small"><p class="my-2">Socios activos por calificación</p>
                <table id="niveles_gas" class="table table-striped table-bordered w-100 m-0 table-sm text-center">
                    <thead>
                        <tr>
                            <th width="15%"></th>
                            <th width="17%">1</th>
                            <th width="17%">2</th>
                            <th width="17%">3</th>
                            <th width="17%">4</th>
                            <th width="17%">5</th>
                        </tr>
                    </thead>
                    <tbody>
                    <tr>
                            <td>1</td>
                            <td class="py-0 <?php echo $calificacion >= 1 ? "table-success" : ( $calificacion ? "table-danger" : "" ); ?>"></td>
                            <td class="py-0 <?php echo $calificacion >= 2 ? "table-success" : ( $calificacion ? "table-danger" : "" ); ?>"></td>
                            <td class="py-0 <?php echo $calificacion >= 3 ? "table-success" : ( $calificacion ? "table-danger" : "" ); ?>"></td>
                            <td class="py-0 <?php echo $calificacion >= 4 ? "table-success" : ( $calificacion ? "table-danger" : "" ); ?>"></td>
                            <td class="py-0 <?php echo $calificacion >= 5 ? "table-success" : ( $calificacion ? "table-danger" : "" ); ?>"></td>
                        </tr>
                        <tr>
                            <td>2</td>
                            <td class="py-0 <?php echo $calificacion >= 1 ? "table-success" : ( $calificacion ? "table-danger" : "" ); ?>"></td>
                            <td class="py-0 <?php echo $calificacion >= 2 ? "table-success" : ( $calificacion ? "table-danger" : "" ); ?>"></td>
                            <td class="py-0 <?php echo $calificacion >= 3 ? "table-success" : ( $calificacion ? "table-danger" : "" ); ?>"></td>
                            <td class="py-0 <?php echo $calificacion >= 4 ? "table-success" : ( $calificacion ? "table-danger" : "" ); ?>"></td>
                            <td class="py-0 <?php echo $calificacion >= 5 ? "table-success" : ( $calificacion ? "table-danger" : "" ); ?>"></td>
                        </tr>
                        <tr>
                            <td>3</td>
                            <td class="py-0 <?php echo $calificacion >= 1 ? "table-success" : ( $calificacion ? "table-danger" : "" ); ?>"></td>
                            <td class="py-0 <?php echo $calificacion >= 2 ? "table-success" : ( $calificacion ? "table-danger" : "" ); ?>"></td>
                            <td class="py-0 <?php echo $calificacion >= 3 ? "table-success" : ( $calificacion ? "table-danger" : "" ); ?>"></td>
                            <td class="py-0 <?php echo $calificacion >= 4 ? "table-success" : ( $calificacion ? "table-danger" : "" ); ?>"></td>
                            <td class="py-0 <?php echo $calificacion >= 5 ? "table-success" : ( $calificacion ? "table-danger" : "" ); ?>"></td>
                        </tr>
                        <tr>
                            <td>4</td>
                            <td class="py-0 <?php echo $calificacion >= 1 ? "table-success" : ( $calificacion ? "table-danger" : "" ); ?>"></td>
                            <td class="py-0 <?php echo $calificacion >= 2 ? "table-success" : ( $calificacion ? "table-danger" : "" ); ?>"></td>
                            <td class="py-0 <?php echo $calificacion >= 3 ? "table-success" : ( $calificacion ? "table-danger" : "" ); ?>"></td>
                            <td class="py-0 <?php echo $calificacion >= 4 ? "table-success" : ( $calificacion ? "table-danger" : "" ); ?>"></td>
                            <td class="py-0 <?php echo $calificacion >= 5 ? "table-success" : ( $calificacion ? "table-danger" : "" ); ?>"></td>
                        </tr>
                    </tbody>
                </table>
            </div>


            </td>
        </tr>
    </table>

    

    <?php
        if( $tarjeta[ "compra" ] > 0 ){
            if( $tarjeta[ "numero" ] ){
                echo "\n<table class=\"mb-1\" align=\"center\"><tr><td><strong>TARJETA</strong></td><td class=\"small\">".estatus( $tarjeta[ "estatus" ] )."</td></tr></table>".( $usuario->data->tarjeta->estatus == "623-ENTREGA" ? "<button class=\"btn btn-danger col-12\" onclick=\"$( '#activa_tarjeta' ).modal( 'show' )\"><i class=\"fa fa-credit-card text-white small\"></i> ACTIVALA AQUI</button>" : "<div class=\"alert alert-info m-0 text-center fs-3 py-1\"><i class=\"fa fa-credit-card text-white small\"></i> {$tarjeta[ "numero" ]}</div>" );
            }
            else{
                echo "\n<table class=\"mb-1\" align=\"center\"><tr><td><strong>TARJETA</strong></td><td class=\"small\">".estatus( "330-EN-ESPERA" )."</td></tr></table><div class=\"alert alert-warning m-0 text-center\">Tarjeta en espera de entrega a socio</div>";
            }
        }
        else{
            echo "\n<table class=\"mb-1\" align=\"center\"><tr><td><strong>TARJETA</strong></td><td class=\"small\">".estatus( "126-NO-ADQUIRIDO" )."</td></tr></table><div class=\"alert alert-light m-0 text-center\">No cuentas aun con tu tarjeta física</div>";
        }
        
    ?>

<p class="text-center mt-3 mb-0"><?php echo "Día ".ceil( $transcurridos / 24 / 60 )." de ".$total_dias; ?></p>

<div class="progress" role="progressbar" aria-valuemin="0" aria-valuemax="100" style="height:24px; border-radius:10px">
    <div class="progress-bar bg-teal" style="width: <?php echo $porc_bono; ?>%"><?php echo $porc_bono."%"; ?></div>
</div>     
</div>



<div class="modal" tabindex="-1" id="activa_tarjeta">
	<div class="modal-dialog modal-lg">
		<div class="modal-content">
            <div class="modal-header bg-marine">
                <h5 class="modal-title text-white"><i class="fa fa-credit-card"></i> Activar tarjeta</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-lg-4 text-center">
                        <img src="<?php echo base_url(); ?>assets/img/productos/915-TARJETA.png" class="img-fluid px-3">
                    </div>
                    <div class="col-lg-8 pt-4">
                        <p class="mb-1">Escriba los 16 dígitos de la tarjeta</p>
                        <div class="row"><div class="col-lg-6"><input type="text" class="form-control mb-3" name="v_tarjeta1"></input></div></div>

                        <p class="mb-1">Repita con cuidado los 16 dígitos de la tarjeta</p>
                        <div class="row"><div class="col-lg-6"><input type="text" class="form-control" name="v_tarjeta2" disabled></input></div></div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button class="btn btn-light" id="submit_tarjeta" disabled><i class="fa fa-check"></i> Activar</button>
            </div>
		</div>
	</div>
</div>

<script>

$(document).ready(function(){
    var tabla = $( '#niveles_gas' );
    
    tabla.find( 'th:eq(0)' ).html( loader );

    $.ajax({
        url: base_url + "niveles_gas", 
        type: "POST",
        dataType: "json",
        contentType: 'application/x-www-form-urlencoded; charset=UTF-8',
        data: { 
            [csrf_token] : csrf_hash, 
            socio : <?php echo $usuario->id; ?>
        },
        success: function( result ){
            tabla.find( 'th:eq(0)' ).html( '' );

            for( nivel = 1; nivel <= 4; nivel++ ){
                for( calificacion = 1; calificacion <= 5; calificacion++ ){

                    if( result[ nivel - 1][ calificacion - 1 ] > 0 )
                    tabla.find( 'tbody > tr:eq( ' + ( nivel - 1 ) + ' ) > td:eq( ' + calificacion + ' )' ).html( '<h5 class="m-0 text-teal">' + result[ nivel - 1 ][ calificacion - 1 ] + '</h5>' );
                }
            }
        }
    });    
});

</script>
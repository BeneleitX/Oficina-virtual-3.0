<link href="<?php echo base_url(); ?>assets/css/datatables.css" rel="stylesheet"/>
<script src="<?php echo base_url(); ?>assets/js/datatables.js" type="text/javascript"></script>
<script src="<?php echo base_url(); ?>assets/js/datatables_bs5.js" type="text/javascript"></script>

<h4 class="mt-1 mb-4"><?php echo $titulo; ?> <span class="badge bg-marine"><?php echo mes( date( "m" ) )." ".date( "Y" ); ?></span></h4>



<?php 

$inversiones = $usuario->get_inversiones();

if( sizeof( $inversiones ) ){
    foreach( $inversiones as $i ){

        $p = model( "ProductoModel" )->find( $i[ "producto_codigo" ] );

        $i[ "fechas" ] = update_fecha_inversion( $i, $p );

        echo "\n<div class=\"col-lg-6\">
                    <div class=\"card\">
                        <div class=\"card-header\">
                            <div class=\"row\">
                                <div class=\"col-1\">
                                    <img src=\"".base_url()."assets/img/productos/{$i[ "producto_codigo" ]}.png\" style=\"width:60px\">
                                </div>
                                <div class=\"col-6 pt-2\">
                                    <h5 class=\"m-0 text-{$p->data->color}\">{$p->data->nombre}</h5>
                                    ".estatus( $i[ "estatus_codigo" ] )."
                                </div>
                                <div class=\"col-5 pt-1 text-end\">
                                    <h1 class=\"m-0\"><img src=\"https://static.tronscan.org/production/logo/usdtlogo.png\" style=\"width:24px\"> $".number_format( $i[ "cantidad" ], 2 )."</h1>
                                </div>
                            </div>

                        </div>

                        <div class=\"card-body text-red py-3\">
                            <div class=\"row\">
                                <div class=\"col-6\">
                                    <table class=\"table table-sm m-0\">
                                        <tr>
                                            <td>Inicio de inversión</td>
                                            <td class=\"text-end\">".fecha( $i[ "fechas" ][ "inversion" ] )."</td>
                                        </tr>
                                        <tr>
                                            <td>Cierre de inversión</td>
                                            <td class=\"text-end\">".fecha( $i[ "fechas" ][ "cierre" ] )."</td>
                                        </tr>
                                        <tr>
                                            <td>Días efectivos en el mes</td>
                                            <td class=\"text-end\">".( $i[ "fechas" ][ "dias" ] )."</td>
                                        </tr>                                        
                                    </table>
                                </div>
                                <div class=\"col-6\">
                                    <table class=\"table table-sm m-0\">
                                   
                                        <tr>
                                            <td>Rendimiento mensual</td>
                                            <td class=\"text-end\"><span class=\"badge bg-teal\">".( $p->data->porcentaje )."%</span> $".number_format( $i[ "cantidad" ] * ( $p->data->porcentaje / 100 ), 2 )."</td>
                                        </tr>                                        
                                        <tr>
                                            <td>Rendimiento diario</td>
                                            <td class=\"text-end\">$".( $diario = number_format( rendimiento_diario( $i[ "cantidad" ], $p->data->porcentaje, date( "Ym" ) ), 2 ) )."</td>
                                        </tr>
                                        <tr>
                                            <td>Rendimiento total</td>
                                            <td class=\"text-end\"><strong>$".number_format( $i[ "fechas" ][ "dias" ] * $diario, 2 )."</strong></td>
                                        </tr>
                                                                         
                                    </table>                                
                                </div>
                            </div>
                        </div>

                        <div class=\"card-footer text-red text-end\">
                            <button class=\"btn d-none btn-sm btn-success\"><i class=\"fa fa-file-arrow-down\"></i> Estado de cuenta</button>
                            <button class=\"btn d-none btn-sm btn-info\"><i class=\"fa fa-magnifying-glass\"></i> Detalles</button>
                            <button class=\"btn btn-sm btn-danger\" disabled><i class=\"fa fa-right-from-bracket\"></i> Programar retiro</button>
                        </div>
                    </div>
                </div>";
    }
}else{
    echo "<div class=\"row m-3\" style=\"zoom:3\"><div class=\"col-4 display-3 text-gray-300 text-end\"><i class=\"fa fa fa-arrow-trend-up\"></i></div><div class=\"col-8 pt-3 mt-3 text-gray-500 text-start\">Aun no tienes inversiones</div></div>";
}

?>


    
</div>
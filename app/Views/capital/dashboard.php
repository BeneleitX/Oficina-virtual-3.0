<link href="<?php echo base_url(); ?>assets/css/datatables.css" rel="stylesheet"/>
<script src="<?php echo base_url(); ?>assets/js/datatables.js" type="text/javascript"></script>
<script src="<?php echo base_url(); ?>assets/js/datatables_bs5.js" type="text/javascript"></script>

<h4 class="mt-1 mb-4"><?php echo $titulo; ?></h4>

<?php 

$inversiones = $usuario->get_inversiones();

if( sizeof( $inversiones ) ){
    foreach( $inversiones as $i ){

        $p = model( "ProductoModel" )->find( $i[ "producto_codigo" ] );

        $date1 = new DateTime( $i[ "fechas" ][ "inversion" ] );
        $date2 = new DateTime( $i[ "extras" ][ "meses" ][ 24 ][ "termina" ] );
        $interval = $date1->diff( $date2 );
        $total_dias = $interval->days + 1;

        $date2 = new DateTime( date( "Y-m-d" ) );
        $interval = $date1->diff( $date2 );
        $transcurridos = $interval->days;

        $porc_bono = ceil( $transcurridos * 100 / $total_dias );

        if( $i[ "extras" ][ "TxHash" ] && strlen( $i[ "extras" ][ "TxHash" ] ) == 64 ){
            $hash = $i[ "extras" ][ "TxHash" ];
        }
        else{
            $hash = "<i class=\"fa fa-warning\"></i> Este paquete de inversión aun no cuenta con TxHash";
        }

        $mes_actual = 24;

        for( $a = 0; $a < 24; $a++ ){
            if( $i[ "extras" ][ "meses" ][ $a ][ "Ym" ] == date( "Ym" ) ){
                $mes_actual = $a;
            }
        }

        $bt = balance_inversion( $i );

        echo "\n
                    <div class=\"card mb-4\">
                        <div class=\"card-header\">
                            <div class=\"row\">
                                <div class=\"col-2 col-lg-1\">
                                    <img src=\"".base_url()."assets/img/productos/{$i[ "producto_codigo" ]}.png\" style=\"width:60px\">
                                </div>
                                <div class=\"col-10 col-lg-3 pt-2\">
                                    <h5 class=\"m-0 text-{$p->data->color}\">{$p->data->nombre}</h5>
                                    ".estatus( $i[ "estatus_codigo" ] )."
                                </div>

                                <div class=\"col-lg-4 text-center\">
                                    <span style=\"display:block; width:100%\" class=\"mt-2 fs-3 badge bg-gray-300 text-marine\"><img src=\"https://static.tronscan.org/production/logo/usdtlogo.png\" style=\"width:24px\"> $".number_format( $bt[ "total" ], 2 )."</span>
                                </div>

                                <div class=\"col-lg-4\">
                                    <p class=\"text-center text-marine mt-1 mb-0 fw-bold \">Día {$transcurridos} de {$total_dias} / Mes ".($mes_actual+1)." de 24</p>
                                    <div class=\"progress\" role=\"progressbar\" aria-valuemin=\"0\" aria-valuemax=\"100\" style=\"height:24px; border-radius:10px\">
                                        <div class=\"progress-bar bg-teal\" style=\"width: {$porc_bono}%\">{$porc_bono}%</div>
                                    </div>                                  
                                </div>

                            </div>

                        </div>

                        <div class=\"card-body text-red py-3\">
                            <div class=\"row\">
                                <div class=\"col-lg-8\">
                                <h5 class=\"text-center text-gray-400 mb-3 mb-lg-3\">{$hash}</h5>
                                    <div class=\"row\">
                                        <div class=\"col-lg-6\">
                                            <table class=\"table table-sm m-0\">
                                                <tr>
                                                    <td>Inicio de inversión</td>
                                                    <td class=\"text-end\">".fecha( $i[ "fechas" ][ "inversion" ] )."</td>
                                                </tr>
                                                <tr>
                                                    <td>Cierre de inversión</td>
                                                    <td class=\"text-end\">".fecha( $i[ "extras" ][ "meses" ][ 24 ][ "termina" ] )."</td>
                                                </tr>
                                                <tr>
                                                    <td>Rendimiento mensual</td>
                                                    <td class=\"text-end\">{$i[ "extras" ][ "meses" ][ $mes_actual ][ "Porcentaje" ] }%</td>
                                                </tr>
                                            </table>
                                            
                                        </div>
                                        <div class=\"col-lg-6\">
                                            <table class=\"table table-sm m-0\">
                                        
                                                <tr>
                                                    <td>Capital semilla</td>
                                                    <td class=\"text-end\">$".number_format( $bt[ "semilla" ], 2 )."</td>
                                                </tr>
                                                <tr>
                                                    <td>Rendimiento</td>
                                                    <td class=\"text-end\">$".number_format( $bt[ "rendimiento" ], 2 )."</td>
                                                </tr>
                                                <tr>
                                                    <td>Retiros</td>
                                                    <td class=\"text-end\">$".number_format( $bt[ "retiros" ], 2 )."</td>
                                                </tr>
                                                                                
                                            </table>  
                                            
                                        </div>
                                    </div>
                                    <div class=\"row mb-3 my-lg-0 \">
                                        <div class=\"col-lg-6\"><button disabled class=\"btn btn-lg mt-4 btn-outline-info w-100\"><i class=\"fa fa-magnifying-glass\"></i> Detalles de cuenta</button></div>
                                        <div class=\"col-lg-6\"><button disabled class=\"btn btn-lg btn-outline-danger w-100 mt-4 \" onclick=\"$( '#stock_modal' ).modal( 'show' )\"><i class=\"fa fa-right-from-bracket\"></i> Programar retiro</button></div>                                         
                                    </div>
                                    
                                </div>

                                <div class=\"col-lg-4\">
                                    <img src=\"".base_url()."assets/img/chart.png\" class=\"img-fluid p-3 border border-teal rounded\" style=\"opacity:0.2\">
                                </div>
                            </div>   
                            
                               
                        </div>
                    </div>
                
                ";
    }
}else{
    echo "<div class=\"row m-3\" style=\"zoom:3\"><div class=\"col-4 display-3 text-gray-300 text-end\"><i class=\"fa fa fa-arrow-trend-up\"></i></div><div class=\"col-8 pt-3 mt-3 text-gray-500 text-start\">Aun no tienes inversiones</div></div>";
}

?>
    

<div class="modal" tabindex="-1" id="stock_modal">
	<div class="modal-dialog">
		<div class="modal-content">
			<div class="modal-header bg-red">
				<div class="modal-title me-3">
                    <h5 class="text-white">Programar retiro</h5>
				</div>
				<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
			</div>

            <form action="<?php echo base_url( "addstock" ); ?>" method="post">
                <?php echo csrf_field(); ?>
                <input type="hidden" id="stock_producto" name="producto" value="">
                <div class="modal-body">
                    <div class="row">
                    <div class="col-4">
                            <img id="stock_avatar" class="img-fluid p-2" src="">
                        </div>
                        <div class="col-8">
                            <p>DATA</p>
                            <input class="form-control w-50" name="cantidad">
                        </div>                        

                    </div>
                    <div class="nombre"></div>
                </div>
                <div class="modal-footer">
                    <button class="btn btn-danger my-2" id="confirma_agregar">Programar retiro ahora</button>
                </div>
            </form>
		</div>
	</div>
</div>
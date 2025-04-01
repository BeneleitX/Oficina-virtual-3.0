<script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>

<h4 class="my-1"><?php echo $titulo; ?></h4>
<p>
    <a class="btn btn-light btn-sm" href="<?php echo base_url( "capital" ); ?>"><i class="fa fa-undo"></i> Regresar a listado de inversiones</a>
</p>

<div id="chart"></div>

<?php

$p   = model( "ProductoModel" )->find( $i[ "producto_codigo" ] );

$f_i = get_fecha_inversion( $i[ "fechas" ][ "pagado" ] ); 

if(!isset($i[ "extras" ][ "meses" ][ 0 ] ) ){
    $pedido = model( "PedidoModel" )->find( $i[ "pedido_id" ] );
    $i[ "extras" ][ "meses" ] = genera_meses( $pedido, $i[ "id" ], $p );

    model( "InversionModel" )->save( $i );
}

$date1 = new DateTime( $f_i );
$date2 = new DateTime( $i[ "extras" ][ "meses" ][ 24 ][ "termina" ] );
$interval = $date1->diff( $date2 );
$total_dias = $interval->days + 1;

if( date( "Y-m-d" ) < $f_i ){
    $transcurridos = 0;
}
else{
    $date3 = new DateTime( date( "Y-m-d" ) );
    $interval = $date1->diff( $date3 );
    $transcurridos = $interval->days > $total_dias ? $total_dias : $interval->days;
}

$porc_bono = ceil( $transcurridos * 100 / $total_dias );

if( $i[ "extras" ][ "TxHash" ] ){
    $hash = "<span class=\"text-teal\">{$i[ "extras" ][ "TxHash" ]}</span>";
}
else{
    $hash = "<span class=\"text-mustard\"><i class=\"fa fa-warning\"></i> Este paquete de inversión aun no cuenta con TxHash</span> <button class=\"d-none btn btn-sm btn-warning\" onclick=\"carga_hash( {$i[ "id" ]} )\"><i class=\"fa fa-plus\"></i> Agregar ahora</button>";
}

$mes_actual = 24;
$meses       = [];
$semilla     = [];
$compuesto   = [];
$rendimiento = [];
$retiros     = [];

$tablas = [];
$r      = 0;
$h      = 0;

for( $a = 0; $a < 25; $a++ ){
    // inicializamos tabla desglose de mes

    $m = $i[ "extras" ][ "meses" ][ $a ];

    if( $m[ "Ym" ] < date( "Ym" ) ){
        $semilla[]   = $m[ "semilla" ];
        $compuesto[] = $m[ "compuesto" ];
        $dias = $m[ "dias_parcial" ];
        $r  = $m[ "rendimiento_mes" ];
        $h += $m[ "rendimiento_mes" ];
        $retiros[] = $m[ "retiros" ];
    }
    elseif( $m[ "Ym" ] == date( "Ym" ) ){
        $mes_actual  = $a;
        $semilla[]   = $m[ "semilla" ];
        $compuesto[] = $m[ "compuesto" ];
        $retiros[] = $m[ "retiros" ];
        
        $h   += $m[ "rendimiento_mes" ];
        $dias = date( "d" ) - ( $m[ "dias_en_mes" ] - $m[ "dias_parcial" ] );

        if( $dias < 0 ){
            $dias = 0;
        }                

        $r = $m[ "rendimiento_dia" ] * $dias;
    }
    else{
        $semilla[] = 0;
        $compuesto[] = 0;
        $r = 0;
        $dias = 0;
        $retiros[] = 0;
    }

    $rendimiento[] = $r;
    $meses[]       = substr( $m[ "Ym" ], 0, 4 )." ".mes( substr( $m[ "Ym" ], 4, 2 ), 3 );

    // cierre de tabla desglose de mes

    if( $m[ "Ym" ] <= date( "Ym" ) ){


//        if( $m[ "Ym" ] == date( "Ym" ) && $m[ "rendimiento_mes" ] != $r ){
        if( $m[ "Ym" ] == date( "Ym" ) || ( $m[ "Ym" ] > date( "Ym" ) && $a == 24 ) ){

            $tablas[ $a ]  = "\n<tr>
            <td class=\"".( $m[ "Ym" ] == date( "Ym" ) ? " fw-bold " : "" )."text-center\">{$a}</td>
            <td class=\"".( $m[ "Ym" ] == date( "Ym" ) ? " fw-bold " : "" )."text-start\">".strtoupper( $meses[ $a ] )."</td>
            <td class=\"".( $m[ "Ym" ] == date( "Ym" ) ? " fw-bold " : "" )."text-end\">$".number_format( $semilla[ $a ], 2 )."</td>
            <td class=\"".( $m[ "Ym" ] == date( "Ym" ) ? " fw-bold " : "" )."text-end\">$".number_format( $compuesto[ $a ], 2 )."</td>
            <td class=\"".( $m[ "Ym" ] == date( "Ym" ) ? " fw-bold " : "" )."text-center\">{$m[ "Porcentaje" ]}%</td>
            <td class=\"".( $m[ "Ym" ] == date( "Ym" ) ? " fw-bold " : "" )."text-end\">$".number_format( $m[ "rendimiento_dia" ], 2 )."</td>
            <td class=\"".( $m[ "Ym" ] == date( "Ym" ) ? " fw-bold " : "" )."text-end\">{$dias}</td>
            <td class=\"".( $m[ "Ym" ] == date( "Ym" ) ? " fw-bold " : "" )."text-end\">$".number_format( $r, 2 )."</td>
            <td class=\"".( $m[ "Ym" ] == date( "Ym" ) ? " fw-bold " : "" )."text-end\">$".number_format( $r + $compuesto[ $a ], 2 )."</td>
            <td class=\"".( $m[ "Ym" ] == date( "Ym" ) ? " fw-bold " : "" )."text-end\">".( $retiros[ $a ] ? "<i class=\"fa fa-arrow-down text-red\"></i>" : "$0.00" )."</td>
            <td class=\"".( $m[ "Ym" ] == date( "Ym" ) ? " fw-bold " : "" )."text-end\">$".number_format( $semilla[ $a ] + $compuesto[ $a ] + $r, 2 )."</td>
            </tr>";
            
            $tablas[ $a+1 ]  = "\n<tr>
            <td></td>
            <td class=\"\" colspan=\"5\" style=\"color:var(--bs-gray-500) !important\">Cantidades proyectadas al cierre de mes:</td>
            <td class=\"text-end\" style=\"color:var(--bs-gray-500) !important\">{$m[ "dias_en_mes" ]}</td>
            <td class=\"text-end\" style=\"color:var(--bs-gray-500) !important\">$".number_format( $m[ "rendimiento_mes" ], 2 )."</td>
            <td class=\"text-end\" style=\"color:var(--bs-gray-500) !important\">$".number_format( $m[ "rendimiento_mes" ] + $compuesto[ $a ], 2 )."</td>
            <td class=\"text-end\"><span class=\"".( $retiros[ $a ] ? "text-red" : "" )."\">$".number_format( $retiros[ $a ], 2 )."</td>
            <td class=\"text-end\" style=\"color:var(--bs-gray-500) !important\">$".number_format( $semilla[ $a ] + $compuesto[ $a ] + $m[ "rendimiento_mes" ] - $retiros[ $a ], 2 )."</td>
            </tr>";
    
        }
        else{
            $tablas[ $a ]  = "\n<tr>
            <td class=\"".( $m[ "Ym" ] == date( "Ym" ) ? " fw-bold " : "" )."text-center\">{$a}</td>
            <td class=\"".( $m[ "Ym" ] == date( "Ym" ) ? " fw-bold " : "" )."text-start\">".strtoupper( $meses[ $a ] )."</td>
            <td class=\"".( $m[ "Ym" ] == date( "Ym" ) ? " fw-bold " : "" )."text-end\">$".number_format( $semilla[ $a ], 2 )."</td>
            <td class=\"".( $m[ "Ym" ] == date( "Ym" ) ? " fw-bold " : "" )."text-end\">$".number_format( $compuesto[ $a ], 2 )."</td>
            <td class=\"".( $m[ "Ym" ] == date( "Ym" ) ? " fw-bold " : "" )."text-center\">{$m[ "Porcentaje" ]}%</td>
            <td class=\"".( $m[ "Ym" ] == date( "Ym" ) ? " fw-bold " : "" )."text-end\">$".number_format( $m[ "rendimiento_dia" ], 2 )."</td>
            <td class=\"".( $m[ "Ym" ] == date( "Ym" ) ? " fw-bold " : "" )."text-end\">{$dias}</td>
            <td class=\"".( $m[ "Ym" ] == date( "Ym" ) ? " fw-bold " : "" )."text-end\">$".number_format( $r, 2 )."</td>
            <td class=\"".( $m[ "Ym" ] == date( "Ym" ) ? " fw-bold " : "" )."text-end\">$".number_format( $r + $compuesto[ $a ], 2 )."</td>
            <td class=\"".( $m[ "Ym" ] == date( "Ym" ) ? " fw-bold " : "" )."text-end\"><span class=\"".( $retiros[ $a ] ? "text-red" : "" )."\">$".number_format( $retiros[ $a ], 2 )."</span></td>
            <td class=\"".( $m[ "Ym" ] == date( "Ym" ) ? " fw-bold " : "" )."text-end\">$".number_format( $semilla[ $a ] + $compuesto[ $a ] + $r - $retiros[ $a ], 2 )."</td>
            </tr>";
        }
    }
}






$bt = balance_inversion( $i );

echo "\n
    <div class=\"card mb-4\" inversion=\"{$i[ "id" ]}\" rendimiento=\"{$h}\" mes=\"{$i[ "extras" ][ "meses" ][ $mes_actual ][ "rendimiento_mes" ]}\">
        <div class=\"card-header\">
            <div class=\"row\">
                <div class=\"col-2 col-lg-1\">
                    <img src=\"".base_url()."assets/img/productos/{$i[ "producto_codigo" ]}.png\" style=\"width:60px\">
                </div>
                <div class=\"col-10 col-lg-2 pt-2\">
                    <h5 class=\"m-0 text-{$p->data->color}\">{$p->data->nombre}</h5>
                    ".estatus( $i[ "estatus_codigo" ] )."
                </div>

                <div class=\"col-lg-4 text-center\">
                    <span style=\"display:block; width:100%\" class=\"mt-2 fs-3 badge bg-gray-300 text-marine\"><img src=\"https://static.tronscan.org/production/logo/usdtlogo.png\" style=\"width:24px\"> $".number_format( $bt[ "total" ], 2 )."</span>
                </div>

                <div class=\"col-lg-5\">
                    <p class=\"text-center text-marine mt-1 mb-0 fw-bold \">Día {$transcurridos} de {$total_dias} / Mes ".($mes_actual)." de 24</p>
                    <div class=\"progress\" role=\"progressbar\" aria-valuemin=\"0\" aria-valuemax=\"100\" style=\"height:24px; border-radius:10px\">
                        <div class=\"progress-bar bg-".( $porc_bono == 100 ? "gray-500" : "teal" )."\" style=\"width: {$porc_bono}%\">".( $porc_bono == 100 ? "INVERSIÓN FINALIZADA" : $porc_bono."%" )."</div>
                    </div>                                  
                </div>

            </div>

        </div>

        <div class=\"card-body pt-3 \">
            <h5 class=\"text-center text-gray-400 mb-3 mb-lg-3\">{$hash}</h5>
            <div class=\"row\">
                <div class=\"col-lg-6\">
                    <table class=\"table table-sm m-0\">
                        <tr>
                            <td>Fecha de compra</td>
                            <td class=\"text-end\">".fecha( $i[ "fechas" ][ "pagado" ] )."</td>
                        </tr>
                        <tr>
                            <td>Inicio de inversión</td>
                            <td class=\"text-end\">".fecha( $f_i )."</td>
                        </tr>
                        <tr>
                            <td>Cierre de inversión</td>
                            <td class=\"text-end\">".fecha( $i[ "extras" ][ "meses" ][ 24 ][ "termina" ] )."</td>
                        </tr>
                        <tr>
                            <td>Periodo de inversión</td>
                            <td class=\"text-end\">24 meses</td>
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
                            <td>Rendimiento total</td>
                            <td class=\"text-end\">$".number_format( $bt[ "suma" ], 2 )."</td>
                        </tr>
                        <tr>
                            <td>Retiros</td>
                            <td class=\"text-end\"><span class=\"text-red \">$".number_format( $bt[ "retiros" ], 2 )."</span></td>
                        </tr>
                        <tr>
                            <td>Rendimiento actual</td>
                            <td class=\"text-end\">$".number_format( $bt[ "full" ], 2 )."</td>
                        </tr>  
                        <tr>
                            <td>Balance de cuenta</td>
                            <td class=\"text-end\">$".number_format( $bt[ "total" ], 2 )."</td>
                        </tr>                                                     
                    </table>  
                    
                </div>
            </div>
                
        </div>
    </div>
    
    <script> 
        chart = { 
            \"id\": {$i[ "id" ]}, 
            \"meses\": [ \"".implode( "\", \"", $meses )."\" ],
            \"valores\" : [
                {\"name\":\"Capital semilla\",\"data\":[ ".implode( ", ", $semilla )." ]},{\"name\":\"Interés compuesto\",\"data\":[ ".implode( ", ", $compuesto )." ]},{\"name\":\"Rendimiento\",\"data\":[ ".implode( ", ", $rendimiento )." ]}
            ]
        };
    </script>
        ";

    echo "<div class=\"card mb-4\"><div class=\"card-header bg-marine\"><h5 class=\"m-0 text-white\">Estado de cuenta</h5></div><table class=\"table table-striped m-0\"><thead>
        <tr>
            <th class=\"text-center\">No.</th>
            <th class=\"text-start\">Mes</th>
            <th class=\"text-end\">Cap. Semilla</th>
            <th class=\"text-end\">Int. Compuesto</th>
            <th class=\"text-center\">Porcentaje</th>
            <th class=\"text-end\">Rend. x día</th>
            <th class=\"text-end\">Días</th>
            <th class=\"text-end\">Rend del mes</th>
            <th class=\"text-end\">Rend acumulado</th>
            <th class=\"text-end\">Retiros</th>
            <th class=\"text-end\">Saldo final</th>
        </tr>
    </thead><tbody>";
    
    foreach( $tablas as $tabla ){
        echo $tabla;
    }

    echo "</tbody></table></div></div>";
?>
        
<script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>

<div class="row">
    <div class="col-4">
    <h4 class="mt-1 mb-0"><?php echo $titulo; ?></h4>

    </div>

    <div class="col-8 text-end pt-2 pb-4">

        <table align="right"><tr><td>
            <?php
            $directos = $usuario->getDirectosActivos( "50-INVERSION" );
            $rango    = $usuario->getRangoInversion( sizeof( $directos ) );
            ?>
            <h5 style="line-height:0" class="m-0">
                <img src="<?php echo base_url()."assets/img/rangos/".$rango[ "codigo" ]; ?>.png" style="width:50px" alt="">
                <?php echo $rango[ "nombre" ]; ?>
            </h5>
        </td><td nowrap>
            &nbsp; <a class="btn btn-primary" href="<?php echo base_url( "rangos_paquetes" ); ?>"><i class="fa fa-magnifying-glass"></i> Detalles de rangos</a>
        </td></tr></table>
    </div>
</div>


<script> var chart = []; </script>
<?php 

$inversiones = $socio ? $socio->get_inversiones() : $usuario->get_inversiones();

if( sizeof( $inversiones ) ){
    
    foreach( $inversiones as $i ){

        $p   = model( "ProductoModel" )->find( $i[ "producto_codigo" ] );
        $f_i = get_fecha_inversion( $i[ "fechas" ][ "pagado" ] ); 

        /* if( $f_i < "2025-03-01" ){
            $f_i = "2025-03-01";
        } */

        if( !isset($i[ "extras" ][ "meses" ][ 0 ] ) || !isset( $i[ "extras" ][ "refresh" ] ) ){
            $pedido = model( "PedidoModel" )->find( $i[ "pedido_id" ] );

            $ms = genera_meses( $pedido, $i[ "id" ], $p );

            $i[ "extras" ][ "meses" ] = $ms[ 0 ];
            $i[ "extras" ][ "semilla_retirada" ] = $ms[ 1 ];
            $i[ "extras" ][ "refresh" ] = date( "Y-m-d" );

            model( "InversionModel" )->save( $i );
        }

        $date1 = new DateTime( $f_i );
        $date2 = new DateTime( $i[ "extras" ][ "meses" ][ sizeof( $i[ "extras" ][ "meses" ] ) -1 ][ "termina" ] );
        $interval = $date1->diff( $date2 );
        $total_dias = $interval->days + 1;

        if( $i[ "estatus_codigo" ] != "140-SUSPENDIDO" && $date2->format( "Y-m-d" ) < date( "Y-m-d" ) ){
            $i[ "estatus_codigo" ] = "140-SUSPENDIDO";

            model( "InversionModel" )->save( $i );
        }

        $sql = "SUBSTRING( estatus_codigo, 1, 3 ) > 200 
                AND inversion_id = {$i[ "id" ]} 
                AND json_unquote( json_extract( fechas, '$.mes' ) ) = '{$i[ "extras" ][ "meses" ][ 24 ][ "Ym" ]}' ";

        $rts = model( "RetiroModel" )->where( $sql )->findAll();

        if( !sizeof( $rts ) && $i[ "extras" ][ "meses" ][ 24 ][ "inicia" ] <= date( "Y-m-d" ) ){
            $i = crea_retiro_final( $i);
        }

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
            $hash = "<span class=\"text-mustard\"><i class=\"fa fa-warning\"></i> Este paquete aun no cuenta con TxHash</span> <button class=\"d-none btn btn-sm btn-warning\" onclick=\"carga_hash( {$i[ "id" ]} )\"><i class=\"fa fa-plus\"></i> Agregar ahora</button>";
        }

        $mes_actual  = sizeof( $i[ "extras" ][ "meses" ] );
        $meses       = [];
        $semilla     = [];
        $compuesto   = [];
        $rendimiento = [];
        $r = 0;
        $h = 0;

        for( $a = 0; $a < sizeof( $i[ "extras" ][ "meses" ] ); $a++ ){
            $m = $i[ "extras" ][ "meses" ][ $a ];

            /**********************************************************/
            // QUITAR RENDIMIENTOS

            $m[ "rendimiento_dia" ] = 0;
            $m[ "rendimiento_mes" ] = 0;
            $m[ "porcentaje" ] = 0;
            $m[ "compuesto" ] = 0;
            $m[ "retiros" ] = 0;
            /**********************************************************/


            if( $m[ "Ym" ] < date( "Ym" ) ){
                $semilla[]     = $m[ "semilla" ];
                $r  = $m[ "rendimiento_mes" ];
                $h += $m[ "rendimiento_mes" ] - $m[ "retiros" ];
                $compuesto[] = $m[ "compuesto" ];
            }

            elseif( $m[ "Ym" ] == date( "Ym" ) ){
                $mes_actual  = $a;
                $semilla[]   = $m[ "semilla" ];
                $compuesto[] = $m[ "compuesto" ];
                $h += $m[ "rendimiento_mes" ] - $m[ "retiros" ];
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
            }

            $rendimiento[] = $r;
            $meses[] = mes( substr( $m[ "Ym" ], 4, 2 ), 3 )." ".substr( $m[ "Ym" ], 2, 2 );
        }

        $bt = balance_inversion( $i );

        $retiros_pendientes = "";
        $retiros = model( "RetiroModel" )->where( ( session( "admin" ) ? "" : "JSON_UNQUOTE( JSON_EXTRACT( fechas, '$.mes' ) ) = '".date( "Ym" )."' AND" )." substring( estatus_codigo,1,3 ) between 160 and IF( JSON_UNQUOTE( JSON_EXTRACT( fechas, '$.mes' ) ) = '".date( "Ym" )."', 300, 200 ) AND inversion_id = {$i[ "id" ]} and tipo in ( 'TOTAL', 'PARCIAL', 'MENSUAL' )" )->findAll();

        if( sizeof( $retiros ) ){
            $retiros_pendientes = "<table class=\"table table-sm m-0\">";

            foreach( $retiros as $retiro ){

                $br = $retiro[ "estatus_codigo" ] == "165-ESPERANDO-CODIGO" ? "border:none" : "";

                if( $retiro[ "estatus_codigo" ] == "165-ESPERANDO-CODIGO" ){

                    $a = [ $usuario->password_original().$i[ "extras" ][ "TxHash" ], $retiro[ "id" ] ];
                    $url = base_url()."confirma_retiro/".urlencode( base64_encode( json_encode( $a ) ) );

                    $retiros_pendientes .= "<tr><td colspan=\"3\"><div class=\"card border-red text-red\"><div class=\"card-header\"><i class=\"fa fa-warning\"></i> <strong>Solicitud de retiro de <span class=\"badge bg-red\">PRODUCTOS</span> <span class=\"badge bg-marine\">".id( $retiro[ "id" ], 5 )."</span> pendiente de confirmación.</strong></div><div class=\"card-body text-red\">Debes confirmar tu solicitud de retiro haciendo click en el enlace que hemos enviado a tu correo electrónico (el mensaje puede tardar hasta 10 minutos en llegar).<br><p class=\"text-end m-0\"><button class=\"btn btn-sm btn-light text-red\" onclick=\"cancela_retiro( {$retiro[ "id" ]} )\"><i class=\"fa fa-times\"></i> cancelar </button> ";

                    if( session( "admin" ) && session( "admin" ) != urlencode( base64_encode( $usuario->password_original() ) ) ){
                        $retiros_pendientes .= "<a href=\"{$url}\" class=\"btn btn-sm btn-danger\"> ADMIN: Confirmar retiro</a>"; 
                    }

                    $retiros_pendientes .= "</p></div></div></td></tr>";
                }
                else{
                     $retiros_pendientes .= "\n<tr class=\"\">
                        <td style=\"{$br}\">Solicitud de retiro de <i class=\"fa fa-sack-dollar text-green\"></i> PRODUCTOS</td>
                        <td style=\"{$br}\">".( $retiro[ "estatus_codigo" ] == "255-PENDIENTE" ? estatus( "522-CONFIRMADO" ) : "" )." ".estatus( $retiro[ "estatus_codigo" ] )."</td>
                        <td style=\"{$br}\" width=\"25%\" class=\"text-end\"><span class=\"text-red\"><button class=\"btn btn-sm btn-link text-gray-500\" onclick=\"cancela_retiro( {$retiro[ "id" ]} )\" style=\"text-decoration:none\"><i class=\"fa fa-times\"></i> cancelar </button> &nbsp; $".number_format( $retiro[ "cantidad" ], 2 )."</span></td></tr>";
                }

                
            }

            $retiros_pendientes .= "</table>";
        }

        $semilla_pendientes = "";
        $retiros = model( "RetiroModel" )->where( ( session( "admin" ) ? "" : "JSON_UNQUOTE( JSON_EXTRACT( fechas, '$.mes' ) ) = '".date( "Ym" )."' AND " )."substring( estatus_codigo,1,3 ) between 160 and IF( JSON_UNQUOTE( JSON_EXTRACT( fechas, '$.mes' ) ) = '".date( "Ym" )."', 300, 200 ) AND inversion_id = {$i[ "id" ]} and tipo in ( 'STOTAL', 'SPARCIAL' )" )->findAll();

        if( sizeof( $retiros ) ){
            $semilla_pendientes = "<table class=\"table table-sm m-0\">";


            foreach( $retiros as $retiro ){
                $br  = $retiro[ "estatus_codigo" ] == "165-ESPERANDO-CODIGO" ? "border:none" : "";

              

                if( $retiro[ "estatus_codigo" ] == "165-ESPERANDO-CODIGO" ){
                    $a   = [ $usuario->password_original().$i[ "extras" ][ "TxHash" ], $retiro[ "id" ] ];
                    $url = base_url()."confirma_retiro/".urlencode( base64_encode( json_encode( $a ) ) );

                    $semilla_pendientes .= "<tr><td colspan=\"3\"><div class=\"card border-red text-red\"><div class=\"card-header\"><i class=\"fa fa-warning\"></i> <strong>Solicitud de retiro de <span class=\"badge bg-red\">PAQUETES</span> <span class=\"badge bg-marine\">".id( $retiro[ "id" ], 5 )."</span> pendiente de confirmación.</strong></div><div class=\"card-body text-red\">Debes confirmar tu solicitud de retiro haciendo click en el enlace que hemos enviado a tu correo electrónico (el mensaje puede tardar hasta 10 minutos en llegar).<p class=\"text-end m-0\"><button class=\"btn btn-sm btn-light text-red\" onclick=\"cancela_retiro( {$retiro[ "id" ]} )\"><i class=\"fa fa-times\"></i> cancelar </button> ";
                    
                    if( session( "admin" ) && session( "admin" ) != urlencode( base64_encode( $usuario->password_original() ) ) ){
                        $semilla_pendientes .= "<a href=\"{$url}\" class=\"btn btn-sm btn-danger\">ADMIN: Confirmar retiro</a>"; 
                    }
                    
                    $semilla_pendientes .= "</p></div></div></td></tr>";
                }                    
                else{
                    $semilla_pendientes .= "\n<tr class=\"\">
                        <td style=\"{$br}\">Solicitud de retiro de <i class=\"fa fa-seedling text-red\"></i> PAQUETES</td>
                        <td style=\"{$br}\">".( $retiro[ "estatus_codigo" ] == "255-PENDIENTE" ? estatus( "522-CONFIRMADO" ) : "" )." ".estatus( $retiro[ "estatus_codigo" ] )."</td>
                        <td style=\"{$br}\" width=\"25%\" class=\"text-end\"><span class=\"text-red\"><button class=\"btn btn-sm btn-link text-gray-500\" onclick=\"cancela_retiro( {$retiro[ "id" ]} )\" style=\"text-decoration:none\"><i class=\"fa fa-times\"></i> cancelar </button> &nbsp; $".number_format( $retiro[ "cantidad" ], 2 )."</span></td></tr>";
                }
            }

            $semilla_pendientes .= "</table>";
        }

        $nueve_finalizada = $p->data->porcentaje == 9 && $i[ "extras" ][ "meses" ][ 24 ][ "Ym" ] <= date( "Ym" );
        $aviso_semilla = aviso_semilla( $i, $p );

        $v = $usuario->get_verificacion( "50-INVERSION" );

        if( !isset( $i[ "extras" ][ "meses" ][ $mes_actual ] ) ){
            $mes_actual = 0;
        }

        switch( $i[ "extras" ][ "meses" ][ $mes_actual ][ "Porcentaje" ] ){
            case "3" : $r_mensual = "1 a 3"; break;
            case "6" : $r_mensual = "3 a 6"; break;
            case "9" : $r_mensual = "6 a 8"; break;
            case "" : $r_mensual = "pendiente..."; break;
        }

        echo "\n
            <div class=\"card mb-5\" semilla=\"{$m[ "semilla" ]}\" inversion=\"{$i[ "id" ]}\" rendimiento=\"{$bt[ "finmes" ]}\" mes=\"{$i[ "extras" ][ "meses" ][ $mes_actual ][ "rendimiento_mes" ]}\" aviso_semilla=\"{$aviso_semilla}\">
                <div class=\"card-header\">
                    <div class=\"row\">
                        <div class=\"col-2 col-lg-1\">
                            <img src=\"".base_url()."assets/img/productos/{$i[ "producto_codigo" ]}.png\" style=\"width:60px\">
                        </div>
                        <div class=\"col-10 col-lg-2 pt-2\">
                            <h5 class=\"m-0 text-{$p->data->color}\">{$p->data->nombre}</h5>
                            <span class=\"badge bg-gray-500\">{$i[ "id" ]}</span> ".estatus( $i[ "estatus_codigo" ] )."
                        </div>

                        <div class=\"col-lg-4 text-center\">
                            <span style=\"display:block; width:100%\" class=\"mt-2 fs-3 badge bg-gray-300 text-marine\"><img src=\"https://static.tronscan.org/production/logo/usdtlogo.png\" style=\"width:24px\"> $".number_format( $bt[ "total" ], 2 )."</span>
                        </div>

                        <div class=\"col-lg-5\">
                            <p class=\"text-center text-marine mt-1 mb-0 fw-bold \">Día {$transcurridos} de {$total_dias} / Mes ".($mes_actual )." de ".sizeof( $i[ "extras" ][ "meses" ] )."</p>
                            <div class=\"progress\" role=\"progressbar\" aria-valuemin=\"0\" aria-valuemax=\"100\" style=\"height:24px; border-radius:10px\">
                                <div class=\"progress-bar bg-".( $porc_bono == 100 ? "gray-500" : "teal" )."\" style=\"width: {$porc_bono}%\">".( $porc_bono == 100 ? "INVERSIÓN FINALIZADA" : $porc_bono."%" )."</div>
                            </div>                                  
                        </div>

                    </div>

                </div>

                <div class=\"card-body pt-3 pb-0\">
                    <div class=\"row\">
                        <div class=\"col-lg-7\">
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
                                            <td class=\"text-end\">".fecha( $i[ "extras" ][ "meses" ][ sizeof( $i[ "extras" ][ "meses" ] ) -1 ][ "termina" ] )."</td>
                                        </tr>
                                        <tr>
                                            <td>Periodo de inversión</td>
                                            <td class=\"text-end\">".sizeof( $i[ "extras" ][ "meses" ] )." meses</td>
                                        </tr>                                        
                                        <tr>
                                            <td>Rendimiento mensual</td>
                                            <td class=\"text-end\">{$r_mensual} %</td>
                                        </tr>
                                    </table>
                                    
                                </div>
                                
                                <div class=\"col-lg-6\">
                                    <table class=\"table table-sm m-0\">
                                
                                        <tr>
                                            <td>Paquete inicial</td>
                                            <td class=\"text-end\">$".number_format( $bt[ "semilla_inicial" ], 2 )."</td>
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
                            {$retiros_pendientes}
                            {$semilla_pendientes}

                            <div class=\"row mb-3 mt-lg-0 \">
                                <div class=\"col-lg-6\"><a href=\"".base_url()."statement/".urlencode( base64_encode( $i[ "extras" ][ "TxHash" ] ) )."\" class=\"btn xbtn-lg mt-4 btn-outline-info w-100\"><i class=\"fa fa-magnifying-glass\"></i> Detalles de cuenta</a></div>
                                ".( 0 && $bt[ "total" ] && !$socio && ( !$retiros_pendientes || !$semilla_pendientes ) && $p->data->porcentaje != 9 && $i[ "extras" ][ "meses" ][ 24 ][ "termina" ] > date( "Y-m-d" ) ? "
                                <div class=\"col-lg-6\">
                                
                                <button class=\"d-none btn xbtn-lg btn-outline-danger w-100 mt-4 \" onclick=\"ask_retiro({$i[ "id" ]})\"><i class=\"fa fa-right-from-bracket\"></i> Retirar productos</button>
                                
                                <div class=\"dropdown\">
                                    <button class=\"btn w-100 mt-4 btn-outline-danger dropdown-toggle\" type=\"button\" data-bs-toggle=\"dropdown\" aria-expanded=\"false\">
                                        <i class=\"fa fa-right-from-bracket\"></i> Programar retiro
                                    </button>
                                    

                                    <ul class=\"dropdown-menu\">
                                    ".( $v->estatus || session( "admin" ) ? "

                                    ".( !$retiros_pendientes ? "
                                        <li><a class=\"dropdown-item\" href=\"javascript:ask_retiro({$i[ "id" ]})\"><i class=\"fa fa-sack-dollar text-green\"></i> Retiro de productos</a></li>
                                      
                                    " : "" ).( !$semilla_pendientes ? "
                                        <li><a class=\"dropdown-item\" href=\"javascript:ask_semilla({$i[ "id" ]})\"><i class=\"fa fa-seedling text-".( $aviso_semilla ? "red" : "green" )."\"></i> Retiro de paquetes</a></li>
                                    " : "" )."

                                        " : "<li><span class=\"dropdown-item text-red\"><i class=\"fa fa-warning\"></i> Necesitas verificar tu cuenta para realizar retiros</span></li>" )."
                                    </ul>
                                </div>
                                
                                </div>" : "" ) ."             
                            </div>
                            
                            
                        </div>

                        <div class=\"col-lg-5\"><div id=\"chart_{$i[ "id" ]}\"></div></div>
                    </div>   
                    
                        
                </div>
            </div>
            
            <script> 
                chart.push( { 
                    \"id\": {$i[ "id" ]}, 
                    \"meses\": [ \"".implode( "\", \"", $meses )."\" ],
                    \"valores\" : [
                        {\"name\":\"Paquetes\",\"data\":[ ".implode( ", ", $semilla )." ]},{\"name\":\"Interés compuesto\",\"data\":[ ".implode( ", ", $compuesto )." ]},{\"name\":\"Productos\",\"data\":[ ".implode( ", ", $rendimiento )." ]}
                    ]
                });
            </script>
                ";
    }




}else{
    echo "<div class=\"row m-3\" style=\"zoom:3\"><div class=\"col-4 display-3 text-gray-300 text-end\"><i class=\"fa fa fa-box\"></i></div><div class=\"col-8 pt-3 mt-3 text-gray-500 text-start\">Aun no tienes paquetes</div></div>";
}


// $resultado = generarSerieMensual(1, 3, '2026-02-14');


if( 0 && sizeof( $inversiones ) ){
?>  

    <div class="modal" tabindex="-1" id="stock_modal">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header bg-red">
                    <div class="modal-title me-3">
                        <h5 class="text-white m-0"><i class="fa fa-right-from-bracket"></i> Programar retiro de productos</h5>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>

                <form action="<?php echo base_url( "crea_retiro" ); ?>" method="post">
                    <?php echo csrf_field(); ?>
                    <input type="hidden" name="inversion_id" value="">
                    <input type="hidden" name="tipo" value="rendimiento">
                    <div class="modal-body">

                        <div class="row mb-3">
                            <div class="col-lg-4 <?php echo $nueve_finalizada ? "d-none" : ""; ?>">
                                <input type="radio" class="btn-check" name="opciones_retiro" id="type_1" autocomplete="off" value="1">
                                <label class="btn btn-outline-info text-start w-100 mb-2" for="type_1">
                                    <p class="fs-4">Retiro mensual</p>                                   
                                    <p>Retirar productos del mes actual</p>
                                    <input readonly value="" id="cantidad_1" name="mes" class="cantidades form-control text-center mb-1"></i>
                                </label>
                            </div>

                            <div class="col-lg-4">
                                <input type="radio" class="btn-check" name="opciones_retiro" id="type_2" autocomplete="off" value="2">
                                <label class="btn btn-outline-info text-start w-100 mb-2" for="type_2">
                                    <p class="fs-4">Retiro total</p>                                   
                                    <p>Retirar el total de productos acumulados</p>
                                    <input readonly value="" id="cantidad_2" name="total" class="cantidades form-control text-center mb-1"></i>
                                </label>
                            </div>

                            <div class="col-lg-4 <?php echo $nueve_finalizada ? "d-none" : ""; ?>">
                                <input type="radio" class="btn-check" name="opciones_retiro" id="type_3" autocomplete="off" value="3">
                                <label class="btn btn-outline-info text-start w-100 mb-2" for="type_3">
                                    <p class="fs-4">Retiro parcial</p>                                   
                                    <p>Retirar una cantidad específica menor al total</p>
                                    <input type="number" step="0.01" class="cantidades form-control text-center mb-1" id="cantidad_3" name="custom"></i>
                                </label>
                            </div>
                        </div>

                        <?php if( session( "admin" ) || ( isset( $usuario->data->wallet ) && strlen( $usuario->data->wallet ) == 34 ) ) { ?>

                            <div class="row mb-3">
                                <div class="col-lg-8 small">
                                    <strong> La solicitud será procesada al finalizar el mes seleccionado y la transferencia se aplicará en los primeros 3 días hábiles del mes siguiente. </strong>
                                </div>
                                <div class="col-lg-4">
                                    <select name="mes_apply" class="form-select">
                                        <?php
                                            $date = new DateTime( date( "Y-m-d" ) );
                                            $date->modify( "first day of this month" );
                                                                            
                                            do{
                                                echo "\n<option ".( $date->format( "Ym" ) == date( "Ym" ) ? "selected" : "" )." value=\"".$date->format( "Ym" )."\">".mes( $date->format( "m" ) )." ".$date->format( "Y" )."</option>";
                                                $date->modify( "- 1 month" );
                                            }
                                            while( intval( $date->format( "Ym" ) ) >= ( session( "admin" ) ? 202408 : date( "Ym" ) ) );
                                        ?>
                                    </select>
                                </div>
                            </div>

                            <div class="alert alert-danger py-2">
                                <i class="fa fa-warning"></i> <strong>IMPORTANTE:</strong> Aunque Beneleit no te cobra comisiones al retirar tus productos, la red TRON/USDT genera en automático una tarifa de transacción variable entre <strong>$7.00 y $10.00 USD</strong> que será descontada del monto de tu retiro. Te sugerimos considerarlo al momento de crear tu solicitud.
                            </div>

                        <?php } else { ?>
                            <div class="alert alert-danger mb-0">
                            No puedes programar retiros en este momento. No existe una dirección (wallet) para recepción de transferencias. Registrala en tu <a href="<?php echo base_url(); ?>perfil">perfil de usuario</a>.
                            </div>
                        <?php }?>

                    </div>

                    <?php if( session( "admin" ) || ( isset( $usuario->data->wallet ) && strlen( $usuario->data->wallet ) == 34 ) ) { ?>
                        <div class="modal-footer">
                            <button type="submit" name="submit_socio" value="1" class="btn btn-outline-danger my-2" disabled id="confirma_agregar">Programar retiro</button>
                        </div>
                    <?php } ?>
                </form>
            </div>
        </div>
    </div>

    <div class="modal" tabindex="-1" id="semilla_modal">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header bg-red">
                    <div class="modal-title me-3">
                        <h5 class="text-white m-0"><i class="fa fa-right-from-bracket"></i> Programar retiro de paquetes</h5>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>

                <form action="<?php echo base_url( "crea_retiro" ); ?>" method="post">
                    <?php echo csrf_field(); ?>
                    <input type="hidden" name="inversion_id" value="">
                    <input type="hidden" name="tipo" value="semilla">
                    <div class="modal-body">
                        <input type="radio" class="d-none" name="opciones_semilla" id="stype_1" autocomplete="off" value="1">

                        <div class="row mb-3">

                            <div class="col-lg-6">
                                <input type="radio" class="btn-check" name="opciones_semilla" id="type_4" autocomplete="off" value="4">
                                <label class="btn btn-outline-info text-start w-100 mb-2" for="type_4">
                                    <p class="fs-4">Retiro total</p>                                   
                                    <p>Retirar el total de paquetes</p>
                                    <input readonly value="" id="semilla_2" name="total" class="cantidades form-control text-center mb-1"></i>
                                    </label>
                            </div>

                            <div class="col-lg-6">
                                <input type="radio" class="btn-check" name="opciones_semilla" id="type_5" autocomplete="off" value="5">
                                <label class="btn btn-outline-info text-start w-100 mb-2" for="type_5">
                                    <p class="fs-4">Retiro parcial</p>                                   
                                    <p>Retirar una cantidad específica menor al total</p>
                                    <input type="number" step="0.01" class="cantidades form-control text-center mb-1" id="semilla_3" name="custom"></i>
                                </label>
                            </div>
                        </div>

                        <?php if( session( "admin" ) || ( isset( $usuario->data->wallet ) && strlen( $usuario->data->wallet ) == 34 ) ) { ?>

                            <div class="row mb-3">
                                <div class="col-lg-7 small">
                                    <strong> La solicitud será procesada al finalizar el mes seleccionado y la transferencia se aplicará en los primeros 3 días hábiles del mes siguiente. </strong>
                                </div>
                                <div class="col-lg-5">
                                    <select name="mes_apply" class="form-select">
                                        <?php
                                            $date = new DateTime( date( "Y-m-d" ) );
                                            $date->modify( "first day of this month" );
                                                                            
                                            do{
                                                echo "\n<option ".( $date->format( "Ym" ) == date( "Ym" ) ? "selected" : "" )." value=\"".$date->format( "Ym" )."\">".mes( $date->format( "m" ) )." ".$date->format( "Y" )."</option>";
                                                $date->modify( "- 1 month" );
                                            }
                                            while( intval( $date->format( "Ym" ) ) >= ( session( "admin" ) ? 202408 : date( "Ym" ) ) );
                                        ?>
                                    </select>
                                </div>
                            </div>

                                <div class="alert alert-danger py-2" id="aviso_semilla">
                                    <i class="fa fa-warning"></i> <strong>IMPORTANTE: Se aplicará un cargo del 25% de la cantidad a retirar y el porcentaje de productos será reducido a la mitad.</strong> Aunque Beneleit no te cobra comisiones al retirar tus productos, la red TRON/USDT genera en automático una tarifa por transacción de <strong>$7.00 USD</strong> que será descontada de tu retiro. Considera esto al momento de crear tu solicitud.
                                </div>
                                <?php 
                            } 
                            else { ?>
                            <div class="alert alert-danger mb-0">
                            No puedes programar retiros en este momento. No existe una dirección (wallet) para recepción de transferencias. Registrala en tu <a href="<?php echo base_url(); ?>perfil">perfil de usuario</a>.
                            </div>
                        <?php }?>

                    </div>

                    <?php if( session( "admin" ) || ( isset( $usuario->data->wallet ) && strlen( $usuario->data->wallet ) == 34 ) ) { ?>
                        <div class="modal-footer">
                            <button type="submit" name="submit_socio" value="1" class="btn btn-outline-danger my-2" disabled id="confirma_semilla">Programar retiro</button>
                        </div>
                    <?php } ?>
                </form>
            </div>
        </div>
    </div>    

<?php } ?>

<div class="modal" tabindex="-1" id="carga_hash">
	<div class="modal-dialog modal-lg">
		<div class="modal-content">
			<div class="modal-header bg-mustard">
				<div class="modal-title">
                    <h5 class="text-white m-0"><i class="fa fa-qrcode"></i> Actualizar TxHash de inversión</h5>
				</div>
				<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
			</div>
            <div id="loader" class="modal-body text-center">
                <img src="<?php echo base_url(); ?>assets/img/loader.gif" style="width:150px; height:150px; opacity:0.4" class="m-5">
            </div>            
            <div class="modal-body text-center" id="principal">
                <p class="text-center">
                <h3 class="text-center">Pega aquí tu TxHash:</h3>
                    <input type="text" class="form-control text-center border-3 border-teal" name="_txhash">
                    <pre id="error" class="mt-2 alert alert-danger text-center" style="display:none"></pre>
                </p>

                <p class="text-end mt-4 mb-0"><button class="btn btn-warning my-2" id="confirma_hash"><i class="fa fa-check"></i> Registrar inversión</button></p>
            </div>
		</div>
	</div>
</div>


<div class="modal" tabindex="-1" id="cancela_retiro">
	<div class="modal-dialog">
		<div class="modal-content">
			<div class="modal-header bg-red">
				<div class="modal-title">
                    <h5 class="text-white m-0"><i class="fa fa-trash"></i> Cancelar solicitud de retiro</h5>
				</div>
				<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
			</div>
            <div class="modal-body text-center">
                <p class="text-center">¿Estas seguro de cancelar esta solicitud?</p>

                <form method="post" action="<?php echo base_url(); ?>cancela_retiro">
                <?php echo csrf_field(); ?>
                <input type="hidden" name="solicitud_id" value="">

                <p class="text-end mt-4 mb-0"><button class="btn btn-danger" type="submit"><i class="fa fa-check"></i> Continuar</button></p>
                </form>
            </div>
		</div>
	</div>
</div>


<script>
    var aviso_semilla = <?php echo $aviso_semilla ?? 1; ?>;
</script>
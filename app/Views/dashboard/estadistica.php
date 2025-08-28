
<img style="position:absolute; right:20px; top:30px; width:120px" src="<?php echo base_url(); ?>assets/img/logo_color.png">
<h4 class="mt-1"><?php echo $titulo; ?></h4>
<p><a class="btn btn-sm btn-light" href="<?php echo base_url( "sociodata/".urlencode( base64_encode( $socio->password_original() ) ) ); ?>"><i class="fa fa-undo"></i> Regresar a detalles de socio</a></p>

 <p class="text-end">
            <?php echo $socio->avatar()." ".$socio->id( $modelo )." ".$socio->nombre( 2 ); ?>
        </p>

<ul class="nav nav-tabs">
    <?php
    foreach( MODELOS as $m ){
        echo "\n<li class=\"nav-item\"><a class=\"nav-link ".( $m[ "codigo" ] == $modelo ? "active fw-bold" : "" )."\" aria-current=\"page\" href=\"".base_url( "estadistica/".urlencode( base64_encode( $socio->password_original() ) )."/".$m[ "codigo" ] )."\"><span class=\"text-{$m[ "settings" ][ "color" ]}\"><i class=\"fa fa-{$m[ "settings" ][ "icono" ]}\"></i> {$m[ "nombre" ]}</span></a></li>";
    }
    ?>

</ul>

<div class="card mb-5" style="border-color: var(--bs-border-color); border-top:none; border-radius: 0 0 6px 6px">
    <div class="card-body">
       <table class="w-100">
            <tr>    
                <td class="text-center py-3" style="width: 40%" colspan="2">
                    <table align="center">
                        <tr>
                            <?php 
                            for( $i = 1; $i <= MODELOS[ $modelo][ "settings" ][ "niveles" ]; $i++ ){ 
                                echo "\n<td style=\"padding-right: 5px\"><div class=\"input-group input-group-sm\">
                                        <span class=\"input-group-text bg-gray-200 text-gray-500 border-gray-200 fw-bold\" id=\"basic-addon1\">{$i}°</span>
                                        <input style=\"display:inline-block; width:50px\" type=\"text\" class=\"form-control border-gray-200\" value=\"".number_format( $stats[ "202508" ][ "niveles" ][ $i ] )."\">
                                        </div></td>";
                            //    echo "\n<td class=\"text-center\"><span class=\"small text-center\">{$i}</span><h5 class=\"my-0 mx-3\">{}</h5></td>"; 
                            }  
                            ?>
                            <td>
                    <h2 class="my-0 mx-3">
                        <?php echo number_format( array_sum( $stats[ "202508" ][ "niveles" ] ) ); ?>
                    </h2>
                            </td>
                        </tr>
                    </table>
                    Socios activos en la red
                </td>
                <td class="text-center py-3" style="width: 20%">
                    <h2 class=" m-0"><?php echo number_format( $stats[ "202508" ][ "nuevos" ] ); ?></h2>
                    Socios nuevos en la red
                </td>
                <td class="text-center py-3" style="width: 20%">
                    <h2 class=" m-0"><?php echo number_format( $stats[ "202508" ][ "rojos" ] ); ?></h2>
                    Socios en rojo
                </td>
                <td class="text-center py-3" style="width: 20%">
                    <h2 class=" m-0"><?php echo number_format( $stats[ "202508" ][ "compras_red" ] ); ?></h2>
                    Compras en la red
                </td>
            </tr>


            <tr>
                <td class="text-center py-3" style="width: 20%">
                    <h2 class=" m-0">$<?php echo number_format( $stats[ "202508" ][ "consumo" ], 2 ); ?></h2>
                    Consumo propio en el mes
                </td>
                <td class="text-center py-3" style="width: 20%">
                    <h2 class=" m-0">$<?php echo number_format( $stats[ "202508" ][ "consumo_red" ], 2 ); ?></h2>
                    Consumo de la red en el mes
                </td>
                <td class="text-center py-3" style="width: 20%">
                    <h2 class=" m-0">$<?php echo number_format( $stats[ "202508" ][ "ticket_promedio" ], 2 ); ?></h2>
                    Ticket promedio
                </td>
                <td class="text-center py-3" style="width: 20%">
                    <h2 class=" m-0">$<?php echo number_format( $stats[ "202508" ][ "ingresos" ], 2 ); ?></h2>
                    Comisiones propias en el mes
                </td>
                <td class="text-center py-3" style="width: 20%">
                    <h2 class=" m-0">$<?php echo number_format( $stats[ "202508" ][ "ingresos_red" ], 2 ); ?></h2>
                    Comisiones de la red en el mes
                </td>
            </tr>
        </table>
    </div>
</div>

<div class="row mb-5">

    <div class="col-lg-6 mb-4">
        <div class="card">
            <div class="card-header bg-teal">
                <h5 class="m-0 text-white">Socios activos</h5>
            </div>
            <div class="card-body text-center">
                <img src="<?php echo base_url(); ?>assets/img/demo.png" class="img-fluid">
            </div>
        </div>
    </div>

    <div class="col-lg-6 mb-4">
        <div class="card">
            <div class="card-header bg-teal">
                <h5 class="m-0 text-white">Nuevos socios</h5>
            </div>
            <div class="card-body text-center">
                <img src="<?php echo base_url(); ?>assets/img/demo.png" class="img-fluid">
            </div>
        </div>
    </div>

    <div class="col-lg-6 mb-4">
        <div class="card">
            <div class="card-header bg-teal">
                <h5 class="m-0 text-white">Volumen de consumo</h5>
            </div>
            <div class="card-body text-center">
                <img src="<?php echo base_url(); ?>assets/img/demo.png" class="img-fluid">
            </div>
        </div>
    </div>

    <div class="col-lg-6 mb-4">
        <div class="card">
            <div class="card-header bg-teal">
                <h5 class="m-0 text-white">Comisiones generadas</h5>
            </div>
            <div class="card-body text-center">
                <img src="<?php echo base_url(); ?>assets/img/demo.png" class="img-fluid">
            </div>
        </div>
    </div>
</div>
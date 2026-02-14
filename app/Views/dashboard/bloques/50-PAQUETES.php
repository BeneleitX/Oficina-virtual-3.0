<?php

$saldo = $usuario->saldo( "50-INVERSION", true );

if( $saldo > 0 ){ 

    if( !isset( $usuario->historial->vigencia ) ){
        $registro = substr( $usuario->historial->registro, 0, 10 ); 
        $fecha    = $registro > "2025-03-01" ? $registro : "2025-03-01";

        $fecha = endCycle( $fecha, 6 ); // date( "Y-m-d", strtotime( $fecha ." + 6 months - 1 day" ) );

        $historial = $usuario->historial;
        $historial->vigencia = $fecha;
        $usuario->historial = $historial;

        model( "UsuarioModel" )->save( $usuario );
    }

    $fecha = $usuario->historial->vigencia;

    // saldo vencido
    if( $fecha < date( "Y-m-d" ) ){
        
        $data = $usuario->data;
        $data->saldo->{"50-INVERSION"}->USDT = 0;
        $usuario->data = $data;

        model( "UsuarioModel" )->save( $usuario );

        // BITACORA marca periodo como pagado
        bitacora( 99, $usuario->id, [
            "saldo" => $saldo
        ] );        

        $saldo = 0;
    }
    else{
        echo "<div class=\"mt-3 px-3\"><a href=\"".base_url()."tienda/50-INVERSION\" class=\"btn w-100 mb-0 btn-success text-center\"><h1 class=\"text-white m-0\">$".number_format( $saldo, 2 )."</h1><p class=\"small m-0\">Tienes un saldo disponible para invertir en paquetes de producto<br>Utilizalo ahora mismo haciendo click aquí</p><p class=\"text-center\"><span class=\"badge text-mustard\" style=\"background:rgba(0,0,0,0.3)\">Vigencia del saldo: ".fecha( $fecha )."</span></p></a></div>"; 
    }
}

?>

<div class="pt-3 px-3 mb-3">
<?php
    echo isset( $usuario->data->wallet ) && strlen( $usuario->data->wallet ) == 34 ? 
        "<a href=\"".base_url()."paquete\" data-bs-toggle=\"tooltip\" title=\"Click para ver detalles de paquete\" class=\"btn btn-outline-info p-0 w-100\" style=\"overflow:hidden\">" : 
        "<a href=\"".base_url()."perfil\" data-bs-toggle=\"tooltip\" title=\"Click para agregar wallet digital\"  class=\"btn btn-outline-danger p-0 w-100\" style=\"overflow:hidden\">"; 
 ?>
    <table class="w-100 m-0">
    <tr>
        <td class="px-4 py-1" nowrap><i class="fa fa-wallet fs-3"></i></td>
        <td class="xp-0 w-100"><?php echo isset( $usuario->data->wallet ) && strlen( $usuario->data->wallet ) == 34 ? "<span class=\"small\">{$usuario->data->wallet}</span>" : "Wallet personal no registrada"; ?></td>
    </tr>
    </table>
</a>
</div>

<div class="px-3 py-0">
    <?php
    $inversiones = $usuario->get_inversiones();

    if( sizeof( $inversiones ) ){
        $inv = [];

        foreach( $inversiones as $i ){

            $px = substr($i[ "producto_codigo" ], 0, 13);
            $p  = model( "ProductoModel" )->find( $px );

            if( !isset( $inv[ $px ] ) ){
                $inv[ $px ] = [ 
                    "total" => 0.00,
                    "inversiones" => 0
                ];
            }

            if( !isset($i[ "extras" ][ "meses" ][ 0 ] ) ){
                $pedido = model( "PedidoModel" )->find( $i[ "pedido_id" ] );

                $ms = genera_meses( $pedido, $i[ "id" ], $p );
                $i[ "extras" ][ "meses" ] = $ms[ 0 ];
                $i[ "extras" ][ "semilla_retirada" ] = $ms[ 1 ];
    
                model( "InversionModel" )->save( $i );
                
            }

            $bt = balance_inversion( $i );

            $inv[ $px ][ "total" ] += $bt[ "total" ];
            $inv[ $px ][ "inversiones" ] ++;
        }

        $ps = model( "ProductoModel" )->where( "modelo_codigo = '50-INVERSION' AND estatus_codigo = '201-ACTIVO'" )->findAll();

        foreach( $ps as $p ){
    
            if( !isset( $inv[ $p->codigo ] ) ){
                $inv[ $p->codigo ] = [ 
                    "total" => 0.00,
                    "inversiones" => 0
                ];
            }

            $k = $inv[ $p->codigo ];

            if( $k[ "total" ] )
            echo "\n<a href=\"".base_url()."paquete\" class=\"btn w-100 border border-{$p->data->color} p-0 mb-1\"><div class=\"row m-0\">
                    <div class=\"col-7 py-2 text-start\">
                        <h5 class=\"m-0 text-".( $k[ "total" ] ? $p->data->color : "gray-400" )."\">{$p->data->nombre}".( $k[ "total" ] ? " <span class=\"badge bg-{$p->data->color}\">{$k[ "inversiones" ]}</span>" : "")."</h5>
                        
                    </div>
                    <div class=\"col-5 py-2 text-end\">
                        ".( $k[ "total" ] ? 
                        "<h5 class=\"m-0\"><img src=\"https://static.tronscan.org/production/logo/usdtlogo.png\" style=\"width:24px\"> $".number_format( $k[ "total" ], 2 )."</h5>"
                        : 
                        "<h5 class=\"m-0 text-gray-400\">$0.00</h5>" )."
                    </div></div></a>";
        }
    }else{
        echo "<div class=\"row m-3\"><div class=\"col-4 display-3 text-gray-300 text-end\"><i class=\"fa fa fa-box\"></i></div><div class=\"col-8 pt-3 mt-3 text-gray-500 text-start\">Aun no tienes Paquetes</div></div>";
    }
    
    ?>
</div>


<div class="card small m-3">
    <div class="card-body py-2">
        <p class="text-center"><strong>Socios directos activos en primer nivel</strong></p>
        <table class="w-100 m-0"><tr>
            <?php 
                $f = 0;

                $directos = $usuario->getDirectosActivos( "50-INVERSION" );
                $a = 0;
             
                foreach( $directos as $d){
                    $u = model( "UsuarioModel" )->find( $d[ "id" ] );

                    if( $f++ == 12){
                        break;
                    }

                    echo "<td width=\"7%\">".$u->avatar( 21 )."</td>";
                    if( !( ($a++ + 1 ) % 4 ) ){
                        
                        echo "<td width=\"8%\"> </td>";
                    }
                }

                for( $a = sizeof( $directos ); $a < 12; $a++ ){
                    echo "\n<td width=\"7%\"><div class=\"rounded-circle bg-gray-200 mb-0\" style=\"margin:0 auto; width:20px; height:20px; display:inline-block\">&nbsp;</div></td>";
                    if( !( ($a + 1 ) % 4 ) ){
                        echo "<td width=\"8%\"> </td>";
                    }
                }

                $rango_inversion = $usuario->getRangoInversion( sizeof( $directos ) );
            ?>
        </tr></table>
    </div>
</div>

<a href="<?php echo base_url( "rangos_paquetes" ); ?>">
    <div class="row text-center mx-1 mb-0">
        <div class="col-4"><div class="card"><div class="card-body">
            <p class="text-teal">Directos</p>
            <h4 style="line-height:0.5rem" class=""><?php echo sizeof( $directos ); ?></h4>
        </div></div></div>
        <div class="col-4">
            <img src="<?php echo base_url()."assets/img/rangos/".$rango_inversion[ "codigo" ]; ?>.png" class="img-fluid">
            <p style="line-height:0" class="m-0"><span class="badge bg-<?php echo $rango_inversion[ "color" ]; ?>"><?php echo $rango_inversion[ "nombre" ]; ?></span></p>
        </div>
        <div class="col-4"><div class="card"><div class="card-body">
            <p class="text-teal">Bono</p>
            <h4 style="line-height:0.5rem" class=""><?php echo number_format( $rango_inversion[ "cantidades" ][ "porcentaje" ], 2 ); ?>%</h4>
        </div></div></div>
    </div>
</a>

<div class="card m-3">
    <div class="card-body text-center">
        Volumen grupal de productos en tu red
        <h1 id="volumen_semilla" class="text-teal m-0"></h1>
    </div>
</div>

<?php

if( sizeof( $directos ) ){
    $pendientes = $usuario->get_pendientes( "50-INVERSION" );

    if( sizeof( $pendientes ) ){
        echo "<div class=\"alert alert-warning m-3\">";
        
        echo "<p class=\"m-0 text-center\">Comisiones generadas por paquetes mayores a 10K que se encuentran pendientes de pago</p>";
        echo "<table class=\"table my-3\"><thead><tr><th>Paquete</th><th>A pagar</th><th class=\"text-end\">Comision</th></tr></thead><tbody>";

        foreach( $pendientes as $p ){
            echo "<tr>
                    <td>".get_semana( $p->fechacompra )."</td><td>".get_semana( $p->fechapago )."</td><td class=\"text-end\"><strong class=\"text-teal\">$".number_format( $p->cantidad, 2 )."</strong></td></tr>";   
        }
        
        echo "</tbody></table>
        <p class=\"small m-0\"><i class=\"text-red fa fa-warning\"></i> Recuerda que cuando la ssuma de los paquetes de un socio realizadas durante las ultimas 4 semanas superan los 10K, comenzarán a generar comisiones que serán pagadas después de un plazo de 8 semanas.</p>
        </div>";
    }
}



?>


<script>

$(document).ready(function(){

    $( '#volumen_semilla' ).html( loader );

    $.ajax({
        url: base_url + 'bolsa_inversiones',
        method:'POST',
        data:{ [csrf_token] : csrf_hash, token: get_token_beneleit( <?php echo $usuario->id; ?>)  }
    }).done( function( datax ) {
        $( '#volumen_semilla' ).html( datax );
    });

});


</script>
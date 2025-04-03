<?php

$saldo = $usuario->saldo( "50-INVERSION", true );

if( $saldo ){
    echo "<div class=\"mt-3 px-3\"><a href=\"".base_url()."tienda/50-INVERSION\" class=\"btn w-100 mb-0 btn-success text-center\"><h1 class=\"text-white m-0\">$".number_format( $saldo, 2 )."</h1><p class=\"small m-0\">Tienes un saldo disponible para invertir en Capital24<br>Utilizalo ahora mismo haciendo click aquí</p></a></div>";
}

?>

<div class="pt-3 px-3 mb-3">
<?php
    echo isset( $usuario->data->wallet ) && strlen( $usuario->data->wallet ) == 34 ? 
        "<a href=\"".base_url()."capital\" data-bs-toggle=\"tooltip\" title=\"Click para ver detalles de Capital24\" class=\"btn btn-outline-info p-0 w-100\" style=\"overflow:hidden\">" : 
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
            $p  = model( "ProductoModel" )->find( $i[ "producto_codigo" ] );

            if( !isset( $inv[ $i[ "producto_codigo" ] ] ) ){
                $inv[ $i[ "producto_codigo" ] ] = [ 
                    "total" => 0.00,
                    "inversiones" => 0
                ];
            }

            if( !isset($i[ "extras" ][ "meses" ][ 0 ] ) ){
                $pedido = model( "PedidoModel" )->find( $i[ "pedido_id" ] );
                $i[ "extras" ][ "meses" ] = genera_meses( $pedido, $i[ "id" ], $p );
    
                model( "InversionModel" )->save( $i );
            }

            $bt = balance_inversion( $i );

            $inv[ $i[ "producto_codigo" ] ][ "total" ] += $bt[ "total" ];
            $inv[ $i[ "producto_codigo" ] ][ "inversiones" ] ++;
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

            echo "\n<a href=\"".base_url()."capital\" class=\"btn w-100 border border-{$p->data->color} p-0 mb-1\"><div class=\"row m-0\">
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
        echo "<div class=\"row m-3\"><div class=\"col-4 display-3 text-gray-300 text-end\"><i class=\"fa fa fa-arrow-trend-up\"></i></div><div class=\"col-8 pt-3 mt-3 text-gray-500 text-start\">Aun no tienes inversiones</div></div>";
    }
    
    ?>
</div>


<div class="card small m-3">
    <div class="card-body py-2">
        <p class="text-center"><strong>Socios directos activos en primer nivel</strong></p>
        <table class="w-100 m-0"><tr>
            <?php 

                $directos = $usuario->getDirectosActivos( "50-INVERSION" );
                $a = 0;
             
                foreach( $directos as $d){
                    $u = model( "UsuarioModel" )->find( $d[ "id" ] );

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


<div class="p-3 small">
    <table class="table table-striped table-bordered w-100 m-0 table-sm text-center">
        <thead>
            <tr>
                <th width="16%">Nivel</th>
                <th width="28%">Socios</th>
                <th width="28%">Bolsa</th>
            </tr>
        </thead>
        <tbody>
        <tr>
                <td>1</td>
                <td class="py-0"></td>
                <td class="py-0"></td>
            </tr>
            <tr>
                <td>2</td>
                <td class="py-0"></td>
                <td class="py-0"></td>
            </tr>
            <tr>
                <td>3</td>
                <td class="py-0"></td>
                <td class="py-0"></td>
            </tr>
            <tr>
                <td>4</td>
                <td class="py-0"></td>
                <td class="py-0"></td>
            </tr>
        </tbody>
    </table>
</div>


<?php

/*   if( $usuario->id == 55 ){
    $inversiones = model( "InversionModel" )->findAll();

    foreach( $inversiones as $i ){
    
        $pedido = model( "PedidoModel" )->find( $i[ "pedido_id" ] );

    //    $f_i = get_fecha_inversion( $i[ "fechas" ][ "pagado" ] );
    //    $i[ "fechas" ][ "inversion" ] = $f_i;
    //    $i[ "fechas" ][ "cierre" ] = get_fecha_cierre( $f_i );
        $i[ "extras" ][ "meses" ] = genera_meses( $pedido, $i[ "id" ] );

      //    if( !$i[ "extras" ][ "TxHash" ] || $i[ "extras" ][ "TxHash" ] == 'null' ){
      //       $i[ "extras" ][ "TxHash" ] = time()."_".md5( $i[ "pedido_id" ] );
            model( "InversionModel" )->save( $i );
      //   } 
    } 
  } */
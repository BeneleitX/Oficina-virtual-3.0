<div class="px-3 py-0">
    <?php
    $inversiones = $usuario->get_inversiones();

    if( sizeof( $inversiones ) ){
        foreach( $inversiones as $i ){
            $p  = model( "ProductoModel" )->find( $i[ "producto_codigo" ] );

            if( !isset($i[ "extras" ][ "meses" ][ 0 ] ) ){
                $pedido = model( "PedidoModel" )->find( $i[ "pedido_id" ] );
                $i[ "extras" ][ "meses" ] = genera_meses( $pedido, $p );
    
                model( "InversionModel" )->save( $i );
            }

            $bt = balance_inversion( $i );

            echo "\n<div class=\"row border border-{$p->data->color} rounded my-2 m-0\">
                    <div class=\"col-2 py-2\">
                        <img src=\"".base_url()."assets/img/productos/{$i[ "producto_codigo" ]}.png\" style=\"width:60px\">
                    </div>
                    <div class=\"col-5 pt-3\">
                        <strong class=\"m-0 text-{$p->data->color}\">{$p->data->nombre}</strong>
                        <span class=\"small\">".estatus( $i[ "estatus_codigo" ] )."</span>
                    </div>
                    <div class=\"col-5 pt-3 text-end\">
                        <h5 class=\"m-0\"><img src=\"https://static.tronscan.org/production/logo/usdtlogo.png\" style=\"width:24px\"> $".number_format( $bt[ "total" ], 2 )."</h5>
                    </div></div>";
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
/*
  if( $usuario->id == 55 ){
    $inversiones = model( "InversionModel" )->findAll();

    foreach( $inversiones as $i ){
    
        $pedido = model( "PedidoModel" )->find( $i[ "pedido_id" ] );

    //    $f_i = get_fecha_inversion( $i[ "fechas" ][ "pagado" ] );
    //    $i[ "fechas" ][ "inversion" ] = $f_i;
    //    $i[ "fechas" ][ "cierre" ] = get_fecha_cierre( $f_i );
        $i[ "extras" ][ "meses" ] = genera_meses( $pedido );

         if( !$i[ "extras" ][ "TxHash" ] || $i[ "extras" ][ "TxHash" ] == 'null' ){
            $i[ "extras" ][ "TxHash" ] = time()."_".md5( $i[ "pedido_id" ] );
            model( "InversionModel" )->save( $i );
        } 
   // } */

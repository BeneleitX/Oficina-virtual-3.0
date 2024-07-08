<script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>

<?php

$db = db_connect();
           
$sql = "SELECT * FROM t_pedidos
        WHERE usuario_id = {$usuario->id}
        AND SUBSTRING( estatus_codigo, 1, 3 ) > 400
        ORDER BY id DESC LIMIT 5";

$pedidos = $db->query( $sql )->getResultArray();
load_catalogo( "promociones" );
?>

<table class="table table-striped bg-white m-0">

    <tbody>
        <?php 
        if( sizeof( $pedidos ) ){
            foreach( $pedidos as $p ){
                $PTS = "";
                $p[ "PTS" ] = json_decode( $p[ "PTS" ], 1 );
                $p[ "data" ] = json_decode( $p[ "data" ], 1 );
                $p[ "promociones" ] = json_decode( $p[ "promociones" ], 1 );
                $p[ "fechas" ] = json_decode( $p[ "fechas" ], 1 );

                foreach( $p[ "PTS" ] as $tipo => $cantidad ){
                    if( $cantidad ){
                        $PTS .= "<span class=\"badge bg-".(PROMOCIONES[ $tipo ][ "settings" ][ "clase" ])."\">".(PROMOCIONES[ $tipo ][ "settings" ][ "siglas" ])."</span> ";
                    }
                }

                echo "\n<tr pedido=\"{$p[ "id" ]}\">
                    <td><p class=\"mb-0 small text-".MODELOS[ $p[ "modelo_codigo"] ][ "settings" ][ "color" ]."\"><i class=\"fa fa-".MODELOS[ $p[ "modelo_codigo"] ][ "settings" ][ "icono" ]."\"></i> ".MODELOS[ $p[ "modelo_codigo"] ][ "nombre" ]."</p><span class=\"badge bg-marine\">{$p[ "referencia" ]}</span></td>
                    <td><p class=\"mb-0 small\">Productos: {$p[ "data" ][ "productos" ]}</p>{$PTS}</td>
                    <td><p class=\"small mb-0\">".substr( $p[ "fechas" ][ "pagado" ], 0, 10)."</p>".estatus( $p[ "estatus_codigo" ] )."</td>
                    <td><p class=\"small mb-0\">Califica en</p><span class=\"badge bg-".( substr( $p[ "fechas" ][ "califica" ], 5, 2 ) == date( "m" ) ? "teal" : "red" )."\">".substr( $p[ "fechas" ][ "califica" ], 0, 4 )."-".substr( $p[ "fechas" ][ "califica" ], 5, 2 )."</span></td>
                </tr>";
            }            
        }
        else{
            echo "<div class=\"row m-3\"><div class=\"col-4 display-1 py-3 text-gray-300 text-center ps-5\"><i class=\"fa fa-cart-arrow-down\"></i></div><div class=\"col-8 pt-5 text-gray-500 text-center\">Aun no hay compras<br>en tu historial</div></div>";
        }
        ?>
     
    </tbody>
</table>


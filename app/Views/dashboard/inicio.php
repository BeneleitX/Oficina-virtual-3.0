<script src="https://core.beneleit.talentonet.com/static/beneleit/beneleit.js"></script>

<div class="row">
    <div class="col-lg-8 col-sm-6">
        <h4 class="mt-1 mb-0"><?php echo $titulo; ?> <span class="iconify rounded-1" data-width="24" data-icon="flag:<?php echo strtolower( $usuario->data->ubicacion->origen ); ?>-4x3"></span></h4>
        <p class="mb-3">Hoy es <?php echo dia( date("N") )." ".date("d")." de ".mes( date("m") ).", ".date("Y") ?></p>
    </div>

    <div class="col-lg-4 col-sm-6 text-end">
        <table class="w-100">
            <tr>
                <td class="pe-3 pt-3">
                    <?php
                    if( 
                        $usuario->permiso( "32-EDICION" ) || 
                        $usuario->permiso( "40-ADMIN" ) 
                    ){
                    ?>
                    <form action="<?php echo base_url( "sociodata" ); ?>" method="post" class="m-0">
                        <?php echo csrf_field(); ?>
                        <div class="input-group xinput-group-sm">
                            <input type="text" name="search_id" value="" placeholder="SOCIO O PEDIDO" class="form-control">
                            <span class="input-group-text bg-purple border-0 px-3"><i class="fa fa-magnifying-glass"></i></span>                           
                        </div>
                    </form>        
                    <?php
                    }     
                    ?>               
                </td>
                
                <td>
                    <img style="xposition:absolute; right:20px; top:30px; width:120px" src="<?php echo base_url(); ?>assets/img/logo_color.png">
                </td>
            </tr>
        </table>
    </div>
</div>

<div class="row">

<?php
    // Todo esto es temporal, se debe reemplazar por un grid dinámico 
    // donde los socios puedan reacomodar los bloques usando drag&drop
    // y sea 100% personalizable

    $columnas = ["","",""];

    foreach( $bloques as $b ){
        $mostrar = 0;

        foreach( $usuario->rol_codigos as $rol ){
            if( in_array( $rol, $b[ "data" ][ "roles" ] ) ){
                $mostrar = 1;
            }
        }

        if( $mostrar ){
            $html = "\n<div class=\"card my-4\" style=\"overflow:hidden\">";

            switch( $b[ "codigo" ] ){
                case "30-INGRESOS-SEMANA" : 
                    $b[ "data" ][ "titulo" ] .= " ".date( "W-o" );
                    break;

                case "18-MIS-PEDIDOS":
                    $b[ "data" ][ "titulo" ] .= " ".mes( date( "m" ) )." ".date( "Y" );
                    break;

                case "22-BONO-MENSUAL-PROMOS":
                    $b[ "data" ][ "titulo" ] .= " ".mes( date( "m" ) )." ".date( "Y" );
                    break;

                case "20-BONO-ANIVERSARIO":
                    $y = date( "Y" ) - ( date( "n" ) < 9 ? 1 : 0 );
                    $b[ "data" ][ "titulo" ] .= " {$y}-".( $y + 1 );
                    break;
            }

            $html .= "<div style=\"cursor:pointer\" onclick=\"save_layout( '{$b[ "codigo" ]}' )\" class=\"card-header bg-{$b[ "data" ][ "fondo" ]}\" data-bs-toggle=\"collapse\" data-bs-target=\"#div_{$b[ "codigo" ]}\" aria-expanded=\"true\" aria-controls=\"div_{$b[ "codigo" ]}\"><strong class=\"m-0 text-white\">{$b[ "data" ][ "titulo" ]}</strong></div>";


            $html .= "<div id=\"div_{$b[ "codigo" ]}\" class=\"accordion-collapse collapse ".( ( $usuario->data->layout->{$b[ "codigo" ]} ?? true ) == "true" ? "show" : "" )."\"><a ";
            
            if( $b[ "data" ][ "link" ] ){
                if( str_contains( $b[ "data" ][ "link" ], "http" ) ){
                    $html .= "target=\"_blank\" href=\"{$b[ "data" ][ "link" ]}\"";
                }
                else{
                    $html .= "href=\"".base_url()."{$b[ "data" ][ "link" ]}\"";
                }
            }
            
            $html .= ">";

            $file = "../app/Views/dashboard/bloques/{$b[ "codigo" ]}.php";

            if( file_exists( $file ) ){
                ob_start();
                include( $file );
                $html .= ob_get_clean();
            }
            else{
                $html.= "<div class=\"card-body bg-gray-500 text-white\">No se encuentra bloque ".$b[ "codigo" ]."</div>";
            }
            $html .= "</a></div></div>";

            $columnas[ $b[ "columna" ] - 1 ] .= $html;
        }
    }

    if(0 && !$usuario->data->sat->estatus ){
        $columnas[ 0 ] .= "<div class=\"mt-3 alert alert-warning text-mustard\"><i class=\"fa fa-warning\"></i> Las cantidades totales acumuladas que se muestran son antes de impuestos, En el corte del periodo se aplicará al pago final la retención de ISR correspondiente. </div>";
    }

    foreach( $columnas as $c ){
        echo "\n<div class=\"col-12 col-md-6 col-lg-4\">{$c}</div>";
    }

?>


</div>



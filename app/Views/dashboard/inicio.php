
<img style="position:absolute; right:20px; top:30px; width:120px" src="<?php echo base_url(); ?>assets/img/logo_color.png">
<h4 class="mt-1 mb-0"><?php echo $titulo; ?></h4>

<p class="mb-3">Hoy es lunes 11 de marzo, 2024</p>

<div class="row">

<?php
    $columnas = ["","",""];

    foreach( $bloques as $b ){
        $mostrar = 0;

        foreach( $usuario->rol_codigos as $rol ){
            if( in_array( $rol, $b[ "data" ][ "roles" ] ) ){
                $mostrar = 1;
            }
        }

        if( $mostrar ){
            $html = "\n<div class=\"card mt-3\" style=\"overflow:hidden\">";

            switch( $b[ "codigo" ] ){
                case "30-INGRESOS-SEMANA" : 
                    $b[ "data" ][ "titulo" ] .= " ".date( "W-Y" );
                    break;

                case "22-BONO-MENSUAL-PROMOS":
                    $b[ "data" ][ "titulo" ] .= " ".strtoupper( mes( date( "m" ) ) )." ".date( "Y" );
                    break;
            }

            $html .= "<div style=\"cursor:pointer\" onclick=\"save_layout( '{$b[ "codigo" ]}' )\" class=\"card-header bg-{$b[ "data" ][ "fondo" ]}\" data-bs-toggle=\"collapse\" data-bs-target=\"#div_{$b[ "codigo" ]}\" aria-expanded=\"true\" aria-controls=\"div_{$b[ "codigo" ]}\"><h5 class=\"m-0 text-white\">{$b[ "data" ][ "titulo" ]}</h5></div>";


            $html .= "<div id=\"div_{$b[ "codigo" ]}\" class=\"accordion-collapse collapse ".( ( $usuario->data->layout->{$b[ "codigo" ]} ?? true ) == "true" ? "show" : "" )."\"><a ";
            
            if( $b[ "data" ][ "link" ] ){
                $html .= "href=\"".base_url()."{$b[ "data" ][ "link" ]}\"";
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



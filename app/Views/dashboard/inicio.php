
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
            $file = "../app/views/dashboard/bloques/{$b[ "codigo" ]}.php";

            if( file_exists( $file ) ){
                ob_start();
                include( $file );
                $html .= ob_get_clean();
            }
            else{
                $html.= "<div class=\"card-body bg-gray-500 text-white\">No se encuentra bloque ".$b[ "codigo" ]."</div>";
            }
            $html .= "</div>";

            if( $b[ "data" ][ "link" ] ){
                $html = "<a href=\"".base_url()."{$b[ "data" ][ "link" ]}\">{$html}</a>";
            }

            $columnas[ $b[ "columna" ] - 1 ] .= $html;
        }
    }

    foreach( $columnas as $c ){
        echo "\n<div class=\"col-12 col-md-6 col-lg-4\">{$c}</div>";
    }
?>


</div>







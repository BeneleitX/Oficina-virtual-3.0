
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
            $file = "../app/Views/dashboard/bloques/{$b[ "codigo" ]}.php";

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

    if( !$usuario->data->sat->estatus ){
        $columnas[ 0 ] .= "<div class=\"mt-3 alert alert-warning text-mustard\"><i class=\"fa fa-warning\"></i> Las cantidades totales acumuladas que se muestran son antes de impuestos, En el corte del periodo se aplicará al pago final la retención de ISR correspondiente. </div>";
    }

    foreach( $columnas as $c ){
        echo "\n<div class=\"col-12 col-md-6 col-lg-4\">{$c}</div>";
    }
?>


</div>



<div class="modal fade" id="modal_splash" tabindex="-1" aria-labelledby="add_rolLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-body">
                <p class="text-center p-5"><i class="fa-solid fa-circle-notch fa-spin"></i></p>
                <button type="button" class="d-none btn bg-secondary" data-bs-dismiss="modal" ><i class="i-cancelar"></i> Cerrar</button>
            </div>
        </div>
    </div>
</div> 

<?php 
foreach( (array)$usuario->data->splash as $k => $splash ){
    switch( $splash->tipo ){
        case "rango": 
            echo "<script>$(document).ready(function(){ modal_splash( 'rango', '".json_encode( $splash->parametros )."' ) });</script>";
            
            $data = $usuario->data;
            if( is_array($data->splash) ) unset( $data->splash[$k] );
            $usuario->data = $data;
            model( "UsuarioModel" )->save( $usuario );
            
            $db = db_connect();
            $db->query( "CALL p_update_rango( {$usuario->id}, '10-NUTRICION', '".date("Ym")."' );" );
            break;
    }
    
    
} 
?>




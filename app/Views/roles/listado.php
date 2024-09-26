<link href="<?php echo base_url(); ?>assets/css/datatables.css" rel="stylesheet"/>
<script src="<?php echo base_url(); ?>assets/js/datatables.js" type="text/javascript"></script>
<script src="<?php echo base_url(); ?>assets/js/datatables_bs5.js" type="text/javascript"></script>

<h4 class="mt-1 mb-0"><?php echo $titulo; ?></h4>
<p><a href="<?php echo base_url( "admin" ); ?>"><i class="fa fa-undo"></i> Regresar a configuración</a></p>

<table class="table table-striped bg-white" id="tabla_roles">
    <thead>
        <tr>
            <th>Código</th>
            <th>Descripción</th>
            <th>Tipo</th>
            <th>Usuarios</th>
        </tr>
    </thead>

    <tbody>
        <?php 
            foreach( $roles as $rol ){
                $socios = "";
                
                switch( $rol[ "tipo" ] ){
                    case "BLOQUEO": $color = "red"; break; 
                    case "SOCIO":   $color = "mustard"; break; 
                    case "PERMANENTE":   $color = "teal"; break; 
                    case "ADMIN":   $color = "blue"; break; 
                    case "ROOT":    $color = "magenta"; break; 
                }

                $json = json_decode( $rol[ "socios" ] );
                sort( $json );

                foreach( $json as $temp ){
                    $socios .= "<span class=\"badge bg-{$color}\">{$temp}</span> ";
                }

                echo "\n<tr pasarela=\"{$rol[ "codigo" ]}\">
                    <td><span class=\"badge bg-marine\">{$rol[ "codigo" ]}</span></td>
                    <td nowrap>{$rol[ "descripcion" ]}</td>
                    <td><span xclass=\"badge bg-marine\">{$rol[ "tipo" ]}</span></td>
                    <td class=\"text-start\">{$socios}</td>
                </tr>";
            }
        ?>
     
    </tbody>
</table>

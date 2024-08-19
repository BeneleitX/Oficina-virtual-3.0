<link href="<?php echo base_url(); ?>assets/css/datatables.css" rel="stylesheet"/>
<script src="<?php echo base_url(); ?>assets/js/datatables.js" type="text/javascript"></script>
<script src="<?php echo base_url(); ?>assets/js/datatables_bs5.js" type="text/javascript"></script>

<h4 class="mt-1 mb-0"><?php echo $titulo; ?></h4>
<p><a href="<?php echo base_url( "admin" ); ?>"><i class="fa fa-undo"></i> Regresar a configuración</a></p>

<?php echo pills( "productos", $modelo ); ?>


<table class="table table-striped bg-white" id="tabla_productos">
    <thead>
        <tr>
            <th></th>
            <th>Nombre</th>
            <th>Descripcion</th>
            <th>Peso</th>
            <th>Pts</th>
            <th>Estatus</th>
            <th class="text-end">Precio</th>
            <th></th>
        </tr>
    </thead>

    <tbody>
        <?php 
            foreach( $productos as $producto ){

                foreach( MODELOS[ $modelo ][ "settings" ][ "promocion_base" ] as $promo ){
                    $Pts = $producto->data->puntos->{$promo} ?? 0;
                }

                echo "\n<tr producto=\"{$producto->codigo}\">
                    <td valign=\"middle\" class=\"text-center\"><img style=\"width:60px; height:60px; border-radius:5px \" src=\"".base_url()."assets/img/productos/".($producto->data->avatar ? $producto->codigo : "NO-IMAGEN").".png\"></td>
                    <td valign=\"middle\">".strtoupper( $producto->data->nombre )."<br><span class=\"badge bg-marine\">{$producto->codigo}</span></td>
                    <td valign=\"middle\">{$producto->data->descripcion}</td>
                    <td valign=\"middle\">".number_format( $producto->data->dimensiones->peso, 1 )."g</td>
                    <td valign=\"middle\">".number_format( $Pts, 1 )." Pts</td>
                    <td valign=\"middle\">".estatus( $producto->estatus_codigo )."</td>
                    <td valign=\"middle\" class=\"text-end\">$".number_format( $producto->precio->total, 2 )."<br>
                    <span class=\"small text-teal\">Comisionable $<strong>".number_format( $producto->precio->base ?? 0, 2 )."</strong></span></td>
                    <td valign=\"middle\" class=\"text-end\"><a href=\"".base_url( "promo_detalle/".$producto->codigo )."\" class=\"disabled btn btn-xs btn-primary\">DETALLES</a></td>
                </tr>";
            }
        ?>
     
    </tbody>
</table>

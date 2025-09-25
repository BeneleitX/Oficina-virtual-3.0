<h4 class="mt-1 mb-0"><?php echo $titulo; ?></h4>
<p><a class="btn btn-light btn-sm" href="<?php echo base_url( "admin" ); ?>"><i class="fa fa-undo"></i> Regresar a administración</a></p>

<form method="post" action="<?php echo base_url( "guarda_verificaciones" ); ?>">
    <?php echo csrf_field() ?>
    
    <table class="table table-striped bg-white"><thead>
    <?php
        echo "\n<tr><td>&nbsp;</td>";

        
        foreach( MODELOS as $modelo ){
            echo "<th colspan=\"".sizeof( VARIABLES[ "tipos_de_cuenta" ][ "valor" ] )."\" width=\"13%\" class=\"text-center \"><h5><span class=\"w-100 badge bg-{$modelo[ "settings" ][ "color" ]}\"><i class=\"fa fa-{$modelo[ "settings" ][ "icono" ]}\"></i> {$modelo[ "nombre" ]}</h5></th>";
        }

        echo "</tr><tr><th>&nbsp;</th>";

        foreach( MODELOS as $modelo ){
            foreach( VARIABLES[ "tipos_de_cuenta" ][ "valor" ] as $tipo => $data ){
                echo "<td class=\"small\"><span class=\"text-{$modelo[ "settings" ][ "color" ]}\">{$tipo}</span></td>";
            }
        }
        
        echo "</tr></thead><tbody>";

        foreach( VARIABLES[ "verificaciones" ][ "valor" ] as $punto ){
            echo "\n<tr><td><i class=\"text-teal fa fa-check\"></i> {$punto[ "descripcion" ]}</td>";

            foreach( MODELOS as $modelo ){

                foreach( VARIABLES[ "tipos_de_cuenta" ][ "valor" ] as $tipo => $data ){
                    $valor = $modelo[ "settings" ][ "verificaciones" ][ $tipo ][ $punto[ "codigo" ] ] ?? false;

                    echo "<td class=\"text-center\"><input name=\"check[{$modelo[ "codigo" ]}][{$tipo}][{$punto[ "codigo" ]}]\" style=\"zoom:1.5\" type=\"checkbox\" value=\"1\" ".( $valor ? "checked" : "" )."></td>";                    
                }
            }

            echo "</tr>";
        }
    ?>

    </tbody></table>

    <p class="text-end">
        <button class="btn btn-primary" type="submit"><i class="fa fa-save"></i> Guardar cambios</button>
    </p>
</form>
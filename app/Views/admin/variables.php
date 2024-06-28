
<h4 class="mt-1 mb-0"><?php echo $titulo; ?></h4>
<p><a href="<?php echo base_url( "admin" ); ?>"><i class="fa fa-undo"></i> Regresar a configuración</a></p>

<div class="alert alert-danger"><i class="fa fa-warning"></i> IMPORTANTE: Modificar estas variables modificará el comportamiento del sistema. Respaldar siempre valores anteriores.</div>
<div class="card border-red">
    <div class="card-header bg-red border-red"><h5 class="text-white">Variables generales</h5></div>
</div>
<table class="table w-100 border-red" id="tabla_variables">


    <tbody>
        <?php 
            foreach( VARIABLES as $v ){
                echo "\n<tr variable=\"{$v[ "codigo" ]}\">\n<td width=\"30%\" style=\"font-weight:700\">{$v[ "descripcion" ]}";

                if( $v[ "tipo" ] == "JSON" ){
                    echo "<p class=\"small text-mustard\"><i class=\"fa fa-warning\"></i> Mantener formato JSON</p></td><td width=\"70%\"><textarea rows=\"6\"class=\"form-control\">".json_encode( $v[ "valor" ] )."</textarea>";
                }
                else{
                    $valor = is_array( $v[ "valor" ] ) ? json_encode( $v[ "valor" ] ) : $v[ "valor" ];
                    echo "</td><td width=\"70%\"><input type=\"".( $v[ "tipo" ] == "TEXTO" ? "text" : "number" )."\" value=\"{$valor}\" class=\"form-control\">";
                }

                echo "</td></tr>";
            }
        ?>
     
    </tbody>
</table>

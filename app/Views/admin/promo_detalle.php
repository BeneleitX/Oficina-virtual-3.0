<link href="<?php echo base_url(); ?>assets/css/datatables.css" rel="stylesheet"/>
<script src="<?php echo base_url(); ?>assets/js/datatables.js" type="text/javascript"></script>
<script src="<?php echo base_url(); ?>assets/js/datatables_bs5.js" type="text/javascript"></script>

<h4 class="mt-1 mb-0"><?php echo $titulo."<span class=\"badge bg-{$promocion[ "settings" ][ "clase" ]}\">{$promocion[ "settings" ][ "siglas" ]}</span> <span class=\"badge bg-".MODELOS[ $promocion[ "modelo_codigo" ] ][ "settings" ][ "color" ]."\">".MODELOS[ $promocion[ "modelo_codigo" ] ][ "nombre" ]."</span> ".$promocion[ "settings" ][ "nombre" ]; ?></h4>
<p><a href="<?php echo base_url( "promociones/".$promocion[ "modelo_codigo" ] ); ?>"><i class="fa fa-undo"></i> Regresar a lista de promociones de <?php echo MODELOS[ $promocion[ "modelo_codigo" ] ][ "nombre" ]; ?></a></p>

<div class="alert alert-info">
    <div class="row">
        <div class="col-md-4">
            <h5>1. Editar configuración</h5>
            <p>Modifica la información en los formularios para que los parámetros coincidan con los datos y reglas que se hayan establecido para la promoción.</p>
        </div>

        <div class="col-md-4">
            <h5>2. Guardar datos</h5>
            <p>Verifica que todos los datos sean correctos y haz click en el botón de guardar cambios.</p>
        </div>

        <div class="col-md-4">
            <h5>3. Probar resultados</h5>
            <p>Puedes ver los resultados y probar los cambios en la tienda en línea. Si eliges el estatus de FASE DE PRUEBA, sólo los administradores podrán ver la promoción antes de lanzarla.</p>
        </div>
    </div>    
</div>

<input type="hidden" name="promocion_codigo"  value="<?php echo $promocion[ "codigo" ]; ?>">
<h5>Identificación</h5>
<div class="row mb-3">
    <div class="col-md-2">
        <label>Código</label>
        <input disabled type="text" name="n_codigo" class="form-control mb-3" value="<?php echo $promocion[ "codigo" ]; ?>">
    </div>
    <div class="col-md-2">
        <label>Nombre</label>
        <input type="text" name="n_nombre" class="form-control mb-3" value="<?php echo $promocion[ "settings" ][ "nombre" ]; ?>">
    </div>    
    <div class="col-md-4">
        <label>Descripción</label>
        <input type="text" name="n_descripcion" class="form-control mb-3" value="<?php echo $promocion[ "settings" ][ "descripcion" ]; ?>">
    </div>
    <div class="col-md-1">
        <label>Siglas (4)</label>
        <input type="text" name="n_siglas" class="form-control mb-3" value="<?php echo $promocion[ "settings" ][ "siglas" ]; ?>">
    </div>
    <div class="col-md-3">
        <label>color</label>
        <select name="n_clase" class="form-select mb-3">
            <?php
        $colores = [
            "indigo",
            "deep-purple",
            "purple",
            "violet",
            "pink",
            "red",
            "deep-orange",
            "orange",
            "mustard",
            "amber",
            "yellow",
            "lime",
            "light-green",
            "green",
            "teal",
            "cyan",
            "light-blue",
            "blue",
            "brown",
        ];

        foreach( $colores as $color ){
            echo "\n<option value=\"{$color}\" ".( $color == $promocion[ "settings" ][ "clase" ] ? "selected" : "" ).">{$color}</option>";
        }
        ?>
        </select>    
    </div>    
</div>
<h5>Vigencia <?php echo estatus( $promocion[ "vigencia" ] ); ?></h5>
<div class="row">
    <div class="col-md-2">
        <label>Comienza</label>
        <input type="datetime-local" name="n_inicia" class="form-control mb-3" value="<?php echo $promocion[ "inicia" ]; ?>">
    </div>
    <div class="col-md-2">
        <label>Termina</label>
        <input type="datetime-local" name="n_termina" class="form-control mb-3" value="<?php echo $promocion[ "termina" ]; ?>">
    </div>
    <div class="col-md-6 pt-4">
        <ul class="small">
            <li>La fecha de inicio siempre debe ser menor a la de término</li>
            <li>Si la fecha de inicio es mayor a hoy, la promoción se desactivará en automático, sin importar su estatus. Mismo caso cuando la fecha de término sea menor a hoy</li>
            <li>La hora es en la zona centro del país</li>
            <li>El estatus por vigencia puede ser: EN ESPERA, VIGENTE o FINALIZADA</li>
        </ul>
    </div>        
</div>

<div class="row mb-1">
    <div class="col-lg-3 col-md-6 mb-3">
        <div class="card border-teal">
            <div class="card-body">
            <div class="form-check form-switch">
                <input name="n_paquete" class="form-check-input" type="checkbox" role="switch" <?php echo $promocion[ "settings" ][ "paquete" ] == "true" ? "checked" : ""; ?>>
                    <h5 class="text-teal">Cobro como paquete</h5>
                </div>                
                
                <p>Activa esta opción si la promoción se cobra como paquete, es decir, tiene un precio fijo sin importar cuales o cuantos productos tenga.</p>
            </div>
        </div>
    </div>
    <div class="col-lg-3 col-md-6 mb-3">
        <div class="card border-teal">
            <div class="card-body">
            <div class="form-check form-switch">
                <input name="n_obligatoria" class="form-check-input" type="checkbox" role="switch" <?php echo $promocion[ "settings" ][ "obligatoria" ] == "true" ? "checked" : ""; ?>>
                    <h5 class="text-teal">Compra obligatoria</h5>
                </div>                
                
                <p>Define si la compra de esta promoción debe ser oblihatoria. En caso de apagarse, se considerará opcional y no afectará el proceso de compra el que no se incluyan productos en ella.</p>
            </div>
        </div>
    </div>
    <div class="col-lg-3 col-md-6 mb-3">
        <div class="card border-teal">
            <div class="card-body">
            <div class="form-check form-switch">
                <input name="n_exacto" class="form-check-input" type="checkbox" role="switch" <?php echo $promocion[ "settings" ][ "exacto" ] == "true" ? "checked" : ""; ?>>
                    <h5 class="text-teal">Cantidad exacta</h5>
                </div>                
                
                <p>Activa esta opción si la promoción debe incluir la cantidad exacta de productos permitidos. Tampoco puede quedar °vacía</p>
            </div>
        </div>
    </div>
    <div class="col-lg-3 col-md-6 mb-3">
        <div class="card border-teal">
            <div class="card-body">
            <div class="form-check form-switch">
                <input name="n_forced" class="form-check-input" type="checkbox" role="switch" <?php echo isset( $promocion[ "settings" ][ "forced" ] ) && $promocion[ "settings" ][ "forced" ] == "true" ? "checked" : ""; ?>>
                    <h5 class="text-teal">Auto llenado</h5>
                </div>                
                
                <p>Activa esta opción si la promoción debe llenarse automáticamente con los productos precargados. El socio no podrá quitar o agregar productos.</p>
            </div>
        </div>
    </div>    
</div>

<h5>Productos elegibles</h5>
<div class="row mb-3 " id="elegibles">
<?php  
    foreach( $productos as $p ){
        echo "
        <div class=\"col-6 text-center col-lg-2 small mb-2\">
            <input name=\"\" value=\"{$p->codigo}\" id=\"btn{$p->codigo}\" type=\"checkbox\" class=\"btn-check\" autocomplete=\"off\" ".( in_array( $p->codigo, isset( $promocion[ "productos" ][ "elegibles" ] ) ? $promocion[ "productos" ][ "elegibles" ] : [] ) ? "checked" : "" ).">
            <label style=\"padding:0;  \" class=\"btn col-12 btn-outline-yesno\" for=\"btn{$p->codigo}\">".mb_strtoupper( $p->data->nombre )."</label>
        </div>";
    }
?>
</div>

<h5 class="m-0">Productos precargados</h5>
<small><i class="fa fa-circle-info"></i> Los productos precargados se agregarán automáticamente al carrito cuando la promoción se active. Sirve para promociones que son paquetes cerrados con productos ya definidos, como en los bonos o los regalos que acompañan el acceso a eventos.</small>

<div class="row mb-4 mt-3" id="precargados">
    <div class="col-6 text-center col-lg-2 small mb-2" id="boton_agrega">
        <button style="padding:0;" class="btn col-12 btn-secondary"><i class="fa fa-plus"></i> Agregar</button>
    </div>
</div>

<div class="alert alert-danger">
    <table><tr><td valign="top"><i class="fa fa-circle-radiation" style="font-size:32px"></i></td><td><ul class="m-0"><li>Modificar los siguientes parámetros puede ocasionar que las compras en proceso tengan problemas al ejecutar su pago.</li><li>Las promociones en fase de prueba solo serán vistas por administradores.</li><li>En caso de colocar fórmulas con errores, el sistema se bloqueará.</li></ul></td></tr></table>
</div>  
<div class="card border-red my-3">
	<div class="card-header">
		<h5 class="text-red mb-0">Cambio de estatus</h5>
	</div>
	<div class="card-body">
      
        <div class="row">
            <div class="col-4 col-lg-3">
                <select class="form-select" name="n_estatus">
                    <option value="140-SUSPENDIDO" <?php echo $promocion[ "estatus_codigo" ] == "140-SUSPENDIDO" ? "selected" : ""?>><?php echo ESTATUS[ "140-SUSPENDIDO" ][ "descripcion" ]; ?></option>
                    <option value="160-PRUEBA" <?php echo $promocion[ "estatus_codigo" ] == "160-PRUEBA" ? "selected" : ""?>><?php echo ESTATUS[ "160-PRUEBA" ][ "descripcion" ]; ?></option>
                    <option value="201-ACTIVO" <?php echo $promocion[ "estatus_codigo" ] == "201-ACTIVO" ? "selected" : ""?>><?php echo ESTATUS[ "201-ACTIVO" ][ "descripcion" ]; ?></option>
                </select>
            </div>
        </div>
	</div>
</div>

<div class="card border-red my-3">
	<div class="card-header">
		<h5 class="text-red mb-0">Fórmulas</h5>
	</div>
	<div class="card-body">       
        <div class="row">
            <div class="col-12 col-lg-4 mb-3">
                <p class="mb-1"><strong>Precio de producto o paquete</strong></p>
                <textarea name="n_precio" style="background: var(--bs-danger-bg-subtle); font-family: monospace" class="form-control border-red" rows="10"><?php echo isset( $promocion[ "formulas" ][ "precio" ] ) ? $promocion[ "formulas" ][ "precio" ] : ""; ?></textarea>
            </div>
            <div class="col-12 col-lg-4 mb-3">
                <p class="mb-1"><strong>Activación</strong></p>
                <textarea name="n_activacion" style="background: var(--bs-danger-bg-subtle); font-family: monospace" class="form-control border-red" rows="10"><?php echo isset( $promocion[ "formulas" ][ "activacion" ] ) ? $promocion[ "formulas" ][ "activacion" ] : ""; ?></textarea>
            </div>
            <div class="col-12 col-lg-4 mb-3">
                <p class="mb-1"><strong>Productos disponibles</strong></p>
                <textarea name="n_disponible" style="background: var(--bs-danger-bg-subtle); font-family: monospace" class="form-control border-red" rows="10"><?php echo isset( $promocion[ "formulas" ][ "disponible" ] ) ? $promocion[ "formulas" ][ "disponible" ] : ""; ?></textarea>
            </div>
        </div>
    </div>
</div>

<div class="alert alert-info">
<ul class="m-0">
            <li>Para no restringir la cantidad de productos disponibles colocar -1</li>
            <li>En las fórmilas interactuan tanto los datos del <span class="badge bg-marine">PEDIDO</span> como del <span class="badge bg-marine">USUARIO</span> como objetos tal como se extraen de la base de datos</li>
            <li>Para extraer información específica de estatus, del producto o de la promoción, se pueden usar las constantes declaradas a partir de los catálogos (arrays)</li>
        </ul>    
</div>

<p class="text-end"><button class="btn btn-primary" id="guarda_cambios">Guardar cambios</button></p>

<div class="modal" tabindex="-1" id="modal_productos" promocion="">
	<div class="modal-dialog modal-lg">
		<div class="modal-content">

			<div class="modal-header">
				<div class="modal-title me-3">
							<div class="input-group">
								<span class="input-group-text"><i class="fa fa-magnifying-glass"></i></span>
								<input type="text" class="form-control" placeholder="Buscar productos" id="busca_producto">
							</div>
				</div>
				<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
			</div>

			<div class="modal-body">
				<div class="row">
					<?php 
					foreach( $productos as $p ){
						echo "
						<div class=\"col-lg-6\" producto=\"{$p->codigo}\">
							<div class=\"card mb-3 boton\" title=\"Click para agregar al pedido\" onclick=\"agrega_producto( '{$p->codigo}' )\">
								<div class=\"row g-0\">
									<div class=\"col-2\">
										<img src=\"".base_url()."assets/img/productos/".( $p->data->avatar ? $p->codigo : "NO-IMAGEN" ).".png\" class=\"img-fluid rounded-start\">
									</div>
									<div class=\"col-10\">
										<div class=\"card-body\">
											<h5>".strtoupper( $p->data->nombre )."</h5>
										<p class=\"small m-0\">{$p->data->descripcion}</p>
										</div>
									</div>
								</div>
							</div>
					  	</div>";
					}
					?>
				</div>
			</div>
		</div>
	</div>
</div>

<?php 

$prods = [];
foreach( $productos as $p ){
    $prods[ $p->codigo ] = $p;
}

?>

<script>
	var cat_productos = <?php echo json_encode( $prods ); ?>,
        precarga      = [ <?php if( isset( $promocion[ "productos" ][ "precarga" ] ) && sizeof( $promocion[ "productos" ][ "precarga" ] ) ) echo "'".implode("', '", $promocion[ "productos" ][ "precarga" ] )."'"; ?> ],
        promocion     = <?php echo json_encode( $promocion ); ?>,
        modelo        = '<?php echo $promocion[ "modelo_codigo" ]; ?>';
</script>

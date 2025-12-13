<link href="<?php echo base_url(); ?>assets/css/datatables.css" rel="stylesheet"/>
<script src="<?php echo base_url(); ?>assets/js/datatables.js" type="text/javascript"></script>
<script src="<?php echo base_url(); ?>assets/js/datatables_bs5.js" type="text/javascript"></script>

<h4 class="mt-1 mb-0"><?php echo $titulo; ?></h4>
<p><a class="btn btn-light btn-sm" href="<?php echo base_url( "pines/10-NUTRICION" ); ?>"><i class="fa fa-undo"></i> Regresar a rangos</a></p>

<div class="row">
    <div class="col-lg-8">
        <?php echo pills( "rangos", $modelo ); ?>
    </div>
</div>

<table class="table table-striped bg-white" id="tabla_rangos">
    <thead>
        <tr>
            <th>Código</th>
            <th>Nombre</th>
            <th>Activos</th>
            <th>Inactivos</th>
            <th>Desde</th>
            <th>Hasta</th>
            <th></th>
        </tr>
    </thead>

    <tbody>
        <?php 
            foreach( $rangos as $rango ){
                $activos = isset( $socios[ $rango[ "codigo" ] ][ "activos"] ) ? $socios[ $rango[ "codigo" ] ][ "activos"] : 0;
                $inactivos = isset( $socios[ $rango[ "codigo" ] ][ "inactivos"] ) ? $socios[ $rango[ "codigo" ] ][ "inactivos"] : 0;

                echo "\n<tr rango=\"{$rango[ "codigo" ]}\">
                    <td><span class=\"badge bg-{$rango[ "color" ]}\">{$rango[ "codigo" ]}</span></td>
                    <td>{$rango[ "nombre" ]}</td>
                    <td>{$activos}</td>
                    <td>{$inactivos}</td>
                    <td class=\"text-end\">".number_format( $rango[ "cantidades" ][0] ?? 0 , 2 )."</td>
                    <td class=\"text-end\">".number_format( $rango[ "cantidades" ][1] ?? 0 , 2 )."</td>
                    <td class=\"text-end\"><a href=\"".base_url( "promo_detalle/".$rango[ "codigo" ] )."\" class=\"d-none btn btn-xs btn-primary\">DETALLES</a></td>
                </tr>";
            }
        ?>
     
    </tbody>
</table>

<div class="row mb-5">
    <div class="col-md-6 col-lg-4">

        <h5 class="text-teal mt-4 mb-3">Lugares de entrega</h5>
        <table class="table table-striped bg-white mb-4" id="tabla_rangos">
            <tbody>
                <?php 
                    foreach( VARIABLES[ "entrega_pines" ][ "valor" ] as $e ){
                        echo "\n<tr><td><a href=\"".base_url()."borra_lugar/".urlencode( base64_encode( $e ) )."\" class=\"btn btn-outline-danger btn-sm\">X</a> {$e}</td></tr>";
                    }
                ?>
            
            </tbody>
        </table>

        <button class="btn btn-primary" onclick="$( '#agregar_lugar' ).modal( 'show' )"><i class="fa fa-plus"></i> Agregar lugar</button>
    </div>
</div>


<div class="modal" tabindex="-1" id="agregar_lugar">
	<div class="modal-dialog">
		<div class="modal-content">
			<form method="post" action="<?php echo base_url( "agrega_lugar" ); ?>">
                <?php echo csrf_field() ?>

				<div class="modal-header bg-teal">
					<h5 class="modal-title m-0 text-white"><i class="fa fa-plus"></i> Agregar lugar de entrega de pines</h5>
					<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
				</div>
				<div class="modal-body">
                    <div class="row mb-2">
                        <div class="col-6"><label for="entrega_fecha">Nuevo lugar</label></div>
                        <div class="col-6"><input type="text" oninput="this.value = this.value.toUpperCase()" class="form-control" name="nuevo_lugar"></div>
                    </div>
				</div>
				<div class="modal-footer">
					<button type="submit" class="btn btn-primary"><i class="fa fa-save"></i> Agregar</button>
				</div>
			</form>
		</div>
	</div>
</div>



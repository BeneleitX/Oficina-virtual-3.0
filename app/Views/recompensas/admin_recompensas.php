
<link href="<?php echo base_url(); ?>assets/css/datatables.css" rel="stylesheet"/>
<script src="<?php echo base_url(); ?>assets/js/datatables.js" type="text/javascript"></script>
<script src="<?php echo base_url(); ?>assets/js/datatables_bs5.js" type="text/javascript"></script>

<h4 class="mt-1 mb-0"><?php echo $titulo; ?></h4>
<p><a href="<?php echo base_url( "admin" ); ?>"><i class="fa fa-undo"></i> Regresar a configuración</a></p>

<?php
$ciclos = [];


foreach( RECOMPENSAS as $r ){
    $ciclos[ $r[ "ciclo" ] ][] = $r;
}

$socios = [];

foreach( $ciclos as $k => $c ){

    echo "\n<h4 class=\"\">Ciclo {$k}</h4>";
    echo "\n<div class=\"accordion\" id=\"ciclo_{$k}\">";

    foreach( $c as $d ){

        // get redenciones
        $db = db_connect();

        $sql = "select * from t_redenciones where recompensa_codigo = '{$d[ "codigo" ]}' AND substring( estatus_codigo, 1, 3) > 300";
        $redenciones = $db->query( $sql )->getResult();        

        $sql = "select * from t_redenciones where recompensa_codigo = '{$d[ "codigo" ]}' AND estatus_codigo = '623-ENTREGA'";
        $entregados = $db->query( $sql )->getResult();        

        echo "\n<div class=\"accordion-item mb-3\">
            <div class=\"accordion-button collapsed\" type=\"button\" data-bs-toggle=\"collapse\" data-bs-target=\"#premio_{$d[ "codigo" ]}\" aria-expanded=\"false\" aria-controls=\"premio_{$d[ "codigo" ]}\">
                        <table class=\"w-100\"><tr>

                            <td width=\"6%\"><img src=\"".base_url()."/assets/img/recompensas/{$d[ "codigo" ]}.png\" style=\"width:60px; height:auto;\"></td>
                            <td width=\"23%\"><h4><strong>{$d[ "nombre" ]}</strong></h4></td>
                            <td width=\"20%\" class=\"m-0\">Estrellas: <strong>{$d[ "estrellas" ]}</strong> <i class=\"fa fa-star text-amber\"></i></td>
                            <td width=\"20%\" class=\"m-0\">Rango necesario: <span class=\"badge bg-".RANGOS[ $d[ "rango_codigo" ] ][ "color" ]."\">".RANGOS[ $d[ "rango_codigo" ] ][ "nombre" ]."</span></td>
                            <td width=\"20%\" class=\"m-0\">".( sizeof( $redenciones ) ? ( sizeof( $redenciones ) == sizeof( $entregados ) ? "Todos entregados" : "<strong><i class=\"fa fa-warning text-mustard\"></i> ".( sizeof( $redenciones ) - sizeof( $entregados ) )." por entregar</strong>" ) : "Sin ganadores" )."</td>
                            <td width=\"10%\"><h1 class=\"m-0 pe-4\"><span class=\"badge bg-".( sizeof( $redenciones ) ? ( sizeof( $redenciones ) == sizeof( $entregados ) ? "teal" : "red" ) : "gray-500" )." col-12\" style=\"display:inline-block\">".sizeof( $redenciones )."</span></h1></td>
                        
                        </tr></table>
            </div>
        	<div id=\"premio_{$d[ "codigo" ]}\" class=\"accordion-collapse collapse\" data-bs-parent=\"#ciclo_{$k}\">
			    <div class=\"accordion-body\"><table class=\"table table-striped tabla_redenciones w-100\">
                    <thead><tr><th>Fecha</th><th>Socio</th><th>Recompensa</th><th>Estatus</th><th>&nbsp;</th></tr></thead><tbody>
                ";
	
        foreach( $redenciones as $r ){

            if( !isset( $socios[ $r->usuario_id ] ) ){
                $socios[ $r->usuario_id ] = model( "UsuarioModel" )->find( $r->usuario_id );
            }

            echo "<tr redencion=\"{$r->id}\" recompensa=\"{$d[ "codigo" ]}\">
                    <td><span class=\"d-none\">{$r->fecha}</span>".date( "d-m-Y", strtotime( $r->fecha ) )."</td>
                    <td nowrap>".$socios[ $r->usuario_id ]->id( "10-NUTRICION", false, false )." ".$socios[ $r->usuario_id ]->avatar( 24 )." ".$socios[ $r->usuario_id ]->nombre( 2 )."</td>
                    <td nowrap><img src=\"".base_url()."/assets/img/recompensas/{$d[ "codigo" ]}.png\" style=\"width:30px; height:auto;\"> {$d[ "nombre" ]}</td>
                    <td nowrap>".estatus( $r->estatus_codigo )."</td>
                    <td class=\"text-end\">".( $r->estatus_codigo == '330-EN-ESPERA' ? "<button class=\"btn btn-sm btn-secondary\" onclick=\"entregar_recompensa( {$r->id} )\">Marcar como entregado</button>" : "" )."</td></tr>";
        }
                

	    echo "\n</tbody></table></div>
    		</div>

        </div>";
    }    
    
    echo "</div></div>";
}
?>



<div class="modal" tabindex="-1" id="entregar_recompensa">
	<div class="modal-dialog modal-lg">
		<div class="modal-content">
			<div class="modal-header">
				<h5 class="modal-title">Entregar recompensa</h5>
				<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
			</div>
			<div class="modal-body">
                <div class="row mb-4">
                    <div class="col-lg-3 text-center px-4">
                        <img src="" class="px-4 img-fluid img_recompensa">
                    </div>

                    <div class="col-lg-9 txext-center pt-2">
                        <h4><span class="badge bg-marine recompensa_nombre"></span></h4>
                        <div id="socio_data" class="mb-3"></div>
                        
                    </div>
                </div>

                <div class="alert alert-danger"><i class="fa fa-circle-info"></i> IMPORTANTE: Al marcar esta recompensa y cambiar su estatus, será notificado el socio y se cerrará el proceso de entrega.</div>

                <form method="post" action="<?php echo base_url( "entregar_recompensa" ); ?>">
					<?php echo csrf_field() ?>
                    <input type="hidden" name="redencion" value="">
                    <p class="mt-3 mb-0 text-end"><button type="submit" class="boton_entregar btn btn-success">Marcar recompensa como entregada</button></p>
                </form>
			</div>
		</div>
	</div>
</div>

<script>

var cat_recompensas = <?php echo json_encode( RECOMPENSAS ); ?>;

</script>
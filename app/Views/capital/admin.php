<link href="<?php echo base_url(); ?>assets/css/datatables.css" rel="stylesheet"/>
<script src="<?php echo base_url(); ?>assets/js/datatables.js" type="text/javascript"></script>
<script src="<?php echo base_url(); ?>assets/js/datatables_bs5.js" type="text/javascript"></script>

<div class="row">
    <div class="col-6">
    <h4 class="mt-1 mb-0"><?php echo $titulo; ?></h4>
        <p>
            <a class="btn btn-light btn-sm" href="<?php echo base_url( "admin" ); ?>"><i class="fa fa-undo"></i> Regresar a administración</a>
        </p>
    </div>

    <div class="col-6 text-end pt-3">
        <h5> Retiros aplicados en el mes: 
            <span class="badge bg-gray-500" id="pendientes">0</span>
            de <span class="badge bg-gray-500" id="totales">0</span>
            <select id="mes_retiros" class="ms-4 form-select" style="display: inline-block; width:auto">
                <?php
                $fecha = date( "Y-m-d" );
                $mes_x = date( "Ym", strtotime( $fecha ) );

                while( $mes_x >= '202408' ){
                    echo "\n<option ".( $mes_x == $mes ? "selected" : "" )." value=\"{$mes_x}\">".substr( $mes_x, 0, 4)." ".strtoupper( mes( substr( $mes_x, 4, 2) ) )."</option>";

                    $fecha = date( "Y-m-d", strtotime( $fecha." -1 month" ) );
                    $mes_x   = date( "Ym", strtotime( $fecha ) );
                } 
                ?>
            </select>
        </h5>
    </div>
</div>



<div class="alert alert-info">
    <div class="row">
        <div class="col-lg-4">
            <h5>Listado de socios</h5> 
            <p>Este listado incluye a socios que han solicitado retiro de rendimientos durante el mes de <?php echo strtoupper( mes( substr( $mes, 4,2) ) )." ".substr( $mes, 0,4); ?>.</p>
            <p>Para ver las transferencias solicitadas y enviadas, hacer click en el botón <span class="badge bg-teal text-white">TRANSFERENCIAS</span>. Ahí se podrán marcar como procesadas con el botón <span class="badge bg-mustard text-white">MARCAR COMO TRANSFERIDA</span></p>
        </div>

        <div class="col-lg-4">
            <h5>Marcar todos como entregados</h5> 
            <p class="text-red"><i class="fa fa-warning"></i> <strong>IMPORTANTE:</strong> Se puede utilizar este botón para evitar el marcado solicitud por solicitud. Esta acción no puede revertirse. Asegúrate de que todas las transferencias del listado han sido ejecutadas antes de continuar.</p>
            <a href="<?php echo base_url()."entrega_retiros/".$mes; ?>" id="entregar_retiros" class="btn <?php echo date( "Ym" )  <= $mes ? "disabled" : ""; ?> btn-danger"><i class="fa fa-shuffle"></i> Entregar todos</a>
        </div>
        <div class="col-lg-4">
            <h5>Descargar documento con retiros solicitados en el mes</h5> 
            <button class="btn btn-primary" onclick="excel_retiros( <?php echo $mes; ?> )"><i class="fa fa-file-excel"></i> Descargar excel</button>
        </div>

    </div>
</div>


<table class="table table-striped bg-white" id="tabla_solicitudes">
    <thead>
        <tr>
            <th>Socio</th>
            <th>Nombre</th>
            <th>Inversion</th>
            <th>Cantidad</th>
            <th>Tipo</th>
            <th>Estatus</th>
            <th>&nbsp;</th>
        </tr>
    </thead>
    <tbody>
        <?php 
      //  $mes = date( "Ym" );
        
        $transferidas = 0;

        foreach( $solicitudes as $s ){

            if( $s[ "estatus_codigo" ] == "421-APLICADO" ){
                $transferidas++;
            }

            $u = model( "UsuarioModel" )->find( $s[ "usuario_id" ] );
            $i = model( "InversionModel" )->find( $s[ "inversion_id" ] );
            $p = model( "ProductoModel" )->find( $i[ "producto_codigo" ] );
            $e = model( "PedidoModel" )->find( $i[ "pedido_id" ] );

            echo "\n
            <tr socio=\"{$u->id}\">
                <td>".$u->id( "50-INVERSION", false, false )."</td>
                <td nowrap>".$u->avatar( 24 )." ".$u->nombre( 2 )."</td>
                <td><span class=\"badge bg-{$p->data->color}\">{$p->data->porcentaje}%</span> <span class=\"badge bg-gray-600\">{$e[ "referencia" ]}-{$i[ "id" ]}</span></td>
                <td class=\"text-end\"><span class=\"text-teal fw-bold\">$".number_format( $s[ "cantidad" ], 2 )."</span></td>
                <td><span class=\"badge bg-marine\">{$s[ "tipo" ]}</span></td>
                <td>".estatus( $s[ "estatus_codigo" ] )."</td>
                <td class=\"text-end\"><a href=\"".base_url()."statement/".urlencode( base64_encode( $i[ "extras" ][ "TxHash" ] ) )."\" target=\"_blank\" class=\"btn btn-sm btn-info\"><i class=\"fa fa-magnifying-glass\"></i> Ver detalles</a>".( date( "Ym" )  > $s[ "fechas" ][ "mes" ]  ? " <button class=\"btn btn-sm btn-".( $s[ "estatus_codigo" ] == "421-APLICADO" ? "light" : "success" )."\" onclick=\"transferir_fondos( {$u->id}, {$s[ "inversion_id" ]} )\"><i class=\"fa fa-shuffle\"></i> Transferencias</button>" : "" )."</td>
            </tr>";
        }
        ?>
    </tbody>
</table>
 

<div class="modal" tabindex="-1" id="modal_retiros">
	<div class="modal-dialog modal-xl">
		<div class="modal-content">
			<form method="post" action="<?php echo base_url( "do_recarga" ); ?>">
                <?php echo csrf_field() ?>
                <input type="hidden" name="r_socio"  value="">

				<div class="modal-header bg-marine">
                    <h5 class="modal-title text-white m-0">
                        <table class="w-100"><tr><td><i class="fa fa-shuffle"></i> Solicitudes de retiro</td><td class="ps-3 text-white"><span id="m_titulo"></span></td><td class="text-end"></td></tr></table>
                    </h5>
					<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
				</div>
				<div class="modal-body"></div>
			</form>
		</div>
	</div>
</div>

<script>
    var g_todas      = <?php echo intval( sizeof( $solicitudes ) ); ?>,
        g_pendientes = <?php echo intval( sizeof( $solicitudes )  - $transferidas ); ?>,
        g_pagadas    = <?php echo intval( $transferidas ); ?>;
</script>

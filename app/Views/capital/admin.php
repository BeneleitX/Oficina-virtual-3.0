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

        </div>

        <div class="col-lg-4">

        </div>

        <div class="col-lg-4">

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
            <th>Wallet</th>
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

            echo "\n
            <tr socio=\"{$u->id}\">
                <td>".$u->id( "50-INVERSION", false, false )."</td>
                <td nowrap>".$u->avatar( 24 )." ".$u->nombre( 2 )."</td>
                <td><span class=\"badge bg-{$p->data->color}\">{$p->data->nombre}</span></td>
                <td class=\"text-end\"><span class=\"text-teal fw-bold\">$".number_format( $s[ "cantidad" ], 2 )."</span> <button class=\"btn btn-light btn-sm\" onclick=\"navigator.clipboard.writeText( '".number_format( $s[ "cantidad" ], 2 )."' )\"><i class=\"fa fa-copy\"></i></button></td>
                <td><span class=\"badge bg-marine\">{$s[ "tipo" ]}</span></td>
                <td class=\"small\">".( isset( $u->data->wallet ) ? $u->data->wallet." <button class=\"btn btn-light btn-sm\" onclick=\"navigator.clipboard.writeText( '{$u->data->wallet}')\"><i class=\"fa fa-copy\"></i></button>": "SIN WALLET")."</td>
                <td class=\"text-end\"><a href=\"".base_url()."statement/".urlencode( base64_encode( $i[ "extras" ][ "TxHash" ] ) )."\" target=\"_blank\" class=\"btn btn-sm btn-info\"><i class=\"fa fa-magnifying-glass\"></i> Ver detalles</a> <button class=\"btn btn-sm btn-success\"><i class=\"fa fa-shuffle\"></i> Transferir</button></td>
            </tr>";
        }
        ?>
    </tbody>
</table>
 

<div class="modal" tabindex="-1" id="modal_tarjeta">
	<div class="modal-dialog modal-lg">
		<div class="modal-content">
			<form method="post" action="<?php echo base_url( "vincula_tarjeta" ); ?>">
                <?php echo csrf_field() ?>
                <input type="hidden" name="v_socio"  value="">

				<div class="modal-header bg-red">
					<h5 class="modal-title text-white"><i class="fa fa-credit-card"></i> Vincular tarjeta a socio</h5>
					<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
				</div>
				<div class="modal-body">
                    <div class="row">
                        <div class="col-lg-4 text-center">
                            <img src="<?php echo base_url(); ?>assets/img/productos/915-TARJETA.png" class="img-fluid px-3">
                        </div>
                        <div class="col-lg-8">
                            <p class="text-center m-0"><img class="w-50" src="<?php echo base_url();?>assets/img/efectivale.jpg"></p>

                            <div class="card"><div class="card-body">
                            <div class="row">
                                <div class="col-4 text-end">16 dígitos</div>
                                <div class="col-6"><input type="text" class="form-control mb-3" name="v_tarjeta1"></input></div>
                            </div>
                            <div class="row">
                                <div class="col-4 text-end">Repite 16 dígitos</div>
                                <div class="col-6"><input type="text" class="form-control" name="v_tarjeta2"></input></div>
                            </div>
                            </div></div>
                        </div>
                    </div>
				</div>
				<div class="modal-footer">
					<button type="submit" class="btn btn-danger" id="submit_tarjeta" disabled><i class="fa fa-check"></i> Vincular</button>
				</div>
			</form>
		</div>
	</div>
</div>


<script>
    var g_todas      = <?php echo intval( sizeof( $solicitudes ) ); ?>,
        g_pendientes = <?php echo intval( sizeof( $solicitudes )  - $transferidas ); ?>;
        g_pagadas    = <?php echo intval( $transferidas ); ?>;
</script>

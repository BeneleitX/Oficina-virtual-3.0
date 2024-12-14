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
        <h5>Total de recargas en el mes: <span class="badge bg-gray-500" id="totales">0</span>
        <select id="mes_recargas" class="ms-4 form-select" style="display: inline-block; width:auto">
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
            <h5>1. Listado de socios</h5> 
            <p>Este listado incluye a socios que han adquirido su tarjeta para recargas de <strong>GASOLINA</strong>.</p>
            <p>Se puede hacer una nueva vinculaciuón de tarjeta con el socio ingresando su número de 16 dígitos, utilizando el botón <span class="badge bg-mustard text-white">VINCULAR TARJETA</span></p>
        </div>

        <div class="col-lg-4">
        <h5>2. Vincular tarjeta a socio</h5> 
            <p>Para vincluar una tarjeta se debe escribir dos veces el número de la tarjeta. Si la información es correcta, el sistema permitirá avanzar y habilitará el botón de <span class="badge bg-teal text-white">VINCULAR</span></p><p>Una vez que el socio reciba su tarjeta, deberá activarla repitiendo el proceso desde su propia oficina virtual</p>     
        </div>

        <div class="col-lg-4">
            <h5>3. Aplicar recargas</h5> 
            <p>Los socios que hayan adquirido su tarjeta, podrán comprar recargas de gasolina. En este listado se mostrarán las cantidades que el socio vaya adquisiendo en el mes seleccionado y podrán marcarse como aplicadas utilizando el botón <span class="badge bg-red text-white">RECARGAS</span> en cual será de color <span class="text-red">ROJO</span> cuando haya recargas pendientes por aplicar.</p>
        </div>
    </div>
</div>


<table class="table table-striped bg-white" id="tabla_socios">
    <thead>
        <tr>
            <th>Socio</th>
            <th>Nombre</th>
            <th>Paquetes <?php echo mes( date( "m" ) ); ?></th>
            <th>Calificación</th>
            <th>Tarjeta</th>
            <th>&nbsp;</th>
        </tr>
    </thead>
    <tbody>
        <?php
      //  $mes = date( "Ym" );
        
        $todas = 0;

        foreach( $socios as $s ){
            $calificacion = 0;

            $u = model( "UsuarioModel" )->find( $s->id );
            $paquetes = "";
            $recargas = 0;

            foreach( $promociones as $p ){
                $cantidad = $u->historial->modelos->{"40-GASOLINAS"}->calificaciones->{$mes}->{$p[ "codigo" ]} ?? 0;

                if( $p[ "codigo" ] == "414-GASOLINA" ){
                    $recargas = $cantidad;
                    $todas   += $cantidad;
                }

                if( $cantidad ){
                    $calificacion += $cantidad;
                    $paquetes .= "<span class=\"badge bg-{$p[ "settings" ][ "clase" ]}\">{$p[ "settings" ][ "siglas" ]} {$cantidad}</span> ";
                }
            }
            
            if( $calificacion > 5 ){
                $calificacion = 5;
            }

            echo "\n
            <tr socio=\"{$u->id}\">
                <td>".$u->id( "40-GASOLINAS", false, false )."</td>
                <td nowrap>".$u->avatar( 24 )." ".$u->nombre( 2 )."</td>
                <td>{$paquetes}</td>
                <td><span class=\"badge bg-".( ESTATUS[ $u->data->estatus->modelos->{"40-GASOLINAS"} ][ "color" ])."\">NIVEL {$calificacion}</span></td>
                <td>".( $u->data->tarjeta ?? null ? "<span class=\"badge bg-gray-400 text-marine\"><i class=\"fa fa-credit-card\"></i> {$u->data->tarjeta->numero}</span> ".estatus( $u->data->tarjeta->numero ? $u->data->tarjeta->estatus : "330-EN-ESPERA" ) : "<i class=\"fa fa-warning text-red\"></i> Sin vincular")."</td>
                <td class=\"text-end\">".( ( $u->data->tarjeta->numero ?? null ) ? ( $recargas > 0 ? "<button class=\"btn btn-sm btn-".( $s->recargas < $recargas ? "danger" : "light" )."\" onclick=\"do_recarga( {$u->id} )\"><i class=\"fa fa-gas-pump\"></i> Recargas</button>" : "" ) : "<button class=\"btn btn-sm btn-warning\" onclick=\"vincular_tarjeta( {$u->id} )\"><i class=\"fa fa-credit-card\"></i> Vincular tarjeta</button>")."</td>
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
                            <p class="mb-1">Folio <span class="badge bg-mustard">EFECTIVALE</span></p>
                            <div class="row"><div class="col-lg-3"><input type="text" class="form-control mb-3" name="v_folio"></input></div></div>

                            <p class="mb-1">Escriba los 16 dígitos de la tarjeta a vincular al socio <span id="num_socio" class="badge bg-marine"></span></p>
                            <div class="row"><div class="col-lg-6"><input type="text" class="form-control mb-3" name="v_tarjeta1"></input></div></div>

                            <p class="mb-1">Repita con cuidado los 16 dígitos de la tarjeta</p>
                            <div class="row"><div class="col-lg-6"><input type="text" class="form-control" name="v_tarjeta2" disabled></input></div></div>
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


<div class="modal" tabindex="-1" id="modal_recargas">
	<div class="modal-dialog modal-xl">
		<div class="modal-content">
			<form method="post" action="<?php echo base_url( "do_recarga" ); ?>">
                <?php echo csrf_field() ?>
                <input type="hidden" name="r_socio"  value="">

				<div class="modal-header bg-marine">
                    <h5 class="modal-title text-white m-0">
                        <table><tr><td><i class="fa fa-gas-pump"></i> Recargas a tarjeta</td><td class="ps-3 text-white"><span class="badge bg-red" id="m_titulo"></span></td></tr></table>
                    </h5>
					<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
				</div>
				<div class="modal-body"></div>
			</form>
		</div>
	</div>
</div>


<script>
    var g_todas   = <?php echo intval( $todas ); ?>,
        g_pagadas = <?php echo intval( $total ); ?>;
</script>
<link href="<?php echo base_url(); ?>assets/css/datatables.css" rel="stylesheet"/>
<script src="<?php echo base_url(); ?>assets/js/datatables.js" type="text/javascript"></script>
<script src="<?php echo base_url(); ?>assets/js/datatables_bs5.js" type="text/javascript"></script>

<div class="row">
    <div class="col-4">
    <h4 class="mt-1 mb-0"><?php echo $titulo; ?></h4>
        <p>
            <a class="btn btn-light btn-sm" href="<?php echo base_url( "inversiones" ); ?>"><i class="fa fa-undo"></i> Regresar a dashboard Capital24</a>
        </p>
    </div>

    <div class="col-8 text-end pt-3">
        <h5><?php echo false ? "<a href=\"".base_url( "inversiones" )."\"class=\"btn btn-secondary me-5\"><i class=\"fa fa-dashboard\"></i> Ir a dashboard</a> " : ""; ?>Rangos alcanzados en el mes: 
            <span class="badge bg-teal" id="pendientes"><?php echo sizeof( $socios ); ?></span>
            <select id="mes_retiros" class="ms-4 form-select" style="display: inline-block; width:auto">
                <?php
                $fecha = date( "Y-m-d", strtotime( date( "Y-m-d" )." - 1 month") );
                $mes_x = date( "Ym", strtotime( $fecha ) );

                while( $mes_x >= '202502' ){
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
            <p><strong>Los rangos son una recompensa por expandir conexiones y dan derecho automático al bono de liderazgo.</strong></p>
            <p>Según el número de directos activos será el porcentaje de ganancia del volumen grupal semilla invertido.</p>
            <button class="btn btn-primary" onclick="excel_rangos( <?php echo $mes; ?> )"><i class="fa fa-file-excel"></i> Descargar excel</button>
        </div>

        <div class="col-lg-4">
            <table class="w-100"><tr>
            <?php
            $rangos = [ "510-PIONERO", "520-CONQUISTADOR", "530-LEYENDA" ];
            foreach( $rangos as $rango ){
                $r = RANGOS[ $rango ];
                echo "\n<td width=\"33%\" class=\"text-center\"><div class=\"card text-center\"><div class=\"card-body text-center px-0\">
                    <img src=\"".base_url()."assets/img/rangos/{$r[ "codigo" ]}.png\" style=\"width:80px\" alt=\"\">
                    <h5>{$r[ "nombre" ]}</h5>
                    <p class=\"m-0 small\">
                        Directos: <strong>{$r[ "cantidades" ][ "directos" ][ 0 ]}</strong><br>
                        Porcentaje: <strong>{$r[ "cantidades" ][ "porcentaje" ]}%</strong>
                    </p></div></div>
                </td>";    
            }
            ?> 
                
            </tr></table>
        </div>

        <div class="col-lg-4">
            <h5>Cálculo de bono:</h5>
            <ol class="m-0">
                <li>El rango se calcula al finalizar el mes, haciendo un corte de socios directos activos y el volumen de capital semilla de la red.</li>
                <li>Se debe esperar a que transcurra el mes siguiente, para que ese volumen de capital semilla genere rendimientos.</li>
                <li>El bono se pagará al finalizar el mes siguiente, durante los primeros 3 días hábiles.</li>
            </ol>
        </div>
    </div>
</div>



<table class="table table-striped bg-white" id="tabla_solicitudes">
    <thead>
        <tr>
            <th>Socio</th>
            <th>Nombre</th>
            <th>Telefono</th>
            <th>Wallet</th>
            <th>Directos</th>
            <th>Bolsa semilla</th>
            <th>Rango</th>
            <th>X bono</th>
            <th>Total de bono</th>
        </tr>
    </thead>
    <tbody>
        <?php 
      //  $mes = date( "Ym" );
        
        foreach( $socios as $s ){

            $u = model( "UsuarioModel" )->find( $s[ "id" ] );
            $rango = RANGOS[ $s[ "rango" ] ];

            echo "\n
            <tr socio=\"{$u->id}\">
                <td>".$u->id( "50-INVERSION", false, false )."</td>
                <td nowrap><span class=\"d-none\">".".$u->nombre()."."</span>".$u->avatar( 24 )." ".$u->nombre( 2 )."</td>
                <td>{$u->telefono}</td>
                <td><span class=\"badge bg-gray-200 text-marine\">".( $u->data->wallet ?? "<span class=\"text-red\"><i class=\"fa fa-warning\"></i> NO WALLET</span>" )."</span></td>
                <td class=\"text-center\">{$s[ "mes" ][ "directos" ]}</td>
                <td class=\"text-end\">$".number_format( $s[ "mes" ][ "bolsa" ], 2)."</td>
                <td><span class=\"d-none\">{$rango[ "codigo" ]}</span><span class=\"badge bg-{$rango[ "color" ]}\">".strtoupper( $rango[ "nombre" ] )."</span></td>
                <td class=\"text-center\">{$s[ "mes" ][ "bono" ]}</td>
                <td class=\"text-end\">$".number_format( $s[ "mes" ][ "bolsa" ] * $s[ "mes" ][ "bono" ] / 100, 2 )."</td>
            </tr>";
        }
        ?>
    </tbody>
</table>
 

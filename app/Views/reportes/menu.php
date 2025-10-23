<link href="<?php echo base_url(); ?>assets/css/datatables.css" rel="stylesheet"/>
<script src="<?php echo base_url(); ?>assets/js/datatables.js" type="text/javascript"></script>
<script src="<?php echo base_url(); ?>assets/js/datatables_bs5.js" type="text/javascript"></script>

<h4 class="mt-1 mb-0"><?php echo $titulo; ?></h4>
<p><a class="btn btn-sm btn-light" href="<?php echo base_url( "admin" ); ?>"><i class="fa fa-undo"></i> Regresar a administración</a></p>

<table class="table table-striped" id="tabla_reportes">
    <thead><tr><th>Reportes disponibles:</th></tr></thead>
    <tbody>

    <?php
    $reportes = [
        [
            "url" => base_url( "reportes/socios_por_estatus" ),
            "icono" => "file-excel",
            "texto" => "Socios por estatus",
            "permisos" => [ "36-REPORTES", "40-ADMIN" ]
        ],
        [
            "url" => base_url( "reportes/ingresos_por_empresa" ),
            "icono" => "table-cells",
            "texto" => "Ingresos por empresa",
            "permisos" => [ "39-REPORTES-CONTA", "40-ADMIN" ]
        ],
        [
            "url" => base_url( "reportes/pedidos_diarios" ),
            "icono" => "shopping-basket",
            "texto" => "Pedidos por empresa",
            "permisos" => [ "39-REPORTES-CONTA", "40-ADMIN", "36-REPORTES" ]
        ],
        [
            "url" => base_url( "reportes/venta_producto" ),
            "icono" => "spray-can-sparkles",
            "texto" => "Venta por producto",
            "permisos" => [ "39-REPORTES-CONTA", "40-ADMIN", "36-REPORTES" ]
        ],
        [
            "url" => base_url( "reportes/calificaciones_mes" ),
            "icono" => "user-tag",
            "texto" => "Calificaciones",
            "permisos" => [ "40-ADMIN", "36-REPORTES" ]
        ]  
    ];


    foreach( $reportes as $r ){
        $permiso = 0;

        foreach( $r[ "permisos" ] as $p ){

            if( $usuario->permiso( $p ) ){
                $permiso = 1;
            }
        }

        echo "\n<tr><td>".( $permiso ? "<a class=\"d-block\" href=\"{$r[ "url" ]}\"><i class=\"fa fa-{$r[ "icono" ]}\"></i>" : "<i class=\"fa fa-lock text-red\"></i>" )." {$r[ "texto" ]}".( $permiso ? "</a>" : "" )."</td></tr>";
    }
    ?>

</tbody>
</table>
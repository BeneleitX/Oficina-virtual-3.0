<link href="<?php echo base_url(); ?>assets/css/datatables.css" rel="stylesheet"/>
<script src="<?php echo base_url(); ?>assets/js/datatables.js" type="text/javascript"></script>
<script src="<?php echo base_url(); ?>assets/js/datatables_bs5.js" type="text/javascript"></script>

<h4 class="mt-1 mb-0"><?php echo $titulo; ?></h4>
<p><a href="<?php echo base_url( "admin" ); ?>"><i class="fa fa-undo"></i> Regresar a configuración</a></p>

<div class="row">
    <div class="col-lg-10">
        <?php echo pills( "almacenes", $modelo ); ?>        
    </div>
    <div class="col-lg-2">
        <a href="<?php echo base_url( "transferencias/".$modelo ); ?>" class="btn btn-secondary col-12 my-4"><i class="fa fa-truck-arrow-right"></i> Transferir productos</a>
    </div>
</div>


<table class="table table-striped bg-white" id="tabla_almacenes">
    <thead>
        <tr>
            <th>Código</th>
            <th>Nombre</th>
            <th>Estatus</th>
            <th>Socio</th>
            <th>Pedidos</th>
            <th>Tipo</th>
            <th></th>
        </tr>
    </thead>

    <tbody>
        <?php 
            $socios = [];

            foreach( $almacenes as $a ){
                $a[ "settings"  ] = json_decode( $a[ "settings"  ], true );
                $a[ "productos" ] = json_decode( $a[ "productos" ], true );

                if( !isset( $socios[ $a[ "settings" ][ "socio" ] ] )){
                    $socios[ $a[ "settings" ][ "socio" ] ] = $a[ "socio" ] = model( "UsuarioModel" )->find( $a[ "settings" ][ "socio" ] );
                }
                $socio = $socios[ $a[ "settings" ][ "socio" ] ];

                echo "\n<tr almacen=\"{$a[ "codigo" ]}\">
                    <td>{$a[ "codigo" ]}</td>
                    <td>{$a[ "nombre" ]}</td>
                    <td>".estatus( $a[ "estatus_codigo" ] )."</td>
                    <td>".( $socio ? $socio->avatar(24)." ".$socio->id( null, "marine" )." ".$socio->nombre(2) : "" )."</td>
                    <td class=\"text-center\">".( $a[ "pedidos" ] > 0 ? "<strong>{$a[ "pedidos" ]}</strong>" : "-")."</td>
                    <td class=\"text-center\">{$a[ "settings" ][ "tipo" ]}</td>
                    <td class=\"text-end\"><a href=\"".base_url( "almacen/".$a[ "codigo" ] )."\" class=\"btn btn-xs btn-primary\">DETALLES</a></td>
                </tr>";
            }
        ?>
     
    </tbody>
</table>

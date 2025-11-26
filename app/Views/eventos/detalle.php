<link href="<?php echo base_url(); ?>assets/css/datatables.css" rel="stylesheet"/>
<script src="<?php echo base_url(); ?>assets/js/datatables.js" type="text/javascript"></script>
<script src="<?php echo base_url(); ?>assets/js/datatables_bs5.js" type="text/javascript"></script>

<div class="row">
    <div class="col-4">
        <h4 class="mt-1 mb-0"><?php echo $titulo; ?></h4>
        <p class="mb-4"><a href="<?php echo base_url( "eventos" ); ?>" class="btn btn-light btn-sm"><i class="fa fa-undo"></i> Regresar a eventos</a></p>
    </div>
    <div class="col-4">
    <img class="rounded img-fluid" src="<?php echo base_url()."assets/img/promociones/{$evento[ "codigo" ]}.png"; ?> ">
    </div>
    <div class="col-4 text-end pt-0">
        <table align="right"><tr><td>
            <h5 class="mt-2 mb-0 me-4" >Socios registrados: <span class="badge bg-marine"><?php echo sizeof( $socios ); ?></span></h5>
        </td><td>
            <button class="btn btn-success col-12 mt-3" id="descarga_semillero"><i class="fa fa-file-excel"></i><span class="d-none d-lg-inline"> Descargar Excel</span></button>
        </td></tr></table>
    </div>
</div>


<table id="tabla_participantes" class="table table-striped bg-white">
    <thead>
        <tr>
            <th>Socio</th>
            <th>Nombre</th>
            <th>Teléfono</th>
            <th>Estatus</th>
            <th>Pedido</th>
            <th>Pago</th>
            <th>Regalos</th>
            <th>Fecha</th>
        </tr>
    </thead>
    <tbody>
        <?php

        foreach( $socios as $s ){
            $u = model( "UsuarioModel" )->find( $s[ "usuario" ] );

            switch( $s[ "productos" ] ){
                case 8:  $regalo = "blue"; break;
                case 7:  $regalo = "light-blue"; break;
                case 6:  $regalo = "teal"; break;
                case 5:  $regalo = "green"; break;
                case 4:  $regalo = "lime"; break;
                case 3:  $regalo = "yellow"; break;
                case 2:  $regalo = "mustard"; break;
                case 1:  $regalo = "orange"; break;
                default:  $regalo = "red"; break;
            }

            $pk = [
                "referencia" => $s[ "referencia" ],
                "modelo_codigo" => "90-SEMILLERO"
            ];

            echo "\n<tr>
                        <td>".$u->id( null, "marine" )."</td>
                        <td>".$u->avatar( 24 )." ".$u->nombre( 2 )."</td>
                        <td>{$u->telefono}</td>
                        <td>".estatus( $u->estatus_codigo )."</td>
                        <td>".referencia( $pk )."</td>
                        <td><strong>$".number_format( $s[ "pago" ], 2 )."</strong></td>
                        <td class=\"text-center\"><i class=\"fa fa-gift text-{$regalo}\"></i> {$s[ "productos" ]}</td>
                        <td><span class=\"d-none\">{$s[ "fecha" ]}</span> ".fecha( $s[ "fecha" ] )."</td>                        
                    </tr>";
        }
        ?>

    </tbody>
</table> 
 
<script>
    var evento = '<?php echo $evento[ "codigo" ]; ?>';
</script>

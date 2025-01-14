<link href="<?php echo base_url(); ?>assets/css/datatables.css" rel="stylesheet"/>
<script src="<?php echo base_url(); ?>assets/js/datatables.js" type="text/javascript"></script>
<script src="<?php echo base_url(); ?>assets/js/datatables_bs5.js" type="text/javascript"></script>

<div class="row">
    <div class="col-6">
        <h4 class="mt-1 mb-0"><?php echo $titulo; ?></h4>
        <p><a href="<?php echo base_url( "admin" ); ?>" class="btn btn-sm btn-light"><i class="fa fa-undo"></i> Regresar a administración</a></p>
    </div>

    <div class="col-6 text-end text-red">
        
        <a href="<?php echo base_url( "facturas" ); ?>" class="btn btn-<?php echo $facturas ? "danger" : "success"; ?>"><i class="fa fa-file-invoice-dollar"></i> Facturación de pedidos <?php echo $facturas ? "<span class=\"\"> - Pendientes: {$facturas}</span>" : ""; ?> </a>
    </div>
</div>


<div class="alert alert-info mb-5">
    <div class="row">
    <div class="col-lg-4">
            Los socios que aplican para la modalidad de <strong>NO-RETENCION</strong> aplican segun su propia configuración desde su perfil de datos
        </div>
        <div class="col-lg-4">
            Los socios que aplican para la modalidad de <strong>VENTAS</strong> aplican directamente por configuración manual de administrador de sistema. Esta lista puede ser editada directamente en esta pagina.
        </div>
    </div>
</div>

<div class="row">
    <div class="col-lg-6 mb-5">
        <form method="post" action="<?php echo base_url( "poner_ventas" ); ?>">
            <?php echo csrf_field() ?>

            <h5>NO-RETENCIÓN</h5>
            <table class="table table-striped bg-white tabla-facturacion w-100">
                <thead>
                    <tr>
                        <th>Socio</th>
                        <th>Nombre</th>
                        <th></th>
                    </tr>
                </thead>

                <tbody>
                <?php 
                    foreach( $no_retencion as $u ){
                        echo "\n<tr>
                                    <td>".$u->id( null, "marine", false )."</td>
                                    <td>".$u->avatar( 24 )." ".$u->nombre( 2 )."</td>
                                    <td><button type=\"submit\" name=\"socio\" value=\"{$u->id}\" class=\"btn btn-sm btn-success\"><i class=\"fa fa-angles-right\"></i></button></td>
                                </tr>";
                    }
                ?>
                </tbody>
            </table>
        </form>
    </div>

    <div class="col-lg-6 mb-5">
        <form method="post" action="<?php echo base_url( "quitar_ventas" ); ?>">
            <?php echo csrf_field() ?>

            <h5>VENTAS</h5>
            <table class="table table-striped bg-white tabla-facturacion w-100">
                <thead>
                    <tr>
                        <th>Socio</th>
                        <th>Nombre</th>
                        <th></th>
                    </tr>
                </thead>

                <tbody>
                <?php 
                        foreach( $ventas as $u ){
                            echo "\n<tr>
                                        <td>".$u->id( null, "marine", false )."</td>
                                        <td>".$u->avatar( 24 )." ".$u->nombre( 2 )."</td>
                                        <td><button type=\"submit\" name=\"socio\" value=\"{$u->id}\" class=\"btn btn-sm btn-danger\"><i class=\"fa fa-angles-left\"></i></button></td>
                                    </tr>";
                        }
                    ?>
                </tbody>
            </table>
        </form>
    </div>
</div>
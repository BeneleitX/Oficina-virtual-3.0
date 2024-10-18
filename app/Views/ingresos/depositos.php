<link href="<?php echo base_url(); ?>assets/css/datatables.css" rel="stylesheet"/>
<link href="<?php echo base_url(); ?>assets/css/responsive.css" rel="stylesheet"/>
<script src="<?php echo base_url(); ?>assets/js/datatables.js" type="text/javascript"></script>
<script src="<?php echo base_url(); ?>assets/js/datatables_bs5.js" type="text/javascript"></script>
<script src="<?php echo base_url(); ?>assets/js/responsive.js" type="text/javascript"></script>

<h4 class="mt-1 mb-0"><?php echo $titulo; ?></h4>
<p class="mb-3">Hoy es <?php echo dia( date("N") )." ".date("d")." de ".mes( date("m") ).", ".date("Y") ?></p>

<div class="row mb-4">
    <div class="col-lg-8">
        <?php echo pills( "depositos", $modelo ); ?>
    </div>
    <div class="col-lg-4 text-end">
        <button class="btn btn-success d-none"><i class="fa fa-file-excel"></i> Descargar Excel</button>

        <div class="row">
        <div class="col-4">
                <a href="<?php echo base_url()."balance/{$modelo}/".codigo_periodo( $modelo ); ?>" class="btn btn-outline-secondary"> Detalle SEMANAL</a>
            </div>
            <div class="col-4 d-none">
            <a href="#" class="btn btn-outline-secondary"> Detalle MENSUAL</a>
            </div>
            <div class="col-4">
            <a href="<?php echo base_url()."depositos/{$modelo}"; ?>" class="btn btn-secondary"> Depósitos recibidos</a>
            </div>
        </div>
    </div>
</div>

<table class="mb-0 table table-striped bg-white tabla_pagos" id="t_<?php echo date("Y-m-d"); ?>">
    <thead>
        <tr>
            <th class="text-center">Semana</th>
            <th class="text-end">Cantidad</th>
            <th>Estatus</th>
            <th>CLABE Interbancaria</th>
            <th class="text-center">Folio</th>
            <th>fecha de depósito</th>
            <th></th>
        </tr>
    </thead>

    <tbody>

        <?php 
            $socios = [];
            foreach( $pagos as $pago ){
                $desglose = aplicaImpuestos( $pago[ "total" ], $pago[ "impuestos" ], $pago[ "fecha" ] );


                foreach( $desglose as $d ){
                    if( $d[ "descripcion" ] == "TOTAL" ){
                        $total = $d[ "cantidad" ];
                    }
                }

                echo "
                    <tr>
                        <td class=\"text-center\"><span class=\"d-none\">{$pago["periodo"]}</span><span class=\"badge bg-marine\">".periodo( $pago["periodo" ] )."</span></td>
                        <td class=\"text-end\">$".number_format( $total , 2)."</td>
                        <td><span class=\"d-none\">{$pago["estatus"]}</span>".estatus( $pago["estatus"] )."</td>
                        <td>{$pago["clabe"]} ".( $pago["menor"] > 0 ? "<small><span class=\"badge bg-pink\">MENOR</span></small>" : "" )."</td>
                        <td class=\"text-center\">{$pago["folio"]}</td>
                        <td>".( $pago[ "fecha" ] )."</td>
                        <td class=\"text-end\"><button onclick=\"detalle_pago( {$pago["folio"]}, '".periodo( $pago["periodo" ] )."' )\" class=\"btn btn-sm btn-warning\"><i class=\"fa fa-magnifying-glass\"></i> Detalles</button></td>
                    </tr>
                ";
            }
        ?>
        </tbody>
    </table>




    <div class="modal" tabindex="-1" id="detalle_pago">
        <div class="modal-dialog">
            <div class="modal-content">
                <form method="post" action="<?php echo base_url( "cancela_pedido" ); ?>">
                    <?php echo csrf_field() ?>

                    <div class="modal-header">
                        <h5 class="modal-title"><i class="fa fa-xmark"></i> Cancelar pedido</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="alert alert-danger m-0"><ul class="m-0">
                            <li>Al cancelar el pedido se liberarán todas las promociones incluídas por acumulación de puntos, como bono de lealtad o productos de regalo. Para obtenerlas deberá crear un nuevo pedido.</li>
                            <li>Esta acción no es reversible.</li>
                        </ul></div>

                        <?php echo csrf_field(); ?>
                        <input type="hidden" name="pedido" value="<?php // echo $pedido[ "id" ]; ?>">
                    </div>
                </form>
            </div>
        </div>
    </div>



<script>
    var modelo = '<?php echo $modelo; ?>';
</script>

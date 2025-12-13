<link href="<?php echo base_url(); ?>assets/css/datatables.css" rel="stylesheet"/>
<script src="<?php echo base_url(); ?>assets/js/datatables.js" type="text/javascript"></script>
<script src="<?php echo base_url(); ?>assets/js/datatables_bs5.js" type="text/javascript"></script>

<h4 class="mt-1 mb-0"><?php echo $titulo; ?></h4>
<p><a class="btn btn-light btn-sm" href="<?php echo base_url( "admin" ); ?>"><i class="fa fa-undo"></i> Regresar a Administración</a></p>

<div class="row">
    <div class="col-lg-6">
        <?php echo pills( "rangos", $modelo ); ?>
    </div>

    <?php if( $modelo == '10-NUTRICION'){ ?>
    <div class="col-lg-3">
        <a class="btn mt-3 col-12 btn-secondary" href="<?php echo base_url( "rangos/".$modelo ); ?>"><i class="fa fa-gem"></i> Catálogos</a>
    </div>
    <?php } ?>

    <div class="col-lg-3">
        <button class="btn mt-3 col-12 btn-primary" onclick="excel_pines()"><i class="fa fa-file-excel"></i> Descargar Excel</button>
    </div>
</div>

<div class="row">
<?php 
    foreach( $rangos as $r ){

        $activos = isset( $socios[ $r[ "codigo" ] ][ "activos"] ) ? $socios[ $r[ "codigo" ] ][ "activos"] : 0;
        $alcanzados = isset( $socios[ $r[ "codigo" ] ][ "activos"] ) ? $socios[ $r[ "codigo" ] ][ "activos"] : 0;
        $inactivos  = isset( $socios[ $r[ "codigo" ] ][ "inactivos"] ) ? $socios[ $r[ "codigo" ] ][ "inactivos"] : 0;
        $ps = isset( $pendientes[ $r[ "codigo" ] ] ) ? $pendientes[ $r[ "codigo" ] ] : 0;

        echo "\n<div class=\"col-lg-3 col-md-4 col-sm-6\"><a href=\"".base_url( "entrega_pines/{$r[ 'codigo' ]}" )."\">
                    <div class=\"card mb-2\" style=\"overflow:hidden; position:relative\">
                        <div style=\"position:absolute; top:20px; right:15px\"><span class=\"badge fs-6 bg-".( $activos ? "marine" : "gray-400" )."\">{$activos}</span></div>
                        <table class=\"bg-white w-100\"><tr>
                            <td>
                                <img src=\"".base_url()."assets/img/rangos/{$r[ "codigo" ]}.png\" style=\"width:100px\" class=\"m-2\">
                            </td>
                            <td class=\"w-100\" style=\"line-height:1\">
                                <h5 class=\"m-0 text-{$r[ "color" ]}\">{$r[ "nombre" ]}</h5>
                                <span class=\"small text-teal\">".number_format( $r[ "cantidades" ][0], 2 )." - ".number_format( $r[ "cantidades" ][1], 2 )."</span>
                                <p class=\"mt-3 mb-2 d-none\">".( substr( $r[ "codigo" ], 1, 2 ) > 0 ? "Alcanzados: <span class=\"float-end me-3 badge bg-".( $alcanzados ? "teal" : "gray-400" )."\">{$alcanzados}</span>" : "&nbsp;" )."</p>
                                <p class=\"mb-0 mt-3\">".( substr( $r[ "codigo" ], 1, 2 ) > 0 ? "Por entregar: <span class=\"float-end me-3 badge bg-".( $ps ? "mustard" : "gray-400" )."\">{$ps}</span>" : "&nbsp;" )."</p>
                            </td>
                        </tr></table>
                    </div>
                </a></div>";
    }
?>
</div>

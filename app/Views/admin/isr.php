<link href="<?php echo base_url(); ?>assets/css/datatables.css" rel="stylesheet"/>
<script src="<?php echo base_url(); ?>assets/js/datatables.js" type="text/javascript"></script>
<script src="<?php echo base_url(); ?>assets/js/datatables_bs5.js" type="text/javascript"></script>

<h4 class="mt-1 mb-0"><?php echo $titulo; ?></h4>
<p><a href="<?php echo base_url( "admin" ); ?>"><i class="fa fa-undo"></i> Regresar a administración</a></p>


<?php

$year = null;

foreach( $isr as $row ){

    if( $row[ "anio"] != $year ){
        if( $year )
        echo "</tbody></table></div>";

        $year = $row[ "anio" ];

        echo  "\n
        <div class=\"card w-50\"><div class=\"card-header bg-marine\"><h5 class=\"m-0 text-white\">{$row[ "anio" ]}</h5></div><div class=\"card-body\">
        <table class=\"table table-striped bg-white tabla_isr\">
            <thead>
                <tr>
                    <th>Minimo</th>
                    <th>Máximo</th>
                    <th>Fijo</th>
                    <th>Porcentaje</th>
                </tr>
            </thead>

            <tbody>
        ";
    }

    echo "\n<tr>
        <td>{$row[ "minimo" ]}</td>
        <td>{$row[ "maximo" ]}</td>
        <td>{$row[ "fijo" ]}</td>
        <td>{$row[ "porcentaje" ]}</td>
    </tr>";
}

echo "\n</tbody></table></div></div>";
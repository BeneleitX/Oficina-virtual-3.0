<?php

$meses = [
    "2025-10",
    "2025-09",
    "2025-08"
];

$puntos = [
    0,0,0
];

$estatus = [
    "130-NUEVO-SUSPENDIDO",     // gris
    "140-SUSPENDIDO",           // gris
    "210-NUEVO",                // morado
    "309-CLIENTE",              // cafe
    "310-NO-CALIFICADO",        // rojo
    "320-NO-CALIFICADO-COMPRA", // naranja
    "410-CALIFICADO",           // amarillo
    "510-NUEVO-CALIFICADO",     // verde
    "520-CALIFICADO-ACTUAL",    // verde 
    "612-STAFF-PERMANENTE"      // azul
];

?>
<div class="row mb-4">
    <div class="col-3">
        <div class="card">
            <table class="table table-striped mb-0">
                <thead>
                    <tr>
                        <th width="60%" style="background:var( --bs-teal ) !important" class="text-white">MES</th>
                        <th width="40%" style="background:var( --bs-teal ) !important" class="text-white text-center">PUNTOS</th>
                    </tr>
                </thead>
                <tbody>
                    <?php 
                    for( $a = 0; $a < 3; $a++ ){
                        echo "<tr><td>".mes( substr( $meses[ $a ] , 5, 2 ) )." ".substr( $meses[ $a ] , 0, 4 )."</td><td><input type=\"number\" class=\"puntos form-control form-control-sm text-center\" id=\"puntos_{$a}\" value=\"{$puntos[ $a ]}\"></td></tr>";
                    }
                    ?>
                </tbody>
            </table>
        </div>
    </div>

    <div class="col-3">
        <div class="card">
            <table class="table table-striped mb-0">
                <thead>
                    <tr>
                        <th width="60%" style="background:var( --bs-teal ) !important" class="text-white">DATOS EXTRA</th>
                        <th width="40%" style="background:var( --bs-teal ) !important" class="text-white text-center"></th>
                    </tr>
                </thead>
                <tbody>
                    <tr><td>Mes 1er compra</td><td><input class="form-control form-control-sm text-center" id="primera" value="2025-10" type="month"></td></tr>
                    <tr><td>Mes de baja</td><td><input class="form-control form-control-sm text-center" id="baja" value="2026-04" type="month"></td></tr>
                    <tr><td>Tipo calificación</td><td><select class="form-select form-select-sm" id="tipo"><option value="1">Por puntos</option><option value="2">Permanente</option></select"></td></tr>
                </tbody>
            </table>

        </div>
    </div>    

    <div class="col-3">
        <table class="table">
            <tbody>
                <tr><td width="60%">&nbsp;</td><td width="40%"><button id="actualizar" class="btn btn-primary w-100"><i class="fa fa-sync"></i> Actualizar</button</td></tr>
            </tbody>
        </table>
    </div>       
</div>

<p class="fs-1" id="log">
<?php foreach( $estatus as $e ){ echo "<span id=\"e_{$e}\" style=\"text-transform:uppercase\" class=\"badge bg-".ESTATUS[ $e ][ "color" ]." text-gray-200\">".ESTATUS[ $e ][ "descripcion" ]."</span> "; }  ?>
</p>

<script>

var estatus_list = $( "#log" ).html();

function selecciona( estatus ){
    $( "#log" ).find( ':not( #e_'+estatus+')' ).css( 'background', 'white' );
}

function actualiza(){
    var tipo = $( "#tipo" ).val();
    var puntos = [];
    var primera = $( "#primera" ).val();
    var baja = $( "#baja" ).val();
    var estatus = ['<?php echo implode( "', '", $estatus ); ?>'];
    var meses = ['<?php echo implode( "', '", $meses ); ?>'];

    for( var a = 0; a < 3; a++ ){
        puntos[ a ] = parseInt( $( '#puntos_'+a ).val() );
    }

    // reset de lista de estatus
    $( "#log" ).html( estatus_list );

    const hoy = new Date( <?php echo date( "Y,m,d" ); ?>);
    var e = null;
    
    if( tipo - 1 ){ // tipo de calificación: permanente
        e = '612-STAFF-PERMANENTE';
    }
    else{ // tipo de calificación: por puntos
        if( primera ){ // si tiene compras en su historial




            if( puntos[ 0 ] >= 3 ){ // si tiene compras en el mes actual
                if( primera == meses[ 0 ] ){ // si es su primer mes (primer compra)
                    e = '510-NUEVO-CALIFICADO';
                }
                else{ // si no es su primer mes
                    if( puntos[ 1 ] >= 3 ){ // si tambien tiene compras en el mes anterior
                        e = '520-CALIFICADO-ACTUAL';
                    }
                    else{ // si no tiene compras en el mes anterior
                        e = '320-NO-CALIFICADO-COMPRA';
                    }
                }
            }
            else{ // si no tiene compras en el mes actual
                if( puntos[ 1 ] >= 3 ){ // si tiene puntos en el mes anterior
                    e = '410-CALIFICADO';
                }
                else{ // si no tiene compras en el mes anterior
                    if( puntos[ 2 ] >= 3 ){ // si tiene puntos en el mes anterior del anterior
                        e = '310-NO-CALIFICADO';
                    }
                    else{ // si no tiene compras en el mes anterior del anterior, significa que no tiene compras en los ultimos 3 meses y hay que darse de baja
                        if( baja > hoy.getFullYear() + '-' + hoy.getMonth() && ( puntos[ 0 ] + puntos[ 1 ] + puntos[ 2 ] > 0 ) ){
                            e = '309-CLIENTE';
                        }
                        else{
                            e = '140-SUSPENDIDO';
                        }
                    }
                }
            }



            
        }
        else{ // si nunca ha comprado
            if( baja > hoy.getFullYear() + '-' + hoy.getMonth() ){ // Si esta dentro de periodo de gracia
                e = '210-NUEVO';
            }
            else{ // si ya se dio de baja
                e = '130-NUEVO-SUSPENDIDO';
            }
        }
    }

    selecciona( e );
}


$(document).ready(function()
{ 
    $( "#log" ).find( '.badge' ).css( "background", "white" );
    $( "#actualizar" ).on( "click", actualiza );
});

</script>
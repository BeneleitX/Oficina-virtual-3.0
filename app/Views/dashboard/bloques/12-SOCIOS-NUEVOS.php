<script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>

<?php

echo "<div class=\"card-body\">";

$socios = array_reverse( json_decode( $usuario->getDownlineJSON( '10-NUTRICION' ) ) );

if( sizeof( $socios ) > 1 ){

    for($a = 0; $a < 3; $a++){
        $mes = date( "Ym", strtotime( date( "Y-m" )."-01 -{$a} month" ) );

        echo "\n<table class=\"w-100\"><tr>";
        $cols = 0;

        foreach( $socios as $s ){
            $registro = date( "Ym", strtotime( $s->registro ) );
            
            if( $s->id != $usuario->id && $mes == $registro ){
                $u = model( "UsuarioModel" )->find( $s->id );

                $estatuses = "";
                foreach( MODELOS as $m ){
                    $estatus = ESTATUS[ $u->data->estatus->modelos->{$m[ "codigo" ]} ];
                    $estatuses .="<td class=\"col-4 rounded p-1 text-center small bg-{$estatus[ "color" ]} text-white\" style=\"line-height:0\"><i class=\"fa small fa-".$m[ "settings" ][ "icono" ]."\"></i></td>";
                }

                echo "\n<td width=\"20%\" class=\"text-center\">".$u->avatar(60)."<br>".$u->id( null, "marine" )."<table style=\"margin: 0 auto; border-spacing: 5px;border-collapse: separate; \"><tr>{$estatuses}</tr></table></td>";

                if( ++$cols == 5){
                    echo "</tr><tr><td colspan=\"5\" class=\"p-1\"></td></tr><tr>";
                    $cols = 0;
                }
            }
        }

        while( $cols < 5 ){
            echo "<td width=\"20%\"></td>";

            $cols++;
        }

        echo "</tr></table>";
    }
}
else{
    echo "<div class=\"row mx-3\"><div class=\"col-4 display-1 py-2 text-gray-300 text-center ps-5\"><i class=\"fa fa-user-xmark\"></i></div><div class=\"col-8 pt-4 text-gray-500 text-center\">No hay socios nuevos<br>en tus redes</div></div>";    
}

?>
</div>

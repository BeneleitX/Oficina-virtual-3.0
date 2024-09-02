<?php
$query  = "select valor from t_variables where codigo = 'avance_corte'";


if( $_SERVER[ "HTTP_HOST" ] == "v4.app" )
    $link   = new mysqli("localhost", "root", "B3n3l31t**", "app_v3");
else
    $link   = new mysqli("localhost", "vpsbeneleitmx_root", "B3n3l31t**", "vpsbeneleitmx_app");

$result = mysqli_query($link, $query);
echo mysqli_fetch_row( $result )[0];
mysqli_close($link);
<?php
$query  = "select valor from t_variables where codigo = 'avance_corte'";
$link   = new mysqli("localhost", "root", "B3n3l31t**", "v4.beneleit");
$result = mysqli_query($link, $query);
echo mysqli_fetch_row( $result )[0];
mysqli_close($link);
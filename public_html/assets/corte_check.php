<?php

function normaliseVariable(string $name, string $value = ''): array
{
    // Split our compound string into its parts.
    if (strpos($name, '=') !== false) {
        [$name, $value] = explode('=', $name, 2);
    }

    $name  = trim($name);
    $value = trim($value);

    // Sanitize the name
    $name = preg_replace('/^export[ \t]++(\S+)/', '$1', $name);
    $name = str_replace(['\'', '"'], '', $name);

    return [$name, $value];
}

function parse()
{
    $vars = [];
    $lines = file( "../../.env_".$_SERVER['HTTP_HOST'] , FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);

    foreach ($lines as $line) {
        // Is it a comment?
        if (strpos(trim($line), '#') === 0) {
            continue;
        }

        // If there is an equal sign, then we know we are assigning a variable.
        if (strpos($line, '=') !== false) {
            [$name, $value] = normaliseVariable($line);

            if( $name == "database.default.database"){
                return $value;
            }
        }
    }

    return $vars;
}


if( $_SERVER[ "HTTP_HOST" ] == "v4.app" )
    $link   = new mysqli("localhost", "root", "B3n3l31t**", parse() );
else
    $link   = new mysqli("localhost", "vpsbeneleitmx_root", "B3n3l31t**", "vpsbeneleitmx_app");

$result = mysqli_query($link, "select valor from t_variables where codigo = 'avance_corte'" );
echo mysqli_fetch_row( $result )[0];
mysqli_close($link);


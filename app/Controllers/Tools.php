<?php

namespace App\Controllers;

class Tools extends BaseController 
{
    public function compresion( $modelo, $limit = 500, $offset = 0 )
    {
        $db = db_connect();

        // $db->query( "CALL p_mass( '{$modelo}', {$offset}, {$limit}); " );

        if( $limit < 1500 ){
            echo "<h1>{$offset}</h1><meta http-equiv=\"refresh\" content=\"0; url=".base_url()."compresion/{$modelo}/{$limit}/".( $limit + $offset )."\" />";
        }
        else{
            echo "<br><a href=\"".base_url()."\">Inicio</a>";
        }      
    }

    public function no_internet(){
        echo "no internet";
    }

    public function no_permiso(){
        echo template( "errors/no_permiso", $this->data );
    }


}

<?php

namespace App\Controllers;

class Dashboard extends BaseController
{
    public function inicio(){
        $this->data[ "navbar" ] = true;

        echo template( "dashboard/inicio", $this->data );
    }
}

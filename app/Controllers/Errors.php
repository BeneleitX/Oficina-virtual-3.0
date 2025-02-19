<?php

namespace App\Controllers;

class Errors extends BaseController
{
    

    public function error_404(){
        return view( "errors/error_404" );
    }
}

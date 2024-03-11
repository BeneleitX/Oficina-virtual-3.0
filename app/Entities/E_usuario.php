<?php
namespace App\Entities;

use CodeIgniter\Entity\Entity;

class E_usuario extends Entity
{
    protected $casts = [
        "id" => "integer",
        "nombre" => "json",
        "redes"  => "json"
    ];

    protected $dates = [ "created_at", "updated_at" ];

    protected function setPassword( string $password ){
        $this->attributes[ "password" ] = base64_encode( $password );
    }

    public function id($fondo = true){
        if( $fondo )
            return "<span class=\"badge bg-info\">".id( $this->id,6 )."</span>";
        else
            return id( $this->id, 6 );
    }    
}

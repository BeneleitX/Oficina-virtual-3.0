<?php
namespace App\Entities;

use CodeIgniter\Entity\Entity;

class E_usuario extends Entity
{
    protected $casts = [
        "id" => "integer",
        "nombre" => "json",
        "redes"  => "json",
        "curp"   => "string",
        "password" => "string",
        "redes"  => "json"
    ];

    protected $dates = [ "created_at", "updated_at" ];

    protected function setPassword( string $password ){
        $encrypter = service( "encrypter" );
        $this->attributes[ "password" ] = base64_encode( $encrypter->encrypt( $password, [ "key" => $this->attributes[ "curp" ] ] ) );
        return $this->attributes[ "password" ];
    }

    protected function getPassword(){
        $encrypter = service( "encrypter" );
        return $encrypter->decrypt( base64_decode( $this->attributes[ "password" ] ), [ "key" => $this->attributes[ "curp" ] ] );
    }

    protected function setCurp( string $curp ){
        $this->attributes[ "curp" ]     = strtoupper( $curp );
        $this->attributes[ "fechanac" ] = implode("-", [ substr( $curp, 4, 2), substr( $curp, 6, 2), substr( $curp, 8, 2) ] );
        $this->attributes[ "genero" ]   =  substr( $curp, 10, 1) == "H" ? "MASCULINO" : "FEMENINO";
    }

    public function id($fondo = true){
        if( $fondo )
            return "<span class=\"badge bg-info\">".id( $this->id, 6 )."</span>";
        else
            return id( $this->id, 6 );
    }    
}

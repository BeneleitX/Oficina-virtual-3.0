<?php
namespace App\Entities;

use CodeIgniter\Entity\Entity;

class E_usuario extends Entity
{
    protected $casts = [
        "id"       => "integer",
        "estatus_codigo" => "string",
        "data"     => "json",
        "redes"    => "json",
        "curp"     => "string",
        "password" => "string",
        "redes"    => "json"
    ];

    protected $dates = [ "created_at", "updated_at" ];

    protected function setPassword( string $password ): string
    {
        $encrypter = service( "encrypter" );
        $this->attributes[ "password" ] = base64_encode( $encrypter->encrypt( $password, [ "key" => $this->attributes[ "curp" ] ] ) );
        return $this->attributes[ "password" ];
    }


    protected function getPassword(): string 
    {
        $encrypter = service( "encrypter" );
        return $encrypter->decrypt( base64_decode( $this->attributes[ "password" ] ), [ "key" => $this->attributes[ "curp" ] ] );
    }


    protected function setCurp( string $curp ){
        $this->attributes[ "curp" ]     = strtoupper( $curp );
        $this->attributes[ "fechanac" ] = implode("-", [ substr( $curp, 4, 2), substr( $curp, 6, 2), substr( $curp, 8, 2) ] );

        $caras = [
            "face-smile",
            "face-smile-wink",
            "face-meh",
            "face-laugh-wink",
            "face-laugh-squint",
            "face-laugh",
            "face-laugh-beam",
            "face-grin-wide",
            "face-grin-wink",
            "face-grin-tongue-wink",
            "face-grin-tongue-squint",
            "face-grin-stars",
            "face-grin-tongue",
            "face-grin-squint-tears",
            "face-grin-squint",
            "face-grin-beam-sweat",
            "face-grin-beam",
            "face-grin",
        ];

        $colores = [
            "indigo",
            "deep-purple",
            "purple",
            "violet",
            "pink",
            "red",
            "deep-orange",
            "orange",
            "mustard",
            "amber",
            "yellow",
            "lime",
            "light-green",
            "green",
            "teal",
            "cyan",
            "light-blue",
            "blue",
            "brown",
        ];

        $data = json_decode( $this->attributes[ "data" ] );
        $data->genero =  substr( $curp, 10, 1) == "H" ? "MASCULINO" : "FEMENINO";
        $data->nacionalidad = substr( $this->attributes[ "curp" ], 11, 2) != "NE" ? "MEXICANA" : "EXTRANJERA";
        $data->avatarface = $caras[ rand( 0, sizeof( $caras ) - 1 ) ];
        $data->avatarbg = $colores[ rand( 0, sizeof( $colores ) - 1 ) ];
        $this->attributes[ "data" ] = json_encode( $data );
    }


    public function id($fondo = true): string 
    {
        if( $fondo )
            return "<span class=\"badge bg-info\">".id( $this->id, 6 )."</span>";
        else
            return id( $this->id, 6 );
    }


    public function nombre( $apellidos = 0, $mask = false ): string
    {
        $nombre = $this->data->nombre;
        for( $a = 0; $a < $apellidos; $a++ ){
            $nombre .= " ".( $mask ? mask( $this->data->apellidos[ $a ] ) : $this->data->apellidos[ $a ] );
        }
        
        return $nombre;

    }



    public function avatar( $size = 40 ): string 
    {
        if( $this->data->avatar->activo !== null ){
            return "<img class=\"rounded-circle\" style=\"width:{$size}px; height: {$size}px;\" src=\"".base_url()."data/usuarios/{$this->id}/img/avatar/{$this->data->avatar->imagenes[ $this->data->avatar->activo ]}\">";
        }
        else{
            return "<div class=\"emoji\"><div><i style=\"font-size:40px;\" class=\"text-".$this->data->avatarbg." fa fa-".$this->data->avatarface."\"></i></div></div>";
        }
    }
}

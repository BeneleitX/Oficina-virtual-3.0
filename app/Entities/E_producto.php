<?php
namespace App\Entities;

use CodeIgniter\Entity\Entity;

class E_producto extends Entity
{
    protected $casts = [
        "codigo"         => "string",
        "estatus_codigo" => "string",
        "data"           => "json",
        "categoria_id"   => "integer",
        "modelo_codigo"  => "string",
        "precio"         => "json",
    ];


}

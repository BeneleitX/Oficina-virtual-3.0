<?php

namespace App\Models;

use CodeIgniter\Model;

class UsuarioModel extends Model
{
    protected $table      = "t_usuarios";
    protected $primaryKey = "id";

    protected $useAutoIncrement = true;

    protected $returnType     = \App\Entities\E_usuario::class;
    protected $useSoftDeletes = false;

    protected $allowedFields = [
        "estatus_codigo", 
        "rol_codigo",
        "password",
        "data",
        "correo",
        "telefono",
        "fechanac",
        "curp",
        "redes"
    ];

    protected bool $allowEmptyInserts = false;

    // Dates
    protected $useTimestamps = true;
    protected $dateFormat    = "datetime";
    protected $createdField  = "created_at";
    protected $updatedField  = "updated_at";

    // Validation
    protected $validationRules      = [];
    protected $validationMessages   = [];
    protected $skipValidation       = false;
    protected $cleanValidationRules = true;

    // Callbacks
    protected $allowCallbacks = true;
    protected $beforeInsert   = [];
    protected $afterInsert    = [];
    protected $beforeUpdate   = [];
    protected $afterUpdate    = [];
    protected $beforeFind     = [];
    protected $afterFind      = [];
    protected $beforeDelete   = [];
    protected $afterDelete    = [];
}
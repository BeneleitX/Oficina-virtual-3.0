<?php

namespace App\Models;

use CodeIgniter\Model;

class TransferenciaModel extends Model
{
    protected $table      = "t_transferencias";
    protected $primaryKey = "id";

    protected $useAutoIncrement = true;

    protected $returnType     = "array";
    protected $useSoftDeletes = false;

    protected $allowedFields = [
        "estatus_codigo", 
        "producto_codigo",
        "cantidad",
        "origen",
        "destino",
        "fecha",
        "envia",
        "recibe",
        "notas"
    ];

    protected bool $allowEmptyInserts = false;

    // Dates
    protected $useTimestamps = false;
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

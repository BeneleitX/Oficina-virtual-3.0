<?php

namespace App\Models;

use CodeIgniter\Model;

class ColoniaModel extends Model
{
    protected $table      = "t_colonias";
    protected $primaryKey = "id";

    protected $useAutoIncrement = true;

    protected $returnType     = "array";
    protected $useSoftDeletes = false;

    protected $allowedFields = [
        "codigopostal", 
        "nombre",
        "localidad_id",
        "entidad_id"
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

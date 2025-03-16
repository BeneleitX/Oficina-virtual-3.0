<?php

namespace App\Models;

use CodeIgniter\Model;

class RetiroModel extends Model
{
    protected $table      = "t_retiros";
    protected $primaryKey = "id";
    protected $useAutoIncrement = true;
    protected $returnType     = "array";
    protected $useSoftDeletes = false;

    protected $allowedFields = [
        "estatus_codigo",
        "usuario_id",
        "inversion_id",
        "cantidad",
        "tipo",
        "fechas"
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
    protected $beforeInsert   = [ "JSONencode" ];
    protected $afterInsert    = [];
    protected $beforeUpdate   = [ "JSONencode" ];
    protected $afterUpdate    = [];
    protected $beforeFind     = [];
    protected $afterFind      = [ "JSONdecode" ];
    protected $beforeDelete   = [];
    protected $afterDelete    = [];

    protected function JSONdecode(array $data)
    {
        if( $data[ "data" ] ){
            if( $data[ "singleton" ] ){
                $data[ "data" ][ "fechas"  ] = json_decode( $data[ "data" ][ "fechas"  ], true );
            }
            else{
                foreach( $data[ "data" ] as $k => $d ){
                    $data[ "data" ][ $k ][ "fechas"  ] = json_decode( $data[ "data" ][ $k ][ "fechas"  ], true );
                }
            }
        }

        return $data;
    } 
    
    protected function JSONencode(array $data)
    {
        $data[ "data" ][ "fechas"  ] = json_encode( $data[ "data" ][ "fechas"  ] );

        return $data;
    }     
}
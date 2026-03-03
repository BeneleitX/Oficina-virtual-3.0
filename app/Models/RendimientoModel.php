<?php

namespace App\Models;

use CodeIgniter\Model;

class RendimientoModel extends Model
{
    protected $table      = "t_rendimientos";
    protected $primaryKey = "id";
    protected $useAutoIncrement = true;
    protected $returnType     = "array";
    protected $useSoftDeletes = false;

    protected $allowedFields = [
        "id",
        "mes", 
        "porcentajes",
        "producto_codigo",
        "rendimiento"
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
        if( $data[ "singleton" ] && $data[ "data" ] ){
            $data[ "data" ][ "porcentajes"  ] = json_decode( $data[ "data" ][ "porcentajes"  ], true );
        }
        elseif( $data[ "data" ] ){
            foreach( $data[ "data" ] as $k => $d ){
                $data[ "data" ][ $k ][ "porcentajes"  ] = json_decode( $data[ "data" ][ $k ][ "porcentajes"  ], true );
            }
        }

        return $data;
    } 
    
    protected function JSONencode(array $data)
    {
        $data[ "data" ][ "porcentajes"  ] = json_encode( isset( $data[ "data" ][ "porcentajes"  ] ) ? $data[ "data" ][ "porcentajes"  ] : [] );

        return $data;
    }     
}
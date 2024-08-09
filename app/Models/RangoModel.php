<?php

namespace App\Models;

use CodeIgniter\Model;

class RangoModel extends Model
{
    protected $table      = "t_rangos";
    protected $primaryKey = "codigo";
    protected $useAutoIncrement = false;
    protected $returnType     = "array";
    protected $useSoftDeletes = false;

    protected $allowedFields = [
        "codigo", 
        "modelo_codigo",
        "color",
        "nombre",
        "cantidades"
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
                $data[ "data" ][ "cantidades"  ] = json_decode( $data[ "data" ][ "cantidades"  ], true );
            }
            else{
                foreach( $data[ "data" ] as $k => $d ){
                    $data[ "data" ][ $k ][ "cantidades"  ] = json_decode( $data[ "data" ][ $k ][ "cantidades"  ], true );
                }
            }
        }

        return $data;
    } 
    
    protected function JSONencode(array $data)
    {
        $data[ "data" ][ "cantidades"  ] = json_encode( $data[ "data" ][ "cantidades"  ] );

        return $data;
    }     
}
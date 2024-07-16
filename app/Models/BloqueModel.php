<?php

namespace App\Models;

use CodeIgniter\Model;

class BloqueModel extends Model
{
    protected $table      = "t_bloques";
    protected $primaryKey = "codigo";
    protected $useAutoIncrement = false;
    protected $returnType     = "array";
    protected $useSoftDeletes = false;

    protected $allowedFields = [
        "codigo", 
        "estatus_codigo",
        "columna",
        "orden",
        "data"
    ];


    protected bool $allowEmptyInserts = false;

    // Dates
    protected $useTimestamps = false;
    protected $dateFormat    = "datetime";

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
        if( $data[ "singleton" ] ){
            $data[ "data" ][ "data"  ] = json_decode( $data[ "data" ][ "data"  ], true );
        }
        else{
            foreach( $data[ "data" ] as $k => $d ){
                $data[ "data" ][ $k ][ "data"  ] = json_decode( $data[ "data" ][ $k ][ "data"  ], true );
            }
        }

        return $data;
    } 
    
    protected function JSONencode(array $data)
    {
        $data[ "data" ][ "data"  ] = json_encode( isset( $data[ "data" ][ "data"  ] ) ? $data[ "data" ][ "data"  ] : [] );

        return $data;
    }     
}
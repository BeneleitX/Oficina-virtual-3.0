<?php

namespace App\Models;

use CodeIgniter\Model;

class RecompensaModel extends Model
{
    protected $table      = "t_recompensas";
    protected $primaryKey = "codigo";

    protected $useAutoIncrement = false;

    protected $returnType     = "array";
    protected $useSoftDeletes = false;

    protected $allowedFields = [
        "codigo", 
        "estatus_codigo",
        "modelo_codigo",
        "settings"
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
        if($data[ "data" ]){
            if( $data[ "singleton" ]){
                $data[ "data" ][ "settings" ] = json_decode( $data[ "data" ][ "settings" ], true );
            }
            else{
                foreach( $data[ "data" ] as $k => $d ){
                    $data[ "data" ][ $k ][ "settings" ] = json_decode( $data[ "data" ][ $k ][ "settings" ], true );
                    }
            }
        }

        return $data;
    } 
    
    protected function JSONencode(array $data)
    {
        $data[ "data" ][ "settings" ] = json_encode( $data[ "data" ][ "settings" ], true );
        return $data;
    }  

}

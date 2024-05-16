<?php

namespace App\Models;

use CodeIgniter\Model;

class AlmacenModel extends Model
{
    protected $table      = "t_almacenes";
    protected $primaryKey = "codigo";
    protected $useAutoIncrement = false;
    protected $returnType     = "array";
    protected $useSoftDeletes = false;

    protected $allowedFields = [
        "codigo", 
        "estatus_codigo",
        "modelo_codigo",
        "settings",
        "nombre",
        "productos"
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
        if( $data[ "singleton" ] ){
            $data[ "data" ][ "settings"  ] = json_decode( $data[ "data" ][ "settings"  ], true );
            $data[ "data" ][ "productos" ] = json_decode( $data[ "data" ][ "productos" ], true );
        }
        else{
            foreach( $data[ "data" ] as $k => $d ){
                $data[ "data" ][ $k ][ "settings"  ] = json_decode( $data[ "data" ][ $k ][ "settings"  ], true );
                $data[ "data" ][ $k ][ "productos" ] = json_decode( $data[ "data" ][ $k ][ "productos" ], true );
            }
        }

        return $data;
    } 
    
    protected function JSONencode(array $data)
    {
        $data[ "data" ][ "settings"  ] = json_encode( isset( $data[ "data" ][ "settings"  ] ) ? $data[ "data" ][ "settings"  ] : [] );
        $data[ "data" ][ "productos" ] = json_encode( isset( $data[ "data" ][ "productos" ] ) ? $data[ "data" ][ "productos"  ] : [] );

        return $data;
    }     
}
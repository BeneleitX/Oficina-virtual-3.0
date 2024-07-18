<?php

namespace App\Models;

use CodeIgniter\Model;

class ingresoModel extends Model
{
    protected $table      = "t_ingresos";
    protected $primaryKey = "id";
    protected $useAutoIncrement = true;
    protected $returnType     = "array";
    protected $useSoftDeletes = false;

    protected $allowedFields = [
        "operacion", 
        "fecha", 
        "estatus_codigo",
        "metodopago_codigo",
        "usuario_id",
        "cantidad",
        "extras"
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
            $data[ "data" ][ "extras"  ] = json_decode( $data[ "data" ][ "extras"  ], true );
        }
        else{
            foreach( $data[ "data" ] as $k => $d ){
                $data[ "data" ][ $k ][ "extras"  ] = json_decode( $data[ "data" ][ $k ][ "extras"  ], true );
            }
        }

        return $data;
    } 
    
    protected function JSONencode(array $data)
    {
        $data[ "data" ][ "extras"  ] = json_encode( $data[ "data" ][ "extras"  ] );

        return $data;
    }     
}
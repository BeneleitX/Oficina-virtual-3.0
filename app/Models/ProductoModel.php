<?php

namespace App\Models;

use CodeIgniter\Model;

class ProductoModel extends Model
{
    protected $table      = "t_productos";
    protected $primaryKey = "codigo";

    protected $useAutoIncrement = false;

    protected $returnType     = \App\Entities\E_producto::class;
    protected $useSoftDeletes = false;

    protected $allowedFields = [
        "estatus_codigo", 
        "categoria_id",
        "modelo_codigo",
        "data",
        "precio",
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
    protected $afterFind      = [ "upcase" ];
    protected $beforeDelete   = [];
    protected $afterDelete    = [];


    protected function upcase(array $data ){
        if( isset( $data[ "data" ] ) && $data[ "data" ] ){
            if( $data[ "singleton" ]){
                $d = $data[ "data" ]->data;
                $d->nombre = mb_strtoupper( $d->nombre );
                $d->avatar = file_exists( "assets/img/productos/{$data[ "data" ]->codigo}.png" );
                $data[ "data" ]->data = $d;
            }
            else{
                foreach( $data[ "data" ] as $k => $d ){
                    $d = $data[ "data" ][ $k ]->data;
                    $d->nombre = mb_strtoupper( $d->nombre );
                    $d->avatar = file_exists( "assets/img/productos/{$data[ "data" ][ $k ]->codigo}.png" );
                    $data[ "data" ][ $k ]->data = $d;
                }
            }
        }

        return $data;
    }
}
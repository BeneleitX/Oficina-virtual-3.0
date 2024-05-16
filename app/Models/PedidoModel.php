<?php

namespace App\Models;

use CodeIgniter\Model;

class PedidoModel extends Model
{
    protected $table      = "t_pedidos";
    protected $primaryKey = "id";

    protected $useAutoIncrement = true;

    protected $returnType     = "array";
    protected $useSoftDeletes = false;

    protected $allowedFields = [
        "id", 
        "estatus_codigo",
        "modelo_codigo",
        "PTS",
        "usuario_id",
        "data",
        "metodoentrega_codigo",
        "promociones",
        "metodopago_codigo",
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
    protected $afterInsert    = [ "creaReferencia" ];
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
                $data[ "data" ][ "data" ] = json_decode( $data[ "data" ][ "data" ], true );
                $data[ "data" ][ "promociones" ] = json_decode( $data[ "data" ][ "promociones" ], true );
                $data[ "data" ][ "PTS" ] = json_decode( $data[ "data" ][ "PTS" ], true );
                $data[ "data" ][ "fechas" ] = json_decode( $data[ "data" ][ "fechas" ], true );
            }
            else{
                foreach( $data[ "data" ] as $k => $d ){
                    $data[ "data" ][ $k ][ "data" ] = json_decode( $data[ "data" ][ $k ][ "data" ], true );
                    $data[ "data" ][ $k ][ "promociones" ] = json_decode( $data[ "data" ][ $k ][ "promociones" ], true );
                    $data[ "data" ][ $k ][ "PTS" ] = json_decode( $data[ "data" ][ $k ][ "PTS" ], true );
                    $data[ "data" ][ $k ][ "fechas" ] = json_decode( $data[ "data" ][ $k ][ "fechas" ], true );
                    }
            }
        }

        return $data;
    } 
    
    protected function creaReferencia(array $data)
    {
        $db = db_connect();
        $id = getReferencia( $data[ "id" ] );
        $db->query( "update {$this->table} set referencia = {$id} where id = {$data[ "id" ]}" );

        return $data;
    }  
    
    protected function JSONencode(array $data)
    {
        $data[ "data" ][ "data" ] = json_encode( $data[ "data" ][ "data" ], true );
        $data[ "data" ][ "promociones" ] = json_encode( $data[ "data" ][ "promociones" ], true );
        $data[ "data" ][ "PTS" ] = json_encode( $data[ "data" ][ "PTS" ], true );
        $data[ "data" ][ "fechas" ] = json_encode( $data[ "data" ][ "fechas" ], true );
        return $data;
    }  

}

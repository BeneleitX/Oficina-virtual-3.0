<?php

namespace App\Models;

use CodeIgniter\Model;

class UsuarioModel extends Model
{
    protected $table      = "t_usuarios";
    protected $primaryKey = "id";

    protected $useAutoIncrement = true;
    // protected $extras = ["estatus"=>"f_get_estatus"];

    protected $returnType     = \App\Entities\E_usuario::class;
    protected $useSoftDeletes = false;

    protected $allowedFields = [
        "estatus_codigo", 
        "rol_codigos",
        "password",
        "data",
        "correo",
        "telefono",
        "fechanac",
        "curp",
        "redes",
        "historial",
        "pedido"
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
    protected $afterFind      = [ "upcase" ];
    protected $beforeDelete   = [];
    protected $afterDelete    = [];

    protected function upcase(array $data ){
        if( isset( $data[ "data" ] ) && is_array( $data[ "data" ] ) ){
            if( $data[ "singleton" ]){
                $d = $data[ "data" ]->data;
                $d->nombre = mb_strtoupper( $d->nombre );
                $d->apellidos[0] = mb_strtoupper( $d->apellidos[0] );
                $d->apellidos[1] = mb_strtoupper( $d->apellidos[1] );
                $data[ "data" ]->data = $d;
            }
            else{
                foreach( $data[ "data" ] as $k => $d ){
                    $d = $data[ "data" ][ $k ]->data;
                    $d->nombre = mb_strtoupper( $d->nombre );
                    $d->apellidos[0] = mb_strtoupper( $d->apellidos[0] );
                    $d->apellidos[1] = mb_strtoupper( $d->apellidos[1] );
                        $data[ "data" ][ $k ]->data = $d;
                }
            }
        }

        return $data;
    }    
}
<?php

namespace App\Models;

use CodeIgniter\Model;

class UsuarioModel extends Model
{
    protected $table      = "t_usuarios";
    protected $primaryKey = "id";

    protected $useAutoIncrement = true;
    // protected $extras = ["calificaciones"=>"f_get_calificaciones"];

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
        "pedido",
        "calificaciones"
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
        
        if( isset( $data[ "data" ] ) ){
            
            if( $data[ "singleton" ]){
                $data[ "data" ]->data = $this->upper( $data[ "data" ]->data );
            }
            else{
                foreach( $data[ "data" ] as $k => $d ){
                    $data[ "data" ][ $k ]->data = $this->upper( $data[ "data" ][ $k ]->data );
                }
            }
        }

        return $data;
    }    

    private function upper( $d ){
        $d->nombre = mb_strtoupper( $d->nombre );
        $d->apellidos[0] = mb_strtoupper( $d->apellidos[0] );
        $d->apellidos[1] = mb_strtoupper( $d->apellidos[1] ?? "" );
        
        return $d;
    }
}
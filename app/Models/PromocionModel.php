<?php

namespace App\Models;

use CodeIgniter\Model;

class PromocionModel extends Model
{
    protected $table      = "t_promociones";
    protected $primaryKey = "codigo";
    protected $useAutoIncrement = false;
    protected $returnType     = "array";
    protected $useSoftDeletes = false;

    protected $allowedFields = [
        "codigo", 
        "estatus_codigo",
        "modelo_codigo",
        "settings",
        "inicia",
        "termina",
        "productos",
        "formulas"
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
            $data[ "data" ][ "formulas"  ] = json_decode( $data[ "data" ][ "formulas"  ], true );
    
            if( $data[ "data" ][ "inicia" ] > date("Y-m-d H:i:s" ) ){
                $data[ "data" ][ "vigencia" ] = "330-EN-ESPERA";
            }        
            elseif( $data[ "data" ][ "termina" ] < date("Y-m-d H:i:s" ) ){
                $data[ "data" ][ "vigencia" ] = "122-FINALIZADA";
            }
            else{
                $data[ "data" ][ "vigencia" ] = "202-VIGENTE";
            }    
        }
        else{
            
            foreach( $data[ "data" ] as $k => $d ){
                $data[ "data" ][ $k ][ "settings"  ] = json_decode( $data[ "data" ][ $k ][ "settings"  ], true );
                $data[ "data" ][ $k ][ "productos" ] = json_decode( $data[ "data" ][ $k ][ "productos" ], true );
                $data[ "data" ][ $k ][ "formulas"  ] = json_decode( $data[ "data" ][ $k ][ "formulas"  ], true );
        
                if( $data[ "data" ][ $k ][ "inicia" ] > date("Y-m-d H:i:s" ) ){
                    $data[ "data" ][ $k ][ "vigencia" ] = "330-EN-ESPERA";
                }        
                elseif( $data[ "data" ][ $k ][ "termina" ] < date("Y-m-d H:i:s" ) ){
                    $data[ "data" ][ $k ][ "vigencia" ] = "122-FINALIZADA";
                }
                else{
                    $data[ "data" ][ $k ][ "vigencia" ] = "202-VIGENTE";
                }                  
            }

            
        }

        return $data;
    } 
    
    protected function JSONencode(array $data)
    {
        $data[ "data" ][ "settings"  ] = json_encode( isset( $data[ "data" ][ "settings"  ] ) ? $data[ "data" ][ "settings"  ] : [] );
        $data[ "data" ][ "productos" ] = json_encode( isset( $data[ "data" ][ "productos" ] ) ? $data[ "data" ][ "productos" ] : [] );
        $data[ "data" ][ "formulas"  ] = json_encode( isset( $data[ "data" ][ "formulas"  ] ) ? $data[ "data" ][ "formulas"  ] : [] );

        return $data;
    }     
}
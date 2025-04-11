<?php

namespace App\Controllers;

class Tools extends BaseController 
{
    public function compresion( $modelo, $limit = 500, $offset = 0 )
    {
        $db = db_connect();

        // $db->query( "CALL p_mass( '{$modelo}', {$offset}, {$limit}); " );

        if( $limit < 1500 ){
            echo "<h1>{$offset}</h1><meta http-equiv=\"refresh\" content=\"0; url=".base_url()."compresion/{$modelo}/{$limit}/".( $limit + $offset )."\" />";
        }
        else{
            echo "<br><a href=\"".base_url()."\">Inicio</a>";
        }      
    }

    public function no_internet(){
        echo "no internet";
    }

    public function no_permiso(){
        echo template( "errors/no_permiso", $this->data );
    }
    
    public function kkk(){

        $db = db_connect();

        $sql = "select id from t_usuarios";
        $us = $db->query( $sql );

        foreach( $us->getResult() as $u ){

            $sql = "
            UPDATE t_usuarios y SET y.historial = JSON_SET( 
                        y.historial, 
                        '$.modelos.\"10-NUTRICION\".calificaciones', 
                        (
                            SELECT
                                JSON_OBJECTAGG( 
                                    mes, JSON_OBJECT( 
                                        \"010-DISTRIBUIDOR\", p1, 
                                        \"030-PLUS\", p2,
                                        \"230-REGALOBIEX\", p3, 
                                        \"212-PRODUCTIVIDAD-A\", p4,
                                        \"020-PROMO-50\", p5,
                                        \"210-LEALTAD\", p6 
                                    ) 
                                ) as nutri
                            FROM (
                                SELECT 
                                    if( p.fechas->>'$.califica' IS NULL, '202408', DATE_FORMAT( p.fechas->>'$.califica', '%Y%m'  ) ) AS mes,
                                    CAST( SUM( p.PTS->>'$.\"010-DISTRIBUIDOR\"' ) as DECIMAL(6,2) ) AS p1,
                                    CAST( SUM( p.PTS->>'$.\"030-PLUS\"' ) as DECIMAL(6,2) ) AS p2,
                                    CAST( SUM( p.PTS->>'$.\"230-REGALOBIEX\"' ) as DECIMAL(6,2) ) AS p3,
                                    CAST( SUM( p.PTS->>'$.\"212-PRODUCTIVIDAD-A\"' ) as DECIMAL(6,2) ) AS p4,
                                    CAST( SUM( p.PTS->>'$.\"020-PROMO-50\"' ) as DECIMAL(6,2) ) AS p5,
                                    CAST( SUM( p.PTS->>'$.\"210-LEALTAD\"' ) as DECIMAL(6,2) ) AS p6
                                FROM t_usuarios u
                                LEFT JOIN t_pedidos p ON p.usuario_id = u.id
                                WHERE u.id = {$u->id}
                                GROUP BY mes
                            ) res    
                        )
                    ) 
                    WHERE y.id = {$u->id}";

            // $db->query( $sql );
        }
    }

}

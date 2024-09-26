<?php

namespace App\Controllers;

class Roles extends BaseController
{
    public function listado(){
        if( !(
            $this->data[ "usuario" ]->permiso( "40-ADMIN")
        ) ){
            return redirect()->to( "inicio" ); 
        }
        
        /**********************************/

        $this->data[ "navbar" ] = true;
        $this->data[ "titulo" ] = "Roles de usuario";
        
        $db = db_connect();
        
        $this->data[ "roles" ] = $db->query("
        SELECT r.codigo, r.descripcion, r.tipo, JSON_ARRAYAGG( u.id ) as socios 
        FROM t_roles r
        LEFT JOIN t_usuarios u 
            ON r.tipo IN ( 'BLOQUEO', 'ADMIN', 'ROOT', 'PERMANENTE' ) 
            AND u.rol_codigos LIKE CONCAT( '%', r.codigo, '%' )
        GROUP BY r.codigo" )->getResultArray();

        return template( "roles/listado", $this->data );
    }
}

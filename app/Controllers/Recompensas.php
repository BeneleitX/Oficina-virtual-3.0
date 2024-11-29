<?php

namespace App\Controllers;

class Recompensas extends BaseController
{
    public function detalle(){

        $this->data[ "navbar"  ] = true;
        $this->data[ "socio"   ] = $this->data[ "usuario" ];
        $this->data[ "titulo"  ] = "Detalles de recompensas";
        $this->data[ "comisiones" ] = $this->data[ "socio" ]->getComisiones( null, "120-BIEX-3ER-NIVEL", "255-PENDIENTE" );

        load_catalogo( "recompensas");
        echo template( "recompensas/detalle", $this->data );
    }


    public function switch( $recompensa ){
        $socio = $this->data[ "usuario" ];

        $data = $socio->data;
        $previa = $data->recompensas->activa;
        $data->recompensas->activa = $recompensa;
        $socio->data = $data;
        model( "UsuarioModel" )->save( $socio );

        // BITACORA Eliminar beneficiario
        bitacora( 30, $socio->id, [ 
            "previa"     => $previa,
            "recompensa" => $recompensa,
            "usuario"    => $this->data[ "usuario" ]->id
        ] );

        return redirect()->to( "recompensas" )->with( "msg", [ 
            "clase" => "success", 
            "icono" => "check", 
            "texto" => "Se actualizó la recompensa activa" ] );         
    }


    public function guarda_recompensas(){
        extract( $this->request->getPost() );

        $data = $this->data[ "usuario" ]->data;
        $data->recompensas->orden = [ $ciclo => $orden ];
        $this->data[ "usuario" ]->data = $data;
        model( "UsuarioModel" )->save( $this->data[ "usuario" ] );

        // BITACORA Eliminar beneficiario
        bitacora( 70, $this->data[ "usuario" ]->id, [ 
            "orden" => $orden,
        ] );
    }


    public function reclama_recompensa( $recompensa ){
        $r = model( "RecompensaModel" )->find( $recompensa );
        $total_estrellas = $this->data[ "usuario" ]->getEstrellas( $r );
        $alcanzadas = $this->data[ "usuario" ]->recompensas_alcanzadas();

        if( isset( $r[ "estrellas" ] ) && $total_estrellas >= intval( $r[ "estrellas" ] ) && !in_array( $r[ "codigo" ], $alcanzadas ) ){

            // update conteo
            $db = db_connect();
            $db->query( "insert into t_redenciones values( NULL, '330-EN-ESPERA', {$this->data[ "usuario" ]->id}, '{$r[ "codigo" ]}', '".date( "Y-m-d" )."')" );
            $db->query( "call p_cobra_estrellas( {$this->data[ "usuario" ]->id}, '{$r[ "estrellas" ]}' )" );
    
            $data = $this->data[ "usuario" ]->data;
            // notificación flash
            $data->splash[] = [
                "tipo" => "recompensa",
                "parametros" => [ $r[ "codigo" ] ]
            ];

            $data->recompensas->estrellas = intval( $total_estrellas - $r[ "estrellas"] );
            $this->data[ "usuario" ]->data = $data;
            model( "UsuarioModel" )->save( $this->data[ "usuario" ] ); 
        }

        // BITACORA Eliminar beneficiario
        bitacora( 71, $this->data[ "usuario" ]->id, [ 
            "recompensa" => $r[ "codigo" ],
        ] );

        return redirect()->to( "recompensas" )->with( "msg", [ 
            "clase" => "success", 
            "icono" => "check", 
            "texto" => "¡Felicidades! recompensa reclamada" ] );    
    }


    public function admin_recompensas(){

        if( !(
            $this->data[ "usuario" ]->permiso( "27-RECOMPENSAS") ||
            $this->data[ "usuario" ]->permiso( "40-ADMIN")
        ) ){
            return redirect()->to( "inicio" ); 
        }
        
        /**********************************/

        load_catalogo( "recompensas");

        $this->data[ "navbar" ] = true;
        $this->data[ "titulo" ] = "Administración de recompensas";
        
        echo template( "recompensas/admin_recompensas", $this->data );
    }


    public function entregar_recompensa(){

        if( !(
            $this->data[ "usuario" ]->permiso( "27-RECOMPENSAS") ||
            $this->data[ "usuario" ]->permiso( "40-ADMIN")
        ) ){
            return redirect()->to( "inicio" ); 
        }

        $db = db_connect();
        extract( $this->request->getPost() );

        $sql = "select * from t_redenciones where id = ".$redencion;
        $redencion = $db->query( $sql )->getRow();

        $sql = "UPDATE t_redenciones set estatus_codigo = '623-ENTREGA' where id = ".$redencion->id;
        $db->query( $sql );

        // BITACORA Marca recompensa entregada
        bitacora( 75, $this->data[ "usuario" ]->id, [ 
            "redencion"   => $redencion->id,
            "recompensa" => $redencion->recompensa_codigo,
            "socio"      => $redencion->usuario_id
        ] );

        return redirect()->to( "admin_recompensas" )->with( "msg", [ 
            "clase" => "success", 
            "icono" => "check", 
            "texto" => "Esta recompensa se ha marcado como entregada" ] );   
    }

}


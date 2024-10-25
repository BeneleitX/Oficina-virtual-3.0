<?php

namespace App\Controllers;

class Usuarios extends BaseController
{
    function __construct() {
        $this->data[ "menu" ] = "admin";
        
    }


    public function busqueda( $request = null ){
    
        if( !$this->data[ "usuario" ]->permiso( "32-EDICION" ) AND 
            !$this->data[ "usuario" ]->permiso( "40-ADMIN" ) ){
            return redirect()->to( "inicio" ); 
        }

        $this->data[ "saved" ]  = false;
        $this->data[ "query" ]  = null;
        $this->data[ "navbar" ] = true;
        $this->data[ "titulo" ] = "Usuarios de sistema BENELEIT";

        /********** POST *************/

        extract( $this->request->getPost() );
        
        if( isset( $query ) ){
            $query = strtolower( trim( $query ) );

            $queries = explode( " ", $query );

            $this->data[ "query" ]   = $query;
            $this->data[ "queries" ] = $queries;
            
            $sql = "";

            foreach( $queries as $k => $q ){
                $sql .= ($k ? " and " : "" )
                        ."( id = ".intval( $q ).( strlen( $q ) > 3 ? " or
                        telefono like '%{$q}%' or
                        correo like '%{$q}%' or
                        curp like '%{$q}%' or
                        lower( json_unquote( json_extract( data, '$.nombre' ) ) ) like '%{$q}%' or
                        lower( json_unquote( json_extract( data, '$.apellidos[0]' ) ) ) like '%{$q}%' or
                        lower( json_unquote( json_extract( data, '$.apellidos[1]' ) ) ) like '%{$q}%' or
                        lower( json_unquote( json_extract( data, '$.clabe' ) ) ) like '%{$q}%' )" : ")" );
            }
     
            $this->data[ "socios" ]  = model( "UsuarioModel" )->where( $sql )->findAll();
        }
        else{
            $this->data[ "socios" ] = null;

            $sql = "SELECT fecha, JSON_EXTRACT( d.variables, '$.socio' ) AS socio FROM t_bitacoras d
                WHERE accion_id IN (50) AND usuario_id = 55 
                and d.fecha IN (
                    SELECT MAX( d2.fecha ) FROM t_bitacoras d2 WHERE accion_id IN (50) AND usuario_id = 55 and d.variables = d2.variables 
                ) 
                ORDER BY fecha desc limit 10";
                
            $socios = [];
            $db = db_connect();
            $this->data[ "bitacoras" ] = $db->query( $sql )->getResultArray();

            foreach( $this->data[ "bitacoras" ] as $b ){
                $socios[] = $b[ "socio" ];
            }
            
            $socios = model( "UsuarioModel" )->find( $socios );

            foreach( $socios as $b ){
                $this->data[ "historial" ][ $b->id ] = $b;
            }

            //$this->data[ "historial" ] = model( "UsuarioModel" )->distinct()->select( 't_usuarios.id' )->join( "t_bitacoras", "t_bitacoras.accion_id = 50 AND t_bitacoras.usuario_id = ".$this->data[ "usuario" ]->id )->where( "json_extract( t_bitacoras.variables , '$.socio' ) = t_usuarios.id" )->orderby( "t_bitacoras.fecha", "desc" )->findAll( 25 );
        }

        /*****************************/

        echo template( "usuarios/busqueda", $this->data );
    }

    
    public function reset_password(){
        
        if( !$this->data[ "usuario" ]->permiso( "32-EDICION" ) AND 
            !$this->data[ "usuario" ]->permiso( "40-ADMIN" ) ){
            return redirect()->to( "inicio" ); 
        }

        $this->data[ "navbar" ] = true;
        $this->data[ "titulo" ] = "Password temporal generado";
        $this->data[ "admin" ]  = true;
        $this->data[ "nuevo" ]  = model( "UsuarioModel" )->find( $this->request->getPost( "socio" ) );

        $this->data[ "nuevo" ]->resetPassword();
        model( "UsuarioModel" )->save( $this->data[ "nuevo" ] );

        // BITACORANuevo password
        bitacora( 63, $this->data[ "usuario" ]->id, [ 
            "socio"   => $this->data[ "nuevo" ]->id,
            "cambios" => $this->data[ "nuevo" ]->password
        ] );
        
        echo template( "sesion/reset", $this->data );
    }


    public function update_estatus( $request ){

        if( !$this->data[ "usuario" ]->permiso( "32-EDICION" ) ){
            return redirect()->to( "inicio" ); 
        }
        
        $request = base64_decode( urldecode( $request ) );
        $socio = model( "UsuarioModel" )->where( "password = '{$request}'" )->first();

        model( "UsuarioModel" )->save( $socio );


        $db = db_connect();
        foreach( MODELOS as $m ){
            $db->query( "select f_update_PTS( {$socio->id}, '{$m[ "codigo" ]}', '".date( "Ym" )."' )" );  
            $db->query( "call p_update_padre( {$socio->id}, '{$m[ "codigo" ]}' );" );
        }

        $db->query( "select f_get_estatus(  {$socio->id}, 1 )" );
        $db->query( "select f_checks_rango( {$socio->id}, '10-NUTRICION' );" );


        // BITACORA Forzar update
        bitacora( 62, $this->data[ "usuario" ]->id, [ 
            "socio"   => $socio->id
        ] );

        $ruta = urlencode( base64_encode( $socio->password_original() ) );
        return redirect()->to( "sociodata/{$ruta}" );
    }


    public function update_sociodata(){

        if( !$this->data[ "usuario" ]->permiso( "32-EDICION" ) ){
            return redirect()->to( "inicio" ); 
        }
        
        $r   = $this->request->getPost();
        $socio = model( "UsuarioModel" )->find( $r[ "id" ] );

        if( !isset( $r[ "genero" ] ) ){
            $r[ "genero" ] = null;
        }

        $data = $socio->data;
        $cambios = [];

        if( $socio->telefono != $r[ "telefono" ] ){ $cambios[] = [ "telefono", $socio->telefono, $r[ "telefono" ] ]; $socio->telefono = $r[ "telefono" ]; } 
        if( $socio->correo   != $r[ "correo" ]   ){ $cambios[] = [ "correo", $socio->correo,   $r[ "correo" ] ]; $socio->correo   = $r[ "correo" ];   } 
        if( $socio->fechanac != $r[ "fechanac" ] ){ $cambios[] = [ "fechanac", $socio->fechanac, $r[ "fechanac" ] ]; $socio->fechanac = $r[ "fechanac" ]; } 
        if( $socio->curp     != $r[ "curp" ]     ){ $cambios[] = [ "curp", $socio->curp,     $r[ "curp" ] ]; $socio->curp     = $r[ "curp" ];     } 

        if( $data->nombre != $r[ "nombre" ] ){ $cambios[] = [ "nombre", $data->nombre, $r[ "nombre" ] ]; $data->nombre = $r[ "nombre" ]; } 
        if( $data->apellidos[0] != $r[ "apellido1" ] ){ $cambios[] = [ "apellido1", $data->apellidos[0], $r[ "apellido1" ] ]; $data->apellidos[0] = $r[ "apellido1" ]; } 
        if( $data->apellidos[1] != $r[ "apellido2" ] ){ $cambios[] = [ "apellido2", $data->apellidos[1], $r[ "apellido2" ] ]; $data->apellidos[1] = $r[ "apellido2" ]; } 
        if( $data->clabe != $r[ "clabe" ] ){ $cambios[] = [ "clabe", $data->clabe, $r[ "clabe" ] ]; $data->clabe  = $r[ "clabe" ];  } 
        if( $data->sat->rfc != $r[ "rfc" ] ){ $cambios[] = [ "rfc", $data->sat->rfc, $r[ "rfc" ] ]; $data->sat->rfc = $r[ "rfc" ]; } 
        if( $data->genero != $r[ "genero" ] ){ $cambios[] = [ "genero", $data->genero, $r[ "genero" ] ]; $data->genero = $r[ "genero" ]; } 

        if( sizeof( $cambios ) ){
            $socio->data = $data;
            model( "UsuarioModel" )->save( $socio );

            // BITACORA Consulta de datos
            bitacora( 51, $this->data[ "usuario" ]->id, [ 
                "socio"   => $socio->id,
                "cambios" => $cambios
            ] );

            session()->setFlashdata('msg', [ 
                "clase" => "success", 
                "icono" => "user-check", 
                "texto" => "Se actualizaron los datos del socio"
            ]);
        }
        else{
            session()->setFlashdata('msg', [ 
                "clase" => "warning", 
                "icono" => "user-check", 
                "texto" => "No se detectaron cambios en los datos del socio. No hubo guardado de cambios."
            ]);
        }

        $ruta = urlencode( base64_encode( $socio->password_original() ) );
        return redirect()->to( "sociodata/{$ruta}" );
    }



}

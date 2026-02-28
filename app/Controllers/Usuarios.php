<?php

namespace App\Controllers;

class Usuarios extends BaseController
{
    function __construct() {
        $this->data[ "menu" ] = "admin";
        
    }


    /**
     * Buscar usuarios en la base de datos con permiso de edici n.
     *
     * Muestra una lista de usuarios que coinciden con el criterio de b squeda.
     *
     * Permite buscar por id, nombre, apellido, tel fono, correo, CLABE interbancaria o CURP.
     *
     * @param null $request
     * @return void
     */
    public function busqueda( $request = null ){
    
        if( !$this->data[ "usuario" ]->permiso( "32-EDICION" ) AND 
            !$this->data[ "usuario" ]->permiso( "40-ADMIN" ) ){
            return redirect()->to( "no_permiso" ); 
        }

        $this->data[ "saved" ]  = false;
        $this->data[ "query" ]  = null;
        $this->data[ "navbar" ] = true;
        $this->data[ "titulo" ] = "Usuarios de sistema BENELEIT";

        /********** POST *************/

        if( $request ){
            $query = $request;
        }
        else{
            extract( $this->request->getPost() );
        }
        
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

            $sql = "SELECT 
                        x.s as s,
                        cast( max( x.f ) as date ) as f

                    from ( 
                        SELECT 
                            d.variables->'$.socio' AS s, 
                            d.fecha as f
                        FROM t_bitacoras d 
                        WHERE d.accion_id  = 50 
                        AND d.usuario_id = {$this->data[ "usuario" ]->id} 
                    ) x

                    group by x.s
                    order by f desc

                    limit 10";
                
            $socios = [];
            $db = db_connect();
            $this->data[ "bitacoras" ] = $db->query( $sql )->getResultArray();

            foreach( $this->data[ "bitacoras" ] as $b ){
                $socios[] = $b[ "s" ];
            }
            
            if( sizeof( $socios ) ){
                $socios = model( "UsuarioModel" )->find( $socios );
            }

            foreach( $socios as $b ){
                $this->data[ "historial" ][ $b->id ] = $b;
            }

            //$this->data[ "historial" ] = model( "UsuarioModel" )->distinct()->select( 't_usuarios.id' )->join( "t_bitacoras", "t_bitacoras.accion_id = 50 AND t_bitacoras.usuario_id = ".$this->data[ "usuario" ]->id )->where( "json_extract( t_bitacoras.variables , '$.socio' ) = t_usuarios.id" )->orderby( "t_bitacoras.fecha", "desc" )->findAll( 25 );
        }

        /*****************************/

        echo template( "usuarios/busqueda", $this->data );
    }

    
    /**
     * Reset password of a user.
     *
     * If the user does not have permission for edition (32-EDICION) and administration (40-ADMIN), redirect to "no_permiso".
     *
     * Reset password of the user with ID given in POST parameter "socio".
     *
     * Save the new password in DB.
     *
     * Create a new bitacora entry with the new password.
     *
     * Display the "sesion/reset" template with the new password data.
     */
    public function reset_password(){
        
        if( !$this->data[ "usuario" ]->permiso( "32-EDICION" ) AND 
            !$this->data[ "usuario" ]->permiso( "40-ADMIN" ) ){
            return redirect()->to( "no_permiso" ); 
        }

        $this->data[ "navbar" ] = true;
        $this->data[ "titulo" ] = "Password temporal generado";
        $this->data[ "admin" ]  = true;
        $this->data[ "nuevo" ]  = model( "UsuarioModel" )->find( $this->request->getPost( "socio" ) );

        $this->data[ "nuevo" ]->resetPassword();
        
        model( "UsuarioModel" )->save( $this->data[ "nuevo" ] );
        $this->data[ "socio" ]->update_verificacion();

        // BITACORANuevo password
        bitacora( 63, $this->data[ "usuario" ]->id, [ 
            "socio"   => $this->data[ "nuevo" ]->id,
            "cambios" => $this->data[ "nuevo" ]->password
        ] );
        
        echo template( "sesion/reset", $this->data );
    }


    /**
     * Actualiza los estatus de un socio en cada unidad de negocio.
     * 
     * Se llama desde el dashboard de un socio, y se encarga de actualizar
     * los estatus de un socio en cada unidad de negocio.
     * 
     * @param string $request El password del socio, en formato base64.
     * 
     * @return void
     */
    public function update_estatus( $request ){

        if( !$this->data[ "usuario" ]->permiso( "32-EDICION" ) ){
            return redirect()->to( "no_permiso" ); 
        }
        
        $request = base64_decode( urldecode( $request ) );
        $socio = model( "UsuarioModel" )->where( "password = '{$request}'" )->first();

        model( "UsuarioModel" )->save( $socio );

        $db = db_connect();
        foreach( MODELOS as $m ){
            $sql = "do f_update_PTS( {$socio->id}, '{$m[ "codigo" ]}', '".date('Ym', strtotime( date('Y-m').'-01'. ' -1 month' ) )."' )";
            $db->query( $sql ); 
            $db->query( "do f_update_PTS( {$socio->id}, '{$m[ "codigo" ]}', '".date( "Ym" )."' )" );  
            $db->query( "call p_update_padre( {$socio->id}, '{$m[ "codigo" ]}' );" );
        }

        $db->query( "do f_get_estatus(  {$socio->id}, 1 )" );
        $db->query( "do f_checks_rango( {$socio->id}, '10-NUTRICION' );" );


        // BITACORA Forzar update
        bitacora( 62, $this->data[ "usuario" ]->id, [ 
            "socio"   => $socio->id
        ] );

        $ruta = urlencode( base64_encode( $socio->password_original() ) );
        return redirect()->to( "sociodata/{$ruta}" );
    }


    /**
     * Actualiza los datos del socio, actualizando tanto el usuario como sus datos.
     * 
     * Si el usuario no tiene permiso de edici n, se redirige a "no_permiso".
     * 
     * @return void
     */
    public function update_sociodata(){

        if( !$this->data[ "usuario" ]->permiso( "32-EDICION" ) ){
            return redirect()->to( "no_permiso" ); 
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

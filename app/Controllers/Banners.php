<?php

namespace App\Controllers;

class Banners extends BaseController
{
    function __construct() {
        $this->data[ "menu" ] = "admin";
    }

    public function listado(){
        if( !(
            $this->data[ "usuario" ]->permiso( "22-IMAGEN" ) ||
            $this->data[ "usuario" ]->permiso( "40-ADMIN" )
        ) ){
            return redirect()->to( "inicio" ); 
        }

        $this->data[ "navbar" ]  = true;
        $this->data[ "titulo" ]  = "Carrusel de banners";

        $db  = db_connect();
        $sql = "SELECT * from t_banners where estatus_codigo = '201-ACTIVO' order by posicion asc";
        $this->data[ "banners" ] = $db->query( $sql )->getResultArray();

        echo template( "banners/listado", $this->data );
    }


    public function mueve( $direccion, $banner ){
        if( !(
            $this->data[ "usuario" ]->permiso( "22-IMAGEN" ) ||
            $this->data[ "usuario" ]->permiso( "40-ADMIN" )
        ) ){
            return redirect()->to( "inicio" ); 
        }

        $db  = db_connect();

        $banner = model( "BannerModel" )->find( $banner );

        $anterior = $banner[ "posicion" ];
        $banner[ "posicion" ] = $direccion == "abajo" ? $banner[ "posicion" ] + 1 : $banner[ "posicion" ] - 1;
        $sql = "update t_banners set posicion = {$anterior} where posicion = {$banner[ "posicion" ]} and estatus_codigo = '201-ACTIVO'";

        $db->query( $sql );
        model( "BannerModel" )->save( $banner );

        // BITACORA mover banner
        bitacora( 66, $this->data[ "usuario" ]->id, [ 
            "direccion"  => $direccion,
            "banner" => $banner
        ] );   

        return redirect()->to( "banners" ); 
    }

    public function save(){
        if( !(
            $this->data[ "usuario" ]->permiso( "22-IMAGEN" ) ||
            $this->data[ "usuario" ]->permiso( "40-ADMIN" )
        ) ){
            return redirect()->to( "inicio" ); 
        }

        extract( $this->request->getPost() );

        // Si es edición
        if( intval( $banner_id ) > 0 ){
            $banner = model( "BannerModel" )->find( $banner_id );

            // hacer modificaciones al banner
            $banner[ "descripcion" ] = $banner_descripcion;
            $banner[ "inicia" ]      = $banner_fecha_inicia;
            $banner[ "vigencia" ]    = $banner_fecha_vigencia;

            // BITACORA editar banner
            bitacora( 65, $this->data[ "usuario" ]->id, [ 
                "variables"  => $this->request->getPost()
            ] );            
        }

        // insertar nuevo
        else{
            $db  = db_connect();
            $sql = "SELECT count(*) as total from t_banners where estatus_codigo = '201-ACTIVO'";
            $posicion = $db->query( $sql )->getRow()->total + 1;

            // crear estructura para nuevo banner
            $banner = [
                "descripcion" => $banner_descripcion,
                "inicia"      => $banner_fecha_inicia,
                "vigencia"    => $banner_fecha_vigencia,
                "estatus_codigo" => "201-ACTIVO",
                "archivo"     => $banner_archivo,
                "posicion"    => $posicion
            ];

            // BITACORA crear banner
            bitacora( 64, $this->data[ "usuario" ]->id, [ 
                "variables"  => $this->request->getPost()
            ] );
        }

        model( "BannerModel" )->save( $banner );

        return redirect()->to( "banners" ); 
    }


    public function upload_banner(){
        $path        = "assets/img/banners/";
        $filename    = $_FILES[ "image" ][ "name" ];
        $fileTmpName = $_FILES[ "image" ][ "tmp_name" ];

        move_uploaded_file( $fileTmpName, $path.$filename );
        echo json_encode( [ $path, $filename ] );
    }       
}

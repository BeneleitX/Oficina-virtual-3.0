<?php

namespace App\Controllers;

class Geodata extends BaseController
{
    function __construct() {
        $this->data[ "menu" ] = "admin";
    }

    public function inicio(){
        if( !(
            $this->data[ "usuario" ]->permiso( "40-ADMIN" )
        ) ){
            return redirect()->to( "no_permiso" ); 
        }

        $this->data[ "navbar" ]  = true;
        $this->data[ "titulo" ]  = "Datos de distribución geográfica";

        echo template( "geodata/inicio", $this->data );
    }


    /**
     * Mueve el banner en la lista hacia arriba o abajo
     * @param string $direccion "arriba" o "abajo"
     * @param int $banner id del banner a mover
     * @return redirect a la lista de banners
     */
    public function mueve( $direccion, $banner )
    {
        if( !(
            $this->data[ "usuario" ]->permiso( "22-IMAGEN" ) ||
            $this->data[ "usuario" ]->permiso( "40-ADMIN" )
        ) ){
            return redirect()->to( "no_permiso" ); 
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

    
    /**
     * Saves a banner, either updating an existing one or creating a new one.
     * 
     * Checks if the user has the necessary permissions to perform the operation.
     * If the banner_id is provided and greater than 0, it updates the existing banner with the provided details.
     * Otherwise, it creates a new banner with the given information and assigns it a position.
     * Logs the operation in the bitacora with the corresponding action ID.
     * Redirects to the banners list upon completion.
     */

    public function save()
    {
        if( !(
            $this->data[ "usuario" ]->permiso( "22-IMAGEN" ) ||
            $this->data[ "usuario" ]->permiso( "40-ADMIN" )
        ) ){
            return redirect()->to( "no_permiso" ); 
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


    /**
     * Uploads a banner image via AJAX
     * 
     * @return string JSON containing the path and filename of the uploaded image
     */
    public function upload_banner()
    {
        $path        = "assets/img/banners/";
        $filename    = $_FILES[ "image" ][ "name" ];
        $fileTmpName = $_FILES[ "image" ][ "tmp_name" ];

        move_uploaded_file( $fileTmpName, $path.$filename );
        echo json_encode( [ $path, $filename ] );
    }       
}

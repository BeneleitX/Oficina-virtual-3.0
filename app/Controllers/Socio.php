<?php

namespace App\Controllers;

class Socio extends BaseController
{
    public function perfil(){

        $checked = $total = 0;

        $this->data[ "navbar" ] = true;
        $this->data[ "socio"  ] = $this->data[ "usuario" ];
        $this->data[ "titulo" ] = "Perfil de socio ".$this->data[ "socio"  ]->id( null, "marine" );
        $this->data[ "porc" ]   = $this->data[ "socio"  ]->porcentaje_beneficiarios();
        
        foreach( $this->data["socio"]->data->verificacion as $j => $k){
            $total++;

            if( $k == true ){ 
                $checked++;
            }else{
                if($j == "csf"){
                    if( $this->data["socio"]->data->sat->estatus == 0 )
                    $checked++;
                }
            }
        }
        $this->data[ "avance" ] = number_format($checked * 100 / $total,0);
        
        echo template( "socio/perfil", $this->data );
    }


    public function fotografia(){
        $this->data[ "navbar" ] = true;
        $this->data[ "titulo" ] = "Cambiar foto de usuario";
        $this->data[ "socio"  ] = $this->data[ "usuario" ];

        echo template( "socio/fotografia", $this->data );
    }


    public function guarda_avatar(){
        $this->data[ "socio" ] = $this->data[ "usuario" ];

        $data = $this->request->getPost( "image" );
        $path = "data/{$this->data["socio"]->id}/avatar/";
        $filename = $this->data["socio"]->id."_".time().".jpg";

        $json = $this->data["socio"]->data;
        $json->avatar->imagenes[]  = $filename;
        $json->avatar->activo      = sizeof( $json->avatar->imagenes ) -1;
        $json->avatar->updated     = time();
        $json->verificacion->foto  = true;
        $this->data["socio"]->data = $json; 

        model( "UsuarioModel" )->save( $this->data[ "socio" ] );

        list($type, $data) = explode(';', $data);
        list(, $data)      = explode(',', $data);
        $data = base64_decode($data);

        if(!is_dir($path)) mkdir($path, 0755, true);

        file_put_contents($path.$filename, $data);

        // BITACORA Creación de cuenta de usuario
        bitacora( 5, $this->data["socio"]->id, [ 
            "archivo" => $filename,
            "usuario" => $this->data["usuario"]->id
        ] );

        session()->setFlashdata('msg', [ 
            "clase" => "success", 
            "icono" => "user-check", 
            "texto" => "Se ha actualizado la fotografía"]);
    }


    public function credencial(){
        $this->data[ "socio" ] = $this->data[ "usuario" ];

        $credencial = $this->request->getPost( "image" );
        $tipo = $this->request->getPost( "tipo" );

        $path = "data/{$this->data["socio"]->id}/ine/";
        $filename = $this->data["socio"]->id."_".time()."_{$tipo}.jpg";

        $json = $this->data["socio"]->data;
        $json->credencial->{$tipo} = $filename;
        $this->data["socio"]->data = $json; 

        model( "UsuarioModel" )->save( $this->data[ "socio" ] );

        if( !is_dir( $path ) ){
            mkdir( $path, 0755, true );
        }

        $fileTmpName = $_FILES[ "image" ][ "tmp_name" ];
        move_uploaded_file( $fileTmpName, $path.$filename );

        // BITACORA Carga de foto INE o Acta
        bitacora( $this->data["socio"]->es_menor() ? 15 : 6, $this->data[ "socio" ]->id, [ 
            "archivo" => $filename,
            "tipo"    => $tipo,
            "usuario" => $this->data[ "usuario" ]->id
        ] );

        session()->setFlashdata('msg', [ 
            "clase"   => "success", 
            "icono"   => "check", 
            "texto"   => "Se ha recibido el {$tipo} de la credencial"]);

        echo json_encode([
            "frente"  => $this->data["socio"]->data->credencial->frente,
            "reverso" => $this->data["socio"]->data->credencial->reverso,
            "acta"    => $this->data["socio"]->data->credencial->acta,
            "path"    => base_url().$path
        ]);
    }    


    public function cancela_ine( string $tipo ){
        $this->data[ "socio" ] = $this->data[ "usuario" ];

        // BITACORA Cancelar carga de foto INE o Acta
        bitacora( $this->data["socio"]->es_menor() ? 16 : 7, $this->data[ "socio" ]->id, [ 
            "tipo"    => $tipo,
            "usuario" => $this->data[ "usuario" ]->id
        ] );
        
        rename( 
            "data/{$this->data[ "socio" ]->id}/ine/".$this->data["socio"]->data->credencial->{$tipo}, 
            "data/{$this->data[ "socio" ]->id}/ine/backup_".time().".jpg" 
        );

        $json = $this->data["socio"]->data;
        $json->credencial->{$tipo} = null;
        $this->data["socio"]->data = $json; 

        model( "UsuarioModel" )->save( $this->data[ "socio" ] );

        return redirect()->to( "perfil" )->with( "msg", [ 
            "clase" => "success", 
            "icono" => "trash", 
            "texto" => "Se eliminó imagen de credencial INE {$tipo}"] );
    }


    public function valida_credencial(){
        $this->data[ "socio" ] = $this->data[ "usuario" ];

        // BITACORA Enviar credencial para validacion
        bitacora( $this->data["socio"]->es_menor() ? 17 : 10, $this->data[ "socio" ]->id, [ 
            "usuario" => $this->data[ "usuario" ]->id
        ] );
        
        $json = $this->data["socio"]->data;
        $json->credencial->estatus = 1; // enviadas y en espera
        $this->data["socio"]->data = $json; 

        model( "UsuarioModel" )->save( $this->data[ "socio" ] );

        return redirect()->to( "perfil" )->with( "msg", [ 
            "clase" => "success", 
            "icono" => "trash", 
            "texto" => "Se envió la credencial INE para su validación" ] );

    }


    public function add_beneficiario(){
        $this->data[ "socio" ] = $this->data[ "usuario" ];

        $nuevo = [
            "nombre" => $this->request->getPost( "beneficiario_nuevo" ),
            "porcentaje" => $this->request->getPost( "beneficiario_porcentaje" )
        ];

        $json = $this->data["socio"]->data;
        $json->beneficiarios[] = $nuevo;

        $nporc = $nuevo[ "porcentaje" ] + $this->data[ "socio" ]->porcentaje_beneficiarios();

        if( $nporc == 100 ){
            $json->verificacion->beneficiario = true;
        }
        $this->data["socio"]->data = $json; 

        if(  $nporc <= 100 ){
            model( "UsuarioModel" )->save( $this->data[ "socio" ] );

            // BITACORA Agregar beneficiario
            bitacora( 11, $this->data[ "socio" ]->id, [ 
                "nombre" => $nuevo[ "nombre"],
                "porcentaje" => $nuevo[ "porcentaje" ],
                "usuario" => $this->data[ "usuario" ]->id
            ] );
        }

        return redirect()->to( "perfil" )->with( "msg", [ 
            "clase" => "success", 
            "icono" => "check", 
            "texto" => "Se agregó beneficiario" ] );
    }


    public function cancela_beneficiario(){
        $this->data[ "socio" ] = $this->data[ "usuario" ];

        $beneficiario = $this->request->getPost( "old_beneficiario" );

        $json = $this->data["socio"]->data;
        $temp = $json->beneficiarios[ $beneficiario ];

        unset( $json->beneficiarios[ $beneficiario ] );

        $json->beneficiarios = array_values( $json->beneficiarios );
        $json->verificacion->beneficiario = false;
        $this->data["socio"]->data = $json; 

        model( "UsuarioModel" )->save( $this->data[ "socio" ] );

        // BITACORA Eliminar beneficiario
        bitacora( 12, $this->data[ "socio" ]->id, [ 
            "nombre" => $temp->nombre,
            "porcentaje" => $temp->porcentaje,
            "usuario" => $this->data[ "usuario" ]->id
        ] );

        return redirect()->to( "perfil" )->with( "msg", [ 
            "clase" => "success", 
            "icono" => "check", 
            "texto" => "Se eliminó beneficiario" ] );        
    }


    public function guarda_clabe(){
        $socio = $this->data[ "usuario" ];

        $clabe = $this->request->getPost( "clabe" );

        $validation = service( "validation" );
        $validation->setRules( [
            "clabe" => "required|exact_length[18]"
        ] );

        // Si hay errores de validación automática, regresar a formulario
        if( !$validation->withRequest( $this->request )->run() ){

            // BITACORA Error al agregar CLABE
            bitacora( 38, $socio->id, [ 
                "clabe"   => $clabe,
                "usuario" => $this->data[ "usuario" ]->id
            ] );

            return redirect()
                ->back()
                ->with( "errors", $validation->getErrors() )
                ->withInput();
        } 

        $banco = substr( $clabe, 0, 3 );
        
        $db = db_connect();
        if( !$db->query( "select count(*) as existe from t_bancos where codigo = '{$banco}'")->getRow()->existe ){
            // BITACORA Error al agregar CLABE
            bitacora( 38, $socio->id, [ 
                "clabe"   => $clabe,
                "usuario" => $this->data[ "usuario" ]->id
            ] );

            return redirect()
                ->back()
                ->with( "errors", [ "clabe" => "La CLABE no corresponde a un banco reconocido" ] )
                ->withInput();           
        }

        $json = $socio->data;
        $json->clabe = $clabe;
        $json->verificacion->clabe = true;
        $socio->data = $json; 

        model( "UsuarioModel" )->save( $socio );

        // actualizaar pagos pendientes
        $db->query( "update t_pagos set clabe = '{$clabe}' where usuario_id = ".$this->data[ "usuario" ]->id." and substring( estatus_codigo, 1, 3 ) < 400" );

        // BITACORA Actualziar CLABE interbancaria
        bitacora( 13, $socio->id, [ 
            "clabe"   => $clabe,
            "usuario" => $this->data[ "usuario" ]->id
        ] );

        return redirect()->to( "perfil" )->with( "msg", [ 
            "clase" => "success", 
            "icono" => "check", 
            "texto" => "Se actualizó la CLABE interbancaria" ] );        
    }


    public function nuevo_password( $s = null, $m = null, $p = null ){
        $socio     = $s ? model( "UsuarioModel" )->find( $s ) : $this->data[ "usuario" ];
        $actual    = $this->request->getPost( "actual" ) ?? null;
        $nuevo     = $p ?? $this->request->getPost( "nuevo" );
        $nuevo_bis = $p ?? $this->request->getPost( "nuevo_bis" );

        $validation = service( "validation" );
        $validation->setRules( [
            "actual"    => "required",
            "nuevo"     => "required|differs[actual]|min_length[6]",
            "nuevo_bis" => "required|matches[nuevo]",
        ] );

        if( $actual != $socio->password ){
            // BITACORA Error al crear nuevo password
            bitacora( 36, $socio->id, [ 
                "actual"    => $actual,
                "nuevo"     => $nuevo,
                "nuevo_bis" => $nuevo_bis,
                "usuario"   => $this->data[ "usuario" ]->id
            ] );

            return redirect()
                ->to( "perfil#password" )
                ->with( "errors", [ "actual" => "El password actual no es correcto" ] )
                ->withInput();
        }

        // Si hay errores de validacióna utomática, regresar a formulario
        if( !$validation->withRequest( $this->request )->run() ){
            // BITACORA Error al crear nuevo password
            bitacora( 36, $socio->id, [ 
                "actual"    => $actual,
                "nuevo"     => $nuevo,
                "nuevo_bis" => $nuevo_bis,
                "usuario"   => $this->data[ "usuario" ]->id
            ] );

            return redirect()
                ->to( "perfil#password" )
                ->with( "errors", $validation->getErrors() )
                ->withInput();
        } 

        $json = $socio->data;
        $json->verificacion->password = true;
        $socio->data = $json; 
        $socio->password = $nuevo;

        model( "UsuarioModel" )->save( $socio );

        // BITACORA Crear nuevo password
        bitacora( 14, $socio->id, [ 
            "actual"  => $actual,
            "nuevo"   => $nuevo,
            "nuevo_bis"   => $nuevo_bis,
            "usuario" => $this->data[ "usuario" ]->id
        ] );

        if($m){
            return redirect()->to( "red/{$m}" );
        }
        else{
            return redirect()->to( "perfil" )->with( "msg", [ 
                "clase" => "success", 
                "icono" => "check", 
                "texto" => "Se actualizó tu password" ] );        
        }

    }


    public function update_estatus( $s ){
        $db = db_connect();
        $mes_anterior = date('Ym', strtotime( date('Y-m').'-01'. ' -1 month' ) );

        $db->query( "select f_update_PTS( {$s}, codigo, '{$mes_anterior}' ) FROM t_modelos WHERE estatus_codigo = '201-ACTIVO'" );  
        $db->query( "select f_update_PTS( {$s}, codigo, DATE_FORMAT( NOW(), '%Y%m') ) FROM t_modelos WHERE estatus_codigo = '201-ACTIVO'" );  

        $db->query( "do f_get_estatus( {$s}, 1 )" );

        return redirect()->to( "red" );
    }


    public function valida_correo(){
        $email = \Config\Services::email();

        $config['protocol'] = 'sendmail';
        $config['SMTPHost'] = 'beneleit.mx';
        $config['SMTPUser'] = 'hola@beneleit.mx';
        $config['SMTPPass'] = 'Z@p0zEU8';
        $config['SMTPPort'] = 465;
        $config['mailType'] = 'html';
        $config['SMTPCrypto'] = 'ssl';
        $config['mailPath'] = '/usr/sbin/sendmail';
        $config['charset']  = 'utf-8';
        $config['wordWrap'] = true;

        $email->initialize($config);
        $email->setFrom('hola@beneleit.mx', 'Oficina Beneleit');
        $email->setTo('scabbia@gmail.com');
        $email->setMessage('Testing the email class. {unwrap}http://example.com/a_long_link_that_should_not_be_wrapped.html{/unwrap}');

        $email->send( false );


        $this->data[ "socio" ] = $this->data[ "usuario" ];

        $json = $this->data["socio"]->data;
        $json->verificacion->correo = true;
        $this->data["socio"]->data = $json; 

        model( "UsuarioModel" )->save( $this->data[ "socio" ] );

        // BITACORA Enviar correo verificación
        bitacora( 21, $this->data[ "socio" ]->id, [ 
            "usuario"  => $this->data[ "usuario" ]->id
        ] );

        return redirect()->to( "perfil" )->with( "msg", [ 
            "clase" => "success", 
            "icono" => "check", 
            "texto" => "Se envió un correo de verificación" ] );   
    }


    public function valida_cp(){
        $cp = $this->request->getPost( "cp" );

        $respuesta = [
            "total"      => 0,
            "colonias"   => [],
            "localidad"  => [],
            "entidad"    => [],
            "error"      => false
        ];

        $db = db_connect();
        
        $respuesta[ "colonias" ] = $db->query("select c.id, c.nombre, l.id AS l_id, l.nombre AS l_nombre, e.id AS e_id, e.nombre AS e_nombre
        from t_colonias c 
        JOIN t_localidades l ON l.id = c.localidad_id AND l.entidad_id = c.entidad_id
        JOIN t_entidades e ON e.id = c.entidad_id
        where c.codigopostal = '{$cp}' order BY c.nombre" )->getResultArray();

        $respuesta[ "total" ] = sizeof( $respuesta[ "colonias" ] );

        if( $respuesta[ "total" ] > 0 ){
            $respuesta[ "localidad" ] = [
                "id"     => $respuesta[ "colonias" ][ 0 ][ "l_id" ],
                "nombre" => $respuesta[ "colonias" ][ 0 ][ "l_nombre" ]
            ];
            $respuesta[ "entidad" ] = [
                "id"     => $respuesta[ "colonias" ][ 0 ][ "e_id" ],
                "nombre" => $respuesta[ "colonias" ][ 0 ][ "e_nombre" ]
            ];
        }
        else{
            $cp = substr( $cp, 0, 4 )."0";

            $base = $db->query("select c.id, c.nombre, l.id AS l_id, l.nombre AS l_nombre, e.id AS e_id, e.nombre AS e_nombre
            from t_colonias c 
            JOIN t_localidades l ON l.id = c.localidad_id AND l.entidad_id = c.entidad_id
            JOIN t_entidades e ON e.id = c.entidad_id
            where c.codigopostal = '{$cp}' order BY c.nombre" )->getResultArray();

            if( sizeof( $base ) > 0 ){
                $respuesta[ "localidad" ] = [
                    "id"     => $base[ 0 ][ "l_id" ],
                    "nombre" => $base[ 0 ][ "l_nombre" ]
                ];
                $respuesta[ "entidad" ] = [
                    "id"     => $base[ 0 ][ "e_id" ],
                    "nombre" => $base[ 0 ][ "e_nombre" ]
                ];
            }
            else{
              //  $respuesta[ "error" ] = true;
            }
        }

        echo json_encode( $respuesta ); 
    }


    public function create_domicilio(){
        $this->data[ "socio" ] = $this->data[ "usuario" ];

        extract( $this->request->getPost() );

        // crear colonia nueva
        if( $tipo_colonia == "nueva" ){
            $nueva = [
                "codigopostal" => $n_cp, 
                "nombre" => $n_colonia_nueva, 
                "localidad_id" => $n_localidad_id,
                "entidad_id" => $n_entidad_id
            ];

            $coloniamodel = model( "ColoniaModel" );
            $x = $coloniamodel->insert( $nueva );

            // BITACORA Creación de colonia
            bitacora( 54, $this->data[ "socio" ]->id, $nueva );            
        }
        else{
            $x = $n_colonia;
        }

        $recibe = [
            "id" => $dom_id ?? null,
            "estatus_codigo" => "201-ACTIVO", 
            "usuario_id"     => $this->data[ "socio" ]->id,
            "nombre"         => $n_nombre, 
            "calleynumero"   => $n_calle,
            "colonia_id"     => $x,
            "referencias"    => $n_referencias
        ];

        $domiciliomodel = model( "DomicilioModel" );

        if( $dom_id ){
            $domiciliomodel->save( $recibe );
            $id = $dom_id;
        }
        else{
            $id = $domiciliomodel->insert( $recibe );
        }
        
        $json = $this->data["socio"]->data;
        $json->verificacion->domicilio = true;
        if( !isset($json->domicilio) || $json->domicilio == null ){
            $json->domicilio = $id;
        }
        $this->data["socio"]->data = $json; 

        model( "UsuarioModel" )->save( $this->data[ "socio" ] );

        // BITACORA Crea/edita domicilio
        bitacora( $dom_id ? 39 : 20, $this->data[ "socio" ]->id, [ 
            "domicilio_id" => $id,
            "usuario" => $this->data[ "usuario" ]->id
        ] );

        echo $id ?? 0;
    }


    public function delete_domicilio(){
        extract( $this->request->getPost() );

        $domiciliomodel = model( "DomicilioModel" )->find( $dom_id );
        $domiciliomodel[ "estatus_codigo" ] = "110-ELIMINADO";
        model( "DomicilioModel" )->save( $domiciliomodel );

        $socio = $this->data[ "usuario" ];
        $d = $socio->getDomicilios();

        if( !sizeof( $d ) ){

            $json = $socio->data;
            $json->verificacion->domicilio = false;

            $socio->data = $json; 
            model( "UsuarioModel" )->save( $socio );    
        }

        // BITACORA Borra domicilio
        bitacora( 55, $socio->id, [ 
            "domicilio_id" => $dom_id,
            "usuario" => $this->data[ "usuario" ]->id
        ] );

        echo $dom_id;
    }



    public function create_numero(){
        $this->data[ "socio" ] = $this->data[ "usuario" ];

        extract( $this->request->getPost() );

        $recibe = [
            "id" => $t_id ?? null,
            "estatus_codigo" => "201-ACTIVO", 
            "numero"         => $t_numero,
            "nombre"         => $t_nombre, 
            "usuario_id"     => $this->data[ "socio" ]->id,
            "imei"           => $t_imei,
            "fechas"         => [
                "creado" => date( "Y-m-d" ),
                "recargas" => []
            ]
        ];

        $celular = model( "CelularModel" );

        if( $t_id ){
            $celular->save( $recibe );
            $id = $t_id;
        }
        else{
            $id = $celular->insert( $recibe );
        }
        
        // BITACORA Creación de numero telefonico
        bitacora( $t_id ? 41 : 40, $this->data[ "socio" ]->id, [ 
            "celular_id" => $id,
            "numero"     => $t_numero,
            "nombre"     => $t_nombre, 
            "imei"       => $t_imei,
            "usuario"    => $this->data[ "usuario" ]->id
        ] );

        echo $id ?? 0;
    }



    public function check_csf(){
        $this->data[ "socio" ] = $this->data[ "usuario" ];

        $json = $this->data["socio"]->data;
        $json->sat->estatus = $this->request->getPost( "check" ) == "true" ? 0 : 1;
        $this->data["socio"]->data = $json; 

        model( "UsuarioModel" )->save( $this->data[ "socio" ] );   

        // BITACORA Cambio de estatus en check de impuestos
        bitacora( $json->sat->estatus == 1 ? 25 : 24, $this->data[ "socio" ]->id, [ 
            "usuario" => $this->data[ "usuario" ]->id
        ] );
        
        print_r( $json );
    }


    public function carga_csf(){
        $this->data[ "socio" ] = $this->data[ "usuario" ];

        $pdf = $this->request->getPost( "pdf" );

        $path = "data/{$this->data["socio"]->id}/csf/";
        $filename = $this->data["socio"]->id."_".time().".pdf";

        $json = $this->data["socio"]->data;
        $json->sat->csf = $filename;
        $json->sat->estatus = 2;
        $json->verificacion->csf = true;
        $this->data["socio"]->data = $json; 

        model( "UsuarioModel" )->save( $this->data[ "socio" ] );

        if( !is_dir( $path ) ){
            mkdir( $path, 0755, true );
        }

        $fileTmpName = $_FILES[ "pdf" ][ "tmp_name" ];
        move_uploaded_file( $fileTmpName, $path.$filename );

        // BITACORA Carga de CSF
        bitacora( 22, $this->data[ "socio" ]->id, [ 
            "archivo" => $filename,
            "usuario" => $this->data[ "usuario" ]->id
        ] );


        // actualizaar pagos pendientes
        $db = db_connect();
        $db->query( "update t_pagos set data = json_set( data, '$.retencion', 0 ) where usuario_id = ".$this->data[ "socio" ]->id." and substring( estatus_codigo, 1, 3 ) < 400" );

        session()->setFlashdata('msg', [ 
            "clase"   => "success", 
            "icono"   => "check", 
            "texto"   => "Se ha recibido la Constancia de Situación Fiscal"]);

        echo json_encode([
            "frente"  => $this->data["socio"]->data->sat->csf,
            "path"    => base_url().$path
        ]);
    } 


    public function cancela_csf(){
        $this->data[ "socio" ] = $this->data[ "usuario" ];

        // BITACORA Cancelar carga de CSF
        bitacora( 23, $this->data[ "socio" ]->id, [ 
            "usuario" => $this->data[ "usuario" ]->id
        ] );
        
        $json = $this->data["socio"]->data;
        $json->sat->csf = null;
        $json->sat->estatus = 1;
        $json->verificacion->csf = false;
        $this->data["socio"]->data = $json; 

        model( "UsuarioModel" )->save( $this->data[ "socio" ] );

        // actualizaar pagos pendientes
        $db = db_connect();
        $db->query( "update t_pagos set data = json_set( data, '$.retencion', 1 ) where usuario_id = ".$this->data[ "socio" ]->id." and substring( estatus_codigo, 1, 3 ) < 400" );
        
        return redirect()->to( "perfil" )->with( "msg", [ 
            "clase" => "success", 
            "icono" => "trash", 
            "texto" => "Se eliminó la Constancia de Situación Fiscal"] );
    }


    public function guarda_rfc(){
        $socio = $this->data[ "usuario" ];

        $rfc = $this->request->getPost( "rfc" );

        $validation = service( "validation" );
        $validation->setRules( [
            "rfc" => "required|max_length[15]"
        ] );

        // Si hay errores de validación automática, regresar a formulario
        if( !$validation->withRequest( $this->request )->run() ){

            // BITACORA Error al agregar rfc
            bitacora( 49, $socio->id, [ 
                "rfc"   => $rfc,
                "usuario" => $this->data[ "usuario" ]->id
            ] );

            return redirect()
                ->back()
                ->with( "errors", $validation->getErrors() )
                ->withInput();
        } 
      
        $db = db_connect();
        
        $json = $socio->data;
        $json->sat->rfc = $rfc;
        $socio->data = $json; 

        model( "UsuarioModel" )->save( $socio );

        // BITACORA Actualziar CLABE interbancaria
        bitacora( 48, $socio->id, [ 
            "rfc"   => $rfc,
            "usuario" => $this->data[ "usuario" ]->id
        ] );

        return redirect()->to( "perfil" )->with( "msg", [ 
            "clase" => "success", 
            "icono" => "check", 
            "texto" => "Se actualizó tu RFC" ] );        
    }

}

<?php
namespace App\Entities;

use CodeIgniter\Entity\Entity;
use CodeIgniter\I18n\Time;

// Entidad USUARIO

class E_usuario extends Entity
{
    // campos visibles y formatos establecidos

    protected $casts = [
        "id"             => "integer",
        "estatus_codigo" => "string",
        "rol_codigos"    => "json",
        "password"       => "string",
        "data"           => "json",
        "correo"         => "string",
        "telefono"       => "string",
        "fechanac"       => "string",
        "curp"           => "string",
        "redes"          => "json",
        "historial"      => "json",
        "pedido"         => "json",
        "verificado"     => "json"
    ];


    function __construct( $id = null, $data = null ){
        if( $id ){
            $this->id = $id;
        }
        if( $data ){
            $this->data = json_decode( $data );
        }
    }


    /**
     * Obtiene los datos de la entidad E_usuario en un array asociativo
     * 
     * @return array con los datos de la entidad
     */
    public function getDatos()
    {
        return [
            "id"             => $this->id,
            "estatus_codigo" => $this->estatus_codigo,
            "rol_codigos"    => $this->rol_codigos,
            "data"           => $this->data,
            "correo"         => $this->correo,
            "telefono"       => $this->telefono,
            "fechanac"       => $this->fechanac,
            "curp"           => $this->curp,
            "redes"          => $this->redes,
            "historial"      => $this->historial,
            "verificado"     => $this->verificado,
            "PTS"            => $this->PTS
        ];
    }


    /**
     * Setea el password encriptado para el usuario.
     * El password se encripta con la key del id del usuario.
     * @param string $password El password a encriptar.
     * @return string El password encriptado.
     */
    protected function setPassword( string $password ): string
    {
        $data = $this->data;
        $data->hash = md5( $password );
        $this->data = $data;

        //  $db  = db_connect();
        //  $data = $db->query("update v_moodle set password = '{$this->data->hash}' where id = {$this->id}");
        
        $encrypter = service( "encrypter" );
        return $this->attributes[ "password" ] = base64_encode( $encrypter->encrypt( $password, [ "key" => $this->attributes[ "id" ] ] ) );
    }

    
    public function get_reset( $modelo )
    {
        if( !isset( $historial->modelos->{$modelo} ) ){
            $this->valida_modelo();
        }

        if( !isset( $this->historial->modelos->{$modelo}->reset ) ){

            $fecha = MODELOS[ $modelo ][ "settings" ][ "fecha_arranque" ] < $this->historial->registro ? $this->historial->registro : MODELOS[ $modelo ][ "settings" ][ "fecha_arranque" ];

            $historial = $this->historial;
            $historial->modelos->{$modelo}->reset = $fecha;
            $this->historial = $historial;

            model( "UsuarioModel" )->save( $this );
        }
        
        return $this->historial->modelos->{$modelo}->reset;
    }


    /**
     * Valida que el usuario tenga configurado el historial de cada modelo de negocio y que existan los datos necesarios para operar en cada modelo.
     * Si no existe alguno de los datos, se crean con los valores predeterminados.
     * 
     * @return void
     */
    public function valida_modelo()
    {
        $historial = $this->historial;
        $data      = $this->data;
        $redes     = $this->redes;

        $update = 0;

        foreach( MODELOS as $m ){
            $this->verificado = $this->get_verificacion( $m[ "codigo" ] );

            if( $m[ "settings" ][ "efectivo" ] ){

                if( 
                    !isset( $this->historial->modelos->{$m[ "codigo" ]} ) ||
                    !isset( $this->redes->modelos->{$m[ "codigo" ]} ) ||
                    !isset( $this->data->estatus->modelos->{$m[ "codigo" ]} ) 
                ){

                    $fecha = $m[ "settings" ][ "fecha_arranque" ] < $this->historial->registro ? $this->historial->registro : $m[ "settings" ][ "fecha_arranque" ];

                    $historial->modelos->{$m[ "codigo" ]} = [
                        "primercompra"   => json_decode( "{}" ),
                        "ultimacompra"   => null,
                        "fondeos"        => [],
                        "reset"          => $fecha,
                        "ingresos"       => [
                            date( "Ym" ) => []
                        ],
                        "calificaciones" => [
                            date( "Ym" ) => []
                        ]
                    ];

                    $data->saldo->{$m[ "codigo"]} = [
                        "cantidad" => 0.00,
                        "estatus"  => 0
                    ];
                    
                    $data->estatus->modelos->{$m[ "codigo"]} = ( $this->verificado->estatus ?? null ) ? "220-NUEVO-VERIFICADO" : "210-NUEVO"; 
                    
                    $redes->modelos->{$m[ "codigo" ]} = [
                        "padre"           => $redes->patrocinador,
                        "patrocinador"    => $redes->patrocinador,
                        "hijos"           => [],
                        "rango"           =>  $m[ "settings" ][ "rango_base" ] ?? null,
                        "profundidad"     => [
                            "activos"     => [0,0,0],
                            "calificados" => [0,0,0]
                        ]
                    ];

                    $update = 0;
                }
            }
        }
        
        if( !isset( $data->tarjeta) ){
            $data->tarjeta = [
                "numero"   => "",
                "estatus"  => "126-NO-ADQUIRIDO",
                "folio"    => 0,
                "cliente"  => 0
            ];

            $update = 1;
        }
        else{
            if( $data->tarjeta->estatus == "625-ACTIVA" && in_array( $data->tarjeta->cliente ?? 0, [ null, 0, "número no encontrado"] ) ){
                $db = db_connect();
                
                $sql = "SELECT empleado 
                        from t_tarjetas 
                        where tarjeta = ".substr($data->tarjeta->numero, 11, 3).substr($data->tarjeta->numero, 15, 4)." ";

                $data->tarjeta->cliente = $db->query( $sql )->getRow()->empleado ?? "número no encontrado";

                $update = 1;
            }
        }
        
        $this->historial = $historial;
        $this->data      = $data;
        $this->redes     = $redes;
      
        // Actualización de datos de socio al agregar un nuevo modelo de negocio

        if($update){
            model( "UsuarioModel" )->save( $this );
        }
    }

    
    /**
     * Reset password for user.
     * 
     * Generates a new random password and encrypts it using the Encrypter service.
     * Sets the new password on the user object and logs the action in the bitácora.
     * Also sets the password verification flag to false.
     */
    
    public function resetPassword()
    {
        $encrypter = service( "encrypter" );
        $password  = random_password();

        $this->password = $password; // base64_encode( $encrypter->encrypt( $password, [ "key" => $this->id ] ) );

        // BITACORA envío de correo de recuperación de password        
        bitacora( 37, $this->id, [
            "nuevo" => $password
        ] );
        
        $data = $this->data;
        $data->verificacion->password = false;
        $data->verificaciones->{"PASSWORD"} = false;
        $this->data = $data;

        $historial = $this->historial;
        $historial->reset = date( "Y-m-d H:i:s" );
        $this->historial = $historial;

        $this->update_verificacion();
    }


    /**
     * Retorna el patrocinador del socio para el modelo de negocio indicado,
     * si no existe lo crea tomando el patrocinador general.
     * 
     * @param string $modelo Código del modelo de negocio
     * @return int ID del patrocinador
     */
    public function patrocinador( $modelo )
    {
        // validar que existe
        // si no existe, tomamos el general y creamso el registro

        if( !isset( $this->redes->modelos->{$modelo}->patrocinador ) ){
            $redes = $this->redes;
            $redes->modelos->{$modelo}->patrocinador = $this->redes->patrocinador;
            $this->redes = $redes; 

            model( "UsuarioModel" )->save( $this );
        }

        return $this->redes->modelos->{$modelo}->patrocinador;
    }


    /**
     * Regresa el password del usuario sin encriptar.
     * 
     * @return string El password del usuario sin encriptar.
     */
    public function getPassword(): string 
    {
        $encrypter = service( "encrypter" );
        $cadena = base64_decode( $this->attributes[ "password" ] );
        return $encrypter->decrypt( $cadena, [ "key" => $this->id ] );
    }


    /**
     * Actualiza las calificaciones actuales del usuario en el modelo especificado.
     *
     * Esta función no hace nada actualmente, ya que la lógica de actualización de
     * calificaciones se encuentra en una función almacenada en MySQL.
     *
     * @param string $modelo Codigo del modelo a actualizar
     */
    public function updateCalificaciones( $modelo )
    {
        // transferido a stored function en MySQL
    }


    /**
     * Regresa un arreglo con las calificaciones actuales del usuario en el modelo
     * especificado. Si se especifica un mes, regresa solo las calificaciones
     * correspondientes a ese mes, de lo contrario, regresa las calificaciones
     * actuales en todos los meses.
     *
     * @param string $modelo Codigo del modelo
     * @param string $m      Mes a consultar, si se omite, se regresan todas las
     *                      calificaciones actuales en todos los meses
     *
     * @return array Un arreglo con las calificaciones actuales del usuario,
     *               cada una de sus promociones tiene una clave "meses" con las
     *               calificaciones en cada mes y una clave "total" con la suma
     *               de todas las calificaciones.
     */
    public function getCalificaciones( $modelo, $m = false )
    {
        $PTS = [];

        if( !defined( "PROMOCIONES" ) ) {
            load_catalogo( "promociones", "modelo_codigo = '{$modelo}' OR settings->'$.universal' = true");
        }

        if( !isset( $this->historial->modelos->{$modelo} ) ){
            $this->valida_modelo();
        }

        foreach( PROMOCIONES as $promo ){


            if( $m ){
                if( isset( $this->historial->modelos->{$modelo}->calificaciones->{$m}->{$promo[ "codigo" ]} ) ){
                    $PTS[ $promo[ "codigo" ] ] = $this->historial->modelos->{$modelo}->calificaciones->{$m}->{$promo[ "codigo" ]};
                }
            }
            else{
                foreach( MESES as $mes ){
                    $PTS[ $promo[ "codigo" ] ][ "meses" ][ $mes ] = 0;
                }
                $PTS[ $promo[ "codigo" ] ][ "total" ] = 0;
            }
        } 

        if( !$m ){
            foreach( $this->historial->modelos->{$modelo}->calificaciones as $mes => $promos ){
                if( $promos )
                foreach( $promos as $promo => $pts ){
                    $PTS[ $promo ][ "meses" ][ $mes ] = $pts;

                    if( !isset( $PTS[ $promo ][ "total" ] ) ){
                        $PTS[ $promo ][ "total" ] = 0;
                    }

                    $PTS[ $promo ][ "total" ] = $PTS[ $promo ][ "total" ] + $pts;
                }
            }
        }

        return $PTS;
    }
    

    /**
     * Asigna el CURP y extrae la fecha de nacimiento y genero.
     *
     * @param string $curp
     * @return void
     */
    protected function setCurp( string $curp )
    {
        $data = json_decode( $this->attributes[ "data" ] );

        $this->attributes[ "curp" ] = strtoupper( $curp );

        $caras = [
            "face-smile",
            "face-smile-wink",
            "face-meh",
            "face-laugh-wink",
            "face-laugh-squint",
            "face-laugh",
            "face-laugh-beam",
            "face-grin-wide",
            "face-grin-wink",
            "face-grin-tongue-wink",
            "face-grin-tongue-squint",
            "face-grin-stars",
            "face-grin-tongue",
            "face-grin-squint-tears",
            "face-grin-squint",
            "face-grin-beam-sweat",
            "face-grin-beam",
            "face-grin",
        ];

        $colores = [
            "indigo",
            "deep-purple",
            "purple",
            "violet",
            "pink",
            "red",
            "deep-orange",
            "orange",
            "mustard",
            "amber",
            "yellow",
            "lime",
            "light-green",
            "green",
            "teal",
            "cyan",
            "light-blue",
            "blue",
            "brown",
        ];

        $data->avatar->face = $caras[ rand( 0, sizeof( $caras   ) - 1 ) ];
        $data->avatar->bg = $colores[ rand( 0, sizeof( $colores ) - 1 ) ];
    //    $data->beneficiarios = [];
        $this->attributes[ "data" ] = json_encode( $data );
    }


    /**
     * Consulta los usuarios que tienen al menos 6 biex y 3 plus en el mes indicado, y que su primer compra fue en ese mismo mes
     * @param string $mes Mes en formato "Ym". Si no se proporciona, se toma el mes actual.
     * @return array Arreglo con los IDs de los usuarios que cumplen con la condicion.
     */
    public function getPremieres( $mes = null )
    {
        $db  = db_connect();

        if( !$mes ){
            $mes = date( "Ym" );
        }

        $sql = "SELECT 
                DATE_FORMAT( historial->>'$.modelos.\"10-NUTRICION\".primercompra.\"010-DISTRIBUIDOR\"', '%Y%m' ) AS primercompra,
                historial->'$.modelos.\"10-NUTRICION\".calificaciones.\"{$mes}\".\"010-DISTRIBUIDOR\"' AS biex,
                historial->'$.modelos.\"10-NUTRICION\".calificaciones.\"{$mes}\".\"030-PLUS\"' AS plus,
                redes->>'$.modelos.\"10-NUTRICION\".padre' AS padre,
                id
            FROM t_usuarios
            WHERE redes->>'$.modelos.\"10-NUTRICION\".padre' = {$this->id}
            HAVING biex >= 6 AND plus >= 3 and primercompra = '{$mes}'";  
            
        return $db->query($sql)->getResultArray();
    }


    /**
     * Genera un string con la representacion del id del usuario con el siguiente formato:
     * - Si se proporciona un modelo, se muestra el icono del modelo, el nombre del modelo y el id del usuario, y se agrega un tooltip con informacion adicional.
     * - Si no se proporciona un modelo, se muestra el id del usuario con un estilo determinado por el parametro $clase.
     * - Si se proporciona el parametro $verificado, se agrega un indicador de verificacion.
     * @param string $modelo El codigo del modelo a representar.
     * @param string $clase La clase CSS a aplicar al elemento.
     * @param bool $verificado Si se debe mostrar el indicador de verificacion.
     * @return string El string con la representacion del id del usuario.
     */
    public function id( $modelo = null, $clase = null, $verificado = true ): string 
    {
        if( $modelo ){

            // $this->verificado =  = [ "verificado" => "f_get_verificacion" ];

            $this->verificado = $this->get_verificacion( $modelo );

            $m_0 = date('Ym');
            $m_1 = date('Ym', strtotime( date('Y-m').'-01'. ' -1 month' ) );

            if( !defined( "calificaciones" ) ){
                load_catalogo( "calificaciones", "");
            }

            $db  = db_connect();
            $sql = "select f_get_calificacion( {$this->id}, '{$m_1}', '{$modelo}' ) as '{$m_1}', f_get_calificacion( {$this->id}, '{$m_0}', '{$modelo}' ) as '{$m_0}'";
            
            if( !isset( $this->data->estatus->modelos->{$modelo} ) ){
                $this->valida_modelo();
            }

            $calificaciones = $db->query($sql)->getRowArray();

            $estatus = ESTATUS[ $this->data->estatus->modelos->{$modelo} ] ?? ESTATUS[ "000-DESCONOCIDO" ];
            $modelo  = MODELOS[ $modelo ];
          
            switch( $modelo[ "codigo" ] ){
                case "10-NUTRICION" : 
                case "30-ALIMENTOS" : 
                case "40-GASOLINAS" : 
                        $calificacion = CALIFICACIONES[ $calificaciones[ $m_1 ] ][ "descripcion" ]." - ".CALIFICACIONES[ $calificaciones[ $m_0 ] ][ "descripcion" ];                    
                    break;

                default:
                    $calificacion = CALIFICACIONES[ $calificaciones[ $m_0 ] ][ "descripcion" ];                    
            }

            $pendientes = "";
            foreach( $this->verificado->puntos as $p => $e ){
                if( $e == false ){
                    $pendientes .= "<span class='badge bg-red m-1'>{$p}</span>";
                }
            }

            return 
                "<span data-bs-toggle=\"tooltip\" data-bs-html=\"true\" title=\"<p class='mt-3'>"
                .$this->avatar(150, false, true)
                ."</p><p class='m-0'>BENELEIT {$modelo[ "nombre" ]}</p>"
                ."<h3><span class='col-12 w-100 badge bg-{$modelo[ "settings" ][ "color" ]}'>"
                ."<i class='fa fa-{$modelo[ "settings" ][ "icono" ]}'></i> ".id( $this->id, 6 )
                ."</span></h3><p class='m-0'>".$this->nombre( 2 )
                ."</p><span class='badge w-100 bg-".( $this->verificado->estatus ? "teal" : "red" )."'>Socio "
                .( $this->verificado->estatus ? "" : "no" )
                ." verificado</span>{$pendientes}<span class='badge w-100 bg-".$estatus[ "color" ]
                ."'>{$estatus[ "descripcion" ]}</span>"
                ."<div class='py-1'>{$calificacion}</div>\" class=\"badge bg-".$estatus[ "color" ]."\">"
                .( $modelo ? "<i class=\"fa fa-".$modelo[ "settings" ][ "icono" ]."\"></i> " : "" )
                .id( $this->id, 6 )."</span>".
                ( $verificado ? " <span class=\"small\">".$this->verified( $modelo )."</span>" : "" );
        }
        elseif( $clase ){
            return "<span style=\"position:relative\" class=\"badge bg-{$clase}\">".id( $this->id, 6 )."</span>";
        }

        return id( $this->id, 6 );
    }


    /**
     * Gets all the investments made by the user.
     *
     * The query looks for all the orders made by the user with an status code greater than 400.
     * This means that the orders must have been paid and have a status of "inversion" (not just "carrito").
     *
     * @return array An array of InversionModel objects representing the investments.
     */
    public function get_inversiones()
    {
        $where = "t_pedidos.usuario_id = {$this->id} and substring( t_pedidos.estatus_codigo, 1, 3 ) > 400";
        
        return model( "InversionModel" )->select("t_inversiones.*" )->join('t_pedidos', 't_pedidos.id = t_inversiones.pedido_id')->where( $where )->findAll();
    }


    /**
     * Returns an icon representing the verification status of the user.
     *
     * The icon is a circle with a check mark if the user is verified, and a circle with an x mark if not.
     * The color of the icon is teal for verified users and red for unverified users.
     *
     * @return string HTML string containing the icon element.
     */

    public function verified( $modelo = null )
    {

        return $modelo ? "<i class=\"far fa-circle-".( $this->verificado->estatus ? "check text-teal" : "xmark text-red" )."\"></i>" : "";
    }


    /**
     * Muestra el nombre del usuario, con o sin apellidos.
     * 
     * Si $apellidos es cero, solo se muestra el nombre.
     * Si $mask es verdadero, se aplica una m scara de seguridad al nombre y apellidos.
     * Si $text es verdadero, no se aplica formato HTML a la respuesta.
     * 
     * @param int $apellidos N mero de apellidos a mostrar
     * @param bool $mask Aplicar m scara de seguridad
     * @param bool $text No aplicar formato HTML
     * @return string El nombre del usuario
     */
    public function nombre( $apellidos = 0, $mask = false, $text = false ): string
    {
        $nombre = 
             ( $text ? "" : "<strong class='fw-bold'>" )
            .( $mask == true && $mask != 2 ? mask( $this->data->nombre ) : $this->data->nombre )
            .( $text ? "" : "</strong>" )
            .( $mask == 2 ? "" : " " )
            .( $mask ? mask( implode( " ", $this->data->apellidos ) ) : implode( " ", $this->data->apellidos ) );
        
        return $nombre;
    }

 
    /**
     * Muestra el rango del usuario como una imagen.
     * 
     * Se utiliza el c digo del rango para buscar la imagen correspondiente en la carpeta assets/img/rangos/
     * 
     * @param int $size Tama o de la imagen
     * @return string C digo HTML de la imagen
     */
    public function rango( int $size = 40 ): string 
    {
        return "<img style=\"width:{$size}px; height:{$size}px;\" src=\"".base_url()."assets/img/rangos/{$this->data->rango}.png\">";
    }


    /**
     * Muestra el avatar del usuario.
     * 
     * Si el usuario tiene avatar se muestra la imagen correspondiente,
     * de lo contrario se muestra un emoji con las iniciales del usuario.
     * 
     * @param int $size Tama o del avatar
     * @param string $id Valor para el atributo id del elemento img
     * @param mixed $commmas (no se utiliza)
     * @return string C digo HTML del avatar
     */
    public function avatar( int $size = 40, string $id = null, $commmas = null ): string 
    {
        $url = base_url();
        $url = "https://app.beneleit.mx/";

        if( $this->data->avatar->activo !== null ){
            return "<img ".($id ?? "")." class='rounded-circle' style='padding:0 !important;width:{$size}px; height: {$size}px;' src='{$url}data/{$this->id}/avatar/{$this->data->avatar->imagenes[ $this->data->avatar->activo ]}'>";
        }

        return "<div class='emoji'><div style='border-radius:50%; width:{$size}px;height:{$size}px;font-size:".($size/2)."px;line-height:".( $size / 2 )."px; padding-top:20%' class='text-teal bg-gray-400'>".$this->iniciales()."</div></div>";
    }


    /**
     * Determina si el usuario tiene permiso para un rol en particular.
     * 
     * Si el usuario tiene el rol especificado, se devuelve true.
     * Si el usuario tiene el rol "50-ROOT", se devuelve true si no se especifica el par metro $forzado o este es false.
     * De lo contrario, se devuelve false.
     * 
     * @param string $rol C digo del rol a verificar
     * @param bool $forzado Si se debe forzar la verificaci n sin considerar el rol "50-ROOT". Por defecto es false.
     * @return bool true si el usuario tiene permiso, false de lo contrario
     */
    public function permiso( $rol, $forzado = false )
    {
        $r1 = in_array( $rol,      $this->rol_codigos );
        $r2 = in_array( "50-ROOT", $this->rol_codigos );

        return  $r1 OR ( !$forzado && $r2 ); 
    }


    /**
     * Determina si el usuario tiene permiso de administrador.
     * 
     * Se buscan los roles de tipo "ADMIN" en el cat logo de roles y se verifica si el usuario tiene permiso para alguno de ellos.
     * 
     * @return bool Verdadero si el usuario es administrador.
     */
    public function es_admin()
    {
        load_catalogo( "roles" );
        $admin = false;

        foreach( ROLES as $r ){
            if( $r[ "tipo" ] == "ADMIN" ){
                if( $this->permiso( $r[ "codigo" ] ) ){
                    $admin = true;
                }
            }
        }

        return $admin;
    }


    /**
     * Obtiene las iniciales del usuario a partir de su nombre y primer apellido.
     * 
     * @return string Las iniciales del usuario.
     */

    public function iniciales(){
        return substr( $this->data->nombre, 0, 1 ).substr( $this->data->apellidos[0], 0, 1 );
    }


    /**
     * Devuelve el porcentaje de beneficiairos que se han asignado a esta cuenta
     * @param int $porcentaje Porcentaje de beneficiairos que ya se han sumado
     * @return int Porcentaje total de beneficiairos
     */
    public function porcentaje_beneficiarios( $porcentaje = 0 )
    {
        if( !isset( $this->data->beneficiarios ) ){
            $data = $this->data;

            $data->beneficiarios = [];
            $this->data = $data;
        }

        foreach( $this->data->beneficiarios as $b ){
            $porcentaje += $b->porcentaje;
        }

        return $porcentaje;
    }


    /**
     * Regresa el c digo del banco seg n la clabe del usuario
     * 
     * Si se pasa $url como true, regresa la URL de la imagen del banco
     * 
     * @param bool $url Si se pasa como true regresa la URL de la imagen del banco
     * @return string C digo del banco o la URL de la imagen del banco
     */
    public function banco( $url = false )
    {
        $banco_codigo = substr( $this->data->clabe, 0, 3);

        if( $url ){
            $url = "assets/img/bancos/{$banco_codigo}.png";
            if( file_exists( $url ) ){
                return base_url().$url;
            }
            else{
                return base_url()."assets/img/blank.png";
            }
        }
        
        return $banco_codigo;
    }


    public function pedidos_gratis( $modelo, $mes )
    {
        $db  = db_connect();

        switch( $modelo ){
            case "10-NUTRICION" : 
                        
                $sql = "select count(*) as pedidos_gratis 
                        from t_pedidos 
                        where usuario_id = {$this->id} 
                        and modelo_codigo = '{$modelo}' 
                        and substring( estatus_codigo, 1, 3 ) > 400 
                        and date_format( fechas->>'$.pagado', '%Y%m' ) = '{$mes}'
                        and data->>'$.enviogratis' = 1";

                return $db->query( $sql )->getRow()->pedidos_gratis;

            default : 
                return 0;
        }
    }


    /**
     * Busca si el usuario tiene un pedido pendiente (con estatus entre 300 y 400) para el modelo especificado.
     * Si lo encuentra, regresa el id del pedido, de lo contrario regresa null.
     * @param string $modelo El c digo del modelo a buscar
     * @return int|null El id del pedido pendiente o null si no se encontr 
     */
    public function pedidopendiente( $modelo )
    {
        $db  = db_connect();
        $p = $db->query( "select id from t_pedidos where usuario_id = {$this->id} and modelo_codigo = '{$modelo}' and substring( estatus_codigo, 1, 3 ) between 300 and 400 " )->getRow();

        if( $p ){
            return $p->id;
        }

        return null;
    }


    /**
     * Regresa un string en formato JSON con la estructura de la red de downline del usuario, hasta el numero de niveles especificado.
     * @param string $modelo El codigo del modelo a obtener la red de downline
     * @param int $niveles El numero de niveles a obtener. Si no se especifica, se utiliza el valor de configuracion del modelo.
     * @return string Un string en formato JSON con la estructura de la red de downline del usuario
     */
    public function getDownlineJSON( $modelo, $niveles = null )
    {

        $db  = db_connect();
        $sql = "select f_get_downline( {$this->id}, '{$modelo}', ".($niveles ?? MODELOS[ $modelo ][ "settings" ][ "niveles" ] )." ) as downline";
        $r = $db->query( $sql )->getRow();

        return $r->downline;
    }


    /**
     * Gets the upline in a JSON format for the given model.
     *
     * @param string $modelo The model code.
     *
     * @return string The upline in a JSON format.
     */
    public function getUplineJSON( $modelo )
    {

        $db  = db_connect();
        $sql = "select f_get_upline( {$this->id}, '{$modelo}', 1, '".date( "Y-m-d" )."' ) as upline";
        $r = $db->query( $sql )->getRow();

        return $r->upline;
    }


    /**
     * Regresa la bit cora del usuario en forma de arreglo
     * 
     * Cada elemento del arreglo es un objeto con las propiedades:
     * - fecha: fecha y hora de la acci n
     * - indice: n mero de la acci n en la bit cora
     * - codigo: c digo de la acci n
     * - ip: direcci n IP desde la que se realiz  la acci n
     * - string: descripci n de la acci n con variables reemplazadas
     * - variables: objeto con las variables que se reemplazaron en la descripci n de la acci n
     * 
     * @return array Arreglo con las movimientos del usuario
     */
    public function getBitacora()
    {
        $db = db_connect();
        $respuesta = [];

        $sql = "SELECT 
                    t_bitacoras.fecha as fecha, 
                    t_bitacoras.id as indice,
                    t_acciones.id as codigo, 
                    t_bitacoras.ip as ip, 
                    t_acciones.string as string, 
                    t_bitacoras.variables as variables  
                FROM t_bitacoras 
                JOIN t_acciones on t_acciones.id = t_bitacoras.accion_id 
                WHERE usuario_id = {$this->id} 
                ORDER BY fecha desc
                limit 500";

        $movimientos = $db->query( $sql );

        foreach( $movimientos->getResult() as $m ){
            $respuesta[] = $m;
            $m->variables = json_decode( $m->variables ); 

            switch( $m->codigo ){
                case 111:
                case 112:
                    $modelo = MODELOS[ $m->variables->modelo ];
                    $m->variables->modelo_nombre = $modelo[ "nombre" ];
                break;
            }


            foreach($m->variables as $k => $v){

                if(is_string($v)){
                    $m->string = str_replace( "#{$k}#", "<strong>{$v}</strong>", $m->string );
                }
            }
        }

        return $respuesta;
    }


    /**
     * Determines if the user is a minor based on their birthdate.
     *
     * This function calculates the user's age using their birth date and checks 
     * if they are under 18 years old.
     *
     * @return bool Returns true if the user is under 18, false otherwise.
     */
    public function es_menor()
    {
        $fecha = new Time( $this->fechanac );
        return $fecha->getAge() < 18;
    }


    /**
     * Retrieves the user's rewards based on their current cycle and status.
     *
     * If the user's rewards data is not initialized, it sets default values and saves the user.
     * Queries the rewards for the current cycle based on the status code '201-ACTIVO'.
     *
     * If the $activa parameter is true, it returns the first active reward that has not been achieved yet,
     * following the user's reward order if it is set. By default, it looks for "010-CELULAR".
     *
     * @param bool $activa Determines whether to return only the active reward or all rewards.
     * @return array|object Returns an array of all rewards if $activa is false, or the active reward object if true.
     */
    public function getRecompensas( $activa = false )
    {

        if( !isset( $this->data->recompensas ) ){
            $data = $this->data;

            $data->recompensas = [
                "ciclo"      => 1,
                "activa"     => "010-CELULAR",
                "inicia"     => null,
                "estrellas"  => 0
            ];

            $this->data = $data;
            model( "UsuarioModel" )->save( $this );
        }

        $sql = "estatus_codigo = '201-ACTIVO' and ciclo = ".$this->data->recompensas->ciclo;
        $recompensas = model( "RecompensaModel" )->where( $sql , null, false )->findAll();
        $a = $this->recompensas_alcanzadas();

        if( $activa ){
            $busqueda = "010-CELULAR";

            if( isset( $this->data->recompensas->orden ) ){
                foreach( $this->data->recompensas->orden->{1} as $r ){
                    if( !in_array( $r, $a ) ){
                        $busqueda = $r;    
                        break;
                    }
                }
            }

            foreach( $recompensas as $r ){                
                if( $r[ "codigo" ] == $busqueda ) return $r;
            }
        }
        return $recompensas;
    }


    /**
     * Retrieves the original password of the user. If the password is not set,
     * generates a random password, saves it, and then returns it.
     *
     * @return string The user's original password.
     */

    public function password_original()
    {
        
      
        if( !$this->attributes[ "password"] ){
            $this->password = random_password();
            model( "UsuarioModel" )->save( $this );
        }

        return $this->attributes[ "password"];
    }

    
    /**
     * Calculates and updates the user's current star count based on pending commissions.
     *
     * This function retrieves the total number of stars (or points) accumulated by the user
     * from the pending commissions in the database. It compares the retrieved count with
     * the current stored count in the user's data. If the user has reached or exceeded the
     * required stars for a reward (if provided), the reward redemption process is triggered.
     * The function also updates the user's star count and sends a notification if the count
     * has increased.
     *
     * @param array|null $r Optional. The reward array containing the required number of stars for redemption.
     * @return int The total number of stars accumulated by the user.
     */
    public Function getEstrellas( $r = null )
    {
        
        $db  = db_connect();
        $sql = "SELECT SUM(cantidad) as estrellas
                FROM t_comisiones 
                WHERE usuario_id = {$this->id}
                AND esquema_codigo = '120-BIEX-3ER-NIVEL'
                AND estatus_codigo = '255-PENDIENTE'";

        $estrellas = intval( $db->query( $sql )->getRow()->estrellas );

        $data = $this->data;

        if( is_object( $data->recompensas->estrellas ) ){
            $data->recompensas->estrellas = 0;
        }

        // checa si ya alcanzó recompensa
        if( $r && $estrellas >= intval( $r[ "estrellas" ] ) ){

            // redención de premio en automático
            // se debe de cambiar por una pregunta sobre redención del premio seleccionado en primer orden

            /*             
            // update conteo
            $recompensa = $this->redime_recompensa( $r );

            // notificación flash
            $data->splash[] = [
                "tipo" => "recompensa",
                "parametros" => [ $r[ "codigo" ] ]
            ];

            $data->recompensas->activa = $recompensa;
            $data->recompensas->estrellas = intval( $estrellas - $r[ "estrellas"] );
            $this->data = $data;

            model( "UsuarioModel" )->save( $this ); 
            */
        }
        
        if( $estrellas != $data->recompensas->estrellas ){
            
            if( $estrellas > $data->recompensas->estrellas ){
                // notificación flash
                $data->splash[] = [
                    "tipo" => "estrellas",
                    "parametros" => [ intval( $estrellas - $data->recompensas->estrellas ) ]
                ];
            }

            $data->recompensas->estrellas = intval( $estrellas );
            $this->data = $data;

            model( "UsuarioModel" )->save( $this );
        }

        return intval( $estrellas ); 
    }


    /**
     * Retrieves a list of reward codes that have been redeemed and have a status code greater than 200 for the current user.
     *
     * @return array An array of reward codes.
     */

    public function recompensas_alcanzadas()
    {
        $db = db_connect();
        $re = $db->query( "select recompensa_codigo from t_redenciones where substring( estatus_codigo, 1, 3 ) > 200 and usuario_id = '{$this->id}'" );
        $resultado = [];

        foreach( $re->getResult() as $r ){
            $resultado[] = $r->recompensa_codigo;
        }

        return $resultado;
    }


    /**
     * Devuelve un array con los códigos de las recompensas que ha recibido el usuario.
     * @return array
     */
    public function recompensas_recibidas()
    {
        $db = db_connect();
        $re = $db->query( "select recompensa_codigo from t_redenciones where estatus_codigo = '623-ENTREGA' and usuario_id = '{$this->id}'" );
        $resultado = [];

        foreach( $re->getResult() as $r ){
            $resultado[] = $r->recompensa_codigo;
        }

        return $resultado;
    }


    /**
     * Registra la redención de una recompensa y coloca la siguiente recompensa para el usuario.
     * @param array $r Un array con la información de la recompensa a redimir, con al menos los campos "codigo" y "estrellas".
     * @return string El código de la siguiente recompensa a alcanzar.
     */
    public function redime_recompensa( $r )
    {
        $db = db_connect();
        $db->query( "insert into t_redenciones values( NULL, '330-EN-ESPERA', {$this->id}, '{$r[ "codigo" ]}', '".date( "Y-m-d" )."')" );
        $db->query( "call p_cobra_estrellas( {$this->id}, '{$r[ "estrellas" ]}' )" );
    
        // coloca siguiente recompensa
        $sql = "SELECT p.codigo as recompensa 
                FROM t_recompensas p
                WHERE p.estatus_codigo = '201-ACTIVO' AND p.ciclo = {$this->data->recompensas->ciclo}
                AND p.codigo NOT IN(
                    SELECT recompensa_codigo from t_redenciones r WHERE r.usuario_id = {$this->id}
                )
                ORDER BY p.estrellas ASC LIMIT 1";
    
        return $db->query( $sql )->getRow()->recompensa;
    }


    /**
     * Devuelve un array con los celulares asociados al usuario, solo se consideran los que tienen un estatus mayor a 200.
     * @return array Un array con los celulares asociados al usuario.
     */
    public function getCelulares()
    {
        $db  = db_connect();
        $sql = "SELECT * from t_celulares WHERE usuario_id = {$this->id} and substr( estatus_codigo, 1, 3) > 200";
        return $db->query( $sql )->getResultArray();
    }


    /**
     * Devuelve un array de los domicilios del usuario, si $con_colonia es true, solo devuelve los que tienen colonia asignada.
     * Si $todos es true, devuelve todos los domicilios, sin importar si est n activos o no.
     * Si el usuario no tiene un domicilio seleccionado, se selecciona el primero de la lista y se guarda en la base de datos.
     * 
     * @param bool $con_colonia
     * @param bool $todos
     * @return array
     */
    public function getDomicilios( $con_colonia = false, $todos = false )
    {
        $db = db_connect();
        $respuesta = [];
        $existe = false;

        $sql = "SELECT 
                    d.id as id, d.nombre as nombre, d.referencias, d.calleynumero, c.nombre as colonia,c.id as colonia_id, l.nombre as localidad, e.nombre as entidad,  l.id as localidad_id, e.id as entidad_id, c.codigopostal
                from t_domicilios d
                left JOIN t_colonias c ON c.id = d.colonia_id
                left JOIN t_localidades l ON l.id = c.localidad_id AND l.entidad_id = c.entidad_id
                left JOIN t_entidades e ON e.id = c.entidad_id
                WHERE d.usuario_id = {$this->id}  
                ".( $todos ? "" : "and d.estatus_codigo = '201-ACTIVO'" )." 
                ".( $con_colonia ? "and d.colonia_id is not null" : "" )."
                order by d.created_at";

        $temp = $db->query( $sql )->getResultArray();

        foreach( $temp as $data ){
            $respuesta[ $data[ "id" ] ] = $data;

            if( isset( $this->data->domicilio ) && $this->data->domicilio == $data[ "id" ] ){
                $existe = true;
            }
        }

        if( !$existe && sizeof( $temp ) ){
            $data = $this->data;
            $data->domicilio = $temp[ 0 ][ "id" ];
            $this->data = $data;
            
            model( "UsuarioModel" )->save( $this );
        }

        return $respuesta;
    }


    /**
     * Regresa el pedido activo para el modelo dado.
     * 
     * @param string $modelo Codigo del modelo a buscar.
     * @param bool $create Si no hay pedido activo, crea uno nuevo.
     * 
     * @return array|false El pedido activo, o false si no se encuentra y no se debe crear.
     */
    public function getPedido( $modelo, $create = true ){
        $sql    = "modelo_codigo = '{$modelo}' and estatus_codigo = '250-EN-PROCESO' and usuario_id = '{$this->id}'";
        $pedido = model( "PedidoModel" )->where( $sql , null, false )->first();

        if( !$pedido ){

            if( !$create ){
                return false; 
            } 

            $pedido = nuevo_pedido( $modelo );
            $pedido[ "usuario_id" ] = $this->id;

            model( "PedidoModel" )->save( $pedido );
            $pedido = $this->getPedido( $modelo );
        }
        
        return $pedido;
    }


    public function getComprasViaje()
    {
        $db = db_connect();
        $sql = "SELECT f_get_compras( {$this->id}, 6 ) as compras";

        return $db->query( $sql )->getRow()->compras;
    }



    public function getNuevosSocios( $minima, $inicia, $termina )
    {
        $db = db_connect();
        $sql = "SELECT
                    p.usuario_id, 
                    sum( p.PTS->>'$.\"010-DISTRIBUIDOR\"' ) as puntos,
                    sum( p.data->>'$.primercompra' ) as primercompra,
                    JSON_EXTRACT( f_get_verificacion( p.usuario_id, '10-NUTRICION' ), '$.estatus' ) as verificado
                from t_pedidos p
                join t_usuarios u on u.id = p.usuario_id
                where p.modelo_codigo = '10-NUTRICION'
                and substring( p.estatus_codigo, 1, 3 ) > 400
                and cast( p.fechas->>'$.califica' as date ) between '{$inicia}' and '{$termina}' 
                and u.redes->>'$.modelos.\"10-NUTRICION\".padre' = {$this->id}
                group by p.usuario_id
                having puntos >= {$minima} and primercompra > 0
                order by verificado desc";

        $socios = [];
        foreach( $db->query( $sql )->getResult() as $r ){
                $socios[ $r->usuario_id ] = $r->verificado;
        }

        return $socios;
    }

    
    public function update_profundidad()
    {
        if( ( $this->historial->modelos->{"10-NUTRICION"}->update_profundidad ?? "1979-01-01" ) < date( "Y-m-d" ) ){
            $h = $this->historial;
            $h->modelos->{"10-NUTRICION"}->update_profundidad = date( "Y-m-d" );
            $this->historial = $h;

            model( "UsuarioModel" )->save( $this );

            $db = db_connect();
            $sql = "select f_update_nivel( {$this->id}, '10-NUTRICION', ".date( "Ym" ).")";
            $db->query( $sql );
        }   
    }



    /**
     * Obtiene el saldo de un usuario en un modelo de negocio determinado.
     * Si $checasaldo es true, se actualiza el saldo en USDT en caso de que el usuario esté en morado.
     * @param string $modelo El código del modelo de negocio.
     * @param bool $checasaldo Si true, se actualiza el saldo en USDT.
     * @return int El saldo del usuario en el modelo de negocio especificado.
     */
    public function saldo( $modelo, $checasaldo = false ){
        
        // Obtenemos el saldo solo si está activo

        $cantidad = $this->data->saldo->{$modelo}->estatus == 1 ? $this->data->saldo->{$modelo}->cantidad ?? 0 : 0;

        // Para el caso de inversiones, se debe considerar además el saldo en USDT

        if( $modelo == "50-INVERSION" ){

            // Si no existe la propiedad USDT en el objeto saldo, la creamos

            if( !isset( $this->data->saldo->{"50-INVERSION"}->USDT ) ){
                $data = $this->data;
                $data->saldo->{"50-INVERSION"}->USDT = 0;
                $this->data = $data;

                model( "UsuarioModel" )->save( $this );
            }

            // En el caso de los socios en morado, actualizar comisiones ganadas y pasarlas a saldo USDT

            if( $checasaldo ){
                $ps = [];

                // si está en morado
                // buscamos comisiones y las pasamos a saldo USDT

                if( substr( $this->data->estatus->modelos->{"50-INVERSION"},0 ,1 ) <= "2" ){
                    $bolsa = 0;
                    $db    = db_connect();

                    $sql   = "SELECT c.id, c.cantidad
                            FROM t_comisiones c
                            JOIN t_periodos p 
                                ON c.fecha between p.inicia and p.termina 
                                AND p.modelo_codigo = '50-INVERSION' 
                                AND p.estatus_codigo = '422-PERIODO-PAGADO'
                            WHERE c.usuario_id = {$this->id}
                            AND c.esquema_codigo IN ('510-INVERSION')
                            AND c.estatus_codigo = '112-BOLSA'";

                    $comisiones = $db->query( $sql )->getResult();

                    if( sizeof( $comisiones ) ){
                        
                        foreach( $comisiones as $c ){
                            $bolsa += $c->cantidad;

                            $sql = "UPDATE t_comisiones
                            SET estatus_codigo = '421-APLICADO'
                            WHERE id = {$c->id}";

                            $ps[] = [
                                "comision" => $c->id,
                                "socio"    => $c->usuario_id,
                                "pedido"   => $c->pedido_id,
                                "cantidad" => $c->cantidad
                            ];

                            $db->query( $sql );
                        }
                    }

                    if( $bolsa > 0 ){
                        $data = $this->data;
                        $data->saldo->{"50-INVERSION"}->USDT += $bolsa;
                        $this->data = $data;

                        $this->data->saldo->{"50-INVERSION"}->USDT;
                        model( "UsuarioModel" )->save( $this );

                        // BITACORA marca periodo como pagado
                        bitacora( 120, $usuario->id, [
                            "saldo"  => $bolsa,
                            "origen" => $ps
                        ] );                          
                    }
                }
            }

            $cantidad += $this->data->saldo->{"50-INVERSION"}->USDT ?? 0;
        }

        return $cantidad;
    }


    /**
     * Paga un pedido y actualiza los datos del usuario.
     * 
     * @param int $pedido Id del pedido a pagar.
     * @param int $metodo Id del método de pago a usar.
     * @param float $cantidad Cantidad a depositar.
     * @param string $mes Mes en que se debe calificar el pedido (si es null se califica en el mes actual).
     * 
     * @return string Referencia del pedido pagado.
     */
    public function fondeo( $pedido, $metodo, $cantidad, $mes = null ){

        if( $mes && $mes == date( "Ym" ) ) $mes = null;

        $pedido = model( "PedidoModel" )->find( $pedido );
        $modelo = $pedido[ "modelo_codigo" ];

        if( !$pedido || $pedido[ "usuario_id"] != $this->id ){
            return 0;
        }
        
        $saldo          = $this->saldo( $modelo );
        $bultos         = ceil( $pedido[ "data" ][ "peso" ] / $pedido[ "data" ][ "pesoxbulto" ] );
        $productos      = $pedido[ "data" ][ "total" ];
        $metodopago     = model( "MetodopagoModel" )->find( $metodo );
        $subtotal       = $productos + $pedido[ "data" ][ "comisionentrega" ];
        $fecha          = /* $mes ? substr($mes,0,4)."-".substr($mes,4,2)."-01 12:00:00" : */ date( "Y-m-d H:i:s" );
        $fecha_anterior = date( "Y-m-d H:i:s", mktime( 0, 0, 0, date( "m" ), 1, date( "Y" ) ) - 7200 );
        
        $data = $this->data;
        $historial = $this->historial;

        if( $metodopago[ "settings" ][ "tipocomision" ] == "saldo" ){
            $comisionbanco = 0;
        }
        else{
            $historial->modelos->{$modelo}->fondeos[] = [
                "fecha"      => $fecha,
                "metodopago" => $metodopago[ "codigo" ],
                "cantidad"   => $cantidad
            ];

            $comisionbanco  = $metodopago[ "settings" ][ "tipocomision" ] == "porcentaje" ? ceil( ( $subtotal - $saldo ) * $metodopago[ "settings" ][ "comision" ] / 100 ) : $metodopago[ "settings" ][ "comision" ];
        }

        $total = $subtotal + $comisionbanco;

        // PAGA PEDIDO
        // si la cantidad depositada es mayor o igual que el monto a pagar 
        if( $total <= ( $saldo + $cantidad ) ){
            $pedido[ "metodopago_codigo" ] = $metodopago[ "codigo" ];
            $pedido[ "data" ][ "comisionbanco" ] = $comisionbanco;
            $pedido[ "fechas" ][ "pagado" ]   = $fecha;
            $pedido[ "fechas" ][ "reparte" ]  = $fecha;
            $pedido[ "fechas" ][ "califica" ] = intval( $pedido[ "data" ][ "mesanterior" ] ) ? $fecha_anterior : $fecha;
            $pedido[ "estatus_codigo" ] = "420-PAGADO";
    
            $historial->modelos->{$modelo}->ultimacompra = $pedido[ "fechas" ][ "califica" ];
            $pc = $this->getPrimerCompra( $modelo );

            if( !isset( $data->recompensas ) ){
                $data->recompensas = json_decode( "{}" );
            }
            $data->recompensas->inicia = $pedido[ "fechas" ][ "califica" ];

            $mesactual = substr( $pedido[ "fechas" ][ "califica" ], 0, 4 ).substr( $pedido[ "fechas" ][ "califica" ], 5, 2 );
          
            foreach( $pedido[ "PTS" ] as $promo => $pts ){
                if( !is_object( $historial->modelos->{$modelo}->primercompra ) ){
                    $historial->modelos->{$modelo}->primercompra = json_decode( '{}' );
                }

                if( !isset( $historial->modelos->{$modelo}->primercompra->{$promo} ) && $pts > 0 ){
                    $historial->modelos->{$modelo}->primercompra->{$promo} = substr( $pedido[ "fechas" ][ "califica" ], 0, 10 );
                }
            } 

            if( $saldo >= $total && $metodopago[ "settings" ][ "tipocomision" ] == "saldo" ){
                $data->saldo->{$modelo}->cantidad -= $total;
                $pedido[ "data" ][ "saldo" ] = $total;
            }
            else{
                $data->saldo->{$modelo}->cantidad = 0;
                $pedido[ "data" ][ "saldo" ] = $saldo;
            }

            model( "PedidoModel" )->save( $pedido );

            /**************************************************************/
            // todo bien
            // ENVIAR CORREO

            $usuario = $this;

            load_catalogo( "promociones",    "modelo_codigo = '{$pedido[ "modelo_codigo" ]}' OR settings->'$.universal' = true");
            load_catalogo( "metodospago",    "modelo_codigo = '{$pedido[ "modelo_codigo" ]}'");
            load_catalogo( "metodosentrega", "modelo_codigo = '{$pedido[ "modelo_codigo" ]}'");
            load_catalogo( "almacenes",      "modelo_codigo = '{$pedido[ "modelo_codigo" ]}'");

            $subject = "Pago de pedido Beneleit ".MODELOS[ $pedido[ "modelo_codigo" ] ][ "nombre" ]." No. {$pedido[ "referencia" ]}";
            $message = "
                <p>¡Hola ".$usuario->nombre()."! </p>
                <P>Hemos recibido tu pago por lo que procedemos preparar la entrega de tus productos. Este es un resumen de los paquetes y productos que incluye tu compra.¡Muchas gracias!</P>

                <div style=\"width:60%; font-size:0.8rem; overflow:hidden; border:1px solid gray; border-radius:6px; margin-bottom:15px;\">
                    <div style=\"background:#555;color:white;font-weight:bold;padding:5px 10px\">Pedido No. {$pedido[ "referencia" ]}</div>
                    <table width=\"100%\" style=\"font-size:0.8rem; border-collapse:collapse\">
                        <tr><td style=\"padding:5px 10px; border-bottom:1px solid gray;\">Fecha de pago</td><td style=\"padding:5px 10px; border-bottom:1px solid gray;\" align=\"right\">".date( "d-m-Y", strtotime( substr( $pedido[ "fechas" ][ "pagado" ], 0, 10 ) ) )."</td></tr>           
                        <tr><td style=\"padding:5px 10px;\">Califica en el mes de</td><td style=\"padding:5px 10px;\" align=\"right\">".strtoupper( mes(substr( $pedido[ "fechas" ][ "califica" ], 5, 2 ) ) )." ".substr( $pedido[ "fechas" ][ "califica" ], 0, 4 )."</td></tr>
                    </table>
                </div>
            ";

            $total_prods  = 0;
            $total_precio = 0;
            $imagenes     = [];

            foreach( PROMOCIONES as $p ){
                if( isset( $pedido[ "promociones" ][ $p[ "codigo" ] ][ "productos"] ) ){
                    $cant_productos = 0;
                    
                    foreach( $pedido[ "promociones" ][ $p[ "codigo" ] ][ "productos"] as $k ){
                        $cant_productos += $k[ "cantidad" ];
                    }

                    if( $cant_productos ){
                        $message .= "<div style=\"font-size:0.8rem; overflow:hidden; border:1px solid gray; border-radius:6px; margin-bottom:5px;\"><div style=\"background:gray;color:white;font-weight:bold;padding:5px 10px\"><table width=\"100%\" style=\"font-size:0.8rem; border-collapse:collapse;color:white\"><tr><td width=\"70%\">{$p[ "settings" ][ "nombre" ]}</td><td width=\"30%\" style=\"text-align:right; font-size:0.6rem\">{$cant_productos} producto".( $cant_productos-1 ? "s" : "")."</td></tr></table></div><table width=\"100%\" style=\"font-size:0.8rem; border-collapse:collapse\">";

                        foreach( $pedido[ "promociones" ][ $p[ "codigo" ] ][ "productos"] as $j => $k ){
                            if( !isset( $imagenes[ $j ] ) ){
                                $imagenes[ $j ] = "assets/img/productos/{$j}.png";
                            }                        

                            $message .= "<tr><td width=\"10%\" style=\"padding:5px text-align:center; font-size:1.6rem\"><img src=\"%%{$j}%%\" width=\"60\" height=\"60\" alt=\"{$j}\"></td><td width=\"50%\" style=\"padding:5px; \"><p style=\"margin:0;font-weight:bold\">{$k[ "cantidad" ]} {$k[ "nombre" ]}</p><p style=\"margin:0; font-size:0.6rem\">{$k[ "descripcion" ]}</p></td><td align=\"right\" valign=\"top\" width=\"20%\" nowrap style=\"padding:10px;\"><p style=\"margin:0; font-size:0.6rem\">P. unitario</p><p style=\"margin:0;font-weight:normal\">$".number_format( $k[ "precio" ], 2 )."</p></td><td align=\"right\" valign=\"top\" width=\"20%\" nowrap style=\"padding:10px;\"><p style=\"margin:0; font-size:0.6rem\">Subtotal</p><p style=\"margin:0; font-weight:bold\">$".number_format( $k[ "precio" ] * $k[ "cantidad" ], 2 )."</p></td></tr>";
                        }

                        $message .= "\n</table><div style=\"background:#cfcfcf;color:black;padding:5px 10px\"><table width=\"100%\" style=\"font-size:0.8rem; border-collapse:collapse\"><tr><td width=\"80%\" style=\"font-size:0.6rem;text-align:right\">{$p[ "settings" ][ "nombre" ]}</td><td style=\"text-align:right\" width=\"20%\">$".number_format( $subtotal = ( isset( $pedido[ "promociones" ][ $p[ "codigo" ] ][ "precio"] ) ? $pedido[ "promociones" ][ $p[ "codigo" ] ][ "precio"] : 0 ), 2 )."</td></tr></table></div></div>";

                        $total_precio += $subtotal;
                        $total_prods  += $cant_productos;
                    }
                }
            }        

            $message .= "\n<div style=\"font-size:0.8rem; overflow:hidden;border:1px solid gray; border-radius:6px; margin:15px 0;\"><div style=\"background:#555;font-weight:bold;\"><table width=\"100%\" style=\" font-size:0.8rem; border-collapse:collapse\">
                        <tr><td style=\"color:white;padding:5px 10px; font-size:0.6rem\" width=\"30%\">{$total_prods} producto".( $total_prods-1 ? "s" : "")."</td><td style=\"text-align:right; color:white;padding:5px 10px;\" width=\"50%\">Sub total de productos</td><td style=\"text-align:right; color:white;padding:5px 10px;\" width=\"20%\">$".number_format( $total_precio, 2 )."</td></tr>
                    </table></div></div>";

            $me = METODOSENTREGA[ $pedido[ "metodoentrega_codigo" ] ] ?? null;
            $mp = METODOSPAGO[ $pedido[ "metodopago_codigo" ] ];

            if( $me ){
                if( substr( $pedido[ "metodoentrega_codigo" ], 0, 2 ) == "00" ){
                    $entrega = ALMACENES[ $pedido[ "data" ][ "entrega" ] ][ "nombre" ];
                }
                elseif( substr( $pedido[ "metodoentrega_codigo" ], 0, 2 ) == "11" ){
                    $entrega = $pedido[ "data" ][ "entrega" ];
                }
                else{
                    // $domicilios = $socio->getDomicilios();
                    $d = $pedido[ "data" ][ "domicilio" ];

                    $message .= "\n<div style=\"width:60%; font-size:0.6rem; overflow:hidden;border:1px solid gray; border-radius:6px; margin:15px 0; padding:5px 15px\">
                    {$d[ "calleynumero" ]}<br>
                    Colonia {$d[ "colonia" ]}<br>
                    {$d[ "localidad" ]}, {$d[ "entidad" ]}<br>
                    C.P. {$d[ "codigopostal" ]}
                    </div>";

                    $entrega = $d[ "nombre" ];
                }
            }

            $message .= "\n<div style=\"font-size:0.8rem; overflow:hidden; border:1px solid gray; border-radius:6px; margin-bottom:5px;\"><table width=\"100%\" style=\"font-size:0.8rem; border-collapse:collapse\">
                ".( $me ? "<tr><td style=\"padding:5px 10px; border-bottom:1px solid gray; font-size:0.6rem\" width=\"50%\">{$me[ "nombre" ]}</td><td style=\"padding:5px 10px; border-bottom:1px solid gray\" width=\"30%\" align=\"right\">{$entrega}</td><td style=\"padding:5px 10px; border-bottom:1px solid gray\" width=\"20%\" align=\"right\">$".number_format( $pedido[ "data" ][ "comisionentrega" ], 2 )."</td></tr>" : "" )."          
                <tr><td style=\"padding:5px 10px; font-size:0.6rem\" width=\"50%\">{$mp[ "nombre" ]}</td><td style=\"padding:5px 10px;\" align=\"right\" width=\"30%\">Comisión</td><td style=\"padding:5px 10px;\" width=\"20%\" align=\"right\">$".number_format( $pedido[ "data" ][ "comisionbanco" ], 2 )."</td></tr>
                </table></div>";

            $message .= "\n<div style=\"font-size:0.8rem; overflow:hidden;border:1px solid gray; border-radius:6px; margin:15px 0;\">
                <div style=\"background:#555;font-weight:bold;\"><table width=\"100%\" style=\" font-size:0.8rem; border-collapse:collapse\"><tr><td style=\"text-align:right; color:white;padding:5px 10px;\" width=\"80%\">Total de pedido</td><td style=\"text-align:right; color:white;padding:5px 10px;\" width=\"20%\">$".number_format( $pedido[ "data" ][ "total" ] + $pedido[ "data" ][ "comisionentrega" ] + $pedido[ "data" ][ "comisionbanco" ], 2 )."</td></tr></table></div></div><p style=\"text-align:right\"><a href=\"".base_url( "pedido/".$pedido[ "referencia" ] )."\" style=\"text-decoration:none; cursor:pointer; background:#009779; text-align:center; padding:15px 0; width:100%; display:inline-block; border:1px solid #066545; color:white; border-radius:5px; width:60%;font-size:0.8rem;\" value=\"reset password\">Ver pedido en mi oficina virtual</a></p>";

            $respuesta = envia_correo( $usuario, $subject, $message, $imagenes );

            /**************************************************************/

            $this->data = $data;
            $this->historial = $historial;
            model( "UsuarioModel" )->save( $this );

            $cc = $pedido[ "fechas" ][ "califica" ];
            $mescalifica = substr( $cc, 0, 4 ).substr( $cc, 5, 2 );

            $db = db_connect();
            $db->query( "select f_update_PTS( {$this->id}, '{$modelo}', '{$mescalifica}' )" );  
            $db->query( "select f_get_estatus( {$this->id}, 0 )" );
            $afectados = $db->query( "select f_reparte_comisiones( {$pedido[ "id" ]}, 0 )" )->getRow();
        }
        else{
            $data->saldo->{$modelo}->cantidad += $cantidad;

            $this->data = $data;
            $this->historial = $historial;
            model( "UsuarioModel" )->save( $this );
        }

        return $pedido[ "referencia" ];
    }


    /**
     * Retorna la fecha de primer compra de un modelo para este usuario.
     * 
     * Si no existe el registro, lo crea y lo guarda.
     * 
     * @param string $modelo El modelo a buscar
     * @return string La fecha en formato 'Y-m-d H:i:s'
     */
    public function getPrimerCompra( $modelo )
    {

        if( !isset( $historial->modelos->{$modelo} ) ){
            $this->valida_modelo();
        }

        if( !isset( $this->historial->modelos->{$modelo}->arranque ) ){

            $db  = db_connect();
            $sql = "select f_fecha_primercompra( {$this->id}, '{$modelo}' ) as r";

            $historial = $this->historial;
            $historial->modelos->{$modelo}->arranque = $db->query( $sql )->getRow()->r;
            $this->historial = $historial;
            
            model( "UsuarioModel" )->save( $this );
        }

        return $this->historial->modelos->{$modelo}->arranque;
    }


    /**
     * Retrieves the date of the first purchase of a given product.
     * @param string $producto The code of the product.
     * @return string The date of the first purchase in the format 'Y-m-d H:i:s' or null if it does not exist.
     */
    public function getPrimerCompraProducto( $producto )
    {
        $db = db_connect();

        $sql = "SELECT MIN(fechas->>'$.pagado') as fecha 
                FROM t_pedidos 
                WHERE usuario_id = {$this->id} 
                and substring( estatus_codigo,1,3) > 400
                AND promociones like '%\"{$producto}\"%'";
        $result = $db->query($sql)->getRow();
        return $result ? $result->fecha : null;
    }


    /**
     * Retrieves the checks for a given business model for the current user.
     * If the checks for the current month do not exist in the user's data, 
     * it queries the database to obtain the checks for the specified model.
     *
     * @param string $modelo The code of the business model.
     * @return array An associative array containing the checks data.
     */

    public function getChecks( $modelo )
    {
        $a = json_decode( json_encode( $this->data->checks ?? [] ), 1);

    //    if( !isset( $a[ date("Ym") ] ) ){
            $db = db_connect();
            $sql = "SELECT f_checks_rango( {$this->id}, '{$modelo}' ) as checks;";
            $check = $db->query( $sql )->getRowArray();
            $a = json_decode( $check[ "checks" ], 1 );
    //    }
        
        return $a;
    }


    /**
     * Regresa un arreglo con las comisiones del usuario en el esquema y a o especificados,
     * agrupadas por nivel.
     * 
     * @param array  $esquema     Esquema, con su codigo y nombre
     * @param string $y           a o a obtener las comisiones
     * 
     * @return array              Arreglo con las comisiones del usuario, agrupadas por nivel
     */
    public function getBono( $esquema, $y )
    {
        $a   = [
            1 => 0.00,
            2 => 0.00,
            3 => 0.00
        ];
    
        $sql = "SELECT nivel, SUM(cantidad) AS cantidad FROM t_comisiones
                WHERE esquema_codigo = '{$esquema[ "codigo" ]}'   
                AND usuario_id = {$this->id}
                AND fecha between '{$y}-09-01' and '".($y+1)."-08-31'
                AND substring( estatus_codigo, 1, 3 ) between 200 and 400
                GROUP BY nivel";

        $db = db_connect();
        $resultado = $db->query($sql)->getResultArray();

        foreach( $resultado as $r ){
            $a[ $r[ "nivel" ] ] = floatval( $r[ "cantidad" ] );
        }

        return $a;
    }

    
    /**
     * Retrieves the payment records for the current user based on the specified model.
     *
     * This function executes a SQL query to fetch payment details such as folio,
     * status, CLABE, minor status, tax retention status, creation period, total amount,
     * and payment date for the user identified by the current instance. The results
     * are ordered by the creation period in descending order.
     *
     * @param string $modelo The model code used to filter the payments.
     * @return array An array of associative arrays containing payment details.
     */

    public function getPagos( $modelo )
    {

      $sql = "SELECT 
                    a.id AS folio, 
                    a.estatus_codigo as estatus, 
                    a.clabe as clabe, 
                    a.data->>'$.menor' as menor, 
                    a.data->>'$.retencion' as impuestos, 
                    a.data->>'$.periodos.creacion' AS periodo, 
                    a.data->>'$.cantidades.subtotal' AS total,
                    IFNULL( a.data->>'$.fechapago', IFNULL( cast( b.fecha as date ), IFNULL( p.termina, e.termina ) ) ) AS fecha
                FROM t_pagos a
                left JOIN t_periodos p ON p.codigo = a.data->>'$.periodos.deposito'
                left JOIN t_periodos e ON e.codigo = a.data->>'$.periodos.creacion'
                left join t_bitacoras b on b.variables->>'$.periodo' = a.data->>'$.periodos.deposito' and accion_id = 47
                WHERE a.usuario_id = {$this->id} 
                AND a.modelo_codigo = '{$modelo}' 
                AND SUBSTRING( a.estatus_codigo,1,3) > 200
                -- and p.inicia > '2024-08-18'
                ORDER BY a.data->>'$.periodos.creacion' desc";

        $db = db_connect();
        return $db->query($sql)->getResultArray();
    }


    /**
     * Regresa un arreglo con las comisiones del usuario por nivel, de la
     * promocion de 50, para el mes especificado.
     * 
     * @param string $mes       Codigo del mes que se quiere obtener las comisiones
     * 
     * @return array            Arreglo con las comisiones del usuario por nivel
     */
    public function getBonoPromos( $mes = null )
    {
        if( !$mes ){
            $mes = date( "Ym" );
        }

        $a   = [
            1 => 0,
            2 => 0,
            3 => 0
        ];

        $inicia  = substr( $mes, 0, 4 )."-".substr( $mes, 4, 2 )."-01";
        $termina = date( "Y-m-d", strtotime( $inicia." + 1 month - 1 day") );

        $sql = "SELECT nivel, SUM(cantidad) AS cantidad FROM t_comisiones
                WHERE esquema_codigo = '118-PROMOS-50'
                and fecha between '{$inicia}' and '{$termina}'
                AND usuario_id = {$this->id}
                GROUP BY nivel";

        $db = db_connect();
        $resultado = $db->query($sql)->getResultArray();

        foreach( $resultado as $r ){
            $a[ $r[ "nivel" ] ] = floatval( $r[ "cantidad" ] );
        }

        return $a;
    }    


    /**
     * Regresa un arreglo con las comisiones del usuario por dia, dentro del modelo y esquemas especificados.
     * 
     * @param string $modelo       Codigo del modelo que se quiere obtener las comisiones
     * @param array  $esquemas     Codigo de los esquemas que se quiere obtener las comisiones
     * 
     * @return array               Arreglo con las comisiones del usuario, agrupadas por dia
     */
    public function getIngresosPorDia( $modelo, $esquemas )
    {
        $resultado = [];

        $sql = "SELECT 
                    SUM(comision.cantidad) as comisiones, 
                    DATE_FORMAT(comision.fecha, '%Y-%m-%d') as dia 
                FROM t_comisiones comision
                JOIN t_pedidos pedido ON pedido.id = comision.pedido_id
                join t_esquemas esquema on esquema.codigo = comision.esquema_codigo
                WHERE esquema.modelo_codigo = '{$modelo}' 
                    and comision.usuario_id = {$this->id} 
                    AND esquema.settings->>'$.reparto' != 'puntos'
                    AND esquema.settings->>'$.periodo' in ( 'SEMANAL', 'MENSUAL', 'ANUAL' )
                    and substring( comision.estatus_codigo, 1, 3 ) > 200
                    and substring( pedido.estatus_codigo, 1, 3 ) > 400
                    and comision.fecha >= '2024-08-12'
                    ".( sizeof( $esquemas ) ? "and comision.esquema_codigo in ( '".implode( "', '", $esquemas )."' )" : "" )."
                GROUP BY DATE_FORMAT(comision.fecha, '%Y-%m-%d')";

        $db     = db_connect();
        $result = $db->query($sql);

        foreach($result->getResult() as $d){
            $resultado[ $d->dia] = $d->comisiones;
        }

        if(!sizeof($resultado)) $resultado = [0];
        return $resultado;
    }


    /**
     * Retrieves a list of active direct users for the specified model.
     * 
     * This function queries the database to get the IDs of users who are directly
     * linked to the current user (`$this->id`) as their 'padre' within the given model.
     * It only includes users whose status code for the model is greater than 400, indicating
     * they are active.
     * 
     * @param string $modelo The model code representing the business model to check.
     * 
     * @return array An array of associative arrays, each containing the 'id' of an active direct user.
     */

    public function getDirectosActivos( $modelo )
    {
        /* $sql = "SELECT id 
                FROM t_usuarios 
                WHERE redes->>'$.modelos.\"{$modelo}\".padre' = {$this->id} 
                AND SUBSTRING( f_get_calificacion(id, '".date( "Ym" )."', '{$modelo}'), 4 ) != '--'";
             */
        $sql = "SELECT id 
                FROM t_usuarios 
                WHERE redes->>'$.modelos.\"{$modelo}\".padre' = {$this->id} 
                AND SUBSTRING( json_unquote( json_extract( data, concat( '$.estatus.modelos.\"', '{$modelo}','\"') ) ), 1 ,3 ) > 400";

        $db  = db_connect();
        return $db->query($sql)->getResultArray();
    }


    /**
     * Regresa un arreglo con las comisiones del usuario, agrupadas por esquema, 
     * con el total de comisiones por esquema, y el total de comisiones en el 
     * periodo especificado.
     * 
     * @param string $periodo       Codigo del periodo que se quiere obtener las comisiones
     * @param string $esquema       Codigo del esquema que se quiere obtener las comisiones
     * @param string $estatus       Codigo del estatus de la comision que se quiere obtener
     * 
     * @return array                Arreglo con las comisiones del usuario, agrupadas por esquema
     */
    public function getComisiones( $periodo = null, $esquema = null, $estatus = null )
    {
        $resultado = [];

        if( $periodo ){
            $sql = "SELECT comision.fecha, comision.compresion, pedido.promociones, comision.pedido_id, comision.esquema_codigo, comision.nivel, comision.cantidad, pedido.usuario_id, pedido.referencia
                    FROM t_comisiones comision
                    join t_esquemas esquema on esquema.codigo = comision.esquema_codigo
                    join t_periodos periodo on periodo.codigo = '{$periodo}'
                    join t_pedidos pedido on pedido.id = comision.pedido_id and substring( pedido.estatus_codigo, 1, 3 ) > 400
                    JOIN t_modelos modelo ON modelo.codigo = periodo.modelo_codigo
                    WHERE comision.usuario_id = {$this->id} 
                    and substring( comision.estatus_codigo, 1, 3 ) > 200
                    and ".( is_array( $esquema ) ? "esquema.codigo in ( '".implode( "', '", $esquema )."' )" : "esquema.settings->>'$.periodo' = modelo.settings->>'$.periodo'" )."
                    AND comision.fecha between periodo.inicia and periodo.termina
                    AND ".substr( $periodo, 0, 2 )." = substring( esquema.modelo_codigo, 1, 2 );";
        }
        else{

            if( is_array( $esquema ) ){

            }
            else{
                $sql = "SELECT comision.fecha, comision.compresion, comision.pedido_id, comision.esquema_codigo, comision.nivel, comision.cantidad, pedido.usuario_id, pedido.referencia
                        FROM t_comisiones comision
                        join t_pedidos pedido on pedido.id = comision.pedido_id and substring( pedido.estatus_codigo, 1, 3 ) > 400
                        WHERE comision.usuario_id = {$this->id} 
                        and substring( comision.estatus_codigo, 1, 3 ) > 200
                        and comision.esquema_codigo = '{$esquema}'
                        ".( $estatus ? "AND comision.estatus_codigo = '{$estatus}'" : "" );
            }

        }

        $db = db_connect();
        return $db->query($sql)->getResult(); 
    }



    public function password_personalizado()
    {
        $db  = db_connect();

        $sql = "SELECT variables->>'$.nuevo' as password
                from t_bitacoras
                where usuario_id = $this->id
                and accion_id = 37
                order by fecha desc
                limit 1";      

        $password = $db->query( $sql )->getRow();

        if( $password ){
            return $password->password != $this->password;
        }

        return false;
    }

    /**
     * Devuelve el rango de inversiones correspondiente al n mero de directos que se le pasa como par metro.
     * Si el rango cambia, se guarda el nuevo valor.
     * @param int $directos N mero de directos
     * @return array El rango actualizado
     */
    public function getRangoInversion( $directos )
    {

                
        if( in_array( $this->id, [ 164925, 164924, 164923, 164914] ) ){
            $directos = 12;
        }
        // Identificar rango corresponidente

        foreach( RANGOS as $r ){
            if( $r[ "modelo_codigo" ] == "50-INVERSION" ){
                if( $directos >= $r[ "cantidades" ][ "directos" ][ 0 ] && $directos <= $r[ "cantidades" ][ "directos" ][ 1 ] ){
                    $rango = $r[ "codigo" ];
                }
            }
        }

        // Si hay cambios, guardar dato

        if( !isset( $this->data->rango_inversion ) || $this->data->rango_inversion != $rango ){
            $data = $this->data;
            $data->rango_inversion = $rango;
            $this->data = $data;
            model( "UsuarioModel" )->save( $this );
        }

        return RANGOS[ $this->data->rango_inversion ];
    }


    public function get_pendientes( $modelo )
    {
        $db  = db_connect();
        $sql = "SELECT 
                c.cantidad,
                c.estatus_codigo,
                c.fecha as fechapago,
                p.fechas->>'$.pagado' as fechacompra
                from t_comisiones c
                join t_pedidos p on p.id = c.pedido_id
                where c.usuario_id = {$this->id} 
                and c.estatus_codigo in ( '255-PENDIENTE', '112-BOLSA' )
                and substring( c.esquema_codigo, 1, 1 ) = '".substr( $modelo, 0, 1 )."'
                and c.periodo_codigo is null
                and c.fecha >= DATE_ADD( cast( now() as date ), INTERVAL ( 9 - DAYOFWEEK( cast( now() as date ) ) ) DAY )";        

        return $db->query( $sql )->getResult(); 
    }


    /**
     * Actualiza las verificaciones de un usuario
     * 
     * Actualiza las verificaciones de un usuario en base a sus datos personales y de negocio
     * 
     * @return void
     */
    public function update_verificacion()
    {
        // obtenemos datos iniciales

        $estatuses = [];
        foreach( MODELOS as $k => $m ){
            $estatuses[ $m[ "codigo" ] ] = $this->get_verificacion( $m[ "codigo" ] );
        }

        $db  = db_connect(); 

        $data = $this->data;

        if( !isset( $data->verificaciones )){
            $data->verificaciones = new \stdClass();
        }

        // verificación FECHANAC
        $data->verificaciones->{"FECHANAC"} = fecha_valida( $this->fechanac );
        
        // verificación PASSWORD  
        $data->verificaciones->{"PASSWORD"} = !str_contains( $this->password, '*' ) || strlen( $this->password ) > 5; 
        // hay que comparar con el temporal, no con el nuevo, porque al cambiar por mas de una vez el password, se pierde la asociación
            
        // verificación BENEFICIARIO        
        $data->verificaciones->{"BENEFICIARIO"} = $this->porcentaje_beneficiarios() == 100;
  
        // verificación DOMICILIO        
        $data->verificaciones->{"DOMICILIO"} = $data->domicilio > 0;

        // verificación DNI  
        $data->verificaciones->{"DNI"} = !$this->es_menor() && $data->credencial->estatus == 2 && $data->credencial->frente != null && $data->credencial->reverso != null;
            
        // verificación ACTA       
        $data->verificaciones->{"ACTA"} = $this->es_menor() && $data->credencial->estatus == 2 && $data->credencial->acta != null;

        // verificación 
        $data->verificaciones->{"FOTO"} = isset( $data->avatar->updated ) && $data->avatar->updated > 0;

        // verificación CSF        
        $data->verificaciones->{"CSF"} = $data->sat->csf != null;

        // verificación CLABE
        $data->verificaciones->{"CLABE"} = strlen( $data->clabe ) == 18;

        // verificación WALLET        
        $data->verificaciones->{"WALLET"} = strlen( $data->wallet ?? "" ) == 34;

        // verificación RFC        
        $data->verificaciones->{"RFC"} = strlen( $data->sat->rfc ) == 13;        

        // verificación TARJETA        
        $data->verificaciones->{"TARJETA"} = strlen( $data->tarjeta->numero ?? "" ) == 19;        

        // verificación CELULAR     
        $data->verificaciones->{"CELULAR"} = ( $this->data->ubicacion->code ?? "" ) != "MX" ? strlen( $this->telefono ) > 7 : strlen( $this->telefono ) == 10;

        // verificación EMAIL      
        $data->verificaciones->{"EMAIL"} = filter_var( $this->correo, FILTER_VALIDATE_EMAIL ) != false;
        
        $this->data = $data;
        model( "UsuarioModel" )->save( $this );
        
        $tempo = model( "UsuarioModel" )->find( $this->id );


        foreach( MODELOS as $k => $m ){

            $estatus = $tempo->get_verificacion( $m[ "codigo" ] );
            if( $estatuses[ $m[ "codigo" ] ]->estatus != $estatus->estatus ){

                // BITACORA cambio en estatus de verificación
                bitacora( $estatus->estatus == 1 ? 111 : 112, $this->id, [
                    "modelo" => $m[ "codigo" ]
                ] );
            }
        }
    }


/**
 * Devuelve una bandera segun el pais de origen del usuario
 * Si el pais de origen es un codigo de dos caracteres, devuelve una bandera
 * con el icono de la bandera correspondiente. De lo contrario, devuelve
 * una cadena vacia.
 * @return string
 */
    public function bandera(){
        $code =  $this->data->ubicacion->origen ?? "";

        if( strlen( $code ) == 2 ){
            return "<span class=\"iconify rounded-1\" data-width=\"24\" data-icon=\"flag:".strtolower( $this->data->ubicacion->origen )."-4x3\"></span>";
        }

        return "";
    }



    public function get_verificacion( $modelo )
    {
        $db  = db_connect();
        $sql = "select f_get_verificacion( {$this->id}, '{$modelo}' ) as datos";        
        return json_decode( $db->query( $sql )->getRow()->datos );
    }




    public function fecha_arranque( $modelo )
    {
        $db  = db_connect();
        $sql = "SELECT 
			    floor( sum( JSON_EXTRACT( p.PTS, CONCAT( '$.\"', promo,'\"') ) ) ) as ptss, 
                date_format( p.fechas->>'$.califica' , '%Y-%m-%d' ) as f

                from t_pedidos p
                join t_modelos m on m.codigo = p.modelo_codigo,
                JSON_TABLE( m.settings->>'$.promocion_base', '$[*]' COLUMNS (
                    promo VARCHAR(40)  PATH '$'
                ) ) promos
                
                where p.modelo_codigo = '{$modelo}'
                and p.usuario_id = {$this->id}   
                AND CAST( substring( p.estatus_codigo, 1, 3 ) AS UNSIGNED ) > 400
                
                group by date_format( p.fechas->>'$.califica' , '%Y-%m-%d' )
                having ptss >= 1
                ORDER BY f desc";

        $puntos  = $db->query( $sql )->getResult();
        $meses = [];

        foreach( $puntos as $p ){
            $mes = date( "Ym", strtotime( $p->f ) );

            if( !isset( $meses[ $mes ] ) ){
                $meses[ $mes ] = [ 
                    "pts" => 0,
                    "fecha" => $p->f
                ];
            }
            
            $meses[ $mes ][ "pts" ] += $p->ptss;
        }

        $mes   = date( "Ym" );
        $fecha = null;
        $a     = 0;
        
        do{
            $next = 0;
            
            if( isset( $meses[ $mes ] ) ){
                if( $meses[ $mes ][ "pts" ] > 0 ){
                    $fecha = $meses[ $mes ][ "fecha" ];
                    $next = 1;
                }
            }

            $mes = date( "Ym", strtotime( substr( $mes, 0, 4 )."-".substr( $mes, 4, 2 )."-01 - 1 month" ) );

        } while( $next == 1 && $mes > 202201 );

        if( !$fecha ){
            $fecha = $this->getPrimerCompra( $modelo );
        }

        return $fecha;
    }



    public function fecha_arranque_hijos( $modelo )
    {
        $db  = db_connect();
        $sql = "SELECT
                    historial->>'$.modelos.\"{$modelo}\".reset' as fecha
                from t_usuarios
                where
                    redes->>'$.modelos.\"{$modelo}\".padre' = {$this->id}
                and estatus_codigo = '201-ACTIVO'
                and SUBSTRING( data->>'$.estatus.modelos.\"{$modelo}\"', 1, 3 ) > 200
                order by fecha asc
                limit 1";

        return $db->query( $sql )->getRow()->fecha ?? null;
    }    


        /**
     * Calculates the leadership bonus for a user based on their direct partners and the seed values.
     *
     * This function iterates over a list of partners, checking if they meet certain criteria
     * related to their level, seed, status, and activation date. It calculates the number of 
     * direct partners and the total seed value. Based on the number of direct partners, a 
     * leadership bonus multiplier is determined. It then checks if a commission record already 
     * exists for the user for the given month, and if not, it inserts a new commission record 
     * with the calculated bonus. The user's investment history is updated with the details for 
     * the month.
     *
     * @param object $usuario The user object containing user details and history.
     * @param array $ps A list of partner objects to evaluate.
     * @param string $mes The month for which the bonus is being calculated, in the format 'YYYY-MM'.
     */
    public function revisa_bono_liderazgo( $ps, $mes = null )
    {
        $directos = 0;
        $bolsa    = 0;
        
        if( !$mes){
            $mes = date( "Y-m-d" );
        }

        foreach( $ps as $socio ){
            
            if( 
                $socio->nivel > 0 &&
                $socio->semilla > 0 && 
                substr( $socio->estatus, 0, 3 ) > 300 && 
                $mes > $socio->activacion

            ){
                if( $socio->nivel == 1 ){
                    $directos++;
                }

                $bolsa += $socio->semilla;    
            }
        }

        if( in_array( $this->id, [ 164925, 164924, 164923, 164914] ) ){
            $directos = 12;
        }

        if( $directos >= 12 ){
            $bono = 1;
        }
        elseif( $directos >= 8 ){
            $bono = 0.66;
        }
        elseif( $directos >= 4 ){
            $bono = 0.33;
        }
        else{
            $bono = 0;
        }

        $db  = db_connect();
        $sql = "select count(*) as cuenta from t_comisiones where usuario_id = {$this->id} and esquema_codigo = '530-LIDERAZGO' and fecha = '{$mes}' && estatus_codigo = '255-PENDIENTE'";

        $existe = $db->query( $sql )->getRow()->cuenta;
        
      //  if(/*  $existe == 0 &&  */date( "Ym", strtotime($mes) ) >= date( "Ym", strtotime( date( "Y-m-01" )." - 1 month" ) ) ){
            $historial = $this->historial;

            if( !isset( $historial->modelos->{"50-INVERSION"}->corte_mensual ) ){
                $historial->modelos->{"50-INVERSION"}->corte_mensual = new \stdClass();

                $this->historial = $historial; 
                model( "UsuarioModel" )->save( $this );
            }

            if( $directos && $bolsa > 0 ){

                // si existe comision con dato equivocado, la broramos
                if( $existe ){
                    
                    $sql   = "UPDATE t_comisiones set estatus_codigo = '110-ELIMINADO' where usuario_id = {$this->id} and esquema_codigo = '530-LIDERAZGO' and fecha = '{$mes}' && estatus_codigo = '255-PENDIENTE'";

                    $db->query( $sql );
                }

                $total = floor( $bolsa * $bono / 100 * 100 ) / 100;
                $sql   = "INSERT INTO t_comisiones VALUES ( NULL, '255-PENDIENTE', NULL, {$this->id}, '530-LIDERAZGO', 0, 0, $total, '{$mes}', NULL)";

                $db->query( $sql );

                $historial->modelos->{"50-INVERSION"}->corte_mensual->{date( "Ym", strtotime($mes) )} = [
                    "directos" => $directos,
                    "bolsa"    => $bolsa,
                    "bono"     => $bono
                ];

                $this->historial = $historial; 
                model( "UsuarioModel" )->save( $this );

                // BITACORA registro de bono automatico
                bitacora( 113, $this->id, [
                    "mes" => mes( date( "m", strtotime( $mes ) ) ),
                    "fecha" => $mes,
                    "registro" => [
                        "directos" => $directos,
                        "bolsa"    => $bolsa,
                        "bono"     => $bono
                    ]
                ] );     
            }
        //}
    }
}

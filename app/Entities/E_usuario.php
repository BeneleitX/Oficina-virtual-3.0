<?php
namespace App\Entities;

use CodeIgniter\Entity\Entity;
use CodeIgniter\I18n\Time;

// Entidad USUARIO

class E_usuario extends Entity
{
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


    public function getDatos(){
        return [
            "id" => $this->id,
            "estatus_codigo" => $this->estatus_codigo,
            "rol_codigos" => $this->rol_codigos,
            "data" => $this->data,
            "correo" => $this->correo,
            "telefono" => $this->telefono,
            "fechanac" => $this->fechanac,
            "curp" => $this->curp,
            "redes" => $this->redes,
            "historial" => $this->historial,
            "verificado" => $this->verificado,
            "PTS" => $this->PTS
        ];
    }


    protected function setPassword( string $password ): string
    {
        $encrypter = service( "encrypter" );
        return $this->attributes[ "password" ] = base64_encode( $encrypter->encrypt( $password, [ "key" => $this->attributes[ "id" ] ] ) );
    }


    public function valida_modelo(){
        $historial = $this->historial;
        $data      = $this->data;
        $redes     = $this->redes;

        $update = 0;

        foreach( MODELOS as $m ){

            if( $m[ "settings" ][ "efectivo" ] ){

                if( 
                    !isset( $this->historial->modelos->{$m[ "codigo" ]} ) ||
                    !isset( $this->redes->modelos->{$m[ "codigo" ]} ) ||
                    !isset( $this->data->estatus->modelos->{$m[ "codigo" ]} ) 
                ){

                    $historial->modelos->{$m[ "codigo" ]} = [
                        "primercompra"   => json_decode( "{}" ),
                        "ultimacompra"   => null,
                        "fondeos" => [],
                        "reset" => $historial->reset,
                        "ingresos" => [
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
                        "padre" => $redes->patrocinador,
                        "patrocinador" => $redes->patrocinador,
                        "hijos" => [],
                        "rango" =>  $m[ "settings" ][ "rango_base" ] ?? null,
                        "profundidad" => [
                            "activos" => [0,0,0],
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
                $sql = " select empleado from t_tarjetas where tarjeta = ".substr($data->tarjeta->numero, 11, 3).substr($data->tarjeta->numero, 15, 4)." ";
                $data->tarjeta->cliente = $db->query( $sql )->getRow()->empleado ?? "número no encontrado";

                $update = 1;
            }
        }
        
        $this->historial = $historial;
        $this->data = $data;
        $this->redes = $redes;
      
        // Actualización de datos de socio al agregar un nuevo modelo de negocio

        if($update){
            model( "UsuarioModel" )->save( $this );
        }
    }

    
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
        $this->data = $data;

        $historial = $this->historial;
        $historial->reset = date( "Y-m-d H:i:s" );
        $this->historial = $historial;
    }

    public function patrocinador( $modelo ){
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

    public function getPassword(): string 
    {
        $encrypter = service( "encrypter" );
        $cadena = base64_decode( $this->attributes[ "password" ] );
        return $encrypter->decrypt( $cadena, [ "key" => $this->id ] );
    }


    public function updateCalificaciones( $modelo ){
        // transferido a stored function en MySQL
    }


    public function getCalificaciones( $modelo, $m = false ){
        $PTS = [];

        if( !defined( "PROMOCIONES" ) ) {
            load_catalogo( "promociones", "modelo_codigo = '{$modelo}' OR settings->'$.universal' = true");
        }

        foreach( PROMOCIONES as $promo ){

            if( !isset( $this->historial->modelos->{$modelo} ) ){
                $this->valida_modelo();
            }

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
    

    protected function setCurp( string $curp ){
        $this->attributes[ "curp" ]     = strtoupper( $curp );
        $yn = substr( $curp, 4, 2) ;
        $this->attributes[ "fechanac" ] = implode("-", [ ( intval( $yn ) >= date("y") ? "19" : "20").$yn, substr( $curp, 6, 2), substr( $curp, 8, 2) ] );

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

        $data = json_decode( $this->attributes[ "data" ] );
        $data->genero =  substr( $curp, 10, 1) == "H" ? "MASCULINO" : "FEMENINO";
        $data->nacionalidad = substr( $this->attributes[ "curp" ], 11, 2) != "NE" ? "MEXICANA" : "EXTRANJERA";
        $data->avatar->face = $caras[ rand( 0, sizeof( $caras   ) - 1 ) ];
        $data->avatar->bg = $colores[ rand( 0, sizeof( $colores ) - 1 ) ];
        $data->beneficiarios = [];
        $this->attributes[ "data" ] = json_encode( $data );
    }


    public function getPremieres( $mes = null ){
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
            WHERE 
            redes->>'$.modelos.\"10-NUTRICION\".padre' = {$this->id}
            HAVING biex >= 6 AND plus >= 3 and primercompra = '{$mes}'";  
            
        $db  = db_connect();
        return $db->query($sql)->getResultArray();
    }


    public function id( $modelo = null, $clase = null, $verificado = true ): string 
    {
        if( $modelo ){

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
                    $calificacion = CALIFICACIONES[ $calificaciones[ $m_1 ] ][ "descripcion" ]." - ".CALIFICACIONES[ $calificaciones[ $m_0 ] ][ "descripcion" ];                    
                    break;
                case "20-TELEFONIA" : 
                    $calificacion = CALIFICACIONES[ $calificaciones[ $m_0 ] ][ "descripcion" ];                    
                    break;
                case "30-ALIMENTOS" : 
                    $calificacion = CALIFICACIONES[ $calificaciones[ $m_0 ] ][ "descripcion" ];
                    break;
                case "40-GASOLINAS" : 
                    $calificacion = CALIFICACIONES[ $calificaciones[ $m_0 ] ][ "descripcion" ];
                    break;
                case "50-INVERSION" : 
                    $calificacion = CALIFICACIONES[ $calificaciones[ $m_0 ] ][ "descripcion" ];
                    break;
                                    }

            return "<span data-bs-toggle=\"tooltip\" data-bs-html=\"true\" title=\"<p class='mt-3'>".$this->avatar(150, false, true)."</p><p class='m-0'>BENELEIT {$modelo[ "nombre" ]}</p><h3><span class='col-12 w-100 badge bg-{$modelo[ "settings" ][ "color" ]}'><i class='fa fa-{$modelo[ "settings" ][ "icono" ]}'></i> ".id( $this->id, 6 )."</span></h3><p class='m-0'>".$this->nombre( 2 )."</p><span class='badge w-100 bg-".( $this->verificado->estatus ? "teal" : "red" )."'>Socio ".( $this->verificado->estatus ? "" : "no" )." verificado</span><span class='badge w-100 bg-".$estatus[ "color" ]."'>{$estatus[ "descripcion" ]}</span><div class='py-1'>{$calificacion}</div>\" class=\"badge bg-".$estatus[ "color" ]."\">".( $modelo ? "<i class=\"fa fa-".$modelo[ "settings" ][ "icono" ]."\"></i> " : "" ).id( $this->id, 6 )."</span>".( $verificado ? " <span class=\"small\">".$this->verified()."</span>" : "" );
        }
        elseif( $clase ){
            return "<span style=\"position:relative\" class=\"badge bg-{$clase}\" ".( $verificado ? "data-bs-custom-class=\"tooltip-".( $this->verificado->estatus ? "teal" : "red" )."\" data-bs-toggle=\"tooltip\" title=\"Socio ".( $this->verificado->estatus ? "" : "no" )." verificado\"" : "" ).">".id( $this->id, 6 ).( $verificado ? " <span class=\"small\">".$this->verified()."</span>" : "" )."</span>";
        }

        return id( $this->id, 6 );
    }


    public function get_inversiones(){
        $where = "t_pedidos.usuario_id = {$this->id} and substring( t_pedidos.estatus_codigo, 1, 3 ) > 400";
        return model( "InversionModel" )->select("t_inversiones.*" )->join('t_pedidos', 't_pedidos.id = t_inversiones.pedido_id')->where( $where )->findAll();
    }


    public function verified(){
        return "<i class=\"far fa-circle-".( $this->verificado->estatus ? "check text-teal" : "xmark text-red" )."\"></i>";
    }


    public function nombre( $apellidos = 0, $mask = false, $text = false ): string
    {
        $nombre = ( $text ? "" : "<strong>" ).(  $mask ? mask( $this->data->nombre ) : $this->data->nombre ).( $text ? "" : "</strong>" )." ".( $mask ? mask( implode( " ", $this->data->apellidos ) ) : implode( " ", $this->data->apellidos ) );
        
        return $nombre;
    }

 
    public function rango( int $size = 40 ): string 
    {
        return "<img style=\"width:{$size}px; height:{$size}px;\" src=\"".base_url()."assets/img/rangos/{$this->data->rango}.png\">";
    }


    public function avatar( int $size = 40, string $id = null, $commmas = null ): string 
    {
        if( $this->data->avatar->activo !== null ){
            return "<img ".($id ?? "")." class='rounded-circle' style='width:{$size}px; height: {$size}px;' src='".base_url()."data/{$this->id}/avatar/{$this->data->avatar->imagenes[ $this->data->avatar->activo ]}'>";
        }

        return "<div class='emoji'><div style='border-radius:50%; width:{$size}px;height:{$size}px;font-size:".($size/2)."px;line-height:".( $size / 2 )."px; padding-top:20%' class='text-teal bg-gray-400'>".$this->iniciales()."</div></div>";
    }


    public function permiso( $rol, $forzado = false ){
        return in_array( $rol, $this->rol_codigos ) OR ( !$forzado && in_array( "50-ROOT", $this->rol_codigos ) ); 
    }


    public function es_admin(){
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

    public function iniciales(){
        return substr( $this->data->nombre, 0, 1 ).substr( $this->data->apellidos[0], 0, 1 );
    }


    public function porcentaje_beneficiarios( $porcentaje = 0 ){
        foreach( $this->data->beneficiarios as $b ){
            $porcentaje += $b->porcentaje;
        }

        return $porcentaje;
    }


    public function banco( $url = false ){
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


    public function pedidopendiente( $modelo ){
        $db  = db_connect();
        $p = $db->query( "select id from t_pedidos where usuario_id = {$this->id} and modelo_codigo = '{$modelo}' and substring( estatus_codigo, 1, 3 ) between 300 and 400 " )->getRow();

        if( $p ){
            return $p->id;
        }

        return null;
    }


    public function getDownlineJSON( $modelo, $niveles = null ){

        $db  = db_connect();
        $sql = "select f_get_downline( {$this->id}, '{$modelo}', ".($niveles ?? MODELOS[ $modelo ][ "settings" ][ "niveles" ] )." ) as downline";
        $r = $db->query( $sql )->getRow();

        return $r->downline;
    }


    public function getUplineJSON( $modelo ){

        $db  = db_connect();
        $sql = "select f_get_upline( {$this->id}, '{$modelo}', 1, '".date( "Y-m-d" )."' ) as upline";
        $r = $db->query( $sql )->getRow();

        return $r->upline;
    }


    public function getBitacora(){
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
                ORDER BY fecha desc";

        $movimientos = $db->query( $sql );

        foreach( $movimientos->getResult() as $m ){
            $respuesta[] = $m;
            $m->variables = json_decode( $m->variables ); 

            foreach($m->variables as $k => $v){
                if(!is_object($v))
                $m->string = str_replace( "#{$k}#", $v, $m->string );
            }
        }

        return $respuesta;
    }


    public function es_menor(){
        $fecha = new Time( $this->fechanac );
        return $fecha->getAge() < 18;
    }


    public function getRecompensas( $activa = false ){

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


    public function password_original(){
        
      
        if( !$this->attributes[ "password"] ){
            $this->password = random_password();
            model( "UsuarioModel" )->save( $this );
        }

        return $this->attributes[ "password"];
    }

    
    public Function getEstrellas( $r = null ){
        
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


    public function recompensas_alcanzadas(){
        $db = db_connect();
        $re = $db->query( "select recompensa_codigo from t_redenciones where substring( estatus_codigo, 1, 3 ) > 200 and usuario_id = '{$this->id}'" );
        $resultado = [];

        foreach( $re->getResult() as $r ){
            $resultado[] = $r->recompensa_codigo;
        }

        return $resultado;
    }


    public function recompensas_recibidas(){
        $db = db_connect();
        $re = $db->query( "select recompensa_codigo from t_redenciones where estatus_codigo = '623-ENTREGA' and usuario_id = '{$this->id}'" );
        $resultado = [];

        foreach( $re->getResult() as $r ){
            $resultado[] = $r->recompensa_codigo;
        }

        return $resultado;
    }


    public function redime_recompensa( $r ){
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


    public function getCelulares(){
        $db  = db_connect();
        $sql = "SELECT * from t_celulares WHERE usuario_id = {$this->id} and substr( estatus_codigo, 1, 3) > 200";
        return $db->query( $sql )->getResultArray();
    }


    public function getDomicilios( $con_colonia = false, $todos = false ){
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

    
    public function saldo( $modelo, $checasaldo = false ){
        
        // Obtenemos el saldo solo si está activo

        $cantidad = $this->data->saldo->{$modelo}->estatus == 1 ? $this->data->saldo->{$modelo}->cantidad ?? 0 : 0;

        // Para el caso de inversiones, se debe considerar además el saldo en USDT

        if( $modelo == "50-INVERSION" ){

            // Si no existe la propiedad USDT en el objeto saldo, la creamos

            if( !isset( $this->data->saldo->{$modelo}->USDT ) ){
                $data = $this->data;
                $data->saldo->{$modelo}->USDT = 0;
                $this->data = $data;

                model( "UsuarioModel" )->save( $this );
            }

            // En el caso de los socios en morado, actualizar comisiones ganadas y pasarlas a saldo USDT

            if( $checasaldo ){

                // si está en morado
                // buscamos comisiones y las pasamos a saldo USDT

                if( substr( $this->data->estatus->modelos->{$modelo},0 ,1 ) == "2" ){
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

                            $db->query( $sql );
                        }                  
                    }

                    if( $bolsa ){
                        $data = $this->data;
                        $data->saldo->{$modelo}->USDT += $bolsa;
                        $this->data = $data;

                        model( "UsuarioModel" )->save( $this );
                    }
                }
            }

            $cantidad += $this->data->saldo->{$modelo}->USDT ?? 0;
        }

        return $cantidad;
    }


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


    public function getPrimerCompra( $modelo ){

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


    public function getPrimerCompraProducto( $producto ){
        $db = db_connect();

        $sql = "SELECT MIN(fechas->>'$.pagado') as fecha 
                FROM t_pedidos 
                WHERE usuario_id = {$this->id} 
                and substring( estatus_codigo,1,3) > 400
                AND promociones like '%\"{$producto}\"%'";
        $result = $db->query($sql)->getRow();
        return $result ? $result->fecha : null;
    }

    

    public function getChecks( $modelo ){
        $a = json_decode( json_encode( $this->data->checks ?? [] ), 1);

        if( !isset( $a[ date("Ym") ] ) ){
            $db = db_connect();
            $sql = "SELECT f_checks_rango( {$this->id}, '{$modelo}' ) as checks;";
            $check = $db->query( $sql )->getRowArray();
            $a = json_decode( $check[ "checks" ], 1 );
        }
        
        return $a;
    }


    public function getBono( $esquema, $y ){
        $a   = [
            1 => 0.00,
            2 => 0.00,
            3 => 0.00
        ];
        $sql = "SELECT nivel, SUM(cantidad) AS cantidad FROM t_comisiones
                WHERE esquema_codigo = '{$esquema[ "codigo" ]}'   
                AND usuario_id = {$this->id}
                AND fecha between '{$y}-09-01' and '".($y+1)."-08-31'
                GROUP BY nivel";

        $db = db_connect();
        $resultado = $db->query($sql)->getResultArray();

        foreach( $resultado as $r ){
            $a[ $r[ "nivel" ] ] = floatval( $r[ "cantidad" ] );
        }

        return $a;
    }

    
    public function getPagos( $modelo ){

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
                -- and p.inicia > '2024-08-18'
                ORDER BY a.data->>'$.periodos.creacion' desc";

        $db = db_connect();
        return $db->query($sql)->getResultArray();
    }


    public function getBonoPromos( $mes = null ){
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


    public function getIngresosPorDia( $modelo, $esquemas ){
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


    public function getDirectosActivos( $modelo ){
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


    public function getComisiones( $periodo = null, $esquema = null, $estatus = null ){
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


    public function getRangoInversion( $directos ){

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
}

<?php
namespace App\Entities;

use CodeIgniter\Entity\Entity;
use CodeIgniter\I18n\Time;



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

    protected $dates = [ "created_at", "updated_at" ];  

    function __construct( $id = null, $data = null ){
        if( $id ){
            $this->id = $id;
        }
        if( $data ){
            $this->data = json_decode( $data );
        }
    }

    protected function setPassword( string $password ): string
    {
        $encrypter = service( "encrypter" );
        return $this->attributes[ "password" ] = base64_encode( $encrypter->encrypt( $password, [ "key" => $this->attributes[ "curp" ] ] ) );
    }


    public function resetPassword()
    {
        $encrypter = service( "encrypter" );
        $password  = random_password();

        $this->attributes[ "password" ] = base64_encode( $encrypter->encrypt( $password, [ "key" => $this->attributes[ "curp" ] ] ) );

        $data = $this->data;
        $data->verificacion->password = false;
        $this->data = $data;

        $historial = $this->historial;
        $historial->reset = date( "Y-m-d H:i:s" );
        $this->historial = $historial;
    }


    public function getPassword(): string 
    {
        $encrypter = service( "encrypter" );
        return $encrypter->decrypt( base64_decode( $this->attributes[ "password" ] ), [ "key" => $this->attributes[ "curp" ] ] );
    }


    public function getCalificaciones( $modelo ){
        $PTS = [];

        foreach( PROMOCIONES as $promo ){
            foreach( MESES as $mes ){
                $PTS[ $promo[ "codigo" ] ][ "meses" ][ $mes ] = 0;
            }
            $PTS[ $promo[ "codigo" ] ][ "total" ] = 0;
        } 

        foreach( $this->historial->modelos->{$modelo}->calificaciones as $mes => $promos ){
            if( $promos )
            foreach( $promos as $promo => $pts ){
                $PTS[ $promo ][ "meses" ][ $mes ] = $pts;
                $PTS[ $promo ][ "total" ] = $PTS[ $promo ][ "total" ] + $pts;
            }
        }

        return $PTS;
    }
    
    protected function setCurp( string $curp ){
        $this->attributes[ "curp" ]     = strtoupper( $curp );
        $this->attributes[ "fechanac" ] = implode("-", [ substr( $curp, 4, 2), substr( $curp, 6, 2), substr( $curp, 8, 2) ] );

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
        $data->avatarface = $caras[ rand( 0, sizeof( $caras ) - 1 ) ];
        $data->avatarbg = $colores[ rand( 0, sizeof( $colores ) - 1 ) ];
        $data->beneficiarios = [];
        $this->attributes[ "data" ] = json_encode( $data );
    }


    public function getPremieres(){

    $sql = "SELECT 
            historial->'$.modelos.\"10-NUTRICION\".calificaciones.\"202407\".\"010-DISTRIBUIDOR\"' AS biex,
            historial->'$.modelos.\"10-NUTRICION\".calificaciones.\"202407\".\"030-PLUS\"' AS plus,
            redes->>'$.modelos.\"10-NUTRICION\".padre' AS padre,
            id
            FROM t_usuarios
            WHERE 
            redes->>'$.modelos.\"10-NUTRICION\".padre' = {$this->id}
            HAVING biex >= 6 AND plus >= 3";  
            
            $db  = db_connect();
            return $db->query($sql)->getResultArray();
    }


    public function id( $modelo = null, $clase = null, $verificado = true ): string 
    {
        if( $modelo ){

            $m_0 = date('Ym');
            $m_1 = date('Ym', strtotime( date('Y-m').'-01'. ' -1 month' ) );

            $db  = db_connect();
            $sql = "select f_get_calificacion( {$this->id}, '{$m_1}', '{$modelo}' ) as '{$m_1}', f_get_calificacion( {$this->id}, '{$m_0}', '{$modelo}' ) as '{$m_0}'";
            $calificaciones = $db->query($sql)->getRowArray();

            $estatus = ESTATUS[ $this->data->estatus->modelos->{$modelo} ];
            $modelo  = MODELOS[ $modelo ];

            return "<span data-bs-toggle=\"tooltip\" title=\"<i class='fa fa-".$modelo[ "settings" ][ "icono" ]."'></i> ".( $modelo[ "nombre" ] )."<br><span class='badge w-100 bg-".( $this->verificado->estatus ? "teal" : "red" )."'>Socio ".( $this->verificado->estatus ? "" : "no" )." verificado</span>".$estatus[ "descripcion" ]."<span class='badge w-100 bg-".$estatus[ "color" ]."'>".$estatus[ "codigo" ]."</span>[ ".substr( $calificaciones[ $m_1 ], 3, 2 )." - ".substr( $calificaciones[ $m_0 ], 3, 2 )." ]\" class=\"badge bg-".$estatus[ "color" ]."\">".( $modelo ? "<i class=\"fa fa-".$modelo[ "settings" ][ "icono" ]."\"></i> " : "" ).id( $this->id, 6 )."</span>".( $verificado ? " <span class=\"small\">".$this->verified()."</span>" : "" );
        }
        elseif( $clase ){
            return "<span style=\"position:relative\" class=\"badge bg-{$clase}\" ".( $verificado ? "data-bs-custom-class=\"tooltip-".( $this->verificado->estatus ? "teal" : "red" )."\" data-bs-toggle=\"tooltip\" title=\"Socio ".( $this->verificado->estatus ? "" : "no" )." verificado\"" : "" ).">".id( $this->id, 6 ).( $verificado ? " <span class=\"small\">".$this->verified()."</span>" : "" )."</span>";
        }

        return id( $this->id, 6 );
    }

    public function verified(){
        return "<i class=\"far fa-circle-".( $this->verificado->estatus ? "check text-teal" : "xmark text-red" )."\"></i>";
    }

    public function nombre( $apellidos = 0, $mask = false ): string
    {
        $nombre = $this->data->nombre;
        for( $a = 0; $a < $apellidos; $a++ ){
            $nombre .= " ".( $mask ? mask( $this->data->apellidos[ $a ] ) : $this->data->apellidos[ $a ] );
        }
        
        return $nombre;

    }


    public function rango( int $size = 40 ): string 
    {
        return "<img style=\"width:{$size}px; height:{$size}px;\" src=\"".base_url()."assets/img/rangos/{$this->data->rango}.jpg\">";
    }


    public function avatar( int $size = 40, string $id = null ): string 
    {
        if( $this->data->avatar->activo !== null ){
            return "<img ".($id ?? "")." class=\"rounded-circle\" style=\"width:{$size}px; height: {$size}px;\" src=\"".base_url()."data/{$this->id}/avatar/{$this->data->avatar->imagenes[ $this->data->avatar->activo ]}\">";
        }
        else{
            // return "<div class=\"emoji\"><div><i style=\"font-size:{$size}px;\" class=\"text-".$this->data->avatarbg." fa fa-".$this->data->avatarface."\"></i></div></div>";

            return "<div class=\"emoji\"><div style=\"border-radius:50%; width:{$size}px;height:{$size}px;font-size:".($size/2)."px;line-height:".( $size / 2 )."px; padding-top:20%\" class=\"text-teal bg-gray-400\">".$this->iniciales()."</div></div>";
        }
    }


    public function permiso( $rol ){
        return in_array( $rol, $this->rol_codigos ) OR in_array( "50-ROOT", $this->rol_codigos ); 
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

    public function getDownlineJSON( $modelo ){

        $db  = db_connect();
        $sql = "select f_get_downline( {$this->id}, '{$modelo}', ".( MODELOS[ $modelo ][ "settings" ][ "niveles" ] )." ) as downline";
        $r = $db->query( $sql )->getRow();

        return $r->downline;
    }


    public function getUplineJSON( $modelo ){

        $db  = db_connect();
        $sql = "select f_get_upline( {$this->id}, '{$modelo}', 1 ) as upline";
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
                "ciclo"  => 1,
                "activa" => "010-CELULAR",
                "inicia" => null,
                "estrellas" => [
                    date( "Ym" ) => 0
                ],
                "reclamados" => []
            ];

            $this->data = $data;
            model( "UsuarioModel" )->save( $this );
        }

        $sql = "estatus_codigo = '201-ACTIVO' and ciclo = ".$this->data->recompensas->ciclo;
        $recompensas = model( "RecompensaModel" )->where( $sql , null, false )->findAll();

        if( $activa ){
            foreach( $recompensas as $r ){
                if( $r[ "codigo" ] == $this->data->recompensas->activa ){
                    return $r;
                }
            }
        }
        return $recompensas;
    }


    public function password_original(){
        return $this->attributes[ "password"];
    }

    public Function getEstrellas( $mes = null ){
        
        $db  = db_connect();
        $sql = "SELECT SUM(cantidad) as estrellas
                FROM t_comisiones 
                WHERE usuario_id = {$this->id}
                AND esquema_codigo = '120-BIEX-3ER-NIVEL'
                AND estatus_codigo = '255-PENDIENTE'";

        $estrellas = $db->query( $sql )->getRow()->estrellas;
        $data = $this->data;

        if( $estrellas > $data->recompensas->estrellas ){
            
            // notificación flash
            $data->splash[] = [
                "tipo" => "estrellas",
                "parametros" => [ intval( $estrellas - $data->recompensas->estrellas ) ]
            ];

            // update conteo
            $data->recompensas->estrellas = intval( $estrellas );

            $this->data = $data;
            model( "UsuarioModel" )->save( $this );
        }

        return intval( $estrellas ); 
    }



    public function getDomicilios(){
        $db = db_connect();
        $respuesta = [];
        $existe = false;

        $sql = "SELECT 
                    d.id as id, d.nombre as nombre, d.referencias, d.calleynumero, c.nombre as colonia, l.nombre as localidad, e.nombre as entidad, c.codigopostal
                from t_domicilios d
                JOIN t_colonias c ON c.id = d.colonia_id
                JOIN t_localidades l ON l.id = c.localidad_id AND l.entidad_id = c.entidad_id
                JOIN t_entidades e ON e.id = c.entidad_id
                WHERE d.estatus_codigo = '201-ACTIVO' AND d.usuario_id = {$this->id} order by d.created_at";

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

            $PTS    = [];
            $promos = [];

            foreach( PROMOCIONES as $p ){
                $PTS[ $p[ "codigo" ] ] = 0;
                $promos[ $p[ "codigo" ] ] = [];
            }

            ksort( $promos );
            
            $nuevo  = [
                "id" => null,
                "referencia" => null,
                "estatus_codigo" => "250-EN-PROCESO",
                "modelo_codigo" => $modelo,
                "PTS" =>  $PTS ,
                "usuario_id" => $this->id,
                "data" => [
                    "peso" => 0,
                    "saldo" => 0,
                    "mesanterior" => 0,
                    "pesoxbulto" => 0,
                    "total" => 0,
                    "comisionbanco" => 0,
                    "comisionentrega" => 0,
                    "entrega" => null,
                    "productos" => 0,
                    "tercernivel" => [
                        "cantidad" => 0,
                        "socio" => 0
                    ]
                ],
                "promociones" => $promos,
                "metodopago_codigo" => null,
                "metodoentrega_codigo" => null,
                "fechas" => [
                    "creado" => date( "Y-m-d H:i:s" )
                ]
            ];
    
            $pedido = $nuevo;
            model( "PedidoModel" )->save( $nuevo );

            $pedido = $this->getPedido( $modelo );
        }
        
        return $pedido;
    }

    
    public function fondeo( $modelo, $metodo, $cantidad, $mes = null ){

        if( $mes && $mes == date( "Ym" ) ) $mes = null;

        $pedido         = $this->getPedido( $modelo );
        $saldo          = $this->data->saldo->{$modelo};
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

            $comisionbanco  = $metodopago[ "settings" ][ "tipocomision" ] == "porcentaje" ? ( $subtotal - $saldo ) * $metodopago[ "settings" ][ "comision" ] / 100 : $metodopago[ "settings" ][ "comision" ];
        }

        $total = $subtotal + $comisionbanco;

        // PAGA PEDIDO
        // si la cantidad depositada es mayor o igual que el monto a pagar 
        if( $total <= ( $saldo + $cantidad ) ){
            $pedido[ "metodopago_codigo" ] = $metodopago[ "codigo" ];
            $pedido[ "data" ][ "comisionbanco" ] = $comisionbanco;
            $pedido[ "fechas" ][ "pagado" ] = $fecha;
            $pedido[ "fechas" ][ "califica" ] = intval( $pedido[ "data" ][ "mesanterior" ] ) ? $fecha_anterior : $fecha;
            $pedido[ "estatus_codigo" ] = "420-PAGADO";
    
            $historial->modelos->{$modelo}->ultimacompra = $pedido[ "fechas" ][ "califica" ];
            if( !$historial->modelos->{$modelo}->primercompra ){
                $historial->modelos->{$modelo}->primercompra = $pedido[ "fechas" ][ "califica" ];

                if( !isset( $data->recompensas ) ){
                    $data->recompensas = json_decode( "{}" );
                }
                $data->recompensas->inicia = $pedido[ "fechas" ][ "califica" ];
            }

            $mesactual = substr( $pedido[ "fechas" ][ "califica" ], 0, 4 ).substr( $pedido[ "fechas" ][ "califica" ], 5, 2 );
          
            $mp = [];
            foreach( $pedido[ "PTS" ] as $promo => $pts ){
                $mp[ $promo ] = ( $mp[ $promo ] ?? 0 ) + $pts;
            } 

            $historial->modelos->{$modelo}->calificaciones->{$mesactual} = $mp;

            if( $saldo >= $total && $metodopago[ "settings" ][ "tipocomision" ] == "saldo" ){
                $data->saldo->{$modelo} -= $total;
                $pedido[ "data" ][ "saldo" ] = $total;
            }
            else{
                $data->saldo->{$modelo} = 0;
                $pedido[ "data" ][ "saldo" ] = $saldo;
            }

            model( "PedidoModel" )->save( $pedido );

            $this->data = $data;
            $this->historial = $historial;
            model( "UsuarioModel" )->save( $this );

            $cc = $pedido[ "fechas" ][ "califica" ];
            $mescalifica = substr( $cc, 0, 4 ).substr( $cc, 5, 2 );

            $db = db_connect();
            $db->query( "select f_update_PTS( {$this->id}, '{$modelo}', '{$mescalifica}' )" );  
            $db->query( "select f_get_estatus( {$this->id} )" );
            $afectados = $db->query( "select f_reparte_comisiones( {$pedido[ "id" ]} )" )->getRow();
        }
        else{
            $data->saldo->{$modelo} += $cantidad;

            $this->data = $data;
            $this->historial = $historial;
            model( "UsuarioModel" )->save( $this );
        }

        return $pedido[ "referencia" ];
    }


    public function getBono( $esquema ){
        $a   = [
            1 => 0.00,
            2 => 0.00,
            3 => 0.00
        ];
        $sql = "SELECT nivel, SUM(cantidad) AS cantidad FROM t_comisiones
                WHERE esquema_codigo = '116-ANIVERSARIO-24'
                AND usuario_id = {$this->id}
                GROUP BY nivel";

        $db = db_connect();
        $resultado = $db->query($sql)->getResultArray();

        foreach( $resultado as $r ){
            $a[ $r[ "nivel" ] ] = floatval( $r[ "cantidad" ] );
        }

        return $a;
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
        $termina = substr( $mes, 0, 4 )."-".substr( $mes, 4, 2 )."-31";

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


    public function getIngresosPorDia( $modelo ){
        $resultado = [];

        $sql = "SELECT SUM(comision.cantidad) as comisiones, 
                DATE_FORMAT(comision.fecha, '%Y-%m-%d') as dia 
                FROM t_comisiones comision
                join t_esquemas esquema on esquema.codigo = comision.esquema_codigo
                WHERE esquema.modelo_codigo = '{$modelo}' 
                and comision.usuario_id = {$this->id} 
                AND esquema.settings->>'$.reparto' != 'puntos'
                AND esquema.settings->>'$.periodo' = 'SEMANAL'
                and substring( comision.estatus_codigo, 1, 3 ) > 200
                GROUP BY DATE_FORMAT(comision.fecha, '%Y-%m-%d')";

        $db     = db_connect();
        $result = $db->query($sql);

        foreach($result->getResult() as $d){
            $resultado[$d->dia] = $d->comisiones;
        }

        if(!sizeof($resultado)) $resultado = [0];
        return $resultado;
    }


    public function getComisiones( $periodo ){
        $resultado = [];

        $sql = "SELECT comision.fecha, comision.compresion, comision.pedido_id, comision.esquema_codigo, comision.nivel, comision.cantidad, pedido.usuario_id, pedido.referencia
                FROM t_comisiones comision
                join t_esquemas esquema on esquema.codigo = comision.esquema_codigo
                join t_periodos periodo on periodo.codigo = '{$periodo}'
                join t_pedidos pedido on pedido.id = comision.pedido_id
                WHERE comision.usuario_id = {$this->id} 
                and substring( comision.estatus_codigo, 1, 3 ) > 200
                AND esquema.settings->>'$.reparto' != 'puntos'
                AND esquema.settings->>'$.periodo' = 'SEMANAL'
                AND comision.fecha between periodo.inicia and periodo.termina
                AND ".substr( $periodo, 0, 2 )." = substring( esquema.modelo_codigo, 1, 2 );";

        $db = db_connect();
        return $db->query($sql)->getResult();
    }
}

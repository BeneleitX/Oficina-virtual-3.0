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
        "pedido"         => "json"
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


    public function getPassword(): string 
    {
        $encrypter = service( "encrypter" );
        return $encrypter->decrypt( base64_decode( $this->attributes[ "password" ] ), [ "key" => $this->attributes[ "curp" ] ] );
    }


    public function xxxestatus( $modelo ){

        $estatus = "000-DESCONOCIDO";
        $data = $this->historial->modelos->{$modelo};
        
        $mesactual    = date( "Ym" );
        $primercompra = substr( $data->primercompra, 0, 4 ).substr( $data->primercompra, 5, 2 );
        $puntos = [];

        for( $mes = 0; $mes < 3; $mes++ ){
            foreach( MODELOS[ $modelo ][ "settings" ][ "promocion_base" ] as $promo ){
                $puntos[ MESES[ $mes ] ] = ( $puntos[ MESES[ $mes ] ] ?? 0 ) + ( $data->calificaciones->{MESES[ $mes ]}->{$promo} ?? 0 );
            }
        }
        d($puntos);
        if( in_array( "00-BLOQUEADO", $this->rol_codigos ) ){
            // manualmente con rol de bloqueado 
            return ESTATUS[ "120-BAJA" ];
        }
	
        if( in_array( "42-PERMANENTE", $this->rol_codigos ) ){
            // rol de staff
            return ESTATUS[ "612-STAFF-PERMANENTE" ];
        }

        if( $primercompra ){
	        if( $puntos[ MESES[ 0 ] ] ){
                if( $primercompra == MESES[ 0 ] ){
                    // registrado en los ultimos 30 días, con compras	
                    return ESTATUS[ "510-NUEVO-CALIFICADO" ];
                }
                else{
                    if( $puntos[ MESES[ 1 ] ] ){
                        // con compras en mes actual 
                        return ESTATUS[ "520-CALIFICADO-ACTUAL" ];
                    }
                    else{
                        // con compras en mes actual sin compra en mes anterior
                        return ESTATUS[ "320-NO-CALIFICADO-COMPRA" ];
                    }
                }
            }
        
            if( $puntos[ MESES[ 1 ] ] ){
                // con compras en el mes anterior, pero sin compras en mes actual
                return ESTATUS[ "410-CALIFICADO" ];
            }

            if( $puntos[ MESES[ 2 ] ] ){
                // sin compras en los ultimos 2 meses
                return ESTATUS[ "310-NO-CALIFICADO" ];
            }	
        
            // no tiene compras en los ultimos 3 meses
            return ESTATUS[ "140-SUSPENDIDO" ];
        }
        else{
            if( $data->registro > date("Y-m-d", strtotime( date("Y-m-d" )." - 1 days" ) ) ){
                if( $data->validacion ){
                    // registrado en los ultimos 30 días, aun sin compras pero verificado
                    return ESTATUS[ "220-NUEVO-VERIFICADO" ];
                }
                else{
                    // registrado en los ultimos 30 días, aun sin compras y sin verificar
                    return ESTATUS[ "210-NUEVO" ];
                }			

                // nunca hizo compras y venció su periodo de nuevo (30 días)
                return ESTATUS[ "130-NUEVO-SUSPENDIDO" ];
            }
        }

        // return json_decode( $this->attributes['estatus'] )
        // return ESTATUS[$e->{"10-NUTRI"}];
    }


    public function getCalificaciones(){
        $PTS = [];

        foreach( PROMOCIONES as $promo ){
            foreach( MESES as $mes ){
                $PTS[ $promo[ "codigo" ] ][ $mes ] = isset( $this->historial->modelos->{$promo[ "codigo" ]}->calificaciones[ $mes ] ) ? $this->historial->modelos->{$promo[ "codigo" ]}->calificaciones[ $mes ] : 0;
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


    public function id( $estatus = null, $modelo = null, $clase = null ): string 
    {
        if( $estatus ){
            return "<span ".( $modelo ? "data-bs-toggle=\"tooltip\" title=\"".MODELOS[ $modelo ][ "nombre" ]." | ".ESTATUS[ $estatus ][ "descripcion" ]."\" " : "" )." class=\"badge bg-".ESTATUS[ $estatus ][ "color" ]."\">".( $modelo ? "<i class=\"fa fa-".MODELOS[ $modelo ][ "settings" ][ "icono" ]."\"></i> " : "" ).id( $this->id, 6 )."</span>";
        }
        elseif( $clase ){
            return "<span class=\"badge bg-{$clase}\">".id( $this->id, 6 )."</span>";
        }

        return id( $this->id, 6 );
    }


    public function nombre( $apellidos = 0, $mask = false ): string
    {
        $nombre = $this->data->nombre;
        for( $a = 0; $a < $apellidos; $a++ ){
            $nombre .= " ".( $mask ? mask( $this->data->apellidos[ $a ] ) : $this->data->apellidos[ $a ] );
        }
        
        return $nombre;

    }


    public function avatar( int $size = 40, string $id = null ): string 
    {
        if( $this->data->avatar->activo !== null ){
            return "<img ".($id ?? "")." class=\"rounded-circle\" style=\"width:{$size}px; height: {$size}px;\" src=\"".base_url()."data/{$this->id}/avatar/{$this->data->avatar->imagenes[ $this->data->avatar->activo ]}\">";
        }
        else{
            return "<div class=\"emoji\"><div><i style=\"font-size:{$size}px;\" class=\"text-".$this->data->avatarbg." fa fa-".$this->data->avatarface."\"></i></div></div>";
        }
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
        $db     = db_connect();

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

        $pedido         = $this->getPedido( $modelo );
        $saldo          = $this->data->saldo->{$modelo};
        $bultos         = ceil( $pedido[ "data" ][ "peso" ] / $pedido[ "data" ][ "pesoxbulto" ] );
        $productos      = $pedido[ "data" ][ "total" ];
        $metodopago     = model( "MetodopagoModel" )->find( $metodo );
        $subtotal       = $productos + $pedido[ "data" ][ "comisionentrega" ];
        $fecha          = $mes ? substr($mes,0,4)."-".substr($mes,4,2)."-01 12:00:00" : date( "Y-m-d H:i:s" );
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
    
            $historial->modelos->{$modelo}->ultimacompra = $fecha;
            if( !$historial->modelos->{$modelo}->primercompra ){
                $historial->modelos->{$modelo}->primercompra = $fecha;
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
            // $db->query( "call p_update_niveles( {$pedido[ "usuario_id" ]}, '{$modelo}' )" );
            
            
            //$db->query( "call p_update_rango( {$pedido[ "usuario_id" ]}, '{$modelo}' )" );
        }
        else{
            $data->saldo->{$modelo} += $cantidad;

            $this->data = $data;
            $this->historial = $historial;
            model( "UsuarioModel" )->save( $this );
        }

        return $pedido[ "referencia" ];
    }
}

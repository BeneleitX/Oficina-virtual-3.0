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


    public function getEstatus( $modelo ){

        return $this->data->estatus->{$modelo};

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


    public function id($fondo = true): string 
    {
        if( $fondo ){
            return "<span class=\"badge bg-teal\">".id( $this->id, 6 )."</span>";
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
        $sql = "call p_get_downline( {$this->id}, '{$modelo}', ".( MODELOS[ $modelo ][ "settings" ][ "niveles" ] ).", 20)";
        $r = $db->query( $sql )->getResultArray();

        /*** RANDOMIZE ***/ 

        $estados = [
            "220-NUEVO-VERIFICADO",
            "310-NO-CALIFICADO",
            "410-CALIFICADO",
            "520-CALIFICADO-ACTUAL"
        ];

        $calificaciones = [
            "00---",
            "10-B1",
            "15-B2",
            "20-BX",
            "30-EE",
            "40-PR"
        ];      
        
        $rangos = [
            "00-SOCIO",
            "10-3K",
            "20-5K",
            "30-10K",
            "40-BRONCE",
            "50-PLATA",
            "60-ORO",
            "70-RUBI",
            "80-ESMERALDA",
            "90-DIAMANTE"            
        ];              

/*         foreach($r as $k => $s){
            $r[ $k ][ "estatus" ] = $estados[ array_rand( $estados ) ];

            if( intval( substr( $r[ $k ][ "estatus" ], 0, 1 ) > 2 ) ){
                $r[ $k ][ "profundidad" ] = json_encode( [ rand(0,10), rand(0,20), rand(0,30) ] );

                $r[ $k ][ "rango" ] = $rangos[ array_rand( $rangos ) ];

                $r[ $k ][ "calificaciones" ] = [
                    $calificaciones[ array_rand( $calificaciones ) ],
                    $calificaciones[ array_rand( $calificaciones ) ]
                ];

                if( intval( substr( $r[ $k ][ "calificaciones" ][1], 0, 1 ) == 0 ) )
                    $r[ $k ][ "calificaciones" ][ 1 ] = "30-P+";                

                $r[ $k ][ "calificaciones" ] = json_encode( $r[ $k ][ "calificaciones" ] );
            }

        } */

        /******/

        return json_encode( $r );
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
                    "productos" => 0
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

    public function fondeo( $modelo, $metodo, $cantidad ){

        $pedido         = $this->getPedido( $modelo );
        $saldo          = $this->data->saldo->{$modelo};
        $bultos         = ceil( $pedido[ "data" ][ "peso" ] / $pedido[ "data" ][ "pesoxbulto" ] );
        $productos      = $pedido[ "data" ][ "total" ];
        $metodopago     = model( "MetodopagoModel" )->find( $metodo );
        $subtotal       = $productos + $pedido[ "data" ][ "comisionentrega" ];
        $fecha          = date( "Y-m-d H:i:s" );
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

/*             $mesactual = date("Ym");
            if( !isset( $historial->modelos->{$modelo}->calificaciones->{$mesactual} ) ){
                $historial->modelos->{$modelo}->calificaciones[ "{$mesactual}" ] = [];
            }

            foreach( $pedido[ "PTS" ] as $promo => $pts ){
                $historial->modelos->{$modelo}->calificaciones[ "{$mesactual}" ][ $promo] += $pts;
            } */
            

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

            $db = db_connect();
            
            $db->query( "select f_update_PTS({$this->id}, \"{$modelo}\", \"".substr( $pedido[ "fechas" ][ "califica" ], 0, 4).substr( $pedido[ "fechas" ][ "califica" ], 5, 2)."\") ");  
            
            $db->query( "select f_update_estatus({$this->id}, \"{$modelo}\")");


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

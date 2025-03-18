<?php

namespace App\Controllers;

class Dashboard extends BaseController
{
    function __construct() {
        $this->data[ "menu" ] = "inicio";
        
    }

    public function inicio(){
        $this->data[ "navbar" ] = true;
        $this->data[ "titulo" ] = "¡Hola {$this->data[ "usuario" ]->nombre()}! ".$this->data[ "usuario" ]->id( null, "marine");
        $this->data[ "checks" ] = $this->data[ "usuario" ]->getChecks( "10-NUTRICION" );
        
        $this->data[ "usuario" ]->valida_modelo();

        $sql = "estatus_codigo = '201-ACTIVO'";
        $this->data[ "bloques" ] = model( "BloqueModel" )->where( $sql , null, false )->orderBy('columna', 'asc')->orderBy('orden', 'asc')->findAll();

        echo template( "dashboard/inicio", $this->data );
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


    public function sociodata( $request = null ){

        $db = db_connect();

        if( $this->request->getPost( "search_id" ) ){
            $temp = model( "UsuarioModel" )->find( $this->request->getPost( "search_id" ) );

            if( $temp->id ?? false ){
                $request = urlencode( base64_encode( $temp->password_original() ) );
            }
        }

        if( !$request || ( !$this->data[ "usuario" ]->permiso( "32-EDICION" ) AND 
            !$this->data[ "usuario" ]->permiso( "40-ADMIN" ) ) ) {
            return redirect()->to( "inicio" ); 
        }

        $this->data[ "navbar" ] = true;
        $this->data[ "titulo" ] = "Detalles de usuario";

        $request = base64_decode( urldecode( $request ) );
        $this->data[ "socio" ] = model( "UsuarioModel" )->where( "password = '{$request}'" )->first();

        // Se hace doble consulta para que carque la validación
        $this->data[ "socio" ] = model( "UsuarioModel" )->find( $this->data[ "socio" ]->id );
        $this->data[ "socio" ]->valida_modelo();
        
        // BITACORA Consulta de datos
        bitacora( 50, $this->data[ "usuario" ]->id, [ 
            "socio" => $this->data[ "socio" ]->id
        ] );

        load_catalogo( "metodosentrega");
        load_catalogo( "almacenes");
        
        // Obtenemos sus compras recientes
        
        $sql = "select JSON_OBJECTAGG( modelo_codigo, json_array( referencia, fecha, metodoentrega, entrega) ) as data
                FROM ( SELECT  @prev := '' ) init
                JOIN
                ( SELECT modelo_codigo != @prev AS first, @prev := modelo_codigo, modelo_codigo, referencia, metodoentrega_codigo as metodoentrega, data->>'$.entrega' as entrega, CAST( fechas->>'$.pagado' AS DATE ) as fecha
                        FROM  t_pedidos where usuario_id = {$this->data[ "socio" ]->id} AND SUBSTRING( estatus_codigo, 1, 3 ) > 400
                        ORDER BY modelo_codigo, fecha DESC, CAST( fechas->>'$.pagado' AS DATE ) DESC LIMIT 999999
                ) x
                WHERE  first ORDER BY modelo_codigo;";

        $this->data[ "pedidos" ] = json_decode( $db->query( $sql )->getRow()->data, 1 );
        
        echo template( "dashboard/sociodata", $this->data );
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
            $db->query( "do f_update_PTS( {$socio->id}, '{$m[ "codigo" ]}', '".date( 'Ym', strtotime( date('Y-m').'-01'. ' -1 month' ) )."' )" ); 
            $db->query( "do f_update_PTS( {$socio->id}, '{$m[ "codigo" ]}', '".date( "Ym" )."' )" );  
            $db->query( "call p_update_padre( {$socio->id}, '{$m[ "codigo" ]}' );" );
        }

        $db->query( "do f_get_estatus(  {$socio->id}, 0 )" );
        $db->query( "do f_checks_rango( {$socio->id}, '10-NUTRICION' );" );


        // BITACORA Forzar update
        bitacora( 62, $this->data[ "usuario" ]->id, [ 
            "socio"   => $socio->id
        ] );

        $ruta = urlencode( base64_encode( $socio->password_original() ) );
        return redirect()->to( "sociodata/{$ruta}" );
    }


    public function load_padres(){
        if( !$this->data[ "usuario" ]->permiso( "41-RED" ) ){
            return redirect()->to( "inicio" ); 
        }

        $html = "";
        $pat  = model( "UsuarioModel" )->find( $this->request->getPost( "patrocinador" ) );

        if($pat){

            $pat->valida_modelo();

            foreach( MODELOS as $m ){
                $pat2 = $pat;

                while( substr( $pat2->data->estatus->modelos->{$m["codigo"]}, 0, 3 ) < 200 ){
                    $pat2 = model( "UsuarioModel" )->find( $pat2->redes->modelos->{$m[ "codigo" ]}->padre );
                }

                $html .= "<td class=\"text-center\" width=\"20%\"><div class=\"py-3\">".$pat2->avatar(80)."</div><h5 class=\"mb-1\">".$pat2->id( $m[ "codigo" ], null, false )."</h5><span class=\"small text-{$m[ "settings" ][ "color" ]}\"><i class=\"fa fa-{$m[ "settings" ][ "icono" ]}\"></i> {$m[ "nombre" ]}</span></td>";
            } 
        }
        else{
            $html .= "<td class=\"text-center\"><h3 class=\"text-red\">El socio no existe</h3></td>";
        }
        
        echo $html;
    }


    public function cambia_patrocinador(){
        if( !$this->data[ "usuario" ]->permiso( "41-RED" ) ){
            return redirect()->to( "inicio" ); 
        }

        if( $socio = model( "UsuarioModel" )->find( $this->request->getPost( "n_socio" ) ) ){
            $patrocinadores = $this->request->getPost( "patrocinador" );

            $db = db_connect();

            foreach( MODELOS as $m ){
                $patrocinador = $patrocinadores[ $m[ "codigo" ] ];

/*                 if( $socio->patrocinador( $m[ "codigo" ] ) != $patrocinador ){
                    $redes = $socio->redes;
                    $redes->modelos->{$m[ "codigo" ]}->patrocinador = $patrocinador;
                    $socio->redes = $redes;
                    
                    model( "UsuarioModel" )->save( $socio );

                    // BITACORA Cambio de patrocinador
                    bitacora( 84, $this->data[ "usuario" ]->id, [ 
                        "socio"        => $socio->id,
                        "patrocinador" => $patrocinador,
                        "modelo"       => $m[ "codigo" ]
                    ] );

                    $db->query( "call p_update_padre( {$socio->id}, '{$m[ "codigo" ]}' );" );
                } */
            }
            $ruta = urlencode( base64_encode( $socio->password_original() ) );
            return redirect()->to( "sociodata/{$ruta}" );
    
        }

        return redirect()->to( "usuarios" );
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


    public function save_layout(){
        $bloque = $this->request->getPost( "bloque" );

        $json = $this->data["usuario"]->data;

        if( !isset( $json->layout ) or !is_object( $json->layout ) ){
            $json->layout = json_decode( "{}" );
        }

        $json->layout->{$bloque} = $this->request->getPost( "valor" );
        $this->data["usuario"]->data = $json; 

        model( "UsuarioModel" )->save( $this->data[ "usuario" ] );
    }


    public function splash(){
        $html = "";
        extract( $this->request->getPost() );
        $parametros = json_decode( $parametros );
        switch( $tipo ){
            case "rango":
                $rango = model( "RangoModel" )->find( $parametros[0] );
                $html .= "
                <div class=\"text-center\">
                    <h2 class=\"mt-3\">!Felicidades!</h2>
                    <p>¡Has alcanzado un nuevo rango!</p>
                    <p><img src=\"".base_url()."assets/img/rangos/{$rango[ "codigo" ]}.png\" class=\"img-fluid col-10 col-sm-6 col-md-4 col-lg-3 px-5\"></p>

                    <p><span class=\"fs-3 mb-5 badge bg-{$rango[ "color" ]}\">{$rango[ "nombre" ]}</span></p>

                    
                    <p class=\"my-5 text-center\"><button type=\"button\" class=\"btn btn-primary\" data-bs-dismiss=\"modal\">Continuar</button></p>
                    </div>
                    
                        <script>

                        $( document ).ready(function(){
                            var colors = ['#{$rango[ "hex" ]}', '#1A2542'];

                            ( function call_confetti() {
                                confetti({
                                    particleCount: 2,
                                    angle: 45,
                                    spread: 85,
                                    origin: { x: 0, y:0.5 },
                                    colors: colors
                                });
                                confetti({
                                    particleCount: 2,
                                    angle: 135,
                                    spread: 85,
                                    origin: { x: 1, y:0.5 },
                                    colors: colors
                                });
                            
                                timeout = setTimeout(call_confetti, 20);
                            }() );

                            //call_confetti();
                        
                            });
                        </script>                    
                    ";

                break;

            case "cumple":
                $html .= "
                    <div class=\"text-center\">

                    <img src=\"".base_url()."assets/img/welcome.png\" class=\"img-fluid\">
                    <h3 class=\"mt-4\">¡BIENVENIDO!</h3>
                    <p class=\"mb-4\">¡a tu nueva oficina virtual Beneleit!</p>
                    <p class=\"my-4\"><button type=\"button\" class=\"btn btn-primary\" data-bs-dismiss=\"modal\">Comenzar</button></p>
                    </div>

                    <script>

                    $( document ).ready(function(){
                        ( function call_confetti() {
                            confetti({
                                spread: 360,
                                ticks: 50,
                                gravity: 0,
                                decay: 0.94,
                                startVelocity: 30,
                                particleCount: randomInRange(10, 300), 
                                origin: { x: randomInRange(0.2, 0.8), y: randomInRange(0,0.5)} 
                            });
                        
                            timeout = setTimeout(call_confetti, randomInRange(10, 500));
                        }() );

                    });
                    </script>
                    ";

                break;

            case "bienvenida":
                $html .= "
                    <div class=\"text-center\">

                    <p class=\"\"><img src=\"".base_url()."assets/img/ov2.png\" class=\"img-fluid px-5\"></p>
                    <h2 class=\"mt-3\">¡BIENVENIDO!</h2>
                    <h5 class=\"mb-3\">¡a tu nueva oficina virtual Beneleit!</h5>
                    <p class=\"my-4\"><button type=\"button\" class=\"btn btn-primary\" data-bs-dismiss=\"modal\">Continuar</button></p>
                    </div>

                    <script>

                    $( document ).ready(function(){
                        ( function call_confetti() {
                            confetti({
                                spread: 360,
                                ticks: 50,
                                gravity: 0,
                                decay: 0.94,
                                startVelocity: 10,
                                particleCount: randomInRange(10, 300), 
                                origin: { x: randomInRange(0.2, 0.8), y: randomInRange(0,0.5)} 
                            });
                        
                            timeout = setTimeout(call_confetti, randomInRange(10, 500));
                        }() );

                    });
                    </script>
                    ";

                break; 

            case "estrellas":
                $html .= "
                    <div class=\"text-center\">
                    <h2 class=\"mt-3\">!Felicidades!</h2>
                    <p><img src=\"".base_url()."assets/img/estrella.png\" class=\"img-fluid col-10 col-sm-6 col-md-4 col-lg-3 px-5\"></p>
                    
                    <h4 class=\"fs-2 mb-3\">Has conseguido <span class=\"text-mustard\">{$parametros[0]}</span> estrella".( $parametros[0] - 1 ? "s" : "" )."</h4><h5>Total acumulado: <span class=\"text-mustard\">{$this->data[ "usuario" ]->data->recompensas->estrellas}</span></h5>
                    <p class=\"mt-5\"><button type=\"button\" class=\"btn btn-primary\" data-bs-dismiss=\"modal\">Continuar</button></p>
                    </div>

                    <script>

                    $( document ).ready(function(){


                        ( function call_confetti() {
                            confetti({
                                spread: 360,
                                ticks: 150,
                                gravity: 0,
                                decay: 0.93,
                                startVelocity: 10,
                                colors: ['FFE400', 'FFBD00', 'E89400', 'FFCA6C', 'FDFFB8'],
                                particleCount: 10,
                                scalar: 1.2,
                                shapes: ['star'],
                                origin: { y: 0.35 } 
                            });

                            setTimeout(function(){
                            confetti({
                                spread: 360,
                                ticks: 100,
                                gravity: 0,
                                decay: 0.94,
                                startVelocity: 20,
                                colors: ['FFE400', 'FFBD00', 'E89400', 'FFCA6C', 'FDFFB8'],
                                particleCount: 5,
                                scalar: 2,
                                shapes: ['star'],
                                origin: { y: 0.35 } 
                            });

                            }, 400);

                            confetti({
                                spread: 360,
                                ticks: 70,
                                gravity: 0,
                                decay: 0.95,
                                startVelocity: 0,
                                colors: ['FFE400', 'FFBD00', 'E89400', 'FFCA6C', 'FDFFB8'],
                                particleCount: 5,
                                scalar: 0.75,
                                shapes: ['circle'],
                                origin: { y: 0.35 } 
                            });
                        
                            timeout = setTimeout(call_confetti, randomInRange(100, 1000));
                        }() ); 

                    });
                    </script>
                    ";

                break; 
    
            case "recompensa":
                $r = model( "RecompensaModel" )->find( $parametros[0] );

                $html .= "
                    <div class=\"text-center\">
                    <h2 class=\"mt-3\">!Felicidades!</h2>
                    <p class=\"py-4\"><img src=\"".base_url()."assets/img/recompensas/{$r[ "codigo" ]}.png\" class=\"col-10 col-sm-6 col-md-4 col-lg-3 px-5\" style=\"width:256px\"></p>
                    
                    <p class=\"m-0\">Haz alcanzado la recompensa Beneleit</p>
                    <h4 class=\"fs-2 mb-3\"><span class=\"text-mustard\">{$r[ "nombre" ]}</span></h4>
                    <p class=\"mt-5\"><button type=\"button\" class=\"btn btn-primary\" data-bs-dismiss=\"modal\">Continuar</button></p>
                    </div>

                    <script>

                    $( document ).ready(function(){

                        var count = 200;
                        var defaults = {
                        origin: { y: 0.7 }
                        };

                        function fire(particleRatio, opts) {
                        confetti({
                            ...defaults,
                            ...opts,
                            particleCount: Math.floor(count * particleRatio),
                            origin: { y: 1 }
                        });
                        }

                                                ( function call_confetti() {
                                                


                            fire(0.25, {
                            spread: 26,
                            startVelocity: 45,
                            });
                            fire(0.2, {
                            spread: 60,
                            });
                            fire(0.35, {
                            spread: 100,
                            decay: 0.91,
                            scalar: 0.8
                            });
                            fire(0.1, {
                            spread: 300,
                            startVelocity: 15,
                            decay: 0.92,
                            scalar: 1.2
                            });
                            fire(0.1, {
                            spread: 120,
                            startVelocity: 30,
                            });                            
                        
                            timeout = setTimeout(call_confetti, randomInRange(100, 2000));
                        }() ); 

                    });
                    </script>
                    ";

                break; 
        
            case "cash":
                if( substr( $parametros[0], 2, 1 ) == "S"){
                    return;
                }

                $pago = model( "PagoModel")->find( $parametros[0] );

                switch( $pago[ "data" ][ "retencion" ] ){
                    case 2: 
                        $subt  = $pago[ "data" ][ "cantidades" ][ "subtotal" ] / 1.16;                        
                        $promo = $subt * .1; // promo
                        $sub   = $subt - $promo;  // subtotal
                        $iva   = $sub * .16; // iva
                        $ret   = $sub * 0.0125; //( retencion)
                        $total = $pago[ "data" ][ "cantidades" ][ "subtotal" ] - $promo + $iva - $ret;
                        break;
                    case 1:
                        $subt  = $pago[ "data" ][ "cantidades" ][ "subtotal" ] / 1.16; // subtotal
                        $rete  = $subt * 0.1066; // retencion
                        $iva   = $subt * 0.16;  // iva
                        $total = $subt - $rete + $iva; // total
                        break;
                    default:
                        $total = $pago[ "data" ][ "cantidades" ][ "total" ];
                }

                


                $html .= "
                    <div class=\"text-center\">

                    
                    <h3 class=\"mt-4\">¡FELICIDADES!</h3>
                    <p class=\"mb-4\">Estos son tus ingresos de <span class=\"text-".MODELOS[ $pago[ "modelo_codigo" ] ][ "settings" ][ "color" ]."\"><i class=\"fa fa-".MODELOS[ $pago[ "modelo_codigo" ] ][ "settings" ][ "icono" ]."\"></i> ".MODELOS[ $pago[ "modelo_codigo" ] ][ "nombre" ]."</span><br>para la semana <span class=\"badge bg-red\">".periodo( $pago[ "data" ][ "periodos" ][ "creacion" ] )."</span></p>
                    <p class=\"display-1 mb-4\"><span class=\"badge bg-marine\">$".number_format( $total, 2 )."</span></p>
                    <p class=\"mb-5\">Los cuales estan siendo transferidos a la<br>cuenta CLABE ".mask( $pago[ "clabe" ], "clabe" )."</p>
                    <p class=\"my-4\"><button type=\"button\" class=\"btn btn-primary\" data-bs-dismiss=\"modal\">Continuar</button></p>
                    </div>

                    <script>

                    $( document ).ready(function(){


                        ( function call_confetti() {
                        
                            confetti({
                                scalar: 1,
                                decay: 0.95,
                                spread: 180,
                                particleCount: 10,
                                origin: { y: -0.3 },
                                startVelocity: -25,
                                shapes: ['square'],
                                colors: ['#009779', '#47c24c', '#8bc34a']
                            });

                            confetti({
                                scalar: 2,
                                decay: 0.95,
                                spread: 180,
                                particleCount: 10,
                                origin: { y: -0.3 },
                                startVelocity: -25,
                                shapes: ['square'],
                                colors: ['#009779', '#47c24c', '#8bc34a']
                            });                            
                        
                            timeout = setTimeout(call_confetti, randomInRange(10, 1000));
                        }() ); 

                    });
                    </script>
                    ";

                break;                 
        }

        return $html;
    }


    public function niveles_gas(){
        $db = db_connect();

        $matriz = [
            [
                [0,0,0,0,0,0,0,0],
                [0,0,0,0,0,0,0,0],
                [0,0,0,0,0,0,0,0],
                [0,0,0,0,0,0,0,0],
                [0,0,0,0,0,0,0,0],
                [0,0,0,0,0,0,0,0],
            ],
            [
                [0,0,0,0,0,0,0,0],
                [0,0,0,0,0,0,0,0],
                [0,0,0,0,0,0,0,0],
                [0,0,0,0,0,0,0,0],
                [0,0,0,0,0,0,0,0],
                [0,0,0,0,0,0,0,0],
            ]
        ];

        $sql = "call p_get_paquetes( {$this->data[ "usuario" ]->id}, '".date( "Ym", strtotime( date( "Ym" )."-01 - 1 month") )."' )";
        $ps = $db->query( $sql )->getResult();

        foreach( $ps as $socio ){
            $x = $socio->nivel;
            $y = $socio->calificacion;

        if( $x > 0 && $y > 0 )
                $matriz[ 0 ][ $socio->nivel -1 ][ $y -1 ]++;
        }

        $sql = "call p_get_paquetes( {$this->data[ "usuario" ]->id}, '".date( "Ym" )."' )";
        $ps = $db->query( $sql )->getResult();

        foreach( $ps as $socio ){
            $x = $socio->nivel;
            $y = $socio->calificacion;

        if( $x > 0 && $y > 0 )
                $matriz[ 1 ][ $socio->nivel -1 ][ $y -1 ]++;
        }


        echo json_encode( $matriz );
    }



    public function datos_moviles(){
        $token = $this->request->getPost( "token" );
        $html = "<span class=\"badge bg-red\">{$token}</span>";

        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, "https://core.beneleit.talentonet.com/api/beneleit/resumen_servicios?token=".$token );
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true); 
        curl_setopt($curl, CURLOPT_HEADER, 0); 
        $respuesta = json_decode( curl_exec( $curl ) );
        curl_close($curl);
           
        $html = "<table class=\"table w-100 m-0 table-borderless xtable-striped\">";
        $k = 0;
        
        if( $respuesta ){
            foreach( $respuesta->data as $num ){
                $recarga = $num; // end( $num->detalle );
                $plan = model( "ProductoModel" )->find( $recarga->plan__oferta_adicional );
                //$plan->descripcion = str_replace( "B -", "B<br>", $plan->descripcion );

                $f = [
                    "d" => date( "d", strtotime( $recarga->vigencia_fin ) ),
                    "m" => date( "m", strtotime( $recarga->vigencia_fin ) ),
                    "y" => date( "Y", strtotime( $recarga->vigencia_fin ) ),
                ];

                $g = [
                    "d" => date( "d", strtotime( $recarga->vigencia_inicio ) ),
                    "m" => date( "m", strtotime( $recarga->vigencia_inicio ) ),
                    "y" => date( "Y", strtotime( $recarga->vigencia_inicio ) ),
                ];

                if( $recarga->vigencia_fin < date( "Y-m-d" ) ){
                    $vence = "Vencido";
                    $vence_clase = "red";
                }
                else{
                    $vence = "Vence";
                    $vence_clase = "teal";
                }

                $html .= "\n<tr class=\"dt_num ".( $k++ > 3 ? "d-none" : "" )."\">
                    <td class=\"p-0\"><i class=\"fa fa-mobile-retro text-{$vence_clase} fs-1\"></td>
                    <td class=\"py-2 w-100\"><h5 style=\"line-height: 0.2\" class=\"mb-3\">{$num->sim__numero_telefono}</h5><p style=\"line-height: 0.8\"><small><strong>{$plan->data->descripcion}</strong><br>Contratado el ".( $g[ "d" ]." de ".mes( $g[ "m" ] ).", ".$g[ "y" ])."<br><span class=\"text-{$vence_clase}\">{$vence} el ".( $f[ "d" ]." de ".mes( $f[ "m" ] ).", ".$f[ "y" ])."</span></small></p></td>
                    <td class=\"text-end p-0\" nowrap><img src=\"https://v4.app/assets/img/productos/{$plan->codigo}.png\" style=\"border-radius:5px; width:60px; height:60px\"></td>
                </tr>";
            }
        }
        else{
            $html .= "<tr><td class=\"py-5 text-red text-center\">No hay información</td></tr>";
        }
        
        $html .= "</table>";

        if( $k > 4 ){
            $html.= "<p class=\"text-center\"><button onclick=\"$( '.dt_num' ).removeClass( 'd-none' );\" class=\"btn btn-xs btn-outline-danger\">Ver todas las líneas relacionadas a este socio</button></p>";
        }
        elseif( $k == 0 ){
            $html= "<div class=\"row mx-3\"><div class=\"col-4 display-1 py-2 text-gray-300 text-center ps-5\"><i class=\"fa fa-mobile-retro\"></i></div><div class=\"col-8 pt-4 text-gray-500 text-center\">No tienes números de celular asociados a tu cuenta</div></div>";
        }

        return $html;
    }
}

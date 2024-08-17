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

        $sql = "estatus_codigo = '201-ACTIVO'";
        $this->data[ "bloques" ] = model( "BloqueModel" )->where( $sql , null, false )->orderBy('columna', 'asc')->orderBy('orden', 'asc')->findAll();

        echo template( "dashboard/inicio", $this->data );
    }


    public function sociodata( $request = null ){
        
        if( $this->data[ "usuario" ]->id < 60 || in_array( $this->data[ "usuario" ]->id, [ 666, 555 ] ) ){
            $this->data[ "saved" ] = false;
            $this->data[ "navbar" ] = true;
            $this->data[ "titulo" ] = "Consulta";

            if( $request ){

                $request = base64_decode( urldecode( $request ) );
                $this->data[ "saved" ] = true;
                $this->data[ "socio" ] = model( "UsuarioModel" )->where( "password = '{$request}'" )->first();
                $this->data[ "socio" ] = model( "UsuarioModel" )->find( $this->data[ "socio" ]->id );
            }
            elseif( $this->request->getPost( "socio" ) ){
                $this->data[ "socio" ] = model( "UsuarioModel" )->find( $this->request->getPost( "socio" ) );
            }
            else{
                $this->data[ "socio" ] = null;
            }
            
            if( $this->data[ "socio" ] && !$request ){
                
                // BITACORA Consulta de datos
                bitacora( 50, $this->data[ "usuario" ]->id, [ 
                    "socio" => $this->data[ "socio" ]->id
                ] );
            }

            echo template( "dashboard/sociodata", $this->data );
        }
        else{
            return redirect()->route( "logout" );
        }
    }


    public function update_sociodata(){
        $r   = $this->request->getPost();
        $socio = model( "UsuarioModel" )->find( $r[ "id" ] );

        $data = $socio->data;
        $cambios = [];

        if( $socio->telefono != $r[ "telefono" ] ){ $socio->telefono = $r[ "telefono" ]; $cambios[] = [ $socio->telefono, $r[ "telefono" ] ]; } 
        if( $socio->correo   != $r[ "correo" ]   ){ $socio->correo   = $r[ "correo" ];   $cambios[] = [ $socio->correo,   $r[ "correo" ] ]; } 
        if( $socio->fechanac != $r[ "fechanac" ] ){ $socio->fechanac = $r[ "fechanac" ]; $cambios[] = [ $socio->fechanac, $r[ "fechanac" ] ]; } 
        if( $socio->curp     != $r[ "curp" ]     ){ $socio->curp     = $r[ "curp" ];     $cambios[] = [ $socio->curp,     $r[ "curp" ] ]; } 

        if( $data->nombre != $r[ "nombre" ] ){ $data->nombre = $r[ "nombre" ]; $cambios[] = [  $data->nombre, $r[ "nombre" ] ]; } 
        if( $data->apellidos[0] != $r[ "apellido1" ] ){ $data->apellidos[0] = $r[ "apellido1" ]; $cambios[] = [ $data->apellidos[0], $r[ "apellido1" ] ]; } 
        if( $data->apellidos[1] != $r[ "apellido2" ] ){ $data->apellidos[1] = $r[ "apellido2" ]; $cambios[] = [ $data->apellidos[1], $r[ "apellido2" ] ]; } 
        if( $data->clabe != $r[ "clabe" ] ){ $data->clabe  = $r[ "clabe" ]; $cambios[] = [ $data->clabe, $r[ "clabe" ] ]; } 
        if( $data->sat->rfc != $r[ "rfc" ] ){ $data->sat->rfc = $r[ "rfc" ]; $cambios[] = [ $data->sat->rfc, $r[ "rfc" ] ]; } 

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
                $pago = model( "PagoModel")->find( $parametros[0] );

                $html .= "
                    <div class=\"text-center\">

                    
                    <h3 class=\"mt-4\">¡FELICIDADES!</h3>
                    <p class=\"mb-4\">Estos son tus ingresos de <span class=\"text-".MODELOS[ $pago[ "modelo_codigo" ] ][ "settings" ][ "color" ]."\"><i class=\"fa fa-".MODELOS[ $pago[ "modelo_codigo" ] ][ "settings" ][ "icono" ]."\"></i> ".MODELOS[ $pago[ "modelo_codigo" ] ][ "nombre" ]."</span><br>para la semana <span class=\"badge bg-gray-500\">".periodo( $pago[ "data" ][ "periodos" ][ "creacion" ] )."</span></p>
                    <p class=\"display-1 mb-4\"><span class=\"badge bg-marine\">$".number_format( $pago[ "data" ][ "cantidades" ][ "total" ], 2 )."</span></p>
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
}

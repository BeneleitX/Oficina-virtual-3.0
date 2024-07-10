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

        $sql = "estatus_codigo = '201-ACTIVO'";
        $this->data[ "bloques" ] = model( "BloqueModel" )->where( $sql , null, false )->orderBy('columna', 'asc')->orderBy('orden', 'asc')->findAll();

        echo template( "dashboard/inicio", $this->data );
    }


    public function save_layout(){
        $bloque = $this->request->getPost( "bloque" );

        $json = $this->data["usuario"]->data;

        if( !isset( $json->layout ) ){
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
                    <p><img src=\"".base_url()."assets/img/rangos/{$rango[ "codigo" ]}.jpg\" class=\"img-fluid col-10 col-sm-6 col-md-4 col-lg-3 px-5\"></p>

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

                    <p class=\"\"><img src=\"".base_url()."assets/img/ov2.png\" class=\"img-fluid p-5\"></p>
                    <h2 class=\"mt-4\">¡BIENVENIDO!</h2>
                    <h5 class=\"mb-4\">¡a tu nueva oficina virtual Beneleit!</h5>
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
                    
                    <h4 class=\"fs-2 mb-3\">Has conseguido <span class=\"text-mustard\">{$parametros[0]}</span> estrella".( $parametros[0] - 1 ? "s" : "" )."<h4><h5>Total acumulado: <span class=\"text-mustard\">{$this->data[ "usuario" ]->data->recompensas->estrellas}</span></h5>
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
                

            case "cash":
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
                                scalar: 2,
                                decay: 0.95,
                                spread: 180,
                                particleCount: 10,
                                origin: { y: -0.3 },
                                startVelocity: -25,
                                shapes: ['square'],
                                colors: ['#009779', '#47c24c', '#8bc34a']
                            });

                            confetti({
                                scalar: 3,
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

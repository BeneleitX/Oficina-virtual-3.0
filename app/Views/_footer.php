
        </div>        

        <script src="<?php echo base_url(); ?>assets/js/popper.js"></script>
        <script src="<?php echo base_url(); ?>assets/js/bootstrap.js"></script>
        <script src="<?php echo base_url(); ?>assets/js/OverlayScrollbars.js"></script>
        <script src="<?php echo base_url(); ?>assets/js/confetti.js"></script>

        <?php 

        $router = \Config\Services::router();
        $_method = $router->methodName();
        $_controller = explode("\\", $router->controllerName()); 

        if( $usuario->id > 0 ){
            $data = (array)$usuario->data;

            if( $data && sizeof( $data[ "splash" ] ) > 0 && !session( "admin" ) ){
                // shuffle( $data[ "splash" ] );
                $splash = array_shift( $data[ "splash" ] );
                $splash->tipo = strtolower( $splash->tipo );

                echo "
                <div class=\"modal fade\" id=\"modal_splash\" tabindex=\"-1\" aria-labelledby=\"add_rolLabel\" aria-hidden=\"true\">
                    <div class=\"modal-dialog modal-fullscreen\">
                        <div class=\"modal-content\">
                            <div class=\"modal-body\">
                                <p class=\"text-center p-5\"><i class=\"fa-solid fa-circle-notch fa-spin\"></i></p>
                                <button type=\"button\" class=\"d-none btn bg-secondary\" data-bs-dismiss=\"modal\" ><i class=\"i-cancelar\"></i> Cerrar</button>
                            </div>
                        </div>
                    </div>
                </div> 
                
                <script>$(document).ready(function(){ modal_splash( '{$splash->tipo}', '".json_encode( $splash->parametros )."' ) });</script>";
                $usuario->data = $data;
                model( "UsuarioModel" )->save( $usuario );
                ?>

                <script>
                    var myModalEl = document.getElementById('modal_splash');
                    myModalEl.addEventListener('hidden.bs.modal', function (event) {
                        clearTimeout(timeout);
                    })

                    function modal_splash( tipo, parametros ){
                        $( '#modal_splash' ).modal( 'show' );

                        $.ajax({
                            url: base_url + "splash", 
                            type: "POST",
                            data: { [csrf_token] : csrf_hash, tipo : tipo, parametros : parametros },
                            success: function( result ){
                                if( !result ){
                                    $( '#modal_splash' ).modal( 'hide' );
                                }
                                else{
                                    $( '#modal_splash .modal-body' ).html( result );
                                }
                            }
                        });
                    }


                </script>
                <?php
            }
            elseif( $_controller[3] != "Socio" AND $usuario->data->verificaciones->{"PASSWORD"} == false ){
                echo "
                <div class=\"modal fade\" id=\"modal_password\" tabindex=\"-1\" aria-labelledby=\"add_rolLabel\" aria-hidden=\"true\">
                    <div class=\"modal-dialog\">
                        <div class=\"modal-content\">
                            <div class=\"modal-header bg-red\">
                                <h5 class=\"text-center text-white\"><i class=\"fa fa-warning text-mustard\"></i> ¡Atención!</h5>
                            </div>
                            <div class=\"modal-body text-center fw-bold\">
                                <p>Tu cuenta tiene password temporal. Es importante que ingreses a tu perfil de socio y lo actualices por uno propio. Esta acción es obligatoria y es necesaria para el cobro de comisiones.</p>
                                <br>
                                <p class=\"\"><a href=\"".base_url( "perfil" )."\" class=\"btn btn-lg btn-danger\">Ir ahora a mi perfil</a><br>
                                <button type=\"button\" class=\"x-none btn bg-secondary\" data-bs-dismiss=\"modal\" ><i class=\"i-cancelar\"></i> En otro momento</button>
                            </div>
                        </div>
                    </div>
                </div> 
                <script>$(document).ready(function(){ $( '#modal_password' ).modal( 'show' ); }); </script>";
            }
        }

        
        ?>
        <script>

            function randomInRange(min, max) {
                        return Math.random() * (max - min) + min;
                    }
                    
            if( <?php echo isset( $print ) ? 0 : 1; ?> ){
               // const osInstance = OverlayScrollbars(document.querySelector('body'), { });
            }

            const base_url = '<?php echo base_url(); ?>',
                csrf_token = '<?php echo csrf_token() ?>',
                csrf_hash  = '<?php echo csrf_hash(); ?>',
                <?php if( isset( $usuario ) ) echo "usuario_id = {$usuario->id},"; ?>
                <?php if( isset( $socio ) ) echo "socio_id = {$socio->id},"; ?>
                meses      = [<?php echo implode( ",", MESES ); ?>],
                loader     = '<i class="fa-solid fa-circle-notch fa-spin"></i>',
                Moneda     = new Intl.NumberFormat('en-US', {
                    style: 'currency',
                    currency: 'USD',
                });

            <?php if( session( "msg" ) !== null ) echo "$(document).ready(function(){ ".alertas( session('msg') )." });"; ?>

        </script>

        <?php


            $mainscript = "assets/js/beneleit.js";
            if(file_exists( $mainscript )) echo "<script src=\"".base_url().$mainscript."?1".filemtime( $mainscript )."\"></script>";

            $includescript = "assets/js/".strtolower( $_controller[3] )."/".strtolower( $_method ).".js";      
            if(file_exists( $includescript )) echo "<script src=\"".base_url().$includescript."?1".filemtime( $includescript )."\"></script>"; 
        ?>

    </body>
</html>
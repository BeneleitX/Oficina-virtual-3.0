            </div>        
        <script src="<?php echo base_url(); ?>assets/js/popper.js"></script>
        <script src="<?php echo base_url(); ?>assets/js/bootstrap.js"></script>
        <script src="<?php echo base_url(); ?>assets/js/OverlayScrollbars.js"></script>

        <script>

            const osInstance = OverlayScrollbars(document.querySelector('body'), { }),
                base_url = '<?php echo base_url(); ?>',
                csrf_token = '<?php echo csrf_token() ?>',
                csrf_hash = '<?php echo csrf_hash(); ?>';

        /*     OverlayScrollbars(osInstance, {
            scrollbars: {
                theme: 'os-theme-light'
            }
            }); */

            $(document).ready(function(){
                $('[data-bs-toggle="tooltip"]').tooltip({
                    container: 'body',
                    placement : 'top'
                });   

                $('[data-bs-toggle="popover"]').popover();

                // SI existe la propiedad de notificaciones en el navegador actual
                if( "Notification" in window ){

                    // Si no estan activas
                    if( Notification.permission !== "granted" ){  
                        // Notification.requestPermission();

                        alerta( 'warning', 'warning', 'No has autorizado las notificaciones. <button class="btn btn-sm btn-warning" onclick="activa_notificaciones()">Autorizar</button>', 'no_notifica' );
                    }
                }

                $( '.submit' ).on( 'click', function(){
                    $( this ).attr( 'disabled', true ).html( '<i class="fa fa-circle-notch fa-spin"></i> Espere...' );
                    $( this ).closest( 'form' ).submit();
                });
            });

            function notify( $mensaje ){
                // SI existe la propiedad de notificaciones en el navegador actual
                if( "Notification" in window ){

                    // Si ya estan activas
                    if( Notification.permission === "granted" ){  
                        // Check whether notification permissions have already been granted;
                        // if so, create a notification
                        const notification = new Notification( $mensaje );
                    }
                }
            }


            function activa_notificaciones(){
                Notification.requestPermission().then((permission) => {
                    // If the user accepts, let's create a notification
                    if (permission === "granted") {
                        $( '#alerta_no_notifica' ).slideUp();
                    }
                });
            }


            function alerta( clase, icono, mensaje, id = null ){

                $( '#contenedor-body' ).prepend( '<div ' + (id ? 'id="alerta_'+id : '') + '" class="alerta alert alert-' + clase + '"><i class="fa fa-' + icono + '"></i> ' + mensaje + '</div>' );

                $(".alerta").fadeTo(5000, 500).slideUp(500, function() {
                    $(".alerta").slideUp();
                });
            }

            <?php if( session( "msg" ) !== null ) echo alertas( session('msg') ); ?>

        </script>

        <?php
        
        $router = \Config\Services::router();
        $_method = $router->methodName();
        $_controller = explode("\\", $router->controllerName()); 
        $includescript = "assets/js/{$_controller[3]}/{$_method}.js";
        
        if(file_exists( $includescript )) echo "<script src=\"".base_url()."assets/js/".strtolower( $_controller[3] )."/{$_method}.js?".filemtime( $includescript )."\"></script>"; ?>

    </body>
</html>
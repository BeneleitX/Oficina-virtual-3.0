        
        <script src="<?php echo base_url(); ?>assets/js/popper.js"></script>
        <script src="<?php echo base_url(); ?>assets/js/bootstrap.js"></script>
        <script src="<?php echo base_url(); ?>assets/js/OverlayScrollbars.js"></script>

        <script>

            const osInstance = OverlayScrollbars(document.querySelector('body'), { });

            OverlayScrollbars(osInstance, {
            scrollbars: {
                theme: 'os-theme-light'
            }
            });

            $(function(){
                $('[data-bs-toggle="tooltip"]').tooltip({
                    container: 'body',
                    placement : 'top'
                });   
            }); 

            // SI existe la propiedad de notificaciones en el navegador actual
            if( "Notification" in window ){

                // Si no estan activas
                if( Notification.permission !== "granted" ){  
                    // Notification.requestPermission();

                    alerta( 'no_notifica', 'warning', 'No has autorizado las notificaciones. <button class="btn btn-sm btn-warning" onclick="activa_notificaciones()">Autorizar</button>' );
                }
            }


            function notify(){
                // SI existe la propiedad de notificaciones en el navegador actual
                if( "Notification" in window ){

                    // Si ya estan activas
                    if( Notification.permission === "granted" ){  
                        // Check whether notification permissions have already been granted;
                        // if so, create a notification
                        const notification = new Notification("Hi there!");
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


            function alerta( id, tipo, mensaje ){

                switch( tipo ){
                    case 'warning' : 
                        icono = 'warning';
                        clase = 'warning';
                        break;

                    default:
                        icono = 'info';
                        clase = 'info';   
                }

                $( '#contenedor-body' ).prepend( '<div id="alerta_' + id + '" class="alert alert-' + clase + '"><i class="fa fa-' + icono + '"></i> ' + mensaje + '</div>' );
            }

        </script>
    </body>
</html>
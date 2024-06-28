
</div>        


            <script src="<?php echo base_url(); ?>assets/js/popper.js"></script>
        <script src="<?php echo base_url(); ?>assets/js/bootstrap.js"></script>
        <script src="<?php echo base_url(); ?>assets/js/OverlayScrollbars.js"></script>

        <script>

            if( <?php echo isset( $print ) ? 0 : 1; ?> ){
                const osInstance = OverlayScrollbars(document.querySelector('body'), { });
            }

            const base_url = '<?php echo base_url(); ?>',
                csrf_token = '<?php echo csrf_token() ?>',
                csrf_hash  = '<?php echo csrf_hash(); ?>',
                <?php if( isset( $usuario ) ) echo "usuario_id = {$usuario->id},"; ?>
                <?php if( isset( $socio ) ) echo "socio_id = {$socio->id},"; ?>
                meses      = [<?php echo implode( ",", MESES ); ?>],
                loader     = '<i class="fa-solid fa-circle-notch fa-spin"></i>';


            function modal_splash( tipo, parametros ){
                switch( tipo ){
                    case 'rango' :
                        $( '#modal_splash' ).modal( 'show' );

                        $.ajax({
                            url: base_url + "splash", 
                            type: "POST",
                            data: { [csrf_token] : csrf_hash, tipo : tipo, parametros : parametros },
                            success: function( result ){
                                $( '#modal_splash .modal-body' ).html( result );
                            }
                        });
                        break;
                }
            }


            // devuelve un número formateado con ceros a la izquierda
            function id( n, digitos = 0 )
            {
                res   = "<span class='fw-light opacity-50'>";

                for( a = 0; a < digitos - (String(n).length); a++)
                    res += "0";
                res += "</span><span class='fw-bold'>";
                res += n;   
                res += "</span>";
                return res;
            }

            let Moneda = new Intl.NumberFormat('en-US', {
                style: 'currency',
                currency: 'USD',
            });

            $(document).ready(function(){
                $('[data-bs-toggle="tooltip"]').tooltip({
                    container: 'body',
                    html: true,
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

            function delay(fn, ms) {
                let timer = 0
                return function(...args) {
                clearTimeout(timer)
                timer = setTimeout(fn.bind(this, ...args), ms || 0)
                }
            }
            

            function mes(mes)
            {
                switch(mes)
                {
                    case 1 : mespal = "enero";      break;
                    case 2 : mespal = "febrero";    break;
                    case 3 : mespal = "marzo";      break;
                    case 4 : mespal = "abril";      break;
                    case 5 : mespal = "mayo";       break;
                    case 6 : mespal = "junio";      break;
                    case 7 : mespal = "julio";      break;
                    case 8 : mespal = "agosto";     break;
                    case 9 : mespal = "septiembre"; break;
                    case 10: mespal = "octubre";    break;
                    case 11: mespal = "noviembre";  break;
                    case 12: mespal = "diciembre";  break;
                }

                return mespal;
            }


            String.prototype.digitoVerificador = function()
            {
                var luhnArr = [[0,1,2,3,4,5,6,7,8,9],[0,2,4,6,8,1,3,5,7,9]], sum = 0;
                this.replace(/\D+/g,"").replace(/[\d]/g, function(c, p, o){
                    sum += luhnArr[ (o.length-p)&1 ][ parseInt(c,10) ]
                });
                return ((10 - sum%10)%10);
            }

            <?php if( session( "msg" ) !== null ) echo alertas( session('msg') ); ?>

        </script>

        <?php
        $router = \Config\Services::router();
        $_method = $router->methodName();
        $_controller = explode("\\", $router->controllerName()); 

        $includescript = "assets/js/".strtolower( $_controller[3] )."/".strtolower( $_method ).".js";
        
        if(file_exists( $includescript )) echo "<script src=\"".base_url()."assets/js/".strtolower( $_controller[3] )."/{$_method}.js?".filemtime( $includescript )."\"></script>"; ?>

    </body>
</html>
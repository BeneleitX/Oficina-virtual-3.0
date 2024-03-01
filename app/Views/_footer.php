        <script src="<?php echo base_url(); ?>assets/js/popper.js"></script>
        <script src="<?php echo base_url(); ?>assets/js/bootstrap.js"></script>
        
        <script src="<?php echo base_url(); ?>assets/js/OverlayScrollbars.js"></script>

        <script>

            const osInstance = OverlayScrollbars(document.querySelector('#contenedor-body'), { });

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

        </script>
    </body>
</html>
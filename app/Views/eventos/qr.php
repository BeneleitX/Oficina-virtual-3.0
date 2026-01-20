<script type="text/javascript" src="https://unpkg.com/qr-code-styling@1.5.0/lib/qr-code-styling.js"></script>

<style>

    .blogo{
        width:225px;
    }
    @media (max-width: 767px) {
       .blogo{
            width:225px;
        }
    }

.box {
  color: #fff;
  text-align: center;
  position: absolute;
  left: 50%;
  top: 50%;
  -webkit-transform: translateX(-50%) translateY(-50%);
  transform: translateX(-50%) translateY(-50%);
}

</style>

<div class="box">
    <img src="<?php echo base_url(); ?>assets/img/logo_blanco.png" class="img-fluid blogo">

    <div class="text-center text-white pt-4">
        <div id="qrcode" class="text-center"></div>
        <p class="mt-5 display-1"><span class="badge" style="background-color: #111b36"><?php echo $usuario->id(); ?></span></p>
        <p class="display-4 mt-3 mb-0"><?php echo $usuario->nombre(); ?></p>
    </div>
</div>

<script>
    $( 'html' ).addClass( 'bg-marine' );
</script>
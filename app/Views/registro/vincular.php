<!-- Third party dependency bundle -->
<!-- <script src="//cdn.nubarium.com/nubSdk/nubSdk@latest/nubSdk-third.min.js"></script> -->
<!-- Library -->
<!-- <script src="//cdn.nubarium.com/nubSdk/nubSdk@latest/nubSdk-biometrics.min.js"></script> -->

<script src="https://cdn.jsdelivr.net/npm/face-api.js@0.22.2/dist/face-api.min.js"></script>
<script src="<?php echo base_url(); ?>assets/js/liveness.js" type="module" defer></script>

<script>
  if( window.self !== window.top ){
    window.top.location = window.location;
  }

  var v_curp = '<?php echo $curp; ?>',
      v_ine  = '<?php echo $ine; ?>',
      v_vida = '<?php echo $vida; ?>',
      curp   = '<?php echo $usuario->curp; ?>';
</script>

<!-- <script type="module" src="https://assets-newww-app.s3.us-west-1.amazonaws.com/tnbioms/tnbioms-livtlwc.js"></script> -->

<style>

    .select-box {
        position: absolute;
        z-index:100;
        width: 16rem;
    }

    .select-box input {
    }

    input[type="tel"] {
        border-radius: 0 .5rem .5rem 0;
    }

    .select-box input:focus {
        border: .1rem solid var(--bs-primary);
    }

    .selected-option {
        background-color: #fff;
        border-radius: .5rem;
        overflow: hidden;

        display: flex;
        justify-content: space-between;
        align-items: center;
    }

    .selected-option div{
        position: relative;
        width: 5rem;
        padding: 0 2.5rem 0 0.5rem;
        text-align: center;
        cursor: pointer;
    }

    .selected-option div::after{
        position: absolute;
        content: "";
        right: .8rem;
        top: 50%;
        transform: translateY(-50%) rotate(45deg);
        
        width: .8rem;
        height: .8rem;
        border-right: .12rem solid var(--bs-primary);
        border-bottom: .12rem solid var(--bs-primary);

        transition: .2s;
    }

    .selected-option div.active::after{
        transform: translateY(-50%) rotate(225deg);
    }

    .select-box .options {
        position: relative;
        
        width: 100%;
        background-color: #fff;
        border-radius: .5rem;

        display: none;
    }

    .select-box .options.active {
        display: block;
    }

    .select-box .options::before {
        position: absolute;
        content: "";
        left: 1rem;
        top: -1.2rem;

        width: 0;
        height: 0;
        border: .6rem solid transparent;
        border-bottom-color: var(--bs-primary);
    }


    .select-box ol {
        list-style: none;
        max-height: 15rem;
        overflow: overlay;
        margin:0;
        padding: 0 0.3rem;
    }


    .select-box ol::-webkit-scrollbar {
        width: 0.6rem;
    }

    .select-box ol::-webkit-scrollbar-thumb {
        width: 0.4rem;
        height: 3rem;
        background-color: #ccc;
        border-radius: .4rem;
    }

    .select-box ol li {
        padding: 0.3rem;
        display: flex;
        justify-content: space-between;
        cursor: pointer;
    }

    .select-box ol li.hide {
        display: none;
    }

    .select-box ol li:not(:last-child) {
        border-bottom: .1rem solid #eee;
    }

    .select-box ol li:hover {
        background-color: lightcyan;
    }

    .select-box ol li .country-name {
        margin-left: .4rem;
    }

    .wizard {
        font-family: 'Roboto', sans-serif;
        font-size: 16px;
        font-weight: 300;
        color: #888;
        -- line-height: 30px;
    }


    .f1-steps { overflow: xhidden; position: relative; margin-top: 15px; text-align:center }
    .f1-progress { position: absolute; top: 28px; left: 0; width: 100%; height: 5px; background: #ccc; }
    .f1-progress-line { position: absolute; top: 0; left: 0; width: <?php echo $bar_inicial; ?>%;height: 5px; background: var(--bs-teal); }

    .f1-step { z-index:0; position: relative; float: left; width: <?php echo $bar_inicial; ?>%; padding-left: calc( <?php echo $bar_inicial/2; ?>% - 25px ); }

    .f1-step-icon {
        width: 50px; height: 50px; margin-top: 4px; background: #ccc;
        font-size: 22px; color: #fff; line-height: 55px;
        margin-left:0px;
        -moz-border-radius: 50%; -webkit-border-radius: 50%; border-radius: 50%;
    }
    .f1-step.activated .f1-step-icon {
        background: #fff; border: 5px solid var(--bs-teal); color: var(--bs-teal);line-height: 45px;
    }
    .f1-step.active .f1-step-icon { background: var(--bs-teal);}

    .f1-step p { color: #ccc; }
    .f1-step.activated p { color: var(--bs-teal); }
    .f1-step.active p { color: var(--bs-teal); }
    .f1 fieldset { display: none; text-align: left; }

    @media (max-width: 767px) {
        .f1-progress-line { height: 3px; }
        .f1-progress { position: absolute; top: 18px; left: 0; width: 100%; height: 3px; background: #ddd; }
        .f1-step { padding: 0; }
        .f1-step-icon {
            display: inline-block; width: 30px; height: 30px; margin-top: 4px; 
            font-size: 16px; color: #fff; line-height: 32px; margin-left:-6px
            -moz-border-radius: 50%; -webkit-border-radius: 50%; border-radius: 50%;
        }
        .f1-step.activated .f1-step-icon {
            border: 3px solid var(--bs-teal); line-height: 24px;
        }    
    }

    #formulario {
        margin-top: 30px;
        border: 1px solid #ccc;
        background:white;
        padding: 20px;
        border-radius: 5px;
    }

    .zflared {
        position: relative;
    }

    .zflared::after {
        position: absolute;
        content: "";
        top: 0;
        left: 0;
        right: 0;
        z-index: -1;
        height: 100%;
        width: 100%;
        transform: scale(0.7) translateZ(0);
        filter: blur(15px);
        background: linear-gradient(to left, var(--bs-teal), var(--bs-blue), var(--bs-purple) );
        background-size: 200% 200%;
        -webkit-animation: animateGlow 3.25s linear infinite;
                animation: animateGlow 3.25s linear infinite;
    }

    @-webkit-keyframes animateGlow {
        0% {
            background-position: 0% 50%;
        }
        100% {
            background-position: 200% 50%;
        }
    }

    @keyframes animateGlow {
        0% {
            background-position: 0% 50%;
        }
        100% {
            background-position: 200% 50%;
        }
    }

    .es-invalido {
        border-color: var(--bs-red) !important;
    }

    .es-invalido input {
        padding-right: calc(1.5em + 0.75rem);
        background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 12 12' width='12' height='12' fill='none' stroke='%23dc3545'%3e%3ccircle cx='6' cy='6' r='4.5'/%3e%3cpath stroke-linejoin='round' d='M5.8 3.6h.4L6 6.5z'/%3e%3ccircle cx='6' cy='8.2' r='.6' fill='%23dc3545' stroke='none'/%3e%3c/svg%3e");
        background-repeat: no-repeat;
        background-position: right calc(0.375em + 0.1875rem) center;
        background-size: calc(0.75em + 0.375rem) calc(0.75em + 0.375rem);        
    }

    .paso{
        display: none;
    }

</style>

<script src="https://code.iconify.design/3/3.1.0/iconify.min.js"></script>

<div class="container wizard d-none">
    <div class="row">
        <div class="col-sm-10 offset-sm-1 col-md-8 offset-md-2 col-lg-6 offset-lg-3">

            <p class="text-center"><img src="<?php echo base_url(); ?>assets/img/logo_color.png" class="w-50 px-4 pt-4 m-0">
            <br><?php echo $titulo; ?></p>

    
            <div class="f1-steps">
                <div class="f1-progress">
                    <div class="f1-progress-line" data-now-value="0" style="width: 0%;"></div>
                </div>

                <?php
                foreach( $pasos as $k => $paso ){
                    $estatus = "";

                    if( $paso['inicio'] ?? false ){
                     //   $estatus = "active flared";
                    } 

                    echo "\n<div step=\"{$k}\" class=\"f1-step {$estatus}\" data-bs-toggle=\"tooltip\" title=\"{$paso['titulo']}\"><div class=\"f1-step-icon \"><i class=\"fa {$paso['icono']}\"></i></div></div>";
                }
                ?>
                
            </div>
        </div>
        
        <div class="col-lg-10 offset-lg-1 col-xl-8 offset-xl-2">
            <h4 class="d-none text-center mb-5 mt-3" id="titulo_paso"></h4>

            <div id="formulario">
                
                <!-- login -->
                <div style="display:none" class="paso w-100" step="0">    
                    <?php if( $usuario->id ){ ?>
                    <div class="row mb-3">
                        <div class="col-md-8 col-lg-9">
                            <?php echo "<p class=\"mb-1\">".$usuario->avatar( 32 )." <span class=\"text-marine\">".$usuario->nombre()."</span></p><p>".$usuario->id( false, "marine" )." <span class=\"badge bg-gray-700\">{$usuario->curp}</span> ".$usuario->bandera()."</p>"; ?>
                        </div>
                        <div class="col-md-4 col-lg-3 text-end">
                            <a href="<?php echo base_url(); ?>logout/white" class="<?php echo $usuario->id ? "" : "d-none"; ?> btn btn-sm btn-outline-danger btn-logout">Cambiar de socio <i class="fa fa-xmark"></i></a>
                        </div>
                    </div>
                    <?php } ?>
                    <h5>¡Bienvenido!</h5>
                    <p>En estos sencillos pasos, te guiaremos a través del proceso de vinculación de tu cuenta con tu documentación oficial. Es importante que tomes las siguientes consideraciones:
                        <ul>
                            <li>Este proceso es únicamente para socios ya registrados previamente.</li>
                            <li>La vinculación la debe hacer el titular. No es posible que puedas vincular cuentas de terceros.</li>
                            <li>Debes contar con una buena conexión a Internet</li>
                            <li>Realiza la vinculación desde un dispositivo con cámara frontal y bocinas o auriculares</li>
                            <li>Tener a la mano tu credencial de elector con fotografía</li>
                        </ul>
                    </p>

                    <?php
                    if( $usuario->id ){
                        if( $usuario->data->ubicacion->origen == "MX" ){
                            if( $curp && $ine && $vida ){
                                echo "\n<div class=\"alert alert-success fw-bold mt-4 mb-0\">Tus datos ya han sido vinculados</div>";
                            }
                        }
                        else{
                            echo "\n<div class=\"alert alert-danger fw-bold mt-4 mb-0\">Esta herramienta es únicamente para personas en México</div>";
                        }
                    }
                    else{
                        echo "\n<p class=\"fw-bold text-marine mt-4 mb-0\">Para empezar el proceso, inicia sesión con tus datos de acceso a Beneleit</p>";
                    }
                    ?>
                </div>
                
                <!-- datos de contacto -->
                <div style="display:none" class="paso w-100" step="1">    
                    <p class="">Asegurate de que tu CURP esté bien capturada y coincida con tu documentación oficial. </p>
                    <?php if( $usuario->id ){ ?>
                    <div class="row">
                        <div class="col-md-6 mt-3">
                            <div id="curp_group">
                                <p class="fw-bold text-marine mb-1">CURP asociada:</p>
                               
                                <div class="input-group">
                                     <input <?php echo strlen( $usuario->data->valida_curp->codigoValidacion ?? "" ) > 5 ? "disabled" : ""; ?> name="curp" id="curp" type="text" class="form-control" maxlength="18" value="<?php echo $usuario->curp; ?>" />

                                     <?php if( strlen( $usuario->data->valida_curp->codigoValidacion ?? "" ) > 5 ){ 
                                        echo "\n<button class=\"btn btn-success\" disabled  type=\"button\"><i class=\"fa fa-check\"></i> Verificado</button>";
                                     }
                                     else{
                                        echo "\n<button id=\"valida_curp\" class=\"btn btn-outline-warning\" type=\"button\"><i class=\"fa fa-magnifying-glass\"></i> Verificar</button>";
                                     } ?>
                                </div>
                                                                
                                <p class="small text-red m-0" id="curp_error"></p>
                            </div>
                        </div>                        
                    </div>  
                    <?php } ?>
                </div>

                <!-- INE -->
                <div style="display:none" class="paso w-100" step="2">    
                    <p>Utilizando la cámara de tu dispositivo, toma fotografías de tu documento de identificación oficial con fotografía, asegurate de que sean claras y legibles.</p><p class="fw-bold text-marine">Carga las fotos haciendo click en los recuadros</p>
                    
                    <?php 
                        $f = null;
                        $r = null;

                        $iv = strlen( $usuario->data->valida_ine->codigoValidacion ?? "" ) > 5;

                        if( $usuario->id ){ 
                            $f = $usuario->data->credencial->frente;
                            $r = $usuario->data->credencial->reverso;
                        }
                    ?>

                    <div class="row my-3">
                        <div class="col-md-6">
                            <div class="card px-5" style="position:relative; aspect-ratio: 7/4">
                                <img id="shot_frente" src="<?php echo base_url().( "data/{$usuario->id}/ine/".$f ?? "assets/img/frente.png" ); ?>" class="<?php echo $f ? "" : "grayscale"; ?> rounded-2 my-3 img-fluid w-100" alt="">
                                <div class="vertical-center" style="position:absolute">
                                    <?php if( !$iv ){ ?><button onclick="shoot( 'frente' )" class="btnc center-btn btn btn-warning" id="frente" style="display:none"><i class="fa fa-camera"></i> Foto frente</button><?php } ?>
                                </div>
                            </div>
                            
                        </div>

                        <div class="col-md-6">
                            <div class="card px-5" style="position:relative; aspect-ratio: 7/4">
                                <img id="shot_reverso" src="<?php echo base_url().( "data/{$usuario->id}/ine/".$r ?? "assets/img/reverso.png" ); ?>" class="<?php echo $f ? "" : "grayscale"; ?> rounded-2 my-3 img-fluid w-100" alt="">
                                <div class="vertical-center" style="position:absolute">
                                    <?php if( !$iv ){ ?>
                                    <button onclick="shoot( 'reverso' )" class="btnc center-btn btn btn-warning" id="reverso" style="display:none"><i class="fa fa-camera"></i> Foto reverso</button><?php } ?>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <p class="mb-1">
                        <?php 
                        if( $iv ){
                            echo "<button class=\"btn btn-success\" disabled><i class=\"fa fa-check\"></i> Verificado</button>";
                        }
                        else{
                            echo "<button id=\"valida_ine\" class=\"btn btn-outline-warning\"><i class=\"fa fa-magnifying-glass\"></i> Verificar documento</button>";
                        }
                        ?>
                        
                        <input class="d-none" name="credencial">
                    </p>
                    <p class="small text-red m-0" id="credencial_error"></p>
                        
                </div>

                <!-- Validación de persona -->
                <div style="display:none" class="paso w-100" step="3">   

                <?php /*************************************************************/ ?>

    <!-- Main Content -->
            <p class="fw-bold mb-1">Prueba de vida</p>

            <!-- Consent Form -->
            <div id="consent-form">
                <div class="my-4">
                    <ul>
                        <li>La prueba de vida es un test para certificar que el registro está siendo solicitado por una persona real.</li>
                        <li>Valida que tu imagen no se trata de una fotografía o video previamente grabado.</li>
                        <li>Al comenzar, se activará tu cámara y deberás seguir unas instrucciones sencillas</li>
                        <li>La información se procesa totalmente desde tu dispositivo, no guardaremos ninguna imagen tuya en nuestros servidores.</li>
                    </ul>
                </div>
                <form>
                    <button type="submit" class="btn btn-primary">
                        <i class="fa fa-video"></i> Comenzar
                    </button>
                </form>
            </div>

            <!-- Loading Section -->
            <div id="loading-section" class="text-center d-none">
                    <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-blue-600 mx-auto mb-4"></div>
                    <p class="text-center"><i class="fas fa-spinner fa-spin" style="font-size:50px"></i></p>
                    <p class="">Cargando...</p>
            </div>

            <!-- Webcam Section -->
            <div id="webcam-section" class="d-none">
                <div style="position:relative" class="col-lg-6 offset-lg-3">
                    <video id="webcam" style="width:100%" class="rounded" autoplay playsinline></video>
                        
                    <div style="width:100%; position:absolute; top:0; left:0;"><img style="width:100%" src="<?php echo base_url(); ?>assets/img/mask.png"></div>
                    
                </div>
                <h5 class="text-center text-red fw-bold">Por favor acercate a la cámara</h5>
            </div>

            <!-- Results Section -->
            <div id="results-section" class="d-none">
                <div class="text-center">
                    <i style="font-size:150px" class="fa fa-check-circle text-green my-5"></i>
                    <h5>
                    Prueba completada con éxito</h5>
                </div>
            </div>


<?php /*************************************************************/ ?>
                    
                    <p class="small text-red m-0 mt-1" id="vida_error"></p>
                </div>

                <!-- Terminos y condiciones -->
                <div style="display:none" class="paso w-100" step="4">    
                    <p class="fw-bold mb-1">¡Muchas gracias!</p><p class="fw-bold text-marine mb-1">La vinculación de tus datos ha sido finalizada exitosamente</p>



                    <p class="small text-red m-0 mt-1" id="tyc_error"></p>
                </div>
            </div> 
            
        </div> 
        
    </div>

    <?php if( !$usuario->id ){ ?>
        <iframe src="login/white" frameborder="0" width="100%" height="300px" scrolling="no" style="overflow:hidden"></iframe>
    <?php }
    else{
        echo "<div class=\"mt-4 mb-5 text-center\" id=\"botonera_interactiva\" style=\"display:none\">";
        if(  $usuario->data->ubicacion->origen != "MX" || ( $curp && $ine && $vida ) ){
            echo "\n<a href=\"".base_url()."inicio\" class=\"btn btn-secondary btn-back\">Ir a oficina virtual</a>";
    }
    else{ 
        ?>
   
        <button type="button" class="btn btn-outline-secondary2 btn-previous"><i class="fa fa-undo"></i> Anterior</button>
        <button type="button" class="btn btn-secondary btn-next">Comenzar proceso <i class="fa fa-arrow-right"></i></button>
        <button type="button" class="btn btn-primary btn-end" style="display:none">finalizar y salir <i class="fa fa-arrow-right"></i></button>
              
    <?php }

    echo "</div>  ";
    } ?>
    
    
</div>

<div class="modal" tabindex="-1" id="camara_ine">
	<div class="modal-dialog modal-fullscreen">
		<div class="modal-content">
            <iframe id="camara" width="100%" height="100%" src="" frameborder="0" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture" allowfullscreen></iframe>
		</div>
	</div>
</div>


<script>
    var pasos = <?php echo json_encode( $pasos ); ?>,
        tempID = '<?php echo $tempID; ?>',
        jwt   = <?php echo json_encode( $jwt ); ?>,
        bar_inicial = <?php echo $bar_inicial; ?>,
        target_post = '<?php echo base_url( "procesa_vinculacion" ); ?>';


var paso_activo = 0,
    request = {
        "usuario"   : <?php echo $usuario->id; ?>,
        "curp"         : <?php echo strlen( $usuario->curp ?? "" ) > 5 ? "'".$usuario->curp."'" : "null"; ?>,

        "valida_curp"  : <?php echo strlen( $usuario->data->valida_curp->codigoValidacion ?? "" ) > 5 ? "'".$usuario->data->valida_curp->codigoValidacion."'" : "null"; ?>,
        "valida_vida"  : <?php echo strlen( $usuario->data->valida_vida->codigoValidacion ?? "" ) > 5 ? "'".$usuario->data->valida_vida->codigoValidacion."'" : "null"; ?>,
        "valida_ine"   : <?php echo strlen( $usuario->data->valida_ine->codigoValidacion ?? "" ) > 5 ? "'".$usuario->data->valida_ine->codigoValidacion."'" : "null"; ?>,

        "imagenes": {
            "frente"   : <?php echo ( $usuario->data->credencial->frente ?? 0 ) ? "true" : "null"; ?>,
            "reverso"  : <?php echo ( $usuario->data->credencial->reverso ?? 0 ) ? "true" : "null"; ?>
        },
        "curp_verificado"  : <?php echo strlen( $usuario->data->valida_curp->codigoValidacion ?? "" ) > 5 ? 1 : 0; ?>,
        "vida_verificado"  : <?php echo strlen( $usuario->data->valida_vida->sessionToken ?? "" ) > 5 ? 1 : 0; ?>,
        "ine_verificado"   : <?php echo strlen( $usuario->data->valida_ine->codigoValidacion ?? "" ) > 5 ? 1 : 0; ?>
    };

</script>
<!-- Third party dependency bundle -->
<!-- <script src="//cdn.nubarium.com/nubSdk/nubSdk@latest/nubSdk-third.min.js"></script> -->
<!-- Library -->
<!-- <script src="//cdn.nubarium.com/nubSdk/nubSdk@latest/nubSdk-biometrics.min.js"></script> -->

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

<div class="container wizard">
    <div class="row">
        <div class="col-sm-10 offset-sm-1 col-md-8 offset-md-2 col-lg-6 offset-lg-3">

            <p class="text-center"><img src="<?php echo base_url(); ?>assets/img/logo_color.png" class="w-50 px-4 pt-4 m-0">
            <br>Registro de nuevo socio</p>

    
            <div class="f1-steps">
                <div class="f1-progress">
                    <div class="f1-progress-line" data-now-value="0" style="width: 0%;"></div>
                </div>

                <?php
                foreach( $pasos as $k => $paso ){
                    $estatus = "";

                    if( $paso['inicio'] ?? false ){
                        $estatus = "active flared";
                    } 

                    echo "\n<div step=\"{$k}\" class=\"f1-step {$estatus}\" data-bs-toggle=\"tooltip\" title=\"{$paso['titulo']}\"><div class=\"f1-step-icon \"><i class=\"fa {$paso['icono']}\"></i></div></div>";
                }
                ?>
                
            </div>
        </div>
        
        <div class="col-lg-10 offset-lg-1 col-xl-8 offset-xl-2">
            <h4 class="d-none text-center mb-5 mt-3" id="titulo_paso"></h4>

            <input type="hidden" name="origen" value="">

            <div id="formulario">
                
                <!-- pais y curp -->
                <div class="paso" step="0">    
                    <h5>¡Bienvenido!</h5>
                    <p>Es importante asegurate en cada paso que la información que proporciones sea correcta, y realizar el registro desde un dispositivo con cámara frontal, ya que más adelante necesitaremos validar tu identidad.</p><p class="fw-bold mb-1">Para comenzar el proceso, selecciona tu país:</p>
                    <div class="row">
                        <div class="col-md-6 col-lg-4">
                            <div class="campo_nacionalidad rounded border p-0 w-100" style="border: 2px solid var(--bs-border-color);" >
                                <div class="input-group mb-0">
                                    <div class="selected-option" tipo="nacionalidad"><div></div></div>
                                    <input type="text" class="form-control border-0 text-teal" name="nacion" value="" placeholder="" id="nacion" readonly>
                                </div>

                                <div class="select-box" id="nacionalidad">
                                    <div class="options">
                                        <input type="text" class="search-box form-control" placeholder="Buscar por país">
                                        <ol></ol>
                                    </div>
                                </div>
                            </div>
                            <p class="small text-red m-0" id="nacionalidad_error"></p>
                        </div>
                    </div>

                    <div id="curp_group" class="mt-4 d-none">
                        <div class="row">

                            <div class="col-md-6 col-lg-6">
                                <p class="fw-bold mb-1">Por favor, proporciona tu CURP:</p>
                               
                                <div class="input-group">
                                     <input name="curp" id="curp" type="text" class="form-control" maxlength="18" placeholder="" />
                                    <button id="valida_curp" class="btn btn-outline-warning" type="button"><i class="fa fa-magnifying-glass"></i> Verificar</button>
                                </div>
                                                                
                                <p class="small text-red m-0" id="curp_error"></p>
                            </div>

                            <div class="col-md-6 col-lg-6" id="datos_curp">&nbsp;<br>
                                <div class="py-2" id="curp_card" style="display:none">
                                    <span class="badge fs-5 bg-gray-200 py-2 px-3 text-gray-500"></span>
                                </div>
                            </div>                            
                        </div>
                    </div>
                    <div id="dni_group" class="mt-4 d-none">
                        <p class="fw-bold mb-1">Por favor, proporciona el número de tu identificación oficial (DNI) exactamente como aparece en tu identificación oficial:</p>
                        <div class="row">
                            <div class="col-md-6 col-lg-4">
                                <input name="dni" id="dni" type="text" class="form-control" maxlength="18" placeholder="" />
                                <p class="small text-red m-0" id="dni_error"></p>
                            </div>
                        </div>
                    </div>                            

                </div>

                <!-- datos generales -->
                <div class="paso" step="1">    
                    <p class="m-0" id="instrucciones_datos"></p>
                    <div class="row">
                        <div class="col-md-6 mt-4 col-lg-4">
                            <p class="fw-bold mb-1">Nombre(s)</p>
                            <input name="nombre" type="text" class="form-control" placeholder="" />
                            <p class="small text-red m-0" id="nombre_error"></p>
                        </div>

                        <div class="col-md-6 mt-4 col-lg-4">
                            <p class="fw-bold mb-1">Primer apellido</p>
                            <input name="apellido1" type="text" class="form-control" placeholder="" />
                            <p class="small text-red m-0" id="apellido1_error"></p>
                        </div>

                        <div class="col-md-6 mt-4 col-lg-4">
                            <p class="fw-bold mb-1">Segundo apellido</p>
                            <input name="apellido2" type="text" class="form-control" placeholder="" />
                            <p class="small text-red m-0" id="apellido2_error"></p>
                        </div>

                        <div class="col-md-6 mt-4 col-lg-4">
                            <p class="fw-bold mb-1">Fecha de nacimiento</p>
                            <input name="fechanac" type="date" class="form-control" placeholder="" />
                            <p class="small text-red m-0" id="fechanac_error"></p>
                        </div>
                        
                        <div class="col-md-6 mt-4 col-lg-4">
                            <p class="fw-bold mb-1">Sexo</p>
                            <select name="sexo" class="form-select">
                                <option value="">Selecciona una opción</option>
                                <option value="H">HOMBRE</option>
                                <option value="M">MUJER</option>
                            </select>
                            <p class="small text-red m-0" id="sexo_error"></p>
                        </div>
                    </div>  
                </div>

                
                <!-- datos de contacto -->
                <div class="paso" step="2">    
                    <p class="">Tus datos de contacto serán esenciales para finalizar el proceso de registro. Asegurate de que tu correo electrónico esté activo, pues ahi recibirás tus datos de acceso a la plataforma.</p>
                    <div class="row">
                        <div class="col-md-6">
                            <p class="fw-bold mb-1">Correo electrónico</p>
                            <div class="input-group">
                                <input name="correo" type="email" class="form-control" autocomplete="off" />
                                <button id="valida_correo" class="btn btn-outline-warning" type="button"><i class="fa fa-magnifying-glass"></i> Verificar</button>
                            </div>                            
                            <p class="small text-red m-0" id="correo_error"></p>
                        </div>

                    </div>  
                </div>

                <!-- patrocinador -->
                <div class="paso" step="3">    
                    <p class="m-0"> Tu cuenta estará vinculada a un patrocinador. Por favor verifica que el número de socio Beneleit que te ha proporcionado tu patrocinador sea el correcto. recuerda que una vez creada tu cuenta, no podrás modificarlo.</p>
                    <div class="row mt-3">
                     
                        <div class="col-md-6 col-lg-6">
                            <p class="fw-bold mb-1">No. de patrocinador</p>
                            
                            <div class="input-group">
                                    <input name="patrocinador" id="patrocinador" type="text" class="form-control" maxlength="18" placeholder="" />
                                <button id="valida_pat" class="btn btn-outline-warning" type="button"><i class="fa fa-magnifying-glass"></i> Verificar</button>
                            </div>
                                                            
                            <p class="small text-red m-0" id="patrocinador_error"></p>
                        </div>

                        <div class="col-md-6 col-lg-6" id="datos_patrocinador">&nbsp;<br>
                            <div class="py-2" id="pat_card" style="display:none">
                                <div class="col-9 fs-6" id="pat_nombre"></div>
                            </div>
                        </div>   

                    </div>  
                </div>

                <!-- INE -->
                <div class="paso" step="4">    
                    <p>Utilizando la cámara de tu dispositivo, toma fotografías de tu documento de identificación oficial con fotografía. Asegurate de que sean claras y legibles.</p>

                    <div class="row mt-3">
                        <div class="col-md-6">
                            <div class="card px-5" style="position:relative; aspect-ratio: 7/4">
                                <img id="shot_frente" src="<?php echo base_url(); ?>assets/img/frente.png" class="grayscale rounded-2 my-3 img-fluid w-100" alt="">
                                <div class="vertical-center" style="position:absolute">
                                    <button onclick="shoot( 'frente' )" class="center-btn btn btn-warning" id="frente" style="display:none"><i class="fa fa-camera"></i> Tomar foto</button>
                                </div>
                            </div>
                            <p class="text-center mt-1 mb-1 fw-bold">Frente</p>
                            <p class="small text-center m-0" id="frente_error"></p>
                        </div>

                        <div class="col-md-6">
                            <div class="card px-5" style="position:relative; aspect-ratio: 7/4">
                                <img id="shot_reverso" src="<?php echo base_url(); ?>assets/img/reverso.png" class="grayscale rounded-2 my-3 img-fluid w-100" alt="">
                                <div class="vertical-center" style="position:absolute">
                                    <button onclick="shoot( 'reverso' )" class="center-btn btn btn-warning" id="reverso" style="display:none"><i class="fa fa-camera"></i> Tomar foto</button>
                                </div>
                            </div>
                            <p class="text-center mt-1 mb-1 fw-bold">Reverso</p>
                            <p class="small text-center m-0" id="reverso_error"></p>
                        </div>
                    </div>
                    
                </div>

                <!-- Terminos y condiciones -->
                <div class="paso w-100" step="5">    
                    <p class="fw-bold mb-1">Por último, lee y acepta nuestros Términos y Condiciones para finalizar el proceso y generar tu cuenta de usuario:</p>
                    <textarea style="font-size:0.8rem; font-family: monospace" class="form-control small w-100" readonly id="terminos" rows="15"><?php echo $terminos; ?></textarea>
                </div>

                <!-- INE -->
                <div class="paso" step="9">    
                    <div id="id_component"></div>

                    <div class="row">
                        <div class="col-6"><img id="image-front" style="max-height:260px" /></div>
                        <div class="col-6"><img id="image-back" style="max-height:260px" /></div>
                    </div>    
                    
                </div>

            </div> 
        </div> 
    <div class="mt-4 mb-5 text-center">
        <button type="button" class="btn btn-outline-secondary2 btn-previous"><i class="fa fa-undo"></i> Anterior</button>
        <button type="button" class="btn btn-secondary btn-next" ajax="false">Siguiente <i class="fa fa-arrow-right"></i></button>
        <button type="button" class="btn btn-primary btn-end">Aceptar y finalizar <i class="fa fa-check"></i></button>
    </div>            
    
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
        target_post = '<?php echo base_url( "procesa_registro" ); ?>';
</script>
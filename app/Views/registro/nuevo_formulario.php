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
        line-height: 30px;
    }


    .f1-steps { overflow: xhidden; position: relative; margin-top: 15px; text-align:center }
    .f1-progress { position: absolute; top: 28px; left: 0; width: 100%; height: 5px; background: #ccc; }
    .f1-progress-line { position: absolute; top: 0; left: 0; height: 5px; background: var(--bs-teal); }
    .f1-step { z-index:0; position: relative; float: left; width: 14.28%; padding: 0 5px; }
    .f1-step-icon {
        width: 50px; height: 50px; margin-top: 4px; background: #ccc;
        font-size: 22px; color: #fff; line-height: 55px;
        
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

        .f1-step-icon {
            display: inline-block; width: 30px; height: 30px; margin-top: 4px; 
            font-size: 16px; color: #fff; line-height: 32px;
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

</style>

<script src="https://code.iconify.design/3/3.1.0/iconify.min.js"></script>

<div class="container wizard">
    <div class="row">
        <div class="col-sm-10 offset-sm-1 col-md-8 offset-md-2 col-lg-6 offset-lg-3">

            <p class="text-center"><img src="<?php echo base_url(); ?>assets/img/logo_color.png" class="w-50 px-4 pt-4 m-0">
            <br>Registro de nuevo socio</p>

    
            <div class="f1-steps">
                <div class="f1-progress">
                    <div class="f1-progress-line" data-now-value="14.285" style="width: 14.285%;"></div>
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
            <h4 class="text-center mb-5 mt-3" id="titulo_paso"></h4>
            <div id="formulario">
                

                <div class="paso" step="0">    
                            <h5>¡Bienvenido!</h5>
                            <p>Es importante asegurate en cada paso que la información que proporciones sea correcta, y realizar el registro desde un dispositivo con cámara frontal, ya que más adelante necesitaremos validar tu identidad.</p><p class="fw-bold">Para comenzar el proceso, selecciona tu país de residencia:</p>
                            <div class="rounded border border-1 p-1" style="width:300px">
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

                </div>

                <div class="paso" step="1">    
                    <label>Datos personales</label>
                    <div id="datos_personales"></div>
                </div>

            </div> 
        </div> 
    <div class="mt-4 mb-5 text-center">
        <button type="button" class="btn btn-outline-secondary2 btn-previous"><i class="fa fa-undo"></i> Anterior</button>
        <button type="button" class="btn btn-secondary btn-next">Siguiente <i class="fa fa-arrow-right"></i></button>
        <button type="button" class="btn btn-primary btn-end">Aceptar y finalizar <i class="fa fa-check"></i></button>
    </div>            
    
</div>


<script>
    var pasos = <?php echo json_encode( $pasos ); ?>;
</script>
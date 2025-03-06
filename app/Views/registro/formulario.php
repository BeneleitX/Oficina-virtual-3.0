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
        background-color: #eee;
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

</style>

<script src="https://code.iconify.design/3/3.1.0/iconify.min.js"></script>

<div class="row">
    <div class="col-8 offset-2 col-md-4 offset-md-4 col-lg-4 offset-lg-4 col-xl-2 offset-xl-5" style="padding-top:50px">
        <p class="text-center"><img src="<?php echo base_url(); ?>assets/img/logo_color.png" class="w-50"></p>
    </div>
    <h4 class="text-center mb-4"><?php echo $titulo; ?></h4>
</div>
        
<form method="post" action="<?php echo base_url( "procesa_registro" ); ?>" id="procesa_registro">
    <?php echo csrf_field() ?>
    <input type="hidden" name="pais" value="">
    <input type="hidden" name="origen" value="">
    <input type="hidden" name="code" value="">
    <div class="row">
        <div class="col-md-4">
            <div class="card mb-3"><div class="card-header"><h5>1. Nombre de nuevo socio</h5>Escribe tu nombre tal como aparece en tu identificación oficial vigente</div><div class="card-body">

            <div class="alert alert-danger mb-3">
                    <i class="fa fa-circle-info"></i> El registro no está disponible para ciudadanos de Estados Unidos de América.
                </div>

                <label>Nombre</label>
                <input type="text" style="text-transform: uppercase;" class="form-control <?php echo session( "errors.nombre" ) ? "is-invalid" : ""; ?>" name="nombre" value="<?php echo old( "nombre" ); ?>" placeholder="">
                <p class="small text-red"><?php echo session( "errors.nombre" ); ?></p>

                <label>Primer apellido</label>
                <input type="text" style="text-transform: uppercase;" class="form-control <?php echo session( "errors.apellido1" ) ? "is-invalid" : ""; ?>" name="apellido1" value="<?php echo old( "apellido1" ); ?>" placeholder="">
                <p class="small text-red"><?php echo session( "errors.apellido1" ); ?></p>
            
                <label>Segundo apellido</label>
                <input type="text" style="text-transform: uppercase;" class="form-control <?php echo session( "errors.apellido2" ) ? "is-invalid" : ""; ?>" name="apellido2" value="<?php echo old( "apellido2" ); ?>" placeholder="">
                <p class="small text-red"><?php echo session( "errors.apellido2" ); ?></p>

            </div></div>
            
        </div>
        <div class="col-md-4">
            <div class="card mb-3"><div class="card-header"><h5>2. Datos de contacto</h5>Es necesario que sean reales y activos para poder enviarte información complementaria a tu registro</div><div class="card-body">

                <label>Correo electrónico</label>
                <input type="email" class="form-control <?php echo session( "errors.correo" ) ? "is-invalid" : ""; ?>" name="correo" value="<?php echo old( "correo" ); ?>" placeholder="">
                <p class="small text-red"><?php echo session( "errors.correo" ); ?></p>

                <label>Teléfono</label>
                <div class="input-group mb-0 w-75">
                    <div class="selected-option" tipo="telefono"><div></div></div>
                    <span class="input-group-text bg-gray-400 border-0 text-marine fw-bold" id="codigo"></span>
                    <input type="text" class="form-control <?php echo session( "errors.celular" ) ? "is-invalid" : ""; ?>" name="celular" value="<?php echo old( "celular" ); ?>" placeholder="" id="celular">
                </div>

                <div class="select-box" id="telefono">
                    <div class="options">
                        <input type="text" class="search-box form-control" placeholder="Buscar por país">
                        <ol></ol>
                    </div>
                </div>
                
                <p class="small text-red"><?php echo session( "errors.celular" ); ?></p>

                <div class="alert alert-warning m-0">
                    <i class="fa fa-circle-info"></i> Selecciona el país de tu línea telefónica y escribe con cuidado tu número, ya que será nuestro medio de contacto contigo.
                </div>

            </div></div>
        </div>
        <div class="col-md-4">
            <div class="card mb-3"><div class="card-header"><h5>3. Validación de cuenta</h5>Si no conoces tu CURP puedes consultarlo <a class="" href="https://www.gob.mx/curp/" target="_blank">aquí <img style="width:24px; height: 25px" src="<?php echo base_url(); ?>assets/img/logo_curp.png"></a></div><div class="card-body">

                <label>CURP/DNI <i class="far fa-circle-question text-mustard"></i></label>
                <input type="text" style="text-transform: uppercase;" class="form-control <?php echo session( "errors.curp" ) ? "is-invalid" : ""; ?>" name="curp" value="<?php echo old( "curp" ); ?>" placeholder="">
                <p class="small text-red"><?php echo session( "errors.curp" ); ?></p>


                <label>Nacionalidad / País de origen</label>
                <div class="input-group mb-0 w-75">
                    <div class="selected-option" tipo="nacionalidad"><div></div></div>
                    <input type="text"  class="form-control <?php echo session( "errors.nacion" ) ? "is-invalid" : ""; ?>" name="nacion" value="<?php echo old( "nacion" ); ?>" placeholder="" id="nacion">
                </div>

                <div class="select-box" id="nacionalidad">
                    <div class="options">
                        <input type="text" class="search-box form-control" placeholder="Buscar por país">
                        <ol></ol>
                    </div>
                </div>

                <p class="small text-red"><?php echo session( "errors.celular" ); ?></p>

                <div class="alert alert-warning my-3">
                    <i class="fa fa-circle-info"></i> Selecciona tu país de origen. Mas adelante se te solicitará verificar tu cuenta con tu identificación oficial vigente.
                </div>

                <label data-bs-toggle="tooltip" title="Proporciona el número de socio de tu patrocinador">Patrocinador <i class="far fa-circle-question text-mustard"></i></label>
                <input type="number" class="form-control <?php echo session( "errors.patrocinador" ) ? "is-invalid" : ""; ?>" name="patrocinador" value="<?php echo old( "patrocinador" ); ?>" placeholder="">
                <p class="noverifica small text-red"><?php echo session( "errors.patrocinador" ); ?></p>

                <div class=" verificado" xstyle="padding-top: 24px;"></div>
            </div></div>
        </div>
    </div>
    <button type="submit" id="hidden_submit" class="d-none"></button>
</form>

<hr class="border-teal">
<div class="col-6 offset-3 col-md-4 offset-md-4 col-lg-2 offset-lg-5">
    <p class="mt-3 mb-1 text-end"><button id="submit_login" class="e btn btn-primary rounded-pill col-12">Registrate ahora <i class="fa fa-wand-magic-sparkles"></i></button></p>
</div>

<p class="text-center mt-3"><a href="<?php echo base_url( "login" ); ?>"><i class="fa fa-undo"></i> Cancelar y regresar</a></p>

<div class="modal" tabindex="-1" id="modal_confirma">
	<div class="modal-dialog modal-lg">
		<div class="modal-content">
			<div class="modal-header">
				<h5 class="modal-title">Importante:</h5>
				<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
			</div>
			<div class="modal-body">
                <div class="alert alert-danger"><i class="fa fa-warning"></i> Por favor revisa que tus datos y especialmente tu patrocinador sean correctos. recuerda que una vez creada tu cuenta, no podrás modificar el patrocinador.</div>


                <div class="verificado" style="zoom:2"></div>
                
                <p class="mt-3 mb-1 text-end"><button id="submit_ok" class="e btn btn-primary rounded-pill">Mi patrocinador es correcto, quiero continuar</button></p>
			</div>
		</div>
	</div>
</div>


<script>
    var country = '<?php echo old( "pais" ) ?? "MX"; ?>',
        origen  = '<?php echo old( "origen" ) ?? "MX"; ?>';
</script>
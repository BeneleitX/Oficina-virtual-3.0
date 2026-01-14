

<link href="<?php echo base_url(); ?>assets/css/croppie.css" rel="stylesheet"/>
<script src="<?php echo base_url(); ?>assets/js/croppie.js" type="text/javascript"></script>
<script src="<?php echo base_url(); ?>assets/js/exif.js" type="text/javascript"></script>

<input type="file" id="foto_upload" accept="image/*" style="display: none">
<div class="upload-demo-wrap mb-3">
    <div id="upload-demo"></div>
</div>

<div class="row">
    <div class="col-md-4">
        <h5>1. Selecciona la imagen</h5>
        <p>Click en el botón para cargar una fotografía.</p>
        <p>Asegurate de que la identificación se vea clara, con letra legible y de buena calidad.</p>
        <button class="btn btn-primary" onclick="$('#foto_upload').click()">Cargar fotografía</button>
        <a href="javascript:window.parent.closeModal_img();" class="btn btn-outline-danger"><i class="fa fa-xmark"></i> Cancelar</a>
    </div>
    <div class="col-md-4">
        <h5>2. Ajustala al recuadro</h5>
        <p>Utiliza las herramientas en pantalla para dimensionar y centrar la imagen hasta que encaje dentro recuadro.</p>
        <p>Si lo necesitas, puedes girar la imagen:</p>
        <button class="vanilla-rotate btn btn-primary" data-deg="90"><i class="fa fa-undo"></i></button>
        <button class="vanilla-rotate btn btn-primary" data-deg="-90"><i class="fa fa-redo"></i></button>

    </div>
    <div class="col-md-4 mt-3">
        <h5>3. Guarda los cambios</h5>
        <p>Al finalizar, haz click en el botón para continuar con el registro.</p>
        <button class="btn btn-primary upload-result">Guardar y continuar</button>
    </div>
</div>
    
<script>
    var modo = '<?php echo $modo; ?>',
        tempID = '<?php echo $tempID; ?>';
</script>
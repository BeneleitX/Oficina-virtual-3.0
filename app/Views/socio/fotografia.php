
<h4 class="mt-1 mb-0"><?php echo $titulo; ?></h4>
<p><a href="<?php echo base_url( "perfil" ); ?>"><i class="fa fa-undo"></i> Regresar al perfil de socio</a></p>

<link href="<?php echo base_url(); ?>assets/css/croppie.css" rel="stylesheet"/>
<script src="<?php echo base_url(); ?>assets/js/croppie.js" type="text/javascript"></script>
<script src="<?php echo base_url(); ?>assets/js/exif.js" type="text/javascript"></script>

<input type="file" id="foto_upload" accept="image/*" style="display: none">
<div class="upload-demo-wrap mb-3">
    <div id="upload-demo"></div>
</div>

<div class="row">
    <div class="col-md-4">
        <h5>1. Selecciona una imagen</h5>
        <p>Click en el botón para cargar una nueva fotografía.</p>
        <button class="btn btn-success" onclick="$('#foto_upload').click()">Cargar nueva foto</button>
    </div>
    <div class="col-md-4">
        <h5>2. Ajustala a tu gusto</h5>
        <p>Utiliza las herramientas en pantalla para dimensionar y centrar la imagen hasta que su posición sea de tu agrado. Incluso puedes rotarla si lo necesitas.</p>
        <button class="vanilla-rotate btn btn-success" data-deg="90"><i class="fa fa-undo"></i></button>
        <button class="vanilla-rotate btn btn-success" data-deg="-90"><i class="fa fa-redo"></i></button>
    </div>
    <div class="col-md-4">
        <h5>3. Guarda los cambios</h5>
        <p>Al finalizar, haz click en el botón para gaurdar los cambios y actualizar tu perfil.</p>
        <button class="btn btn-success upload-result">Guardar</button>
    </div>
</div>
    
    
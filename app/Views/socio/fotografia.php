
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
        <p>Asegurate de que sea una foto donde se vea tu rostro completo y tenga preferentemente un fondo de color uniforme</p>
        <button class="btn btn-primary" onclick="$('#foto_upload').click()">Cargar nueva foto</button>
    </div>
    <div class="col-md-4 mt-4">
        <h5>2. Ajustala a tu gusto</h5>
        <p>Utiliza las herramientas en pantalla para dimensionar y centrar la imagen hasta que su posición sea de tu agrado. Incluso puedes rotarla si lo necesitas.</p>
        <p>Si lo necesitas, puedes girar la imagen:</p>
        <button class="vanilla-rotate btn btn-primary" data-deg="90"><i class="fa fa-undo"></i></button>
        <button class="vanilla-rotate btn btn-primary" data-deg="-90"><i class="fa fa-redo"></i></button>

        <p class="mt-3">Es importante que tu rostro cubria al menos el 70% de la imagen dentro del círculo. Te mostramos algunos ejemplos:</p>

        <div class="row">
            <div class="col-4 mb-3"><img src="<?php echo base_url(); ?>assets/img/avatar/1.jpg" class="img-fluid rounded-circle"></div>
            <div class="col-4 mb-3"><img src="<?php echo base_url(); ?>assets/img/avatar/2.jpg" class="img-fluid rounded-circle"></div>
            <div class="col-4 mb-3"><img src="<?php echo base_url(); ?>assets/img/avatar/3.jpg" class="img-fluid rounded-circle"></div>
            <div class="col-4 mb-3"><img src="<?php echo base_url(); ?>assets/img/avatar/4.jpg" class="img-fluid rounded-circle"></div>
            <div class="col-4 mb-3"><img src="<?php echo base_url(); ?>assets/img/avatar/5.jpg" class="img-fluid rounded-circle"></div>
            <div class="col-4 mb-3"><img src="<?php echo base_url(); ?>assets/img/avatar/6.jpg" class="img-fluid rounded-circle"></div>
        </div>

    </div>
    <div class="col-md-4 mt-3">
        <h5>3. Guarda los cambios</h5>
        <p>Al finalizar, haz click en el botón para gaurdar los cambios y actualizar tu perfil.</p>
        <button class="btn btn-primary upload-result">Guardar</button>
    </div>
</div>
    
    
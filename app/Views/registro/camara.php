<div class="row">
    <div class="col-md-6 offset-md-3 mb-5 text-center">
        <video id="cameraDisplay" autoplay style="width: 100%; max-width: 100%; background-color: #000; aspect-ratio: 1 / 0.6; border-radius:10px; outline: 40px solid rgba(0,0,0,0.5) ; outline-offset: -40px;"></video>
        
        <select id="cameraList" class="form-control mb-3" onchange="startCamera()"></select>
        <button id="btnCapture" type="button" class="btn btn-primary w-100 btn-lg" onclick="captureImage()" disabled >Capturar fotografía</button>
        <span class="small"><a href="javascript:stopCamera( true )" class="text-red"><i class="fa fa-xmark"></i> Cancelar</a></span>
    </div>
</div>

<tbody id="result"> </tbody>
        
<canvas id="documentcanvas" style="display: none"></canvas>

<script>
    var modo = '<?php echo $modo; ?>',
        tempID = '<?php echo $tempID; ?>';
</script>
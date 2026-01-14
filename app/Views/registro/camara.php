<div id="cam_frame" style="background:black">
    <video id="cameraDisplay" autoplay style="width: 100%; max-width: 100%; height:100%"></video>

    <div class="vertical-center">
        <div class="col-8 col-md-6 offset-md-3 col-lg-4 offset-lg-4 mb-5 text-end center-btn">
            <select id="cameraList" class="form-control mb-3" onchange="startCamera()"></select>
            <button id="btnCapture" type="button" class="btn btn-primary py-5 w-100 btn-lg mb-3" onclick="captureImage()" disabled ><i class="fa fa-camera"></i> Capturar fotografía</button>
            <span class="small"><a href="javascript:stopCamera( true )" class="btn btn-danger btn-sm"><i class="fa fa-xmark"></i> Cancelar</a></span>
        </div>
    </div>
</div>

<canvas id="documentcanvas" style="display: none"></canvas>

<script>
    var modo = '<?php echo $modo; ?>',
        tempID = '<?php echo $tempID; ?>';
</script>
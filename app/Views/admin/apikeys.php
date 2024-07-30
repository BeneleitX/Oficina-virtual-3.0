
<h4 class="mt-1 mb-0"><?php echo $titulo; ?></h4>
<p><a href="<?php echo base_url( "admin" ); ?>"><i class="fa fa-undo"></i> Regresar a configuración</a></p>


<div class="alert alert-info mb-5">
<div class="row">
<div class="col-4">
    <h5 class="mb-0">API</h5>
        <p>Una API es una pieza de código que permite a diferentes aplicaciones comunicarse entre sí y compartir información y funcionalidades..</p>
    </div>
    <div class="col-4">
        <h5 class="mb-0">API key</h5>
        <p>Una API key es una cadena de identificadores únicos destinada principalmente a identificar el tráfico de las aplicaciones de los clientes de la API.</p>
    </div>
    <div class="col-4">
        <h5 class="mb-0">Beneleit API</h5>
        <p>La API de Beneleit está construída con altos niveles de seguridad para garantizar que solo los sistemas autorizados puedan utilizarla. Ayuda a integrar servicios de la empresa en aplicaciones de terceros.</p>
    </div>
</div>
</div>

<div class="row mb-3">
    <div class="col-6"><h1><span class="badge fs-1 bg-white text-blue">https://api.beneleit.mx</span></h1></div>
    <div class="col-6 text-end pt-4"><button class="btn btn-success"><i class="fa fa-key"></i> Crear una nueva API key</button></div>
</div>



        <?php 
            $keys = model( "ApikeyModel" )->findAll();

            foreach( $keys as $k ){ ?>

<div class="card mb-4">
    <div class="card-header bg-gray-600">
        <div class="row">
            <div class="col-6">
                <h5 class="text-white">API key <?php echo $k[ "apikey" ]; ?></h5>
            </div>
            <div class="col-6 text-end">
                <button class="btn btn-sm btn-danger">Desactivar</button>
            </div>
        </div>    
    </div>

    <div class="card-body">
        <p>Cliente: <strong><?php echo $k[ "cliente" ]; ?></strong></p>
        <p>Estatus: <span class="badge bg-teal">ACTIVO</span></p>
    </div>
</div>


        <?php }
        ?>

<div class="row">
    <div class="col-lg-6">
    <div class="card mt-3 bg-marine text-white">
    <div class="card-header bg-blue"><h5 class="text-white mb-0">Respuesta JSON de solicitud user_auth</h5><p class="mb-0 text-yellow"><i class="fa fa-arrow-right"></i> url (POST) https://api.beneleit.mx/user_auth</p></div>
    <pre><code>
    {
        "host": "API.BENELEIT.MX",
        "allow_access": true,
        "timestamp": 1722308032,
        "error": null,
        "response": {
            "error": null,
            "auth": true,
            "data": {
                "id": 12345,
                "avatar": "https://tester.beneleit.mx/data/666/avatar/666_1721508233.jpg",
                "iniciales": "JP",
                "correo": "juanperez12345@gmail.com",
                "nombre": "JUAN PEREZ",
                "apellidos": ""
            }
        },
        "cliente": "CRM Talento.net",
        "estatus": "201-ACTIVO"
    }
    </code></pre>
</div>

    </div>
</div>

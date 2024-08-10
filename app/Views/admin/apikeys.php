
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
    <div class="col-6"><h1><span class="badge fs-1 bg-white text-blue">https://api.beneleit.mx <span class="text-teal">v1.0</span></span></h1></div>
    <div class="col-6 text-end pt-4"><button class="btn btn-success"><i class="fa fa-key"></i> Crear una nueva API key</button></div>
</div>



        <?php 
            $keys = model( "ApikeyModel" )->findAll();

            foreach( $keys as $k ){ ?>

<div class="card mb-4">
    <div class="card-header bg-gray-600">
        <div class="row">
            <div class="col-6">
                <h5 class="text-white mb-0">API key <?php echo "<span class=\"badge bg-mustard fs-5\">********".substr( $k[ "apikey" ], 8 )."</span>"; ?></h5>
            </div>
            <div class="col-6 text-end">
                <button class="btn btn-sm btn-danger">Desactivar</button>
            </div>
        </div>    
    </div>

    <div class="card-body">
        <div class="row">
            <div class="col-lg-2">
                <p>Cliente: <strong><?php echo $k[ "cliente" ]; ?></strong></p>
                <p>Estatus: <span class="badge bg-teal">ACTIVO</span></p>
            </div>
            <div class="col-lg-10 text-center">
                <h1 class="text-gray-300 mt-3">No hay datos para mostrar en la gráfica</h1>
            </div>
        </div>
    </div>
</div>


        <?php }
        ?>

<div class="row">
<div class="col-lg-6">
        <div class="card mt-3 bg-marine text-white">
            <div class="card-header bg-blue"><h5 class="text-white mb-0">Respuesta JSON de solicitud user_auth</h5><p class="mb-0 text-yellow"><i class="fa fa-arrow-right"></i> url (POST) https://api.beneleit.mx/user_auth</p></div>
            <table class="table table-striped">
                <tr><td><strong>id</strong></td><td>Id único de cliente beneleit</td></tr>
                <tr><td><strong>password</strong></td><td>Password ingresado para su validación</td></tr>
            </table>
            <pre><code>
            {
                "request": "https://api.beneleit.mx/user_auth",
                "timestamp": 1722454696,
                "response": {
                    "error": false,
                    "auth": true
                },
                "client": {
                    "allow_access": true,
                    "ip": "189.203.205.83",
                    "error": false,
                    "name": "CRM Talento.net",
                    "status": "201-ACTIVO"
                }
            }
            </code></pre>
        </div>
    </div>

    <div class="col-lg-6">
        <div class="card mt-3 bg-marine text-white">
            <div class="card-header bg-blue"><h5 class="text-white mb-0">Respuesta JSON de solicitud user_info</h5><p class="mb-0 text-yellow"><i class="fa fa-arrow-right"></i> url (POST) https://api.beneleit.mx/user_info</p></div>
            <table class="table table-striped">
                <tr><td><strong>id</strong></td><td>Id único de cliente beneleit</td></tr>
            </table>
            <pre><code>
            {
                "request": "https://api.beneleit.mx/user_info",
                "timestamp": 1722454696,
                "response": {
                    "error": false,
                    "user": {
                        "id": 123456,
                        "avatar": "https://beneleit.mx/data/avatar/1721508233.jpg",
                        "iniciales": "JP",
                        "correo": "juanperez1234@gmail.com",
                        "nombre": "JUAN",
                        "apellidos": "PEREZ LOPEZ"
                    }
                },
                "client": {
                    "allow_access": true,
                    "ip": "189.203.205.83",
                    "error": false,
                    "name": "CRM Talento.net",
                    "status": "201-ACTIVO"
                }
            }
            </code></pre>
        </div>
    </div>

    <div class="col-lg-6">
        <div class="card mt-3 bg-marine text-white">
            <div class="card-header bg-blue"><h5 class="text-white mb-0">Respuesta JSON de solicitud insert_compra</h5><p class="mb-0 text-yellow"><i class="fa fa-arrow-right"></i> url (POST) https://api.beneleit.mx/insert_compra</p></div>

            <table class="table table-striped">
                <tr><td><strong>socio_id</strong></td><td>Id único de cliente beneleit</td></tr>
                <tr><td><strong>paquete_codigo</strong></td><td>Id único de paquete en compra (ver catálogo paquetes)</td></tr>
                <tr><td><strong>numero_telefono</strong></td><td>Número celular a 10 dígitos asociado a la compra</td></tr>
                <tr><td><strong>timestamp</strong></td><td>Fecha y hora de la compra en formato Y-m-d H:i:s</td></tr>
            </table>

            <pre><code>
            {
                "request": "https://api.beneleit.mx/insert_compra",
                "timestamp": 1722454163,
                "response": {
                    "error": false,
                    "insert": true,
                    "compra_id": "10571339"
                },
                "client": {
                    "allow_access": true,
                    "ip": "189.203.205.83",
                    "error": false,
                    "name": "CRM Talento.net",
                    "status": "201-ACTIVO"
                }
            }
            </code></pre>
        </div>

    </div>   
</div>

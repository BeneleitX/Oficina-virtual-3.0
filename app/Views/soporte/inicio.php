<link href="<?php echo base_url(); ?>assets/css/datatables.css" rel="stylesheet"/>
<script src="<?php echo base_url(); ?>assets/js/datatables.js" type="text/javascript"></script>
<script src="<?php echo base_url(); ?>assets/js/datatables_bs5.js" type="text/javascript"></script>


<h4 class="mt-1 mb-5"><?php echo $titulo; ?></h4>

<div class="row mb-5">
    <div class="col-lg-4">
        <div class="card text-center"><a href="#">
            <p class="my-4"><i class="fab fa-youtube text-red" style="font-size:100px"></i></p>
            <h5 class="mb-4">1. Consulta nuestras guías de usuario</h5>
        </a></div>
    </div>
    
    <div class="col-lg-4">
        <div class="card text-center"><a href="javascript:open_modal()">
            <p class="my-4"><i class="fa fa-ticket text-deep-purple" style="font-size:100px"></i></p>
            <h5 class="mb-4">2. Levanta un ticket de soporte</h5>
        </a></div>
    </div>

    <div class="col-lg-4">
        <div class="card text-center"><a href="#">
            <p class="my-4"><i class="fab fa-square-whatsapp text-green" style="font-size:100px"></i></p>
            <h5 class="mb-4">3. Comunicate a nuestro call center</h5>
        </a></div>
    </div>    

</div>


<table class="table table-striped bg-white" id="tabla_tickets">
    <thead>
        <tr>
            <th>No. de ticket</th>
            <th>Situación</th>
            <th>Tipo</th>
            <th>Area</th>
            <th>Creado</th>
            <th>Atendido por</th>
            <th>Estatus</th>
            <th></th>
        </tr>
    </thead>

    <tbody>
       
     
    </tbody>
</table>


<div class="modal" tabindex="-1" id="nuevo_ticket">
	<div class="modal-dialog modal-lg">
		<div class="modal-content">


				<div class="modal-header bg-deep-purple">
					<h5 class="modal-title text-white"><i class="fa fa-ticket"></i> Crear nuevo ticket</h5>
					<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
				</div>
				<div class="modal-body">
                    <form method="post" action="<?php echo base_url( "save_ticket" ); ?>" class="row g-3">
                        <?php echo csrf_field() ?>


                        <div class="col-12 mt-3">
                            <label for="inputAddress" class="form-label">Título para el ticket</label>
                            <input type="text" class="form-control" id="inputAddress" placeholder="">
                        </div>

                        <div class="col-md-6 mt-3">
                            <label for="inputPassword4" class="form-label">Area</label>
                            <select name="" id="" class="form-select">
                                <option value="">Comisiones</option>
                                <option value="">Tienda y productos</option>
                                <option value="">Oficina virtual</option>
                                <option value="">Facturación</option>
                                <option value="">Imagen / Marketing</option>
                            </select>
                        </div>

                        <div class="col-md-6 mt-3">
                            <label for="inputPassword4" class="form-label">Tipo</label>
                            <select name="" id="" class="form-select">
                                <option value="">PREGUNTA</option>
                                <option value="">SUGERENCIA</option>
                                <option value="">PROBLEMA</option>
                                <option value="">TAREA</option>
                            </select>
                        </div>


                        <div class="col-12 mt-3">
                            <label for="inputAddress" class="form-label">Esta es la parte principal del ticket: ¿Cómo podemos ayudarte? incluye todos los detalles posibles que nos sirvan para darle una mejor atención</label>
                            <textarea class="form-control" rows="5"></textarea>

                            <p>Si necesitas adjuntar imagenes, fotos, capturas de pantalla o algun otro material que complemente tu solicitud, hazlo aqui:</p>
                        </div>
                    </form>
				</div>
				<div class="modal-footer">
					<button type="submit" class="btn btn-success"><i class="fa fa-check"></i> Crear</button>
				</div>
			
		</div>
	</div>
</div>
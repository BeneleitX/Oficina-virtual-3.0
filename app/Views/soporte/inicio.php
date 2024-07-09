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
        <div class="card text-center"><a href="#">
            <p class="my-4"><i class="fa fa-ticket text-teal" style="font-size:100px"></i></p>
            <h5 class="mb-4">2. Levanta un ticket de soporte</h5>
        </a></div>
    </div>

    <div class="col-lg-4">
        <div class="card text-center"><a href="#">
            <p class="my-4"><i class="fa fa-headset text-teal" style="font-size:100px"></i></p>
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

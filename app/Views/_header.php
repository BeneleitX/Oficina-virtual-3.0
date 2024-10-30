<!doctype html>
<html lang="es" class="<?php echo $navbar ? "" : "full-body"; ?>">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="description" content="">
        <meta name="author" content="scabbia@gmail.com">
        <meta name="generator" content="Hugo 0.122.0">
        <link rel="icon" type="image/x-icon" href="<?php echo base_url(); ?>assets/img/fav_beneleit.png">
        <?php echo csrf_meta() ?>

        <title>BENELEIT</title>

        <link href="<?php echo base_url(); ?>assets/css/bootstrap.css?<?php echo filemtime( "assets/css/bootstrap.css" ); ?>" rel="stylesheet">
        <link href="<?php echo base_url(); ?>assets/css/colores.css?<?php echo filemtime( "assets/css/colores.css" ); ?>" rel="stylesheet">
        <link href="<?php echo base_url(); ?>assets/css/fontawesome.css" rel="stylesheet">
        <link href="<?php echo base_url(); ?>assets/css/OverlayScrollbars.css" rel="stylesheet">
        <link href="<?php echo base_url(); ?>assets/css/beneleit.css?<?php echo filemtime( "assets/css/beneleit.css" ); ?>" rel="stylesheet">
        
        <!meta http-equiv="refresh" content="7200">

        <script src="<?php echo base_url(); ?>assets/js/jquery.js"></script>
        <script src="<?php echo base_url(); ?>assets/js/fontawesome.js"></script>

    </head>
    
    <body class="bg-<?php echo $fondo; ?>">
        <div class="p-3 <?php echo $navbar ? "con-navbar" : ""; ?>" id="contenedor-body">
            
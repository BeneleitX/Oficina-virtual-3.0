<!doctype html>
<html lang="es" class="<?php echo $navbar ? "" : "full-body"; ?>">
    <head>
        <link rel="manifest" href="<?php echo base_url(); ?>manifest.json">
        <!meta http-equiv="Content-Type" content="application/x-web-app-manifest+json; charset=UTF-8">

        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="description" content="">
        <meta name="cryptomus" content="ce9af27a" />
        <meta name="author" content="scabbia@gmail.com">
        <meta name="generator" content="Hugo 0.122.0">
        <link rel="icon" type="image/png" href="<?php echo base_url(); ?>assets/img/favicon/fav_beneleit.png">
        <?php echo csrf_meta() ?>

        <title>BENELEIT</title>

        <link href="<?php echo base_url(); ?>assets/css/bootstrap.css?<?php echo filemtime( "assets/css/bootstrap.css" ); ?>" rel="stylesheet">
        <link href="<?php echo base_url(); ?>assets/css/colores.css?<?php echo filemtime( "assets/css/colores.css" ); ?>" rel="stylesheet">
        <link href="<?php echo base_url(); ?>assets/css/fontawesome.css" rel="stylesheet">
        <link href="<?php echo base_url(); ?>assets/css/OverlayScrollbars.css" rel="stylesheet">
        <link href="<?php echo base_url(); ?>assets/css/beneleit.css?<?php echo filemtime( "assets/css/beneleit.css" ); ?>" rel="stylesheet">
        
        <script src="<?php echo base_url(); ?>assets/js/jquery.js"></script>
        <script src="<?php echo base_url(); ?>assets/js/fontawesome.js"></script>

    </head>
    
    <body class="bg-<?php echo $fondo; ?>">
        <div class="p-<?php echo $header_x ? "0" : "3" ?> <?php echo $navbar ? "con-navbar" : ""; ?>" id="contenedor-body">
            
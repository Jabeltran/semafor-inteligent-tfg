<!DOCTYPE html>
<!-- Idioma base del lloc web (català) -->
<html lang="ca">
<head>
    <!-- Configuració bàsica -->
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <!-- Títol de la pàgina -->
    <title>Semàfor Intel·ligent</title>

    <!-- Bootstrap CSS  -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- Estils propis -->
    <link href="/traffic-Int/public/css/principal.css" rel="stylesheet">

    <!-- Icones de Bootstrap -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.8.1/font/bootstrap-icons.css">
    <link rel="icon" href="/traffic-Int/public/img/favicon.ico" type="image/x-icon">
</head>

<!-- Cos de la pàgina -->
<body class="principal-body">
    <!-- Barra de navegació principal -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary">
        <div class="container-fluid">
            <!-- Logo i nom de l'aplicació -->
            <a class="navbar-brand" href="/traffic-Int/public/principal">
                <i class="bi bi-traffic-light"></i> Semàfor Intel·ligent
            </a>

            <!-- Botó de tancar sessió -->
            <div class="d-flex">
                <a href="/traffic-Int/public/logout" class="btn btn-outline-light">
                    <i class="bi bi-box-arrow-right"></i> Tancar Sessió
                </a>
            </div>
        </div>
    </nav>

    <!-- Contenidor principal -->
    <div class="container-fluid mt-4">
<?php
// Inici de sessió per mantenir l'estat de l'usuari
session_start();

// Configuració de l'autoloader per carregar classes automàticament
spl_autoload_register(function ($className) {
    // Adapta el namespace a l'estructura de directoris
    $className = str_replace("app\\", "", $className);
    $filePath = __DIR__ . '/' . str_replace('\\', '/', $className) . '.php';

    // Comprova si l'arxiu de classe existeix abans de carregar-lo
    if (file_exists($filePath)) {
        require_once $filePath;
    }
});

// Connexió a la base de dades
require_once __DIR__ . '/db/Database.php';
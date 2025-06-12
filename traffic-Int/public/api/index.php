<?php

// Defineix el tipus de contingut de la resposta
header('Content-Type: application/json');

// Incloure la configuració de la base de dades fins a app/db/Database.php
require_once '../../app/db/Database.php';

// Utilitzar el namespace de la classe Database
use app\db\Database;

// Obté el cos de la petició HTTP de tipus JSON
$json_data = file_get_contents('php://input');
$data = json_decode($json_data, true); // Decodifica el JSON a un array associatiu

// Comprova si les dades JSON són vàlides
if ($data === null) {
    http_response_code(400); // Bad Request
    echo json_encode(["status" => "error", "message" => "Format JSON invàlid."]);
    exit();
}

// Obtenir la connexió a la base de dades utilitzant Singleton
try {
    $pdo = Database::getInstance(); // Ara obtenim un objecte PDO
} catch (\PDOException $e) {
    http_response_code(500); // Internal Server Error
    echo json_encode(["status" => "error", "message" => "Error de connexió a la base de dades: " . $e->getMessage()]);
    exit();
}


// Determinar el tipus de dada
$type = '';
if (isset($data['type'])) {
    $type = $data['type'];
}

$semafor_id = 1; // Valor per defecte
if (isset($data['semafor_id'])) {
    $semafor_id = intval($data['semafor_id']);
}
$timestamp = date('Y-m-d H:i:s'); // Timestamp actual

// Switch per diferents tipus de dades
switch ($type) {

    // Processament dades ambientals
    case 'ambiental':
        $temperatura = null;
        if (isset($data['temp'])) {
            $temperatura = floatval($data['temp']);
        }

        $humitat = null;
        if (isset($data['hum'])) {
            $humitat = floatval($data['hum']);
        }

        $pressio = null;
        if (isset($data['pres'])) {
            $pressio = floatval($data['pres']);
        }

        $pluja = null;
        if (isset($data['pluja'])) {
            $pluja = intval($data['pluja']);
        }

        // Utilitzem PDO prepare i execute
        $sql = "INSERT INTO sensors_ambientals (timestamp, temperatura, humitat, pressio, pluja, semafor_id) VALUES (?, ?, ?, ?, ?, ?)";
        try {
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$timestamp, $temperatura, $humitat, $pressio, $pluja, $semafor_id]);
            echo json_encode(["status" => "success", "message" => "Dades ambientals inserides."]);
        } catch (\PDOException $e) {
            http_response_code(500);
            echo json_encode(["status" => "error", "message" => "Error al inserir dades ambientals: " . $e->getMessage()]);
        }
        break;

    // Processament dades velocitat
    case 'velocitat':
        $velocitat_cms = null;
        if (isset($data['vel'])) {
            $velocitat_cms = floatval($data['vel']);
        }

        // Utilitzem PDO prepare i execute
        $sql = "INSERT INTO velocitat_cotxes (timestamp, velocitat_cms, semafor_id) VALUES (?, ?, ?)";
        try {
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$timestamp, $velocitat_cms, $semafor_id]);
            echo json_encode(["status" => "success", "message" => "Dades de velocitat inserides."]);
        } catch (\PDOException $e) {
            http_response_code(500);
            echo json_encode(["status" => "error", "message" => "Error al inserir dades de velocitat: " . $e->getMessage()]);
        }
        break;

    default:
        http_response_code(400); // Bad Request
        echo json_encode(["status" => "error", "message" => "Tipus de dada desconegut o no especificat."]);
        break;
}

// Amb PDO, la connexió es tanca automàticament quan l'script finalitza o l'objecte PDO és destruït.
?>

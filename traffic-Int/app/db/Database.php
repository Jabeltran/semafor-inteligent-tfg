<?php
namespace app\db;

class Database {
    private static $instance = null;
    private $pdo;

    /**
     * Connexió amb la base de dades utilitzant PDO (PHP Data Objects).
     * Retorna error si no s'estableix
     */
    private function __construct() {
        $host = 'localhost';
        $dbname = 'trafficDB';
        $user = 'root';
        $pass = '';

        try {
            $this->pdo = new \PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $user, $pass);
            $this->pdo->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
        } catch (\PDOException $e) {
            die("Error de connexió: " . $e->getMessage());
        }
    }
    /**
     * Mètode estàtic que implementa el patró Singleton.
     * Retorna la única instància de la connexió a la base de dades.
     */
    public static function getInstance() {
        if (!self::$instance) {
            self::$instance = new Database();
        }
        return self::$instance->pdo;
    }
}
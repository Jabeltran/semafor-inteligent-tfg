<?php
namespace app\model;

class UserModel {
    private $pdo;
    
    /**
     * Inicialitza una nova instància del model d'usuari, obtenint la connexió PDO
     * de la instància singleton de la base de dades
     */
    public function __construct() {
        $this->pdo = \app\db\Database::getInstance();
    }

    /**
     *Crea un nou usuari al sistema     *
     * @param string $username Nom d'usuari
     * @param string $email Adreça electrònica de l'usuari
     * @param string $password Contrasenya en text pla
     * @return array Retorna un array associatiu amb:
     *               - 'success': boolean indicant si l'operació va tenir èxit
     *               - 'error': missatge d'error en cas de fallada o null si va ser exitós
     */
    public function createUser($username, $email, $password) {
        try {
            $stmt = $this->pdo->prepare("INSERT INTO users (username, email, password) VALUES (?, ?, ?)");
            $hashed = password_hash($password, PASSWORD_DEFAULT);
            $success = $stmt->execute([$username, $email, $hashed]);

            return [
                'success' => $success,
                'error' => $success ? null : $stmt->errorInfo()
            ];
        } catch (\PDOException $e) {
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     Obté un usuari pel seu correu electrònic
     * @param string $email Adreça electrònica de l'usuari a buscar
     * @return array|false Retorna un array associatiu amb les dades de l'usuari si es troba,
     *                     false si no es troba o hi ha un error
     */
    public function getUserByEmail($email) {
        try {
            $stmt = $this->pdo->prepare("SELECT * FROM users WHERE email = ?");
            $stmt->execute([$email]);
            return $stmt->fetch(\PDO::FETCH_ASSOC);
        } catch (\PDOException $e) {
            error_log("Error en getUserByEmail: " . $e->getMessage());
            return false;
        }
    }
}
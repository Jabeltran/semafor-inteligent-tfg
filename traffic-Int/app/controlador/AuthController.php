<?php
namespace app\controlador;

use app\model\UserModel;

/**
 * Controlador d'autenticació que gestiona el login, registre i logout d'usuaris
 */

class AuthController {
    private $userModel;

    /**
     * Constructor - Inicialitza el model d'usuari
     */
    public function __construct() {
        $this->userModel = new UserModel();
    }

    /**
     * Gestiona el procés d'inici de sessió
     * - Verifica credencials POST
     * - Estableix sessió si són correctes
     * - Mostra errors si falla l'autenticació
     */
    public function login() {
        // Només processar si s'envia per POST
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $email = $_POST['email'];
            $password = $_POST['password'];

            // Obtenir usuari de la base de dades
            $user = $this->userModel->getUserByEmail($email);

            // Verificar contrasenya
            if ($user && password_verify($password, $user['password'])) {
                // Establir sessió i redirigir
                $_SESSION['user_id'] = $user['id'];
                header("Location: /traffic-Int/public/principal");
                exit();
            } else {
                $error = "Credencials incorrectes";
            }
        }

        // Carregar vista de login
        require_once __DIR__ . '/../vista/auth/login.php';
    }

    /**
     * Gestiona el registre de nous usuaris
     * - Valida dades d'entrada
     * - Comprova email únic
     * - Crea usuari a la base de dades
     */
    public function register() {
        $error = null;

        // Processar formulari si s'envia per POST
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $username = trim($_POST['username']);
            $email = trim($_POST['email']);
            $password = $_POST['password'];

            // Validacions bàsiques
            if (empty($username) || empty($email) || empty($password)) {
                $error = "Tots els camps són obligatoris";
            }
            elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $error = "Format de correu electrònic invàlid";
            }
            elseif (strlen($password) < 8) {
                $error = "La contrasenya ha de tenir com a mínim 8 caràcters";
            }
            else {
                // Comprovar si l'email ja existeix
                if ($this->userModel->getUserByEmail($email)) {
                    $error = "Aquest correu electrònic ja està registrat";
                }
                else {
                    // Intentar crear l'usuari
                    $result = $this->userModel->createUser($username, $email, $password);

                    if ($result['success']) {
                        // Registre exitós - redirigir a login
                        $_SESSION['register_success'] = true;
                        header("Location: /traffic-Int/public/login");
                        exit();
                    } else {
                        // Error en crear usuari
                        $error = "Error en registrar l'usuari: " . $result['error'];
                        error_log("Error en registre: " . $result['error']);
                    }
                }
            }
        }

        // Carregar vista de registre
        require_once __DIR__ . '/../vista/auth/register.php';
    }

    /**
     * Tanca la sessió de l'usuari
     * - Destrueix la sessió actual
     * - Redirigeix a la pàgina de login
     */
    public function logout() {
        // Destruir totes les dades de sessió
        session_destroy();

        // Redirigir a login
        header("Location: /traffic-Int/public/login");
        exit();
    }
}
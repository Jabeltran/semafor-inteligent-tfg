<?php
namespace app\controlador;

use app\model\MeasurementModel;

/**
 * Controlador principal que gestiona la lògica de la pàgina d'inici
 * i les sol·licituds de dades per als gràfics
 */
class PrincipalController {
    private $measurementModel;

    /**
     * Constructor - Inicialitza el model de dades
     */
    public function __construct() {
        $this->measurementModel = new MeasurementModel();
    }

    /**
     * Acció principal que mostra la pàgina d'inici
     * Gestiona la visualització de dades dels semàfors
     */
    public function index() {
        // Verificar si l'usuari ha iniciat sessió
        if (!isset($_SESSION['user_id'])) {
            header("Location: /traffic-Int/public/login");
            exit();
        }

        // Configuració dels semàfors disponibles
        $semaforos = [
            ['id' => 1, 'ubicacio' => 'Avinguda Principal'],
            ['id' => 2, 'ubicacio' => 'Carrer del Centre'],
            ['id' => 3, 'ubicacio' => 'Plaça Major']
        ];

        // Obtenir el semàfor seleccionat, per defecte l'ID 1
        $selectedSemafor = $_GET['semafor'] ?? 1;
        // Només mostrar dades per al semàfor 1
        $showData = ($selectedSemafor == 1);

        // Inicialitzar arrays de dades
        $ambientData = [];
        $speedData = [];
        $redLightData = [];

        // Obtenir dades només si estem mostrant el semàfor 1
        if ($showData) {
            $ambientData = $this->measurementModel->getAmbientData($selectedSemafor);
            $speedData = $this->measurementModel->getSpeedData($selectedSemafor);
            $redLightData = $this->measurementModel->getRedLightData($selectedSemafor);
        }

        // Carregar la vista principal
        require_once __DIR__ . '/../../app/vista/principal/index.php';
    }

    /**
     * Obtenir dades per als gràfics
     * @return JSON Resposta amb les dades o missatge d'error
     */
    public function getChartData() {
        header('Content-Type: application/json');

        try {
            $semaforId = $_GET['semafor_id'] ?? 1;
            $startDate = $_GET['start_date'] ?? null;
            $endDate = $_GET['end_date'] ?? null;

            $data = $this->measurementModel->getSpeedChartData($semaforId, $startDate, $endDate);

            echo json_encode([
                'success' => true,
                'data' => $data
            ]);
        } catch (\Exception $e) {
            echo json_encode([
                'success' => false,
                'message' => $e->getMessage()
            ]);
        }
    }

    /**
     * Obtenir dades ambientals per als gràfics
     * @return JSON Resposta amb les dades ambientals o missatge d'error
     */
    public function getAmbientChartData() {
        header('Content-Type: application/json');

        try {
            $semaforId = $_GET['semafor_id'] ?? 1;
            $startDate = $_GET['start_date'] ?? null;
            $endDate = $_GET['end_date'] ?? null;

            $data = $this->measurementModel->getAmbientChartData($semaforId, $startDate, $endDate);

            echo json_encode([
                'success' => true,
                'data' => $data
            ]);
        } catch (\Exception $e) {
            echo json_encode([
                'success' => false,
                'message' => $e->getMessage()
            ]);
        }
    }

    /**
     * Obtenir dades de velocitat per als gràfics
     * @return JSON Resposta amb les dades de velocitat o missatge d'error
     */
    public function getSpeedChartData() {
        header('Content-Type: application/json');

        try {
            $semaforId = $_GET['semafor_id'] ?? 1;
            $startDate = $_GET['start_date'] ?? null;
            $endDate = $_GET['end_date'] ?? null;

            $data = $this->measurementModel->getSpeedDataForCharts($semaforId, $startDate, $endDate);

            echo json_encode([
                'success' => true,
                'data' => $data
            ]);
        } catch (\Exception $e) {
            echo json_encode([
                'success' => false,
                'message' => $e->getMessage()
            ]);
        }
    }
}
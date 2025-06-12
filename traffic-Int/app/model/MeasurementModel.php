<?php
namespace app\model;

/**
 * Model que gestiona l'accés a les dades de mesures del sistema
 * Conté mètodes per recuperar dades de sensors ambientals, velocitat i passos en vermell
 */
class MeasurementModel {
    private $pdo;

    /**
     * Constructor - Inicialitza la connexió a la base de dades
     */
    public function __construct() {
        $this->pdo = \app\db\Database::getInstance();
    }

    /**
     * Obté les dades dels sensors ambientals
     * @param int $semaforId ID del semàfor, per defecte 1
     * @param int $limit Nombre de registres a retornar, per defecte 20
     * @return array Dades ambientals amb timestamp, temperatura, humitat, pressió i estat de pluja
     */
    public function getAmbientData($semaforId = 1, $limit = 20) {
        try {
            $stmt = $this->pdo->prepare("
                SELECT
                    timestamp,
                    temperatura,
                    humitat,
                    pressio,
                    CASE WHEN pluja = 1 THEN 'Sí' ELSE 'No' END AS pluja
                FROM sensors_ambientals
                WHERE semafor_id = :semaforId
                ORDER BY timestamp DESC
                LIMIT :limit
            ");
            $stmt->bindValue(':semaforId', $semaforId, \PDO::PARAM_INT);
            $stmt->bindValue(':limit', $limit, \PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetchAll(\PDO::FETCH_ASSOC);
        } catch (\PDOException $e) {
            error_log("Error en getAmbientData: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Obté les dades de velocitat dels vehicles
     * @param int $semaforId ID del semàfor, per defecte 1
     * @param int $limit Nombre de registres a retornar, per defecte 20
     * @return array Dades de velocitat amb timestamp i velocitat en cm/s
     */
    public function getSpeedData($semaforId = 1, $limit = 20) {
        try {
            $stmt = $this->pdo->prepare("
                SELECT
                    timestamp,
                    velocitat_cms AS velocitat
                FROM velocitat_cotxes
                WHERE semafor_id = :semaforId
                ORDER BY timestamp DESC
                LIMIT :limit
            ");
            $stmt->bindValue(':semaforId', $semaforId, \PDO::PARAM_INT);
            $stmt->bindValue(':limit', $limit, \PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetchAll(\PDO::FETCH_ASSOC);
        } catch (\PDOException $e) {
            error_log("Error en getSpeedData: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Obté les dades de passos en vermell
     * @param int $semaforId ID del semàfor, per defecte 1
     * @param int $limit Nombre de registres a retornar, per defecte 20
     * @return array Dades d'infraccions amb timestamp, nombre de sensors activats i temps entre sensors
     */
    public function getRedLightData($semaforId = 1, $limit = 20) {
        try {
            $stmt = $this->pdo->prepare("
                SELECT
                    timestamp,
                    num_sensors_activats,
                    temps_entre_sensors
                FROM passos_en_vermell
                WHERE semafor_id = :semaforId
                ORDER BY timestamp DESC
                LIMIT :limit
            ");
            $stmt->bindValue(':semaforId', $semaforId, \PDO::PARAM_INT);
            $stmt->bindValue(':limit', $limit, \PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetchAll(\PDO::FETCH_ASSOC);
        } catch (\PDOException $e) {
            error_log("Error en getRedLightData: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Obté dades ambientals per a gràfics amb possibilitat de filtrar per dates
     * @param int $semaforId ID del semàfor, per defecte 1
     * @param  $startDate Data d'inici del filtre
     * @param  $endDate Data final del filtre
     * @return array Dades ambientals ordenades cronològicament
     */
    public function getAmbientChartData($semaforId = 1, $startDate = null, $endDate = null) {
        try {
            $sql = "
                SELECT
                    timestamp,
                    temperatura,
                    humitat,
                    pressio,
                    pluja
                FROM sensors_ambientals
                WHERE semafor_id = :semaforId
            ";

            $params = [':semaforId' => $semaforId];

            // Afegir filtres de data si s'especifiquen
            if ($startDate) {
                $sql .= " AND timestamp >= :startDate";
                $params[':startDate'] = $startDate;
            }

            if ($endDate) {
                $sql .= " AND timestamp <= :endDate";
                $params[':endDate'] = $endDate;
            }

            $sql .= " ORDER BY timestamp ASC";

            $stmt = $this->pdo->prepare($sql);

            foreach ($params as $key => $value) {
                $stmt->bindValue($key, $value);
            }

            $stmt->execute();
            return $stmt->fetchAll(\PDO::FETCH_ASSOC);
        } catch (\PDOException $e) {
            error_log("Error en getAmbientChartData: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Obté dades de velocitat per a gràfics amb possibilitat de filtrar per dates
     * @param int $semaforId ID del semàfor, per defecte 1
     * @param string|null $startDate Data d'inici del filtre
     * @param string|null $endDate Data final del filtre
     * @return array Dades de velocitat ordenades cronològicament
     */
    public function getSpeedDataForCharts($semaforId = 1, $startDate = null, $endDate = null) {
        try {
            $sql = "SELECT timestamp, velocitat_cms AS velocitat
                    FROM velocitat_cotxes
                    WHERE semafor_id = :semaforId";

            $params = [':semaforId' => $semaforId];

            // Afegir filtres de data si s'especifiquen
            if ($startDate) {
                $sql .= " AND timestamp >= :startDate";
                $params[':startDate'] = $startDate;
            }

            if ($endDate) {
                $sql .= " AND timestamp <= :endDate";
                $params[':endDate'] = $endDate;
            }

            $sql .= " ORDER BY timestamp ASC";

            $stmt = $this->pdo->prepare($sql);
            foreach ($params as $key => $value) {
                $stmt->bindValue($key, $value);
            }

            $stmt->execute();
            return $stmt->fetchAll(\PDO::FETCH_ASSOC);
        } catch (\PDOException $e) {
            error_log("Error en getSpeedDataForCharts: " . $e->getMessage());
            return [];
        }
    }
}
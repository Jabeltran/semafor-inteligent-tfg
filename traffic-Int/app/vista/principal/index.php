<?php include __DIR__ . '/includes/header.php'; ?>
<!-- Inclou el fitxer header.php amb l'estructura HTML inicial i els estils CSS -->

<div class="principal-container">
    <!-- Contenidor principal de la pàgina -->

    <!-- Capçalera amb botons de semàfors -->
    <div class="semaforos-header">
        <h1 class="text-center mb-4">Sistema de Semàfors Intel·ligents</h1>
        <div class="semaforos-buttons">
            <?php foreach ($semaforos as $semafor): ?>
                <!-- Genera botons per a cada semàfor -->
                <a href="?semafor=<?= $semafor['id'] ?>"
                   class="btn btn-<?= ($selectedSemafor == $semafor['id']) ? 'primary' : 'secondary' ?>">
                    Semàfor #<?= $semafor['id'] ?> - <?= $semafor['ubicacio'] ?>
                </a>
            <?php endforeach; ?>
        </div>
    </div>

    <!-- Contingut dinàmic que canvia segons el semàfor seleccionat -->
    <div class="dynamic-content">
        <?php if ($selectedSemafor == 1): ?>
            <!-- Contingut que només es mostra per al semàfor 1  -->

            <!-- Taules de dades en format de dues columnes -->
            <div class="row mb-4">
                <!-- Columna esquerra: Dades Ambientals -->
                <div class="col-md-6">
                    <div class="card">
                        <div class="card-header bg-primary text-white">
                            <div class="d-flex justify-content-between align-items-center">
                                <span>Dades Ambientals</span>
                                <!-- Botó per mostrar gràfics ambientals -->
                                <button class="btn btn-sm btn-info btn-show-ambient-charts">
                                    <i class="bi bi-graph-up"></i> Gràfics
                                </button>
                            </div>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-striped">
                                    <!-- Capçalera de la taula -->
                                    <thead>
                                        <tr>
                                            <th>Data i Hora</th>
                                            <th>Temp. (°C)</th>
                                            <th>Humitat (%)</th>
                                            <th>Pluja</th>
                                        </tr>
                                    </thead>
                                    <!-- Cos de la taula -->
                                    <tbody>
                                        <?php if (empty($ambientData)): ?>
                                            <!-- Missatge si no hi ha dades -->
                                            <tr><td colspan="4" class="text-center">No hi ha dades</td></tr>
                                        <?php else: ?>
                                            <!-- Llistat de dades ambientals -->
                                            <?php foreach ($ambientData as $data): ?>
                                                <tr>
                                                    <td><?= date('d/m/Y H:i', strtotime($data['timestamp'])) ?></td>
                                                    <td><?= $data['temperatura'] ?></td>
                                                    <td><?= $data['humitat'] ?></td>
                                                    <td><?= $data['pluja'] ?></td>
                                                </tr>
                                            <?php endforeach; ?>
                                        <?php endif; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Columna dreta: Velocitat dels Cotxes -->
                <div class="col-md-6">
                    <div class="card">
                        <div class="card-header bg-primary text-white">
                            <div class="d-flex justify-content-between align-items-center">
                                <span>Velocitat dels Cotxes (cm/s)</span>
                                <!-- Botó per mostrar gràfics de velocitat -->
                                <button class="btn btn-sm btn-info btn-show-speed-charts">
                                    <i class="bi bi-graph-up"></i> Gràfics
                                </button>
                            </div>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-striped">
                                    <thead>
                                        <tr>
                                            <th>Data i Hora</th>
                                            <th>Velocitat</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php if (empty($speedData)): ?>
                                            <tr><td colspan="2" class="text-center">No hi ha dades</td></tr>
                                        <?php else: ?>
                                            <?php foreach ($speedData as $data): ?>
                                                <tr>
                                                    <td><?= date('d/m/Y H:i', strtotime($data['timestamp'])) ?></td>
                                                    <td><?= $data['velocitat'] ?></td>
                                                </tr>
                                            <?php endforeach; ?>
                                        <?php endif; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Taula de passos en vermell -->
            <div class="card mb-4">
                <div class="card-header bg-danger text-white">
                    Passos en Vermell
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th>Data i Hora</th>
                                    <th>Sensors Activats</th>
                                    <th>Temps entre Sensors (s)</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($redLightData)): ?>
                                    <tr><td colspan="3" class="text-center">No hi ha dades</td></tr>
                                <?php else: ?>
                                    <?php foreach ($redLightData as $data): ?>
                                        <tr>
                                            <td><?= date('d/m/Y H:i', strtotime($data['timestamp'])) ?></td>
                                            <td><?= $data['num_sensors_activats'] ?></td>
                                            <td><?= $data['temps_entre_sensors'] ?? 'N/A' ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

        <?php else: ?>
            <!-- Missatge per als altres semàfors -->
            <div class="alert alert-info text-center mt-4">
                <i class="bi bi-info-circle-fill"></i> Opció no implementada - Semàfor #<?= $selectedSemafor ?>
            </div>
        <?php endif; ?>
    </div>
</div>

<!-- Modal per als gràfics ambientals -->
<div class="modal fade" id="ambientChartsModal" tabindex="-1" aria-labelledby="ambientChartsModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header py-2">
                <h5 class="modal-title" id="ambientChartsModalLabel">Gràfics de Dades Ambientals</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tancar"></button>
            </div>
            <div class="modal-body p-3">
                <!-- Controls de data -->
                <div class="row g-2 mb-4">
                    <div class="col-md-4">
                        <label for="ambientStartDate" class="form-label">Data d'inici</label>
                        <input type="date" class="form-control" id="ambientStartDate">
                    </div>
                    <div class="col-md-4">
                        <label for="ambientEndDate" class="form-label">Data final</label>
                        <input type="date" class="form-control" id="ambientEndDate">
                    </div>
                    <div class="col-md-4 d-flex align-items-end">
                        <button id="ambientFilterBtn" class="btn btn-primary">Filtrar</button>
                        <button id="ambientResetBtn" class="btn btn-outline-secondary ms-2">Reiniciar</button>
                    </div>
                </div>

                <!-- Contenidor del gràfic -->
                <div class="row">
                    <div class="col-md-12" style="height: 400px;">
                        <canvas id="ambientChart" style="height: 100%; width: 100%;"></canvas>
                    </div>
                </div>
            </div>
            <div class="modal-footer py-2">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tancar</button>
            </div>
        </div>
    </div>
</div>

<!-- Modal per als gràfics de velocitat -->
<div class="modal fade" id="speedChartsModal" tabindex="-1" aria-labelledby="speedChartsModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header py-2">
                <h5 class="modal-title h6" id="speedChartsModalLabel">Gràfics de Velocitat dels Cotxes</h5>
                <button type="button" class="btn-close btn-close-sm" data-bs-dismiss="modal" aria-label="Tancar"></button>
            </div>
            <div class="modal-body p-3">
                <!-- Controls de data -->
                <div class="row g-2 mb-3">
                    <div class="col-md-4">
                        <label for="speedStartDate" class="form-label small">Data d'inici</label>
                        <input type="date" class="form-control form-control-sm" id="speedStartDate">
                    </div>
                    <div class="col-md-4">
                        <label for="speedEndDate" class="form-label small">Data final</label>
                        <input type="date" class="form-control form-control-sm" id="speedEndDate">
                    </div>
                    <div class="col-md-4 d-flex align-items-end">
                        <button id="speedFilterBtn" class="btn btn-primary btn-sm me-1">Filtrar</button>
                        <button id="speedResetBtn" class="btn btn-outline-secondary btn-sm">Reiniciar</button>
                    </div>
                </div>

                <!-- Contenidor del gràfic -->
                <div class="row">
                    <div class="col-md-12" style="height: 300px;">
                        <canvas id="speedChart" style="height: 100%; width: 100%;"></canvas>
                    </div>
                </div>
            </div>
            <div class="modal-footer py-2">
                <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Tancar</button>
            </div>
        </div>
    </div>
</div>

<!-- Codi JavaScript -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<script>
$(document).ready(function() {
    // Variables globals pels gràfics
    let ambientChart, speedChart;

    // Inicialització dels modals
    const ambientModal = new bootstrap.Modal(document.getElementById('ambientChartsModal'));
    const speedModal = new bootstrap.Modal(document.getElementById('speedChartsModal'));

    /* *******************************/
    /* FUNCIONS PER DADES AMBIENTALS */
    /* ***************************** */

    /**
     * Carrega les dades ambientals des de l'API
     */
    function loadAmbientChartData() {
        const startDate = $('#ambientStartDate').val();
        const endDate = $('#ambientEndDate').val();
        const semaforId = <?= $selectedSemafor ?>;

        $.ajax({
            url: '/traffic-Int/public/principal/getAmbientChartData',
            method: 'GET',
            data: {
                semafor_id: semaforId,
                start_date: startDate,
                end_date: endDate
            },
            success: function(response) {
                if (response.success) {
                    updateAmbientChart(response.data);
                }
            },
            error: function(xhr) {
                console.error("Error:", xhr.responseText);
            }
        });
    }

    /**
     * Actualitza el gràfic ambiental amb dades noves
     * @param data - Dades per mostrar al gràfic
     */
    function updateAmbientChart(data) {
        const ctx = document.getElementById('ambientChart').getContext('2d');

        // Eliminar gràfic anterior si existeix
        if (ambientChart) {
            ambientChart.destroy();
        }

        // Processar dades
        const labels = data.map(item => new Date(item.timestamp).toLocaleString('ca-ES'));
        const temperaturaData = data.map(item => item.temperatura);
        const humitatData = data.map(item => item.humitat);
        const pressioData = data.map(item => item.pressio);
        const plujaData = data.map(item => item.pluja ? 1 : 0);

        // Crear nou gràfic
        ambientChart = new Chart(ctx, {
            type: 'line',
            data: {
                labels: labels,
                datasets: [
                    {
                        label: 'Temperatura (°C)',
                        data: temperaturaData,
                        borderColor: 'rgba(255, 99, 132, 1)',
                        backgroundColor: 'rgba(255, 99, 132, 0.2)',
                        yAxisID: 'y'
                    },
                    {
                        label: 'Humitat (%)',
                        data: humitatData,
                        borderColor: 'rgba(54, 162, 235, 1)',
                        backgroundColor: 'rgba(54, 162, 235, 0.2)',
                        yAxisID: 'y'
                    },
                    {
                        label: 'Pressió (hPa)',
                        data: pressioData,
                        borderColor: 'rgba(75, 192, 192, 1)',
                        backgroundColor: 'rgba(75, 192, 192, 0.2)',
                        yAxisID: 'y1'
                    },
                    {
                        label: 'Pluja',
                        data: plujaData,
                        borderColor: 'rgba(153, 102, 255, 1)',
                        backgroundColor: 'rgba(153, 102, 255, 0.2)',
                        yAxisID: 'y2'
                    }
                ]
            },
            options: {
                responsive: true,
                interaction: {
                    mode: 'index',
                    intersect: false
                },
                scales: {
                    y: {
                        type: 'linear',
                        display: true,
                        position: 'left',
                        title: { display: true, text: 'Temperatura/Humitat' }
                    },
                    y1: {
                        type: 'linear',
                        display: true,
                        position: 'right',
                        title: { display: true, text: 'Pressió (hPa)' },
                        grid: { drawOnChartArea: false }
                    },
                    y2: {
                        type: 'linear',
                        display: true,
                        position: 'right',
                        title: { display: true, text: 'Pluja' },
                        min: 0,
                        max: 1,
                        ticks: {
                            callback: function(value) {
                                return value === 1 ? 'Sí' : 'No';
                            }
                        },
                        grid: { drawOnChartArea: false }
                    }
                }
            }
        });
    }

    // Event del botó de gràfics ambientals
    $('.btn-show-ambient-charts').click(function() {
        const endDate = new Date();
        const startDate = new Date();
        startDate.setDate(startDate.getDate() - 20);

        $('#ambientEndDate').val(endDate.toISOString().split('T')[0]);
        $('#ambientStartDate').val(startDate.toISOString().split('T')[0]);

        loadAmbientChartData();
        ambientModal.show();
    });

    // Events dels botons del modal ambiental
    $('#ambientFilterBtn').click(loadAmbientChartData);
    $('#ambientResetBtn').click(function() {
        const endDate = new Date();
        const startDate = new Date();
        startDate.setDate(startDate.getDate() - 20);
        $('#ambientEndDate').val(endDate.toISOString().split('T')[0]);
        $('#ambientStartDate').val(startDate.toISOString().split('T')[0]);
        loadAmbientChartData();
    });

    /* ********************** */
    /* FUNCIONS PER VELOCITAT */
    /* ********************** */

    /**
     * Carrega les dades de velocitat des de l'API
     */
    function loadSpeedChartData() {
        const startDate = $('#speedStartDate').val();
        const endDate = $('#speedEndDate').val();
        const semaforId = <?= $selectedSemafor ?>;

        $.ajax({
            url: '/traffic-Int/public/principal/getSpeedChartData',
            method: 'GET',
            data: {
                semafor_id: semaforId,
                start_date: startDate,
                end_date: endDate
            },
            success: function(response) {
                if (response.success) {
                    updateSpeedChart(response.data);
                }
            },
            error: function(xhr) {
                console.error("Error:", xhr.responseText);
            }
        });
    }

    /**
     * Actualitza el gràfic de velocitat amb noves dades
     * @param data - Dades per mostrar al gràfic
     */
    function updateSpeedChart(data) {
        const ctx = document.getElementById('speedChart').getContext('2d');

        if (speedChart) {
            speedChart.destroy();
        }

        speedChart = new Chart(ctx, {
            type: 'line',
            data: {
                labels: data.map(item => new Date(item.timestamp).toLocaleString('ca-ES')),
                datasets: [{
                    label: 'Velocitat (cm/s)',
                    data: data.map(item => item.velocitat),
                    borderColor: 'rgba(75, 192, 192, 1)',
                    backgroundColor: 'rgba(75, 192, 192, 0.2)',
                    borderWidth: 1,
                    pointRadius: 3,
                    tension: 0.1
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'top',
                        labels: {
                            boxWidth: 12,
                            padding: 8,
                            font: {
                                size: 11
                            }
                        }
                    }
                },
                scales: {
                    x: {
                        ticks: {
                            maxRotation: 45,
                            minRotation: 30,
                            font: {
                                size: 10
                            }
                        }
                    },
                    y: {
                        beginAtZero: false,
                        title: {
                            display: true,
                            text: 'Velocitat (cm/s)'
                        }
                    }
                }
            }
        });
    }

    // Event del botó de gràfics de velocitat
    $('.btn-show-speed-charts').click(function() {
        const endDate = new Date();
        const startDate = new Date();
        startDate.setDate(startDate.getDate() - 20);

        $('#speedEndDate').val(endDate.toISOString().split('T')[0]);
        $('#speedStartDate').val(startDate.toISOString().split('T')[0]);

        loadSpeedChartData();
        speedModal.show();
    });

    // Events dels botons del modal de velocitat
    $('#speedFilterBtn').click(loadSpeedChartData);
    $('#speedResetBtn').click(function() {
        const endDate = new Date();
        const startDate = new Date();
        startDate.setDate(startDate.getDate() - 20);
        $('#speedEndDate').val(endDate.toISOString().split('T')[0]);
        $('#speedStartDate').val(startDate.toISOString().split('T')[0]);
        loadSpeedChartData();
    });
});
</script>

<?php include __DIR__ . '/includes/footer.php'; ?>
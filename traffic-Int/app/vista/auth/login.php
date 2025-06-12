<?php
// Inclou el capçalera de la pàgina des de la ruta relativa
include __DIR__ . '/../includes/header.php';
?>

<div class="card auth-card">
    <div class="card-body">
        <!-- Títol principal del formulari -->
        <h2 class="text-center mb-4">Inici de Sessió</h2>

        <?php if (isset($error)): ?>
            <!-- Mostra missatge d'error si existeix -->
            <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>

        <!-- Formulari d'inici de sessió -->
        <form method="POST">
            <!-- Camp per al correu electrònic -->
            <div class="mb-3">
                <label class="form-label">Correu electrònic:</label>
                <input type="email" name="email" class="form-control" required>
            </div>

            <!-- Camp per a la contrasenya -->
            <div class="mb-3">
                <label class="form-label">Contrasenya:</label>
                <input type="password" name="password" class="form-control" required>
            </div>

            <!-- Botó d'enviament -->
            <button type="submit" class="btn btn-primary w-100">Iniciar Sessió</button>
        </form>

        <!-- Enllaç a la pàgina de registre -->
        <div class="mt-3 text-center">
            No tens compte? <a href="/traffic-Int/public/register">Registra't</a>
        </div>
    </div>
</div>

<?php
// Inclou el peu de pàgina des de la ruta relativa
include __DIR__ . '/../includes/footer.php';
?>
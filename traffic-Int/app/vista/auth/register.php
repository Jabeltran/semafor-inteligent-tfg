<?php
// Inclou el capçalera de la pàgina des del directori pare
include __DIR__ . '/../includes/header.php';
?>

<div class="card auth-card">
    <div class="card-body">
        <!-- Títol principal del formulari de registre -->
        <h2 class="text-center mb-4">Registre d'Usuari</h2>

        <?php if (isset($_SESSION['register_success'])): ?>
            <!-- Missatge d'èxit quan el registre es completa correctament -->
            <div class="alert alert-success">
                Registre completat amb èxit. Ara pots iniciar sessió.
            </div>
            <?php unset($_SESSION['register_success']);  ?>
        <?php endif; ?>

        <?php if (isset($error)): ?>
            <!-- Missatge d'error si hi ha problemes amb el registre -->
            <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>

        <!-- Formulari de registre amb POST -->
        <form method="POST" action="/traffic-Int/public/register">
            <!-- Camp per al nom d'usuari -->
            <div class="mb-3">
                <label class="form-label">Nom d'usuari:</label>
                <input type="text" name="username" class="form-control" required
                       value="<?= isset($_POST['username']) ? htmlspecialchars($_POST['username']) : '' ?>">
            </div>

            <!-- Camp per al correu electrònic -->
            <div class="mb-3">
                <label class="form-label">Correu electrònic:</label>
                <input type="email" name="email" class="form-control" required
                       value="<?= isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''  ?>">
            </div>

            <!-- Camp per a la contrasenya -->
            <div class="mb-3">
                <label class="form-label">Contrasenya:</label>
                <input type="password" name="password" class="form-control" required>
            </div>

            <!-- Botó per enviar el formulari -->
            <button type="submit" class="btn btn-success w-100">Registrar-se</button>
        </form>

        <!-- Enllaç alternatiu per a usuaris que ja tenen compte -->
        <div class="mt-3 text-center">
            Ja tens compte? <a href="/traffic-Int/public/login">Inicia sessió</a>
        </div>
    </div>
</div>

<?php
// Inclou el peu de pàgina des del directori pare
include __DIR__ . '/../includes/footer.php';
?>
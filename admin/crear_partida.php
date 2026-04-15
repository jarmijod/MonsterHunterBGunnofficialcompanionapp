<?php
session_start();

if (!isset($_SESSION['Id_usuario']) || $_SESSION['Tipo_usuario'] !== 'admin') {
    header('Location: ../login.php');
    exit;
}

require_once '../includes/db.php';

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nombre = $_POST['nombre_partida'] ?? '';
    $descripcion = $_POST['descripcion_partida'] ?? '';
    $jugadores = $_POST['jugadores'] ?? [];

    if (!$nombre) {
        $error = "El nombre de la partida es obligatorio.";
    } else {
        // Insertar nueva partida
        $stmt = $pdo->prepare("INSERT INTO PARTIDA (Nombre_partida, Descripcion_partida) VALUES (?, ?)");
        $stmt->execute([$nombre, $descripcion]);
        $id_partida = $pdo->lastInsertId();

        // Asignar jugadores seleccionados a la partida
        if (!empty($jugadores)) {
            $stmtAsignar = $pdo->prepare("INSERT INTO PARTIDA_USUARIO (Id_usuario, Id_partida, Rol_en_partida) VALUES (?, ?, 'player')");
            foreach ($jugadores as $id_usuario) {
                $stmtAsignar->execute([$id_usuario, $id_partida]);
            }
        }

        $success = "Partida creada correctamente.";
    }
}

// Obtener lista de jugadores para asignar
$stmtJugadores = $pdo->prepare("SELECT Id_usuario, Nombre_usuario FROM USUARIO WHERE Tipo_usuario IN ('player', 'gm')");
$stmtJugadores->execute();
$jugadoresDisponibles = $stmtJugadores->fetchAll();

include '../includes/header.php';
?>

<h1>Crear nueva partida</h1>

<?php if ($error): ?>
    <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
<?php endif; ?>
<?php if ($success): ?>
    <div class="alert alert-success"><?= htmlspecialchars($success) ?></div>
<?php endif; ?>

<form method="POST" class="mb-4">
    <div class="mb-3">
        <label for="nombre_partida" class="form-label">Nombre de la partida</label>
        <input type="text" class="form-control" id="nombre_partida" name="nombre_partida" required>
    </div>

    <div class="mb-3">
        <label for="descripcion_partida" class="form-label">Descripción (opcional)</label>
        <textarea class="form-control" id="descripcion_partida" name="descripcion_partida" rows="3"></textarea>
    </div>

    <div class="mb-3">
        <label class="form-label">Asignar jugadores</label>
        <?php if (empty($jugadoresDisponibles)): ?>
            <p>No hay jugadores disponibles para asignar.</p>
        <?php else: ?>
            <select name="jugadores[]" class="form-select" multiple size="5">
                <?php foreach ($jugadoresDisponibles as $jugador): ?>
                    <option value="<?= $jugador['Id_usuario'] ?>"><?= htmlspecialchars($jugador['Nombre_usuario']) ?></option>
                <?php endforeach; ?>
            </select>
            <div class="form-text">Mantén presionada la tecla Ctrl (o Cmd) para seleccionar múltiples jugadores.</div>
        <?php endif; ?>
    </div>

    <button type="submit" class="btn btn-primary">Crear partida</button>
</form>

<?php include '../includes/footer.php'; ?>
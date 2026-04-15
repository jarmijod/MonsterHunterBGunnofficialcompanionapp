<?php
session_start();

// Verificar sesión y rol admin
if (!isset($_SESSION['Id_usuario']) || $_SESSION['Tipo_usuario'] !== 'admin') {
    header('Location: ../login.php');
    exit;
}


require_once '../includes/db.php';

$id_usuario = $_SESSION['Id_usuario'];

// Consultar si el admin tiene partidas creadas
$stmt = $pdo->prepare("SELECT Id_partida FROM PARTIDA WHERE 1"); // Todas las partidas (puedes agregar filtro si necesario)
$stmt->execute();
$partidas = $stmt->fetchAll();

if (empty($partidas)) {
    // Redirigir a crear partida si no hay partidas
    header('Location: crear_partida.php');
    exit;
}

include '../includes/header.php';
?>

<h1>Bienvenido, administrador <?= htmlspecialchars($_SESSION['Nombre_usuario']) ?></h1>
<p>Desde aquí podrás gestionar partidas, jugadores y misiones.</p>

<h3>Partidas existentes</h3>
<ul class="list-group mt-3">
    <?php foreach ($partidas as $partida): ?>
        <li class="list-group-item">
            Partida ID: <?= $partida['Id_partida'] ?>
        </li>
    <?php endforeach; ?>
</ul>

<?php include '../includes/footer.php'; ?>
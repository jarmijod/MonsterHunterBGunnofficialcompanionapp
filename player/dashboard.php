<?php
session_start();

// contenido de la página va aquí

// Verificar sesión y rol player o gm
if (!isset($_SESSION['Id_usuario']) || !in_array($_SESSION['Tipo_usuario'], ['player', 'gm'])) {
    header('Location: ../login.php');
    exit;
}

require_once '../includes/db.php';

$id_usuario = $_SESSION['Id_usuario'];

// Consultar si el jugador tiene partidas asignadas
$stmt = $pdo->prepare("
SELECT p.Id_partida, p.Nombre_partida
FROM PARTIDA_USUARIO pu
JOIN PARTIDA p ON pu.Id_partida = p.Id_partida
WHERE pu.Id_usuario = ?
");
$stmt->execute([$id_usuario]);
$partidas = $stmt->fetchAll();

include '../includes/header.php';
?>

<h1>Bienvenido, <?= htmlspecialchars($_SESSION['Nombre_usuario']) ?></h1>

<?php if (empty($partidas)): ?>
    <div class="alert alert-info mt-4">
        <strong>No estás participando en ninguna partida.</strong><br />
        Por favor, contacta con un administrador para que te asigne a una partida.
    </div>
<?php endif; ?>

<?php

include '../includes/footer.php';


?>
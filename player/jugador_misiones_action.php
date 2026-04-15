<?php
session_start();
require '../includes/db.php';

if (!isset($_SESSION['Id_usuario']) || $_SESSION['Tipo_usuario'] !== 'player') {
    echo json_encode(['success' => false, 'message' => 'No autorizado']);
    exit();
}

$data = json_decode(file_get_contents("php://input"), true);
$idMisionCazador = $data['idMisionCazador'] ?? null;
$nuevoEstado = $data['nuevoEstado'] ?? null;
$rangoExtra = $data['rangoExtra'] ?? 0;
$idUsuario = $_SESSION['Id_usuario'];
$recompensaExtra = $data['recompensaExtra'] ?? null;

if (!$idMisionCazador || !$nuevoEstado) {
    echo json_encode(['success' => false, 'message' => 'Datos inválidos']);
    exit();
}

// Actualizar estado de la misión
$stmt = $conn->prepare("UPDATE MISION_CAZADOR SET Estado_MisionCazador = ? WHERE Id_MisionCazador = ? AND Id_Usuario = ?");
$ok = $stmt->execute([$nuevoEstado, $idMisionCazador, $idUsuario]);

if ($ok && $nuevoEstado === 'completed' && $rangoExtra > 0) {
    $stmt2 = $conn->prepare("UPDATE USUARIO SET Rango_usuario = Rango_usuario + ? WHERE Id_usuario = ?");
    $stmt2->execute([$rangoExtra, $idUsuario]);
}

echo json_encode(['success' => $ok]);
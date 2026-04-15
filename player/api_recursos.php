<?php
session_start();

header('Content-Type: application/json');

if (!isset($_SESSION['Id_usuario']) || $_SESSION['Tipo_usuario'] !== 'player') {
    echo json_encode(['success' => false, 'message' => 'No autorizado']);
    exit;
}

require_once '../includes/db.php';

$input = json_decode(file_get_contents('php://input'), true);

if (!isset($input['recurso'], $input['cambio'])) {
    echo json_encode(['success' => false, 'message' => 'Parámetros inválidos']);
    exit;
}

$id_usuario = $_SESSION['Id_usuario'];
session_write_close();
$id_recurso = intval($input['recurso']);
$cambio = intval($input['cambio']);

// Obtener cantidad actual
$stmt = $pdo->prepare("SELECT Cantidad_BaulRecurso FROM baul_recurso WHERE Id_usuario = ? AND Id_recurso = ?");
$stmt->execute([$id_usuario, $id_recurso]);
$registro = $stmt->fetch();

if (!$registro) {
    echo json_encode(['success' => false, 'message' => 'Recurso no encontrado para el usuario']);
    exit;
}

$nuevaCantidad = $registro['Cantidad_BaulRecurso'] + $cambio;
if ($nuevaCantidad < 0) $nuevaCantidad = 0;

// Actualizar cantidad
$stmtUpdate = $pdo->prepare("UPDATE baul_recurso SET Cantidad_BaulRecurso = ?, updated_at = NOW() WHERE Id_usuario = ? AND Id_recurso = ?");
if ($stmtUpdate->execute([$nuevaCantidad, $id_usuario, $id_recurso])) {
    echo json_encode(['success' => true, 'nuevaCantidad' => $nuevaCantidad]);
} else {
    echo json_encode(['success' => false, 'message' => 'Error al actualizar cantidad']);
}

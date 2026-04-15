<?php
session_start();
require_once '../includes/db.php';

if (!isset($_SESSION['Id_usuario'])) {
    echo json_encode(['success' => false, 'message' => 'No hay sesión activa']);
    exit;
}

$userId = $_SESSION['Id_usuario'];
$idArmadura = intval($_POST['idArmadura'] ?? 0);

if ($idArmadura <= 0) {
    echo json_encode(['success' => false, 'message' => 'ID inválido']);
    exit;
}

// 1) Obtener parte de la armadura seleccionada
$sqlParte = "SELECT Parte_armadura FROM ARMADURA WHERE Id_armadura = :id";
$stmt = $pdo->prepare($sqlParte);
$stmt->execute(['id' => $idArmadura]);
$parte = $stmt->fetchColumn();

if (!$parte) {
    echo json_encode(['success' => false, 'message' => 'Armadura no encontrada']);
    exit;
}

try {
    $pdo->beginTransaction();

    // 2) Des-equipar todas las armaduras del mismo usuario y misma parte
    $sqlUpdate = "
        UPDATE BAUL_EQUIPO b
        INNER JOIN ARMADURA a ON b.Id_armadura = a.Id_armadura
        SET b.Estado_BaulEquipo = 'forjada'
        WHERE b.Id_usuario = :userId AND a.Parte_armadura = :parte AND b.Estado_BaulEquipo = 'equipada'
    ";
    $stmt = $pdo->prepare($sqlUpdate);
    $stmt->execute(['userId' => $userId, 'parte' => $parte]);

    // 3) Equipar la seleccionada
    $sqlEquipar = "
        UPDATE BAUL_EQUIPO 
        SET Estado_BaulEquipo = 'equipada'
        WHERE Id_usuario = :userId AND Id_armadura = :idArmadura
    ";
    $stmt = $pdo->prepare($sqlEquipar);
    $stmt->execute(['userId' => $userId, 'idArmadura' => $idArmadura]);

    $pdo->commit();

    echo json_encode(['success' => true, 'message' => 'Armadura equipada correctamente']);
} catch (Exception $e) {
    $pdo->rollBack();
    echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
}
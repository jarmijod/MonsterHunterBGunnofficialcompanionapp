<?php
session_start();
require_once '../includes/db.php';

header('Content-Type: application/json');

if (!isset($_SESSION['Id_usuario'])) {
    echo json_encode(['success' => false, 'message' => 'No hay sesión activa']);
    exit;
}

$userId = $_SESSION['Id_usuario'];

// Leer datos (espera JSON)
$input = json_decode(file_get_contents("php://input"), true);
$idArma = intval($input['id_arma'] ?? 0);

if ($idArma <= 0) {
    echo json_encode(['success' => false, 'message' => 'ID de arma inválido']);
    exit;
}

try {
    $pdo->beginTransaction();

    // 1) Buscar las armas actualmente equipadas de este usuario
    $sqlEquipped = "
        SELECT Id_arma FROM BAUL_EQUIPO 
        WHERE Id_usuario = :userId 
          AND Id_arma IS NOT NULL
          AND Estado_BaulEquipo = 'equipada'
    ";
    $stmt = $pdo->prepare($sqlEquipped);
    $stmt->execute(['userId' => $userId]);
    $equipadas = $stmt->fetchAll(PDO::FETCH_COLUMN);

    $desequipadas = [];

    if ($equipadas) {
        // 2) Des-equipar todas las anteriores (pasarlas a 'forjada')
        $sqlUpdate = "
            UPDATE BAUL_EQUIPO 
            SET Estado_BaulEquipo = 'forjada'
            WHERE Id_usuario = :userId 
              AND Id_arma IS NOT NULL
              AND Estado_BaulEquipo = 'equipada'
        ";
        $stmt = $pdo->prepare($sqlUpdate);
        $stmt->execute(['userId' => $userId]);
        $desequipadas = $equipadas;
    }

    // 3) Equipar el arma seleccionada
    $sqlEquipar = "
        UPDATE BAUL_EQUIPO 
        SET Estado_BaulEquipo = 'equipada'
        WHERE Id_usuario = :userId 
          AND Id_arma = :idArma
    ";
    $stmt = $pdo->prepare($sqlEquipar);
    $stmt->execute(['userId' => $userId, 'idArma' => $idArma]);

    $pdo->commit();

    echo json_encode([
        'success' => true,
        'message' => 'Arma equipada correctamente',
        'desequipadas' => $desequipadas
    ]);
} catch (Exception $e) {
    $pdo->rollBack();
    echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
}
<?php
session_start();
require_once '../includes/db.php';

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

if (!isset($_SESSION['Id_usuario'])) {
    echo json_encode(['success' => false, 'message' => 'No autenticado']);
    exit;
}

$userId = $_SESSION['Id_usuario'];
$idArmadura = intval($_POST['idArmadura'] ?? 0);
if ($idArmadura <= 0) {
    echo json_encode(['success' => false, 'message' => 'ID de armadura inválido']);
    exit;
}

// Obtener el registro en baul equipo del usuario para esa armadura
$stmt = $pdo->prepare("SELECT Id_BaulEquipo, Estado_BaulEquipo FROM BAUL_EQUIPO WHERE Id_usuario = :userId AND Id_armadura = :idArma LIMIT 1");
$stmt->execute(['userId' => $userId, 'idArma' => $idArmadura]);
$baul = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$baul) {
    echo json_encode(['success' => false, 'message' => 'Armadura no disponible en el baúl']);
    exit;
}

if ($baul['Estado_BaulEquipo'] === 'forjada' || $baul['Estado_BaulEquipo'] === 'equipada') {
    echo json_encode(['success' => false, 'message' => 'Armadura ya forjada']);
    exit;
}

// Obtener requisitos de la armadura
$stmt = $pdo->prepare("SELECT Requisitos_armadura FROM ARMADURA WHERE Id_armadura = :idArma LIMIT 1");
$stmt->execute(['idArma' => $idArmadura]);
$reqJson = $stmt->fetchColumn();

$requisitos = json_decode($reqJson, true) ?? [];

// Obtener recursos del usuario (supongo tabla RECURSO_USUARIO o similar)
$stmt = $pdo->prepare("SELECT  r.nombre_recurso, b.Cantidad_BaulRecurso FROM Baul_recurso b JOIN recurso r on r.Id_recurso = b.Id_recurso WHERE Id_usuario = :userId");
$stmt->execute(['userId' => $userId]);
$recursosUsuario = $stmt->fetchAll(PDO::FETCH_KEY_PAIR);

// Validar recursos
foreach ($requisitos as $recurso => $cantidadNecesaria) {
    $cantidadUsuario = $recursosUsuario[$recurso] ?? 0;
    if ($cantidadUsuario < $cantidadNecesaria) {
        echo json_encode(['success' => false, 'message' => "Faltan recursos: $recurso"]);
        exit;
    }
}

// Restar recursos usados
$pdo->beginTransaction();
try {
    foreach ($requisitos as $recurso => $cantidadNecesaria) {
        $stmt = $pdo->prepare("UPDATE BAUL_RECURSO JOIN RECURSO ON BAUL_RECURSO.Id_recurso = RECURSO.Id_recurso SET BAUL_RECURSO.Cantidad_BaulRecurso = BAUL_RECURSO.Cantidad_BaulRecurso - :cant WHERE BAUL_RECURSO.Id_usuario = :userId AND RECURSO.Nombre_recurso = :recurso");
        $stmt->execute(['cant' => $cantidadNecesaria, 'userId' => $userId, 'recurso' => $recurso]);
    }

    // Actualizar estado armadura a forjada
    $stmt = $pdo->prepare("UPDATE BAUL_EQUIPO SET Estado_BaulEquipo = 'forjada' WHERE Id_BaulEquipo = :idBaul");
    $stmt->execute(['idBaul' => $baul['Id_BaulEquipo']]);

    $pdo->commit();
    echo json_encode(['success' => true, 'message' => 'Armadura forjada con éxito']);
} catch (Exception $e) {
    $pdo->rollBack();
    echo json_encode(['success' => false, 'message' => 'Error al forjar armadura']);
}
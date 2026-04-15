<?php

// player/ajax_forjar_arma.php
header('Content-Type: application/json; charset=utf-8');
session_start();

if (!isset($_SESSION['Id_usuario'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'No autorizado.']);
    exit;
}

require_once '../includes/db.php'; // ajustar si es necesario

// Soportar POST form-data o JSON
$armaId = 0;
if (isset($_POST['id_arma'])) {
    $armaId = intval($_POST['id_arma']);
} else {
    $body = json_decode(file_get_contents('php://input'), true);
    if (!empty($body['id_arma'])) $armaId = intval($body['id_arma']);
}

$userId = intval($_SESSION['Id_usuario']);
if ($armaId <= 0) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'ID de arma inválido.']);
    exit;
}

try {
    // 1) Obtener arma y registro en BAUL_EQUIPO para este usuario
    $sql = "
      SELECT ar.*, b.Id_BaulEquipo, b.Estado_BaulEquipo
      FROM ARMA ar
      LEFT JOIN BAUL_EQUIPO b ON b.Id_arma = ar.Id_arma AND b.Id_usuario = :userId
      WHERE ar.Id_arma = :idArma
      LIMIT 1
    ";
    $stmt = $pdo->prepare($sql);
    $stmt->execute(['userId' => $userId, 'idArma' => $armaId]);
    $row = $stmt->fetch();

    if (!$row) {
        http_response_code(404);
        echo json_encode(['success' => false, 'message' => 'Arma no encontrada.']);
        exit;
    }

    if (empty($row['Id_BaulEquipo'])) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'El arma no está en tu baúl.']);
        exit;
    }

    $idBaulEquipo = intval($row['Id_BaulEquipo']);
    $estadoActual = $row['Estado_BaulEquipo'] ?? 'disponible';

    if ($estadoActual === 'forjada') {
        echo json_encode(['success' => false, 'message' => 'El arma ya está forjada.', 'estado' => 'forjada']);
        exit;
    }
    if ($estadoActual === 'equipada') {
        echo json_encode(['success' => false, 'message' => 'El arma ya está equipada. Desequípala antes.']);
        exit;
    }

    // Obtener registro del arma a forjar en baul del usuario
    $stmtArmaBaul = $pdo->prepare("
    SELECT Id_BaulEquipo, Estado_BaulEquipo, IdArmaAnterior_BaulEquipo
    FROM BAUL_EQUIPO
    WHERE Id_usuario = :userId AND Id_arma = :armaId
    LIMIT 1
");
    $stmtArmaBaul->execute(['userId' => $userId, 'armaId' => $armaId]);
    $registroArma = $stmtArmaBaul->fetch(PDO::FETCH_ASSOC);

    if (!$registroArma) {
        echo json_encode(['success' => false, 'message' => 'El arma no está disponible en el baúl del usuario.']);
        exit;
    }


    // 4) Si no hay requisitos: forjar (pero ya pasamos la verificación de secuencia)
    if (empty($requisitos)) {
        $pdo->beginTransaction();
        $u = $pdo->prepare("UPDATE BAUL_EQUIPO SET Estado_BaulEquipo = 'forjada' WHERE Id_BaulEquipo = :idBaul");
        $u->execute(['idBaul' => $idBaulEquipo]);
        $pdo->commit();

        echo json_encode([
            'success' => true,
            'message' => 'Arma Desbloqueada.',
            'estado' => 'forjada',
            'updatedResources' => []
        ]);
        exit;
    }
} catch (Exception $e) {
    if ($pdo->inTransaction()) $pdo->rollBack();
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Error del servidor: ' . $e->getMessage()]);
    exit;
}
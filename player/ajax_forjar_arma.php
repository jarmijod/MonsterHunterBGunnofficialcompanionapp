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

    $armaAnteriorId = $registroArma['IdArmaAnterior_BaulEquipo'];

    // Si tiene arma anterior, validar que esté forjada
    if ($armaAnteriorId !== null) {
        $stmtCheckAnterior = $pdo->prepare("
        SELECT Estado_BaulEquipo FROM BAUL_EQUIPO
        WHERE Id_usuario = :userId AND Id_arma = :armaAnteriorId
        LIMIT 1
    ");
        $stmtCheckAnterior->execute(['userId' => $userId, 'armaAnteriorId' => $armaAnteriorId]);
        $estadoAnterior = $stmtCheckAnterior->fetchColumn();

        if (!$estadoAnterior || strtolower($estadoAnterior) === 'disponible') {
            echo json_encode(['success' => false, 'message' => 'Debes forjar primero el arma anterior.']);
            exit;
        }
    }

    // Continuar con validación normal de recursos y forja

    if (!empty($blocked)) {
        // Devolver info de bloqueo al cliente
        http_response_code(400);
        echo json_encode([
            'success' => false,
            'message' => 'No puedes forjar esta arma porque hay armas previas del set que no están forjadas.',
            'blocked_previous' => $blocked
        ]);
        exit;
    }

    // 3) Obtener requisitos del arma (JSON)
    $requisitos = [];
    if (!empty($row['Requisitos_arma'])) {
        $tmp = json_decode($row['Requisitos_arma'], true);
        if (is_array($tmp)) $requisitos = $tmp;
    }

    // 4) Si no hay requisitos: forjar (pero ya pasamos la verificación de secuencia)
    if (empty($requisitos)) {
        $pdo->beginTransaction();
        $u = $pdo->prepare("UPDATE BAUL_EQUIPO SET Estado_BaulEquipo = 'forjada' WHERE Id_BaulEquipo = :idBaul");
        $u->execute(['idBaul' => $idBaulEquipo]);
        $pdo->commit();

        echo json_encode([
            'success' => true,
            'message' => 'Arma marcada como forjada (sin requisitos).',
            'estado' => 'forjada',
            'updatedResources' => []
        ]);
        exit;
    }

    // 5) Mapear nombres de recurso a Id_recurso
    $nombresRecursos = array_keys($requisitos);
    $placeholders = implode(',', array_fill(0, count($nombresRecursos), '?'));
    $stmt = $pdo->prepare("SELECT Id_recurso, Nombre_recurso FROM RECURSO WHERE Nombre_recurso IN ($placeholders)");
    $stmt->execute($nombresRecursos);
    $rowsRec = $stmt->fetchAll();

    $nombreToId = [];
    foreach ($rowsRec as $r) $nombreToId[$r['Nombre_recurso']] = intval($r['Id_recurso']);

    // Verificar que todos los requisitos existan en tabla RECURSO
    $missingResources = [];
    foreach ($nombresRecursos as $nombre) {
        if (!isset($nombreToId[$nombre])) $missingResources[] = $nombre;
    }
    if (!empty($missingResources)) {
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => 'Faltan recursos en RECURSO para algunos requisitos.', 'missing' => $missingResources]);
        exit;
    }

    // 6) Transacción: bloquear filas BAUL_RECURSO con FOR UPDATE, verificar cantidades y descontar
    $pdo->beginTransaction();
    $selectStmt = $pdo->prepare("SELECT Id_BaulRecurso, Cantidad_BaulRecurso FROM BAUL_RECURSO WHERE Id_usuario = :userId AND Id_recurso = :idRecurso FOR UPDATE");
    $updateStmt = $pdo->prepare("UPDATE BAUL_RECURSO SET Cantidad_BaulRecurso = Cantidad_BaulRecurso - :delta, updated_at = NOW() WHERE Id_usuario = :userId AND Id_recurso = :idRecurso");
    $resourceData = [];
    foreach ($requisitos as $nombre => $need) {
        $idRec = $nombreToId[$nombre];
        $selectStmt->execute(['userId' => $userId, 'idRecurso' => $idRec]);
        $rrow = $selectStmt->fetch();
        $have = $rrow ? intval($rrow['Cantidad_BaulRecurso']) : 0;
        $resourceData[$nombre] = ['id_recurso' => $idRec, 'have' => $have, 'need' => intval($need)];
    }

    $insuficientes = [];
    foreach ($resourceData as $nombre => $d) {
        if ($d['have'] < $d['need']) $insuficientes[$nombre] = ['need' => $d['need'], 'have' => $d['have']];
    }
    if (!empty($insuficientes)) {
        $pdo->rollBack();
        echo json_encode(['success' => false, 'message' => 'Recursos insuficientes', 'insuficientes' => $insuficientes]);
        exit;
    }



    // Descontar
    foreach ($resourceData as $nombre => $d) {
        $updateStmt->execute(['delta' => $d['need'], 'userId' => $userId, 'idRecurso' => $d['id_recurso']]);
    }

    // Marcar arma forjada
    $updateEquipo = $pdo->prepare("UPDATE BAUL_EQUIPO SET Estado_BaulEquipo = 'forjada' WHERE Id_BaulEquipo = :idBaul");
    $updateEquipo->execute(['idBaul' => $idBaulEquipo]);

    // Obtener cantidades actualizadas
    $params = array_merge([$userId], $nombresRecursos);
    $placeholders2 = implode(',', array_fill(0, count($nombresRecursos), '?'));
    $sqlNow = "SELECT r.Nombre_recurso, COALESCE(br.Cantidad_BaulRecurso,0) AS Cantidad
               FROM RECURSO r
               LEFT JOIN BAUL_RECURSO br ON br.Id_recurso = r.Id_recurso AND br.Id_usuario = ?
               WHERE r.Nombre_recurso IN ($placeholders2)";
    $stmt = $pdo->prepare($sqlNow);
    $stmt->execute($params);
    $updatedRows = $stmt->fetchAll();

    $updated = [];
    foreach ($updatedRows as $ur) $updated[$ur['Nombre_recurso']] = intval($ur['Cantidad']);

    $pdo->commit();

    echo json_encode([
        'success' => true,
        'message' => 'Arma forjada correctamente.',
        'estado' => 'forjada',
        'updatedResources' => $updated
    ]);
    exit;
} catch (Exception $e) {
    if ($pdo->inTransaction()) $pdo->rollBack();
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Error del servidor: ' . $e->getMessage()]);
    exit;
}
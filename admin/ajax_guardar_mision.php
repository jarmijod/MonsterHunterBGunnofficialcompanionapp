<?php
// ajax_guardar_mision.php
header('Content-Type: application/json; charset=utf-8');
// Opcional en desarrollo:
// ini_set('display_errors', 1); ini_set('display_startup_errors', 1); error_reporting(E_ALL);

session_start();
require_once '../includes/db.php'; // ajusta ruta si hace falta

// Autorización mínima: solo admins
if (!isset($_SESSION['Tipo_usuario']) || $_SESSION['Tipo_usuario'] !== 'admin') {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'No autorizado']);
    exit;
}

// Validar método
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Método no permitido']);
    exit;
}

try {
    // Recibir valores de POST (sanitizar mínimamente)
    $titulo = trim($_POST['titulo'] ?? '');
    $descripcion = trim($_POST['descripcion'] ?? '');
    $recompensa = trim($_POST['recompensa'] ?? '{}');
    $rango = intval($_POST['rango'] ?? 0);
    $jugadores = $_POST['jugadores'] ?? []; // array de ids

    // Validaciones básicas
    if ($titulo === '') {
        echo json_encode(['success' => false, 'message' => 'El título es requerido']);
        exit;
    }


    // Insert con transacción
    $pdo->beginTransaction();

    $stmt = $pdo->prepare("INSERT INTO MISION (Estado_Mision, titulo_mision, Descripción_Mision, Rango_Mision, Recompensa_Mision) VALUES ('available', :titulo, :descripcion, :rango, :recompensa)");
    $stmt->execute([
        ':titulo' => $titulo,
        ':descripcion' => $descripcion,
        ':rango' => $rango,
        ':recompensa' => $recompensa
    ]);
    $idMision = $pdo->lastInsertId();

    if (!empty($jugadores) && is_array($jugadores)) {
        $stmtAssign = $pdo->prepare("INSERT INTO MISION_CAZADOR (Id_Mision, Id_Usuario, Estado_MisionCazador) VALUES (:idMision, :idUsuario, 'assigned')");
        foreach ($jugadores as $jugadorId) {
            $stmtAssign->execute([':idMision' => $idMision, ':idUsuario' => intval($jugadorId)]);
        }
    }

    $pdo->commit();

    echo json_encode(['success' => true, 'message' => 'Misión creada correctamente', 'id' => $idMision]);
} catch (Exception $e) {
    if ($pdo->inTransaction()) $pdo->rollBack();
    // devuelve el mensaje en JSON (en dev). En prod devuelve mensaje genérico.
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Error del servidor: ' . $e->getMessage()]);
}
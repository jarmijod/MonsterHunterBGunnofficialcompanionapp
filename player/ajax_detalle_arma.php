<?php
session_start();
header('Content-Type: application/json');
require_once '../includes/db.php'; // ajusta la ruta si es necesario

if (!isset($_SESSION['Id_usuario'])) {
    http_response_code(403);
    echo json_encode(['error' => 'No autorizado']);
    exit;
}

$userId = $_SESSION['Id_usuario'];
$id = isset($_GET['id']) ? intval($_GET['id']) : 0;
if ($id <= 0) {
    http_response_code(400);
    echo json_encode(['error' => 'ID inválido']);
    exit;
}

// Obtener arma + estado del baul (si existe para este usuario)
$sql = "SELECT ar.*, b.Estado_BaulEquipo
        FROM ARMA ar
        LEFT JOIN BAUL_EQUIPO b ON b.Id_arma = ar.Id_arma AND b.Id_usuario = :userId
        WHERE ar.Id_arma = :id
        LIMIT 1";
$stmt = $pdo->prepare($sql);
$stmt->execute(['userId' => $userId, 'id' => $id]);
$arma = $stmt->fetch();

if (!$arma) {
    http_response_code(404);
    echo json_encode(['error' => 'Arma no encontrada']);
    exit;
}

// Requisitos en formato asociativo
$requisitos = [];
if (!empty($arma['Requisitos_arma'])) {
    $tmp = json_decode($arma['Requisitos_arma'], true);
    if (is_array($tmp)) $requisitos = $tmp;
}

// Obtener inventario del usuario para los recursos implicados (por nombre)
$recursosNombre = array_keys($requisitos);
$recursosUsuario = [];
if (count($recursosNombre) > 0) {
    // Buscamos Id_recurso por nombre y luego la cantidad en BAUL_RECURSO
    // construcción segura de placeholders
    $placeholders = implode(',', array_fill(0, count($recursosNombre), '?'));
    $params = array_merge([$userId], $recursosNombre);

    $sqlRec = "SELECT r.Nombre_recurso, COALESCE(br.Cantidad_BaulRecurso,0) AS Cantidad
             FROM RECURSO r
             LEFT JOIN BAUL_RECURSO br ON br.Id_recurso = r.Id_recurso AND br.Id_usuario = ?
             WHERE r.Nombre_recurso IN ($placeholders)";
    $stmt = $pdo->prepare($sqlRec);
    $stmt->execute($params);
    $rows = $stmt->fetchAll();
    foreach ($rows as $row) {
        $recursosUsuario[$row['Nombre_recurso']] = intval($row['Cantidad']);
    }
}

// Responder
echo json_encode([
    'arma' => $arma,
    'requisitos' => $requisitos,
    'recursosUsuario' => $recursosUsuario,
    'estado' => $arma['Estado_BaulEquipo'] ?? null
]);
<?php
session_start();
require_once '../includes/db.php';

if (!isset($_SESSION['Id_usuario'])) {
    http_response_code(403);
    echo json_encode(['error' => 'No autorizado']);
    exit;
}

$userId = $_SESSION['Id_usuario'];
$idArmadura = $_GET['id'] ?? null;
if (!$idArmadura || !is_numeric($idArmadura)) {
    http_response_code(400);
    echo json_encode(['error' => 'ID inválido']);
    exit;
}

// Consulta datos del armadura
$sqlArmadura = "SELECT * FROM ARMADURA WHERE Id_armadura = :id";
$stmt = $pdo->prepare($sqlArmadura);
$stmt->execute(['id' => $idArmadura]);
$armadura = $stmt->fetch();

if (!$armadura) {
    http_response_code(404);
    echo json_encode(['error' => 'Armadura no encontrada']);
    exit;
}

// Consulta estado del armadura
$sqlEstado = "SELECT * FROM BAUL_EQUIPO WHERE Id_armadura = :id AND Id_usuario = :userId";
$stmt = $pdo->prepare($sqlEstado);
$stmt->execute(['id' => $idArmadura, 'userId' => $userId]);

$estado = $stmt->fetch();


if (!$estado) {
    http_response_code(404);
    echo json_encode(['error' => 'Estado de armadura no encontrado']);
    exit;
}

// Requisitos es JSON, decodificamos
$requisitos = json_decode($armadura['Requisitos_armadura'], true) ?: [];

// Obtenemos inventario de recursos del usuario para esos recursos
$recursosNombre = array_keys($requisitos);
if (count($recursosNombre) > 0) {
    // Preparar consulta para obtener recursos del usuario que coinciden con requisitos
    $placeholders = implode(',', array_fill(0, count($recursosNombre), '?'));
    $sqlRecursos = "
        SELECT r.Nombre_recurso, br.Cantidad_BaulRecurso
        FROM RECURSO r
        LEFT JOIN BAUL_RECURSO br ON br.Id_recurso = r.Id_recurso AND br.Id_usuario = ?
        WHERE r.Nombre_recurso IN ($placeholders)
    ";
    $stmt = $pdo->prepare($sqlRecursos);
    $stmt->execute(array_merge([$userId], $recursosNombre));
    $recursosUsuario = $stmt->fetchAll(PDO::FETCH_KEY_PAIR); // Nombre_recurso => Cantidad
} else {
    $recursosUsuario = [];
}


// Respuesta
echo json_encode([
    'armadura' => $armadura,
    'requisitos' => $requisitos,
    'recursosUsuario' => $recursosUsuario,
    'estado' => $estado,
    //hay que agregar el dato del estado de la armadura
]);
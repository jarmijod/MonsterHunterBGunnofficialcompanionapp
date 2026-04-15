<?php
require_once __DIR__ . '/../config.php';

try {
    // Conexión PDO
    $pdo = new PDO(
        "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4",
        DB_USER,
        DB_PASS,
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        ]
    );

    // Alias para compatibilidad con otros módulos
    $conn = $pdo;
} catch (PDOException $e) {
    // Aquí podrías loguear el error y mostrar un mensaje amigable
    die("Error al conectar a la base de datos: " . $e->getMessage());
}

// Conexión PDO asumida en $pdo

// Obtener todos los usuarios tipo 'player'
$usuarios = $pdo->query("SELECT Id_usuario FROM USUARIO WHERE Tipo_usuario = 'player'")->fetchAll(PDO::FETCH_COLUMN);

// Obtener todos los sets distintos
$sets = $pdo->query("SELECT DISTINCT NombreSet_SetArma FROM SET_ARMA")->fetchAll(PDO::FETCH_COLUMN);

// Por cada usuario
foreach ($usuarios as $userId) {
    foreach ($sets as $setName) {
        // Obtener armas del set ordenadas por Id_arma asc
        $stmtArmas = $pdo->prepare("
            SELECT ar.Id_arma 
            FROM SET_ARMA sa 
            INNER JOIN ARMA ar ON sa.Id_Arma = ar.Id_arma
            WHERE sa.NombreSet_SetArma = :setName
            ORDER BY ar.Id_arma ASC
        ");
        $stmtArmas->execute(['setName' => $setName]);
        $armasSet = $stmtArmas->fetchAll(PDO::FETCH_COLUMN);

        $armaAnterior = null;
        foreach ($armasSet as $armaId) {
            // Insertar registro en BAUL_EQUIPO para usuario y arma con IdArmaAnterior
            $stmtInsert = $pdo->prepare("
                UPDATE BAUL_EQUIPO 
                SET IdArmaAnterior_BaulEquipo = :armaAnterior
                WHERE Id_arma = :armaId AND Id_usuario = :userId
            ");
            $stmtInsert->execute([
                'armaId' => $armaId,
                'userId' => $userId,
                'armaAnterior' => $armaAnterior
            ]);
            $armaAnterior = $armaId; // la siguiente arma tendrá esta como anterior
        }
    }
}

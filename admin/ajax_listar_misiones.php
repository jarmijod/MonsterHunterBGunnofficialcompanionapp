<?php
include '../includes/db.php';
$res = $pdo->query("SELECT Id_Mision, Estado_Mision, Titulo_mision, Descripción_Mision, Rango_Mision, Recompensa_Mision FROM MISION ORDER BY created_at DESC");
$data = [];
while ($row = $res->fetch(PDO::FETCH_ASSOC)) {
    $data[] = [
        "Id" => $row['Id_Mision'],
        "Estado" => $row['Estado_Mision'],
        "Titulo" => $row['Titulo_mision'],
        "Descripcion" => $row['Descripción_Mision'],
        "Rango" => $row['Rango_Mision'],
        "Recompensa" => $row['Recompensa_Mision']
    ];
}
echo json_encode($data);
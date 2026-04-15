<?php
session_start();
require '../includes/db.php';

if (!isset($_SESSION['Id_usuario']) || $_SESSION['Tipo_usuario'] !== 'player') {
    header("Location: login.php");
    exit();
}

$Id_usuario = $_SESSION['Id_usuario'];


// Obtener misiones asignadas al jugador
$sql = "
    SELECT m.Id_Mision, m.Descripción_Mision, m.Recompensa_Mision, m.Rango_Mision,
           mc.Id_MisionCazador, mc.Estado_MisionCazador
    FROM MISION m
    JOIN MISION_CAZADOR mc ON m.Id_Mision = mc.Id_Mision
    WHERE mc.Id_Usuario = ?
    ORDER BY mc.Estado_MisionCazador ASC, m.created_at DESC
";
$stmt = $conn->prepare($sql);
$stmt->execute([$Id_usuario]);
$misiones = $stmt->fetchAll(PDO::FETCH_ASSOC);
include '../includes/header.php';
?>

<style>
body {
    background: #f5f6fa;
}

.accordion-button {
    font-weight: bold;
}

.card-mision {
    border-left: 5px solid #007bff;
    margin-bottom: 10px;
}

.badge {
    font-size: 0.8rem;
}
</style>



<h2 class="mb-4 text-center">📜 Misiones del Cazador</h2>

<!-- Acordeón de misiones disponibles -->
<div class="accordion accordion-flush mb-4" id="accordionDisponibles">
    <div class="accordion-item">
        <h2 class="accordion-header" id="headingDisponibles">
            <button class="accordion-button" type="button" data-bs-toggle="collapse"
                data-bs-target="#collapseDisponibles">
                Misiones Disponibles
            </button>
        </h2>
        <div id="collapseDisponibles" class="accordion-collapse collapse show">
            <div class="accordion-body">

                <?php foreach ($misiones as $m): ?>
                <?php if ($m['Estado_MisionCazador'] !== 'completed'): ?>
                <div class="card card-mision p-3">
                    <p><strong>Descripción:</strong> <?= htmlspecialchars($m['Descripción_Mision']) ?></p>
                    <p><strong>Recompensa:</strong> <?= htmlspecialchars($m['Recompensa_Mision']) ?></p>
                    <p><strong>Rango:</strong> <?= $m['Rango_Mision'] ?? 'N/A' ?></p>
                    <div class="d-flex gap-2">
                        <?php if ($m['Estado_MisionCazador'] === 'assigned'): ?>
                        <button class="btn btn-sm btn-primary"
                            onclick="actualizarMision(<?= $m['Id_MisionCazador'] ?>, 'accepted')">Aceptar</button>
                        <?php elseif ($m['Estado_MisionCazador'] === 'accepted'): ?>
                        <button class="btn btn-sm btn-success"
                            onclick="actualizarMision(<?= $m['Id_MisionCazador'] ?>, 'completed', <?= (int)$m['Rango_Mision'] ?>)">Completar</button>
                        <?php endif; ?>
                    </div>
                </div>
                <?php endif; ?>
                <?php endforeach; ?>

            </div>
        </div>
    </div>
</div>

<!-- Acordeón de misiones completadas -->
<div class="accordion" id="accordionCompletadas">
    <div class="accordion-item">
        <h2 class="accordion-header" id="headingCompletadas">
            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse"
                data-bs-target="#collapseCompletadas">
                Misiones Completadas
            </button>
        </h2>
        <div id="collapseCompletadas" class="accordion-collapse collapse">
            <div class="accordion-body">

                <?php foreach ($misiones as $m): ?>
                <?php if ($m['Estado_MisionCazador'] === 'completed'): ?>
                <div class="card card-mision p-3 bg-light">
                    <p><strong>Descripción:</strong> <?= htmlspecialchars($m['Descripción_Mision']) ?></p>
                    <p><strong>Recompensa:</strong> <?= htmlspecialchars($m['Recompensa_Mision']) ?></p>
                    <p class="text-success fw-bold">✅ Completada</p>
                </div>
                <?php endif; ?>
                <?php endforeach; ?>

            </div>
        </div>
    </div>
</div>


<script>
async function actualizarMision(idMisionCazador, nuevoEstado, rangoExtra = 0) {
    const response = await fetch('jugador_misiones_action.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json'
        },
        body: JSON.stringify({
            idMisionCazador,
            nuevoEstado,
            rangoExtra
        })
    });
    const data = await response.json();
    if (data.success) {
        location.reload();
    } else {
        alert('Error: ' + data.message);
    }
}
</script>


</html>
<?php


include '../includes/footer.php'; ?>
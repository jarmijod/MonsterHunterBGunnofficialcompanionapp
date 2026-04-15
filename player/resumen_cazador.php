<?php
session_start();
require_once '../includes/db.php';

if (!isset($_SESSION['Id_usuario'])) {
    header("Location: login.php");
    exit;
}

$userId = $_SESSION['Id_usuario'];
$sqlUsuario = "
SELECT u.Nombre_usuario, u.Rango_usuario
FROM USUARIO u
WHERE u.Id_usuario = :userId
";
$stmt = $pdo->prepare($sqlUsuario);
$stmt->execute(['userId' => $userId]);
$usuario = $stmt->fetch(PDO::FETCH_ASSOC);

// 1) Obtener arma equipada
$sqlArma = "
SELECT a.Nombre_arma, a.CartasDaño_arma, a.CartasAgregadas_arma, a.CartasQuitadas_arma
FROM BAUL_EQUIPO b
JOIN ARMA a ON b.Id_arma = a.Id_arma
WHERE b.Id_usuario = :userId AND b.Estado_BaulEquipo = 'equipada'
";
$stmt = $pdo->prepare($sqlArma);
$stmt->execute(['userId' => $userId]);
$arma = $stmt->fetch(PDO::FETCH_ASSOC);

// 2) Obtener armaduras equipadas
$sqlArmaduras = "
SELECT ar.Nombre_armadura, ar.Escudo_armadura, ar.ResistenciaElemental_armadura, ar.Habilidad_armadura
FROM BAUL_EQUIPO b
JOIN ARMADURA ar ON b.Id_armadura = ar.Id_armadura
WHERE b.Id_usuario = :userId AND b.Estado_BaulEquipo = 'equipada'
";
$stmt = $pdo->prepare($sqlArmaduras);
$stmt->execute(['userId' => $userId]);
$armaduras = $stmt->fetchAll(PDO::FETCH_ASSOC);

include  '../includes/header.php';
?>


<style>
.card {
    background: #eaeaeaff;
    border: none;
    border-radius: 15px;
    margin-bottom: 1rem;
}

.card-header {
    background: #8b8b8bff;
    border-radius: 15px 15px 0 0;
}

pre.json {
    background: #dfdfdfff;
    width: 100%;
    color: #000000ff;
    padding: 1rem;
    border-radius: 10px;
    font-size: 0.9rem;
    overflow-x: auto;
    white-space: pre-wrap;


}

.tag {
    display: inline-block;
    background: #2e2e2e;
    padding: 4px 10px;
    margin: 3px;
    border-radius: 8px;
    font-size: 0.85rem;
}

.tag-damage {
    background: #ff5252;
    color: #fff;
}

.tag-add {
    background: #4caf50;
    color: #fff;
}

.tag-remove {
    background: #ff9800;
    color: #fff;
}
</style>


<div class="container-sm py-4">
    <h2 class="text-center mb-4">⚔️ Resumen del Cazador</h2>
    <h4 class="text-center mb-4">Jugador: <?= htmlspecialchars($usuario['Nombre_usuario'] ?? 'Desconocido') ?> |
        Rango:
        <?= htmlspecialchars($usuario['Rango_usuario'] ?? 'N/A') ?></h4>

    <!-- Arma equipada -->
    <?php if ($arma): ?>
    <div class="card">
        <div class="card-header">
            <h5><?= htmlspecialchars($arma['Nombre_arma']) ?></h5>
        </div>
        <div class="card-body">
            <h6>Cartas de Daño</h6>
            <div>
                <?php foreach (json_decode($arma['CartasDaño_arma'], true) ?? [] as $carta => $valor): ?>
                <span class="tag tag-damage"> <?= $carta ?> x<?= $valor ?> </span>
                <?php endforeach; ?>
            </div>

            <?php if ($arma['CartasAgregadas_arma'] && $arma['CartasAgregadas_arma'] != '[]'): ?>
            <h6 class="mt-3">Cartas Agregadas</h6>
            <div>
                <?php foreach (json_decode($arma['CartasAgregadas_arma'], true) ?? [] as $carta => $valor): ?>
                <span class="tag tag-add"> <?= $carta ?> x<?= $valor ?> </span>
                <?php endforeach; ?>
            </div>
            <?php endif; ?>

            <?php if ($arma['CartasQuitadas_arma'] && $arma['CartasQuitadas_arma'] != '[]'): ?>
            <h6 class="mt-3">Cartas Quitadas</h6>
            <div>
                <?php foreach (json_decode($arma['CartasQuitadas_arma'], true) ?? [] as $carta => $valor): ?>
                <span class="tag tag-remove"> <?= $carta ?> x<?= $valor ?> </span>
                <?php endforeach; ?>
            </div>
            <?php endif; ?>

        </div>
    </div>
    <?php endif; ?>

    <!-- Armaduras equipadas -->
    <?php foreach ($armaduras as $armadura): ?>
    <table class="table table-sm table-striped mb-0">
        <tr>
            <div class="card">
                <div class="card-header">
                    <h5><?= htmlspecialchars($armadura['Nombre_armadura']) ?></h5>
                </div>
                <div class="card-body">
                    <?php if ($armadura['Escudo_armadura']): ?>
                    <h6>🛡 Escudos</h6>
                    <pre
                        class="json"><?= json_encode(json_decode($armadura['Escudo_armadura'], true), JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) ?></pre>
                    <?php endif; ?>

                    <?php if ($armadura['ResistenciaElemental_armadura']): ?>
                    <h6>🔥 Resistencia Elemental</h6>
                    <pre
                        class="json"><?= str_replace(["{", "}"], "", $armadura['ResistenciaElemental_armadura']) ?></pre>
                    <?php endif; ?>

                    <?php if ($armadura['Habilidad_armadura']): ?>
                    <h6>✨ Habilidades</h6>
                    <pre class="json"><?= $armadura['Habilidad_armadura'] ?></pre>
                    <?php endif; ?>
                </div>
            </div>
        </tr>
    </table>
    <?php endforeach; ?>
</div>


<?php
// Si usas footer.php que cierre body/html, inclúyelo; si no, omítelo.
include '../includes/footer.php';
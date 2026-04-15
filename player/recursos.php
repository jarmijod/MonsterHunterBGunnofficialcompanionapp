<?php

// esta version es segura
session_start();

if (!isset($_SESSION['Id_usuario']) || $_SESSION['Tipo_usuario'] !== 'player') {
    header('Location: ../login.php');
    exit;
}

require_once '../includes/db.php';

$id_usuario = $_SESSION['Id_usuario'];

// Cargar categorías en orden específico
$categorias = ['Común', 'Otros', 'Gran Jagras', 'Barroth', 'Kulu-Ya-Ku', 'Pukei-Pukei', 'Tobi-Kadachi', 'Anjanath', 'Jyuratodus', 'Rathalos', 'Rathalos Celeste', 'Diablos', 'Diablos Negra', 'Kushala Daora', 'Nergigante', 'Teostra'];

// Cargar recursos agrupados por categoría
$recursos_por_categoria = [];

$stmt = $pdo->prepare("SELECT r.Id_recurso, r.Nombre_recurso, r.Descripcion_recurso, r.Categoria_recurso, br.Cantidad_BaulRecurso
    FROM RECURSO r
    JOIN baul_recurso br ON r.Id_recurso = br.Id_recurso
    WHERE br.Id_usuario = ?
    ORDER BY FIELD(r.Categoria_recurso, 'Común', 'Otros', 'Gran Jagras', 'Barroth', 'Kulu-Ya-Ku', 'Pukei-Pukei', 'Tobi-Kadachi', 'Anjanath', 'Jyuratodus', 'Rathalos', 'Rathalos Celeste', 'Diablos', 'Diablos Negra', 'Kushala Daora', 'Nergigante', 'Teostra'), r.Id_recurso");

$stmt->execute([$id_usuario]);
$recursos = $stmt->fetchAll();

foreach ($categorias as $cat) {
    $recursos_por_categoria[$cat] = array_filter($recursos, fn($r) => $r['Categoria_recurso'] === $cat);
}

include '../includes/header.php';
?>

<div class="container mt-4">
    <h1>Mis Recursos</h1>
    <div class="accordion" id="recursosAccordion">
        <?php foreach ($categorias as $index => $cat):
            $idCollapse = "collapse" . $index;
        ?>
            <div class="accordion-item">
                <h2 class="accordion-header" id="heading<?= $index ?>">
                    <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse"
                        data-bs-target="#<?= $idCollapse ?>" aria-expanded="false" aria-controls="<?= $idCollapse ?>">
                        <?= htmlspecialchars($cat) ?>
                    </button>
                </h2>
                <div id="<?= $idCollapse ?>" class="accordion-collapse collapse" aria-labelledby="heading<?= $index ?>"
                    data-bs-parent="#recursosAccordion">
                    <div class="accordion-body">
                        <?php if (empty($recursos_por_categoria[$cat])): ?>
                            <p>No tienes recursos en esta categoría.</p>
                        <?php else: ?>
                            <table class="table table-striped">
                                <thead>
                                    <tr>
                                        <th>Recurso</th>
                                        <th>Cantidad</th>
                                        <th>Acciones</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($recursos_por_categoria[$cat] as $recurso): ?>
                                        <tr data-id="<?= $recurso['Id_recurso'] ?>">
                                            <td><?= htmlspecialchars($recurso['Nombre_recurso']) ?></td>
                                            <td class="cantidad"><?= intval($recurso['Cantidad_BaulRecurso']) ?></td>
                                            <td>
                                                <button class="btn btn-sm btn-success btn-incrementar">+</button>
                                                <button class="btn btn-sm btn-danger btn-reducir">-</button>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
</div>


<script>
    document.addEventListener('DOMContentLoaded', function() {

        // Función AJAX para incrementar o reducir recurso
        async function modificarRecurso(idRecurso, delta) {
            try {
                const res = await fetch('api_recursos.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({
                        recurso: idRecurso,
                        cambio: delta
                    })
                });
                const data = await res.json();
                if (data.success) {
                    const fila = document.querySelector(`tr[data-id='${idRecurso}']`);
                    fila.querySelector('.cantidad').textContent = data.nuevaCantidad;
                } else {
                    alert('Error: ' + data.message);
                }
            } catch (error) {
                alert('Error en la comunicación con el servidor.');
            }
        }

        // Botones para incrementar y reducir recursos
        document.querySelectorAll('.btn-incrementar').forEach(btn => {
            btn.addEventListener('click', () => {
                const idRecurso = btn.closest('tr').dataset.id;
                modificarRecurso(idRecurso, 1);
            });
        });

        document.querySelectorAll('.btn-reducir').forEach(btn => {
            btn.addEventListener('click', () => {
                const idRecurso = btn.closest('tr').dataset.id;
                modificarRecurso(idRecurso, -1);
            });
        });
    });
</script>


<?php


include '../includes/footer.php'; ?>
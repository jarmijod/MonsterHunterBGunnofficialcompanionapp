<?php
session_start();

// Ajusta la ruta según tu estructura real
require_once  '../includes/db.php';

// Si tu header.php ya imprime <html> y carga Bootstrap, usa include; si no, puedes eliminarlo.
include  '../includes/header.php';

if (!isset($_SESSION['Id_usuario'])) {
    header('Location: ..\login.php');
    exit;
}
$userId = $_SESSION['Id_usuario'];

/* --------------------------------------------------------------------------
   1) Consultas: armaduras y armas del usuario (con sus sets)
   -------------------------------------------------------------------------- */

// Armaduras
$sqlArmaduras = "
SELECT 
  b.Id_BaulEquipo, b.Estado_BaulEquipo, 
  a.Id_armadura, a.Nombre_armadura, a.Parte_armadura, a.Rareza_armadura, a.Habilidad_armadura,
  sa.Id_SetArmadura, sa.NombreSet_SetArmadura
FROM BAUL_EQUIPO b
INNER JOIN ARMADURA a ON b.Id_armadura = a.Id_armadura
LEFT JOIN SET_ARMADURA sa ON sa.Id_armadura = a.Id_armadura
WHERE b.Id_usuario = :userId
ORDER BY sa.Id_SetArmadura, a.Id_armadura, a.Parte_armadura
";
$stmt = $pdo->prepare($sqlArmaduras);
$stmt->execute(['userId' => $userId]);
$armaduras = $stmt->fetchAll();

// Agrupar armaduras por set
$armadurasPorSet = [];
foreach ($armaduras as $a) {
    $setName = $a['NombreSet_SetArmadura'] ?? 'Sin Set';
    if (!isset($armadurasPorSet[$setName])) $armadurasPorSet[$setName] = [];
    $armadurasPorSet[$setName][] = $a;
}

// Armas
$sqlArmas = "
SELECT 
  b.Id_BaulEquipo, b.Estado_BaulEquipo, 
  ar.Id_arma, ar.Nombre_arma, ar.Tipo_arma, ar.Rareza_arma,
  sa.Id_SetArma, sa.NombreSet_SetArma
FROM BAUL_EQUIPO b
INNER JOIN ARMA ar ON b.Id_arma = ar.Id_arma
LEFT JOIN SET_ARMA sa ON sa.Id_Arma = ar.Id_arma
WHERE b.Id_usuario = :userId
ORDER BY ar.Tipo_arma, COALESCE(sa.NombreSet_SetArma,'Sin Set'), ar.Id_arma
";
$stmt = $pdo->prepare($sqlArmas);
$stmt->execute(['userId' => $userId]);
$armas = $stmt->fetchAll();

// Agrupar armas por categoría (Tipo_arma) -> set -> lista de armas
$armasPorCategoria = [];
foreach ($armas as $item) {
    $cat = $item['Tipo_arma'] ?? 'Sin categoría';
    $setName = $item['NombreSet_SetArma'] ?? 'Sin Set';
    if (!isset($armasPorCategoria[$cat])) $armasPorCategoria[$cat] = [];
    if (!isset($armasPorCategoria[$cat][$setName])) $armasPorCategoria[$cat][$setName] = [];
    $armasPorCategoria[$cat][$setName][] = $item;
}
?>

<!-- Si tu header.php ya imprimió <head> con Bootstrap, quita las siguientes líneas.
     Si no, mantenlas (están aquí por seguridad). -->

<div class="container-fluid py-3 px-2">
    <h1 class="mb-4">Forja — Equipamiento de <?= htmlspecialchars($_SESSION['Nombre_usuario']) ?></h1>

    <div class="accordion" id="accordionForja">

        <!-- ARMADURAS -->
        <div class="accordion-item">
            <h2 class="accordion-header" id="headingArmaduras">
                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse"
                    data-bs-target="#collapseArmaduras" aria-expanded="false" aria-controls="collapseArmaduras">
                    Armaduras
                </button>
            </h2>

            <!-- NOTA: NO usamos data-bs-parent aquí para permitir abrir/cerrar libremente -->
            <div id="collapseArmaduras" class="accordion-collapse collapse" aria-labelledby="headingArmaduras">
                <div class="accordion-body p-0">
                    <?php if (empty($armaduras)): ?>
                    <p class="m-3">No tienes armaduras asignadas.</p>
                    <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-sm table-striped mb-0">
                            <thead>
                                <tr>
                                    <th>Parte</th>
                                    <th>Nombre</th>
                                    <th>Estado</th>
                                    <th>Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($armadurasPorSet as $setName => $armadurasSet): ?>
                                <tr class="table">
                                    <td colspan="7"><?= htmlspecialchars($setName) ?></td>
                                </tr>
                                <?php foreach ($armadurasSet as $armadura): ?>
                                <tr data-id="<?= intval($armadura['Id_armadura']) ?>">

                                    <td><?= htmlspecialchars($armadura['Parte_armadura']) ?></td>
                                    <td><?= htmlspecialchars($armadura['Nombre_armadura']) ?></td>
                                    <td><?= htmlspecialchars($armadura['Estado_BaulEquipo']) ?></td>
                                    <td>
                                        <button class="btn btn-sm btn-outline-primary armadura-detalle-btn"
                                            data-id="<?= intval($armadura['Id_armadura']) ?>">
                                            Ver
                                        </button>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        <!-- ARMAS (cada categoría es un acordeón item independiente) -->
        <?php if (empty($armasPorCategoria)): ?>
        <p class="mt-4">No tienes armas asignadas.</p>
        <?php else: ?>
        <?php $idx = 0; ?>
        <?php foreach ($armasPorCategoria as $categoria => $sets): ?>
        <?php $idx++;
                $collapseId = "collapseArma{$idx}";
                $headingId = "headingArma{$idx}"; ?>
        <div class="accordion-item mt-3">
            <h2 class="accordion-header" id="<?= $headingId ?>">
                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse"
                    data-bs-target="#<?= $collapseId ?>" aria-expanded="false" aria-controls="<?= $collapseId ?>">
                    <?= htmlspecialchars($categoria) ?>
                </button>
            </h2>
            <!-- NOTA: NO usamos data-bs-parent para permitir abrir/cerrar libremente -->
            <div id="<?= $collapseId ?>" class="accordion-collapse collapse" aria-labelledby="<?= $headingId ?>">
                <div class="accordion-body p-0">
                    <table class="table table-sm table-striped mb-0">
                        <thead>
                            <tr>
                                <th>Set</th>
                                <th>Nombre</th>
                                <th>Rareza</th>
                                <th>Estado</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($sets as $setName => $armasSet): ?>
                            <tr class="table">
                                <td colspan="5"><?= htmlspecialchars($setName) ?></td>
                            </tr>

                            <?php foreach ($armasSet as $arma): ?>
                            <tr data-id="<?= intval($arma['Id_arma']) ?>">
                                <td><?= htmlspecialchars($setName) ?></td>
                                <td><?= htmlspecialchars($arma['Nombre_arma']) ?></td>
                                <td><?= htmlspecialchars($arma['Rareza_arma']) ?></td>
                                <td><?= htmlspecialchars($arma['Estado_BaulEquipo']) ?></td>
                                <td>
                                    <button class="btn btn-sm btn-outline-primary arma-detalle-btn"
                                        data-id="<?= intval($arma['Id_arma']) ?>">Ver</button>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        <?php endforeach; ?>
        <?php endif; ?>
    </div> <!-- /accordionForja -->
</div> <!-- /container -->
<!-- ===== Modales únicos (solo uno por tipo) ===== -->
<!-- Modal Armadura -->
<div class="modal fade" id="modalDetalleArmadura" tabindex="-1" aria-labelledby="modalDetalleArmaduraLabel"
    aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalDetalleArmaduraLabel">Detalle Armadura</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>
            <div class="modal-body">
                <div id="detalleArmaduraContent">Cargando...</div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                <button id="btnForjarArmadura" class="btn btn-success" style="display:none">Forjar</button>
                <button id="btnEquiparArmadura" class="btn btn-primary" style="display:none">Equipar</button>


            </div>
        </div>
    </div>
</div>

<!-- Modal Arma -->
<div class="modal fade" id="modalDetalleArma" tabindex="-1" aria-labelledby="modalDetalleArmaLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalDetalleArmaLabel">Detalle Arma</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>
            <div class="modal-body">
                <div id="detalleArmaContent">Cargando...</div>
            </div>
            <div class="modal-footer">
                <button id="btnDesbloquearArma" class="btn btn-success">Desbloquear</button>
                <button id="btnForjarArma" class="btn btn-success" style="display:none">Forjar</button>
                <button id="btnEquiparArma" class="btn btn-primary" style="display:none">Equipar</button>
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
            </div>
        </div>
    </div>
</div>


<script>
// puedes exponer el userId si lo necesitas en JS; no es necesario para ajax_forjar_arma.php
const jugadorId = <?= json_encode($userId) ?>;

document.addEventListener('DOMContentLoaded', function() {
    // --- Armadura: listeners ---
    document.querySelectorAll('.armadura-detalle-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            const id = this.dataset.id;
            const modalEl = document.getElementById('modalDetalleArmadura');
            if (!modalEl) return console.error('Modal armadura no encontrado en DOM.');
            const modal = new bootstrap.Modal(modalEl);
            const contentDiv = document.getElementById('detalleArmaduraContent');
            contentDiv.innerHTML = '<p class="text-muted">Cargando...</p>';

            fetch(`ajax_detalle_armadura.php?id=${encodeURIComponent(id)}`)
                .then(res => res.json())
                .then(data => {
                    if (data.error) {
                        contentDiv.innerHTML = `<p class="text-danger">${data.error}</p>`;
                        modal.show();
                        return;
                    }
                    const arm = data.armadura;
                    const reqs = data.requisitos || {};
                    const inv = data.recursosUsuario || {};
                    const estado = data.estado;

                    let html = `<h4>${arm.Nombre_armadura} (${arm.Parte_armadura})</h4>`;
                    html += `<p><strong>Rareza:</strong> ${arm.Rareza_armadura}</p>`;
                    html +=
                        `<p><strong>Habilidad:</strong> ${arm.Habilidad_armadura || 'Ninguna'}</p>`;
                    if (arm.Escudo_armadura) html +=
                        `<p><strong>Escudo:</strong> ${arm.Escudo_armadura}</p>`;
                    if (arm.ResistenciaElemental_armadura) {
                        try {
                            const resist = JSON.parse(arm.ResistenciaElemental_armadura);
                            html += `<p><strong>Resistencias Elementales:</strong></p><ul>`;
                            for (const [element, val] of Object.entries(resist)) {
                                html += `<li>${element}: ${val}</li>`;
                            }
                            html += `</ul>`;
                        } catch (e) {
                            /* ignore */
                        }
                    }

                    if (!reqs || Object.keys(reqs).length === 0) {
                        html += `<p><strong>Requisitos:</strong> Ninguno</p>`;
                    } else {
                        if (estado.Estado_BaulEquipo == "disponible") {
                            html += `<p><strong>Requisitos para forjar:</strong></p><ul>`;
                            for (const [rec, cant] of Object.entries(reqs)) {
                                const cantInv = inv[rec] ?? 0;
                                const color = cantInv >= cant ? 'green' : 'red';
                                html +=
                                    `<li>${rec}: <span style="color:${color}">${cantInv} / ${cant}</span></li>`;
                            }
                        }
                        html += `</ul>`;
                    }


                    if (estado.Estado_BaulEquipo) html +=
                        `<p><strong>Estado:</strong> ${estado.Estado_BaulEquipo}</p>`;

                    // Validar si puede forjar (recursos suficientes y estado)
                    let puedeForjar = (estado.Estado_BaulEquipo === 'disponible');
                    for (const [rec, cant] of Object.entries(reqs)) {
                        if ((inv[rec] ?? 0) < cant) {
                            puedeForjar = false;
                            break;
                        } else {
                            puedeForjar = "forjada";
                        }
                    }

                    // Mostrar/ocultar botones
                    const btnForjar = document.getElementById('btnForjarArmadura');
                    const btnEquipar = document.getElementById('btnEquiparArmadura');

                    if (estado.Estado_BaulEquipo == 'disponible') {
                        btnForjar.style.display = 'inline-block';
                        btnEquipar.style.display = 'none';
                    } else if (estado.Estado_BaulEquipo == 'forjada') {
                        btnForjar.style.display = 'none';
                        btnEquipar.style.display = 'inline-block';
                    } else {
                        btnForjar.style.display = 'none';
                        btnEquipar.style.display = 'none';
                    }

                    // Guardar id armadura en el botón para el click
                    btnForjar.dataset.id = arm.Id_armadura;
                    btnEquipar.dataset.id = arm.Id_armadura;

                    contentDiv.innerHTML = html;
                    modal.show();
                })

                .catch(err => {
                    console.error(err);
                    const contentDiv = document.getElementById(
                        'detalleArmaduraContent');
                    contentDiv.innerHTML =
                        '<p class="text-danger">Error cargando datos.</p>';
                    new bootstrap.Modal(document.getElementById('modalDetalleArmadura'))
                        .show();
                });



        });
    });


    // ------- forjar armadura ----------

    document.getElementById('btnForjarArmadura').addEventListener('click', function() {
        const idArmadura = this.dataset.id;
        if (!idArmadura) return alert('ID de armadura no definido');

        fetch('ajax_forjar_armadura.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded'
                },
                body: `idArmadura=${encodeURIComponent(idArmadura)}`
            })
            .then(res => res.text()) // en vez de .json()
            .then(text => {
                console.log("Respuesta cruda:", text);
                try {
                    const data = JSON.parse(text);

                    const contentDiv = document.getElementById(
                        'detalleArmaduraContent');
                    contentDiv.innerHTML +=
                        `<p class="text-success">✓ ${data.message || (data.success ? 'Forjada correctamente' : 'Error')}</p>`;
                    const btnForjar = document.getElementById('btnForjarArmadura');
                    btnForjar.style.display = 'none';
                    if (data.success) setTimeout(function() {
                        location.reload();
                    }, 1000);
                } catch (e) {
                    alert("Respuesta no es JSON válido. Revisa la consola.");
                    console.error("Error parseando JSON:", e);
                }
            })
            .catch(err => {
                alert('Error en la petición');
                console.error(err);
            });
    });


    //------------- equipar armadura -------------

    document.getElementById('btnEquiparArmadura').addEventListener('click', function() {
        const idArmadura = this.dataset.id;
        if (!idArmadura) return alert('ID de armadura no definido');

        fetch('ajax_equipar_armadura.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded'
                },
                body: `idArmadura=${encodeURIComponent(idArmadura)}`
            })
            .then(res => res.json())
            .then(data => {
                const contentDiv = document.getElementById(
                    'detalleArmaduraContent');
                contentDiv.innerHTML +=
                    `<p class="text-success">✓ ${data.message || (data.success ? 'Equipada correctamente' : 'Error')}</p>`;
                const btnEquipar = document.getElementById('btnEquiparArmadura');
                btnEquipar.style.display = 'none';
                if (data.success) setTimeout(function() {
                    location.reload();
                }, 700);

            })
            .catch(err => {
                alert('Error en la petición');
                console.error(err);
            });
    });

    // --- Arma: listeners ---
    function initArmaButtons() {
        document.querySelectorAll('.arma-detalle-btn').forEach(btn => {
            btn.removeEventListener('click', armaDetalleHandler);
            btn.addEventListener('click', armaDetalleHandler);
        });
    }

    // Helper para actualizar la columna "Estado" de la fila del arma
    function actualizarEstadoArma(idArma, nuevoEstado) {
        const row = document.querySelector(`tr[data-id="${idArma}"]`);
        if (!row) return;
        // en tu tabla de armas, Estado es la 4ta celda (índice 3)
        const tds = row.querySelectorAll('td');
        if (tds.length >= 4) {
            tds[3].textContent = nuevoEstado;
        }
    }

    let armaData = null; // objeto con info actual mostrada en el modal

    async function armaDetalleHandler(e) {
        const id = this.dataset.id;
        if (!id) return;
        const modalEl = document.getElementById('modalDetalleArma');
        if (!modalEl) return console.error('Modal arma no encontrado en DOM.');
        const modal = new bootstrap.Modal(modalEl);
        const contentDiv = document.getElementById('detalleArmaContent');
        contentDiv.innerHTML = '<p class="text-muted">Cargando...</p>';

        try {
            const res = await fetch(`ajax_detalle_arma.php?id=${encodeURIComponent(id)}`);
            if (!res.ok) throw new Error('Error en la respuesta del servidor');
            const data = await res.json();
            if (data.error) {
                contentDiv.innerHTML = `<p class="text-danger">${data.error}</p>`;
                modal.show();
                return;
            }

            const arma = data.arma;
            const requisitos = data.requisitos || {};
            const inv = data.recursosUsuario || {};
            armaData = {
                id: parseInt(id, 10),
                nombre: arma.Nombre_arma
            }; // guardamos datos para acciones

            let html = `<h4>${arma.Nombre_arma}</h4>`;
            html +=
                `<p><strong>Tipo:</strong> ${arma.Tipo_arma} — <strong>Rareza:</strong> ${arma.Rareza_arma}</p>`;

            // Cartas de daño
            if (arma.CartasDaño_arma) {
                try {
                    const daño = JSON.parse(arma.CartasDaño_arma);
                    html += '<p><strong>Cartas de daño:</strong></p><ul>';
                    for (const [k, v] of Object.entries(daño)) {
                        html += `<li> ${k}: ${v}</li>`;
                    }
                    html += '</ul>';
                } catch (e) {
                    /* ignore */
                }
            }

            // Cartas agregadas / quitadas
            if (arma.CartasAgregadas_arma !== '[]') {
                try {
                    const cAdd = JSON.parse(arma.CartasAgregadas_arma);
                    html += '<p><strong>Cartas agregadas:</strong></p><ul>';
                    for (const [k, v] of Object.entries(cAdd)) html += `<li>${k}: ${v}</li>`;
                    html += '</ul>';
                } catch (e) {
                    /* ignore */
                }
            } else {

            }
            if (arma.CartasQuitadas_arma !== '[]') {
                try {
                    const cRem = JSON.parse(arma.CartasQuitadas_arma);
                    html += '<p><strong>Cartas quitadas:</strong></p><ul>';
                    for (const [k, v] of Object.entries(cRem)) html += `<li>${k}: ${v}</li>`;
                    html += '</ul>';
                } catch (e) {
                    /* ignore */
                }
            }

            // Requisitos vs inventario
            if (!requisitos || Object.keys(requisitos).length === 0 || data.estado === 'forjada') {

            } else {
                html += '<p><strong>Requisitos para forjar:</strong></p><ul>';
                for (const [rec, cant] of Object.entries(requisitos)) {
                    const cantInv = inv[rec] ?? 0;
                    const ok = cantInv >= cant;
                    html +=
                        `<li>${rec}: <span style="color:${ok ? 'green' : 'red'}">${cantInv} / ${cant}</span></li>`;
                }
                html += '</ul>';
            }

            if (data.estado) html += `<p><strong>Estado:</strong> ${data.estado}</p>`;

            contentDiv.innerHTML = html;

            // Botones forjar / equipar
            const btnDesbloquear = document.getElementById('btnDesbloquearArma');
            const btnForjar = document.getElementById('btnForjarArma');
            const btnEquipar = document.getElementById('btnEquiparArma');

            // lógica simple: puede forjar si tiene recursos y no está forjada
            // 1. No tiene requisitos, o
            // 2. Tiene requisitos y todos están cumplidos
            // Además, no debe estar ya forjada
            const sinRequisitos = Object.keys(requisitos).length === 0;
            const requisitosCumplidos = Object.entries(requisitos).every(([r, c]) => (inv[r] ||
                0) >= c);

            const forjada = data.estado === 'disponible';
            btnDesbloquear.style.display = forjada ? 'inline-block' : 'none';

            const puedeForjar = (sinRequisitos || requisitosCumplidos) && data.estado === 'disponible';


            btnForjar.style.display = puedeForjar ? 'inline-block' : 'none';
            btnForjar.dataset.id = armaData.id;

            // Puede equipar si está forjada
            const puedeEquipar = data.estado === 'forjada';
            btnEquipar.style.display = puedeEquipar ? 'inline-block' : 'none';
            btnEquipar.dataset.id = armaData.id;

            // handler para forjar arma: enviamos JSON { id_arma }
            btnForjar.onclick = async () => {
                if (!armaData) return;
                try {
                    btnForjar.disabled = true;
                    btnForjar.textContent = 'Forjando...';

                    const response = await fetch('ajax_forjar_arma.php', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json'
                        },
                        body: JSON.stringify({
                            id_arma: armaData.id
                        })
                    });

                    if (data.blocked_previous) {
                        const names = data.blocked_previous.map(x => x.Nombre_arma).join(
                            ', ');
                        alert('Debes forjar primero: ' + names);
                    }



                    const result = await response.json();
                    if (result.success) {
                        // actualizar UI: estado fila y modal
                        actualizarEstadoArma(armaData.id, result.estado || 'forjada');
                        // actualizar contenido del modal (estado y recursos si vienen)
                        const nuevoHtml = contentDiv.innerHTML +
                            `<p class="text-success">✓ ${result.message || 'Forjada correctamente'}</p>`;
                        contentDiv.innerHTML = nuevoHtml;
                        // ajustar botones
                        btnForjar.style.display = 'none';
                        btnEquipar.style.display = 'inline-block';
                    } else {
                        alert(`No se pudo forjar: ${result.message || 'Error'}`);
                    }
                } catch (err) {
                    console.error(err);
                    alert('Error en la petición de forjar.');
                } finally {
                    btnForjar.disabled = false;
                    btnForjar.textContent = 'Forjar';
                }
            };

            // handler para desboquear arma
            btnDesbloquear.onclick = async () => {
                if (!armaData) return;
                try {
                    btnDesbloquear.disabled = true;
                    btnDesbloquear.textContent = 'Desbloqueando...';

                    const response = await fetch('ajax_desbloquear_arma.php', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json'
                        },
                        body: JSON.stringify({
                            id_arma: armaData.id
                        })
                    });

                    const result = await response.json();
                    if (result.success) {
                        actualizarEstadoArma(armaData.id, 'desbloqueada');
                        const nuevoHtml = contentDiv.innerHTML +
                            `<p class="text-success">✓ ${result.message || 'Desbloqueada correctamente'}</p>`;
                        contentDiv.innerHTML = nuevoHtml;
                        btnDesbloquear.style.display = 'none';
                    } else {
                        alert(`No se pudo desbloquear: ${result.message || 'Error'}`);
                    }
                } catch (err) {
                    console.error(err);
                    alert('Error en la petición de desbloquear.');
                } finally {
                    btnDesbloquear.disabled = false;
                    btnDesbloquear.textContent = 'Desbloquear';
                }
            };

            // handler para equipar arma
            btnEquipar.onclick = async () => {
                if (!armaData) return;
                try {
                    btnEquipar.disabled = true;
                    btnEquipar.textContent = 'Equipando...';

                    const response = await fetch('ajax_equipar_arma.php', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json'
                        },
                        body: JSON.stringify({
                            id_arma: armaData.id
                        })
                    });

                    const result = await response.json();
                    if (result.success) {
                        actualizarEstadoArma(armaData.id, 'equipada');
                        // si backend devuelve lista de armas des-equipadas, actualizar UI en consecuencia
                        if (result.desequipadas && Array.isArray(result.desequipadas)) {
                            result.desequipadas.forEach(id => actualizarEstadoArma(id,
                                'disponible'));
                        }
                        const nuevoHtml = contentDiv.innerHTML +
                            `<p class="text-success">✓ ${result.message || 'Equipada correctamente'}</p>`;
                        contentDiv.innerHTML = nuevoHtml;
                        btnEquipar.style.display = 'none';
                    } else {
                        alert(`No se pudo equipar: ${result.message || 'Error'}`);
                    }
                } catch (err) {
                    console.error(err);
                    alert('Error en la petición de equipar.');
                } finally {
                    btnEquipar.disabled = false;
                    btnEquipar.textContent = 'Equipar';
                }
            };

            modal.show();

        } catch (err) {
            console.error(err);
            contentDiv.innerHTML = '<p class="text-danger">Error cargando datos.</p>';
            new bootstrap.Modal(document.getElementById('modalDetalleArma')).show();
        }
    }

    // inicializar
    initArmaButtons();
});
</script>

<?php
// Si usas footer.php que cierre body/html, inclúyelo; si no, omítelo.
include '../includes/footer.php';
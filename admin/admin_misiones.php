<?php
session_start();
if ($_SESSION['Tipo_usuario'] !== 'admin') {
    header("Location: index.php");
    exit();
}
require_once '../includes/db.php';;

// Obtener lista de jugadores
$stmt = $pdo->query("SELECT Id_usuario, Nombre_usuario FROM USUARIO WHERE Tipo_usuario='player'");
$jugadores = $stmt->fetchAll(PDO::FETCH_ASSOC);

include '../includes/header.php';
?>


<body class="bg-light">

    <div class="container py-4">
        <h2 class="text-center mb-4">Administrar Misiones</h2>

        <!-- Formulario de creación -->
        <div class="card shadow-sm mb-4">
            <div class="card-body">
                <h5 class="card-title">Crear Nueva Misión</h5>
                <form id="formMision">
                    <div class="mb-3">
                        <label for="titulo" class="form-label">Título de la misión</label>
                        <input type="text" class="form-control" id="titulo" name="titulo" required>
                    </div>
                    <div class="mb-3">
                        <label for="descripcion" class="form-label">Descripción</label>
                        <textarea class="form-control" id="descripcion" name="descripcion" rows="3"></textarea>
                    </div>
                    <div class="mb-3">
                        <label for="recompensa" class="form-label">Recompensas</label>
                        <textarea class="form-control" id="recompensa" name="recompensa" rows="2"
                            placeholder='Ej: {"oro":100,"objeto":"gema"}'></textarea>
                    </div>
                    <div class="mb-3">
                        <label for="rango" class="form-label">Rango (+)</label>
                        <input type="number" class="form-control" id="rango" name="rango" value="0">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Asignar a jugadores</label>
                        <div class="row row-cols-2 row-cols-md-3 g-2">
                            <?php foreach ($jugadores as $j): ?>
                            <div class="col">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="jugadores[]"
                                        value="<?= $j['Id_usuario'] ?>" id="jugador<?= $j['Id_usuario'] ?>">
                                    <label class="form-check-label small" for="jugador<?= $j['Id_usuario'] ?>">
                                        <?= htmlspecialchars($j['Nombre_usuario']) ?>
                                    </label>
                                </div>
                            </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                    <button type="submit" class="btn btn-primary w-100">Crear misión</button>
                </form>
            </div>
        </div>

        <!-- Lista de misiones -->
        <div class="card shadow-sm">
            <div class="card-body">
                <h5 class="card-title">Misiones Creadas</h5>
                <div id="listaMisiones" class="table-responsive">
                    <!-- Se llenará con AJAX -->
                    <table class="table table-sm table-striped">
                        <thead class="table-dark">
                            <tr>
                                <th>ID</th>
                                <th>Título</th>
                                <th>Estado</th>
                                <th>Recompensa</th>
                                <th>Rango</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody id="tablaMisiones">
                            <!-- Contenido dinámico -->
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
    document.getElementById("formMision").addEventListener("submit", function(e) {
        e.preventDefault();
        const form = this;
        const btn = form.querySelector('button[type="submit"]');
        btn.disabled = true;

        fetch("ajax_guardar_mision.php", {
                method: "POST",
                body: new FormData(form)
            })
            .then(async res => {
                const text = await res.text();
                // primer vistazo: si la respuesta comienza con '<', es muy probable que sea HTML/error
                if (text.trim().startsWith('<')) {
                    console.error('Respuesta HTML inesperada:', text);
                    alert('Respuesta del servidor no válida. Revisa la consola (Network -> Response).');
                    return;
                }
                // intentar parsear JSON
                try {
                    const data = JSON.parse(text);
                    if (data.success) {
                        alert(data.message || 'Misión creada');
                        form.reset();
                        cargarMisiones();
                    } else {
                        alert(data.message || 'Error al crear misión');
                    }
                } catch (e) {
                    console.error('No se pudo parsear JSON:', e, text);
                    alert('Respuesta del servidor no es JSON. Revisa la consola.');
                }
            })
            .catch(err => {
                console.error('Fetch error:', err);
                alert('Error de red al intentar guardar la misión.');
            })
            .finally(() => btn.disabled = false);
    });



    function cargarMisiones() {
        fetch("ajax_listar_misiones.php")
            .then(r => r.json())
            .then(data => {
                const tbody = document.getElementById("tablaMisiones");
                tbody.innerHTML = "";
                data.forEach(m => {
                    tbody.innerHTML += `
                <tr>
                    <td>${m.Id}</td>
                    <td>${m.Titulo || '-'}</td>
                    <td><span class="badge bg-${m.Estado=='available'?'success':'secondary'}">${m.Estado}</span></td>
                    <td><code class="small">${m.Recompensa || '-'}</code></td>
                    <td>${m.Rango || '0'}</td>
                    <td>
                        <button class="btn btn-sm btn-danger" onclick="eliminarMision(${m.Id_Mision})">Eliminar</button>
                    </td>
                </tr>
            `;
                });
            });
    }

    function eliminarMision(id) {
        if (!confirm("¿Eliminar misión?")) return;
        fetch("ajax_eliminar_mision.php?id=" + id)
            .then(r => r.json())
            .then(data => {
                alert(data.message);
                cargarMisiones();
            });
    }

    cargarMisiones();
    </script>
</body>

<?php


include '../includes/footer.php'; ?>
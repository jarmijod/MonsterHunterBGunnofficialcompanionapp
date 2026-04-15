<?php
// Asume que ya hay sesión iniciada y $_SESSION['Tipo_usuario'] y $_SESSION['Nombre_usuario'] están seteados
?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8" />
    <title>MH Companion</title>
    <meta name="viewport" content="width=device-width, initial-scale=1" />

    <!-- CSS de Bootstrap -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">



    <link href="../css/style.css" rel="stylesheet" />

</head>

<body>

    <nav class="navbar navbar-light bg-light d-md-none">
        <div class="container-fluid">
            <button class="btn btn-primary" id="btnToggleSidebar" type="button" aria-label="Toggle menu">
                ☰ Menú
            </button>
            <span class="navbar-text">
                MH Companion
            </span>
        </div>
    </nav>

    <div class="d-flex">
        <div id="sidebarMenu" class="bg-light border-end">
            <div class="p-3">
                <h5>MH Companion</h5>
                <hr />
                <p>Usuario: <strong><?= htmlspecialchars($_SESSION['Nombre_usuario']) ?></strong></p>
                <ul class="nav flex-column">
                    <?php if ($_SESSION['Tipo_usuario'] === 'admin'): ?>
                    <li class="nav-item"><a href="/admin/dashboard.php" class="nav-link">Dashboard Admin</a></li>
                    <li class="nav-item"><a href="admin_misiones.php" class="nav-link">Misiones</a></li>
                    <li class="nav-item"><a href="/admin/jugadores.php" class="nav-link">Jugadores</a></li>
                    <?php else: ?>
                    <li class="nav-item"><a href="jugador_misiones.php" class="nav-link">Misiones</a></li>
                    <li class="nav-item"><a href="aldea.php" class="nav-link">Aldea</a></li>
                    <li class="nav-item"><a href="recursos.php" class="nav-link">Baúl</a></li>
                    <li class="nav-item"><a href="forja.php" class="nav-link">Forja</a></li>
                    <li class="nav-item"><a href="resumen_cazador.php" class="nav-link">Resumen Cazador</a></li>
                    <li class="nav-item"><a href="ayudajugador.php" class="nav-link">Ayuda a jugador</a></li>
                    <?php endif; ?>
                    <li class="nav-item"><a href="../logout.php" class="nav-link text-danger">Cerrar
                            sesión</a></li>
                </ul>
            </div>
        </div>

        <div id="sidebarBackdrop"></div>

        <main class="flex-grow-1 p-3">
<?php
session_start();

// Si no hay sesión iniciada, redirigir a login
if (!isset($_SESSION['Id_usuario'])) {
    header('Location: login.php');
    exit;
}

// Redirigir según tipo de usuario
switch ($_SESSION['Tipo_usuario']) {
    case 'admin':
        header('Location: admin/dashboard.php');
        break;
    case 'player':
    case 'gm':
        header('Location: player/dashboard.php');
        break;
    default:
        // Por seguridad, si el tipo no es reconocido, cerrar sesión y redirigir a login
        session_destroy();
        header('Location: login.php');
        break;
}
exit;

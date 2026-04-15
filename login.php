<?php
session_start();
require_once 'includes/db.php';

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $usuario = $_POST['usuario'] ?? '';
    $password = $_POST['password'] ?? '';

    if ($usuario && $password) {
        $stmt = $pdo->prepare("SELECT Id_usuario, Nombre_usuario, Contraseña_usuario, Tipo_usuario FROM USUARIO WHERE Nombre_usuario = ?");
        $stmt->execute([$usuario]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user) {
            if ($password === $user['Contraseña_usuario']) {
                $_SESSION['Id_usuario'] = $user['Id_usuario'];
                $_SESSION['Nombre_usuario'] = $user['Nombre_usuario'];
                $_SESSION['Tipo_usuario'] = $user['Tipo_usuario'];

                if ($user['Tipo_usuario'] === 'admin') {
                    header('Location: admin/dashboard.php');
                } else {
                    header('Location: player/dashboard.php');
                }
                exit;
            } else {
                $error = "Contraseña incorrecta.";
            }
        } else {
            $error = "Usuario no encontrado.";
        }
    } else {
        $error = "Por favor ingresa usuario y contraseña.";
    }
}
?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8" />
    <title>Login - MH Companion</title>
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" />
</head>

<body class="bg-light d-flex align-items-center justify-content-center" style="min-height: 100vh;">

    <div class="card shadow-sm p-4 mx-3" style="max-width: 400px; width: 100%;">
        <h3 class="mb-4 text-center">Iniciar sesión</h3>

        <?php if ($error): ?>
            <div class="alert alert-danger" role="alert"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>

        <form method="POST" autocomplete="off" novalidate>
            <div class="mb-3">
                <label for="usuario" class="form-label">Usuario</label>
                <input type="text" name="usuario" id="usuario" class="form-control" required autofocus>
            </div>
            <div class="mb-3">
                <label for="password" class="form-label">Contraseña</label>
                <input type="password" name="password" id="password" class="form-control" required>
            </div>
            <button type="submit" class="btn btn-primary w-100">Entrar</button>
        </form>
    </div>

</body>

</html>
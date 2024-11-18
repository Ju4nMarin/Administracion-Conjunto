<?php
session_start();


if (isset($_SESSION['usuario'])) {
    if ($_SESSION['usuario']['rol'] === 'admin') {
        header("Location: ../public-admin/inicio.php");
    } else {
        header("Location: ../public-propietarios/dashboard.php");
    }
    exit();
}


if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $correo = $_POST['correo'];
    $clave = $_POST['clave'];


    $usuarios = json_decode(file_get_contents('../config/users.json'), true);

    foreach ($usuarios as $usuario) {
        if ($usuario['usuario'] === $correo && password_verify($clave, $usuario['clave'])) {

            $_SESSION['usuario'] = [
                'id' => $usuario['id'],
                'usuario' => $usuario['usuario'],
                'rol' => $usuario['rol']
            ];

            if ($_SESSION['usuario']['rol'] === 'admin') {
                header("Location: ../public-admin/inicio.php");
            } else {
                header("Location: ../public-propietarios/dashboard.php");
            }
            exit();
        }
    }
    $error = "Usuario o contraseña incorrectos.";
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Iniciar Sesión</title>
    <link rel="stylesheet" href="../css/login.css">
</head>
<body>
    <div class="login-container">
        <div class="login-form">
            <h2>Iniciar Sesión</h2>
            <?php if (isset($error)) : ?>
                <p style="color: red;"><?= $error ?></p>
            <?php endif; ?>
            <form method="POST" action="login.php">
                <label for="correo">Correo Electrónico:</label>
                <input type="email" name="correo" id="correo" placeholder="Ejemplo@correo.com" required>
                
                <label for="clave">Contraseña:</label>
                <input type="password" name="clave" id="clave" placeholder="******" required>
                
                <button type="submit">Entrar</button>

            </form>
        </div>
        <div class="welcome-section">
            <h1>¡Bienvenido de nuevo!</h1>
            <p>Nos alegra mucho verte. Por favor, inicia sesión para continuar 
                con tu experiencia en la plataforma Erre53.</p>
        </div>
    </div>
</body>
</html>

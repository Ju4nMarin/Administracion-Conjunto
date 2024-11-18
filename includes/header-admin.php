<?php
session_start();

if (!isset($_SESSION['usuario'])) {
    header("Location: ../login/login.php");
    exit();
}


$rol = $_SESSION['usuario']['rol'];
$current_page = basename($_SERVER['PHP_SELF']);
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ERRE 53 - <?= $rol === 'admin' ? 'Administración' : 'Propietarios' ?></title>
    <link rel="stylesheet" href="../css/styles-<?= $rol ?>.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11.3.4/dist/sweetalert2.min.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.8/dist/umd/popper.min.js" integrity="sha384-I7E8VVD/ismYTF4hNIPjVp/Zjvgyol6VFvRkX/vR+Vc4jQkC+hVqc2pM8ODewa9r" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.min.js" integrity="sha384-0pUGZvbkm6XF6gxjEnlmuGrJXVbNuzT9qBBavbLwCsOGabYfZo0T0to5eqruptLy" crossorigin="anonymous"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
</head>
<body>
<header>
   
    <nav>
        <ul>
            <?php if ($rol === 'admin') : ?>
                <li><a href="inicio.php" class="<?= $current_page == 'inicio.php' ? 'active' : ''; ?>">Inicio</a></li>
                <li><a href="cuentas.php" class="<?= $current_page == 'cuentas.php' ? 'active' : ''; ?>">Cuentas</a></li>
                <li><a href="propietarios.php" class="<?= $current_page == 'propietarios.php' ? 'active' : ''; ?>">Propietarios</a></li>
                <li><a href="inmuebles.php" class="<?= $current_page == 'inmuebles.php' ? 'active' : ''; ?>">Inmuebles</a></li>
                <li><a href="facturas.php" class="<?= $current_page == 'facturas.php' ? 'active' : ''; ?>">Facturas</a></li>
                <li><a href="pagos.php" class="<?= $current_page == 'pagos.php' ? 'active' : ''; ?>">Pagos</a></li>
                <li><a href="egresos.php" class="<?= $current_page == 'egresos.php' ? 'active' : ''; ?>">Egresos</a></li>
            <?php else : ?>
                <li><a href="dashboard.php" class="<?= $current_page == 'dashboard.php' ? 'active' : ''; ?>">Inicio</a></li>
                <li><a href="facturas.php" class="<?= $current_page == 'facturas.php' ? 'active' : ''; ?>">Facturas</a></li>
                <li><a href="pagos.php" class="<?= $current_page == 'pagos.php' ? 'active' : ''; ?>">Pagos</a></li>
                <li><a href="perfil.php" class="<?= $current_page == 'perfil.php' ? 'active' : ''; ?>">Perfil</a></li>
            <?php endif; ?>
            <li><a href="../login/logout.php">Cerrar Sesión</a></li>
        </ul>
    </nav>
</header>
</body>
</html>

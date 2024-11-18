
<!DOCTYPE html>

<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ERRE 53 - Inicio</title>
    <link rel="stylesheet" href="../css/styles-admin.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11.3.4/dist/sweetalert2.min.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11.3.4/dist/sweetalert2.all.min.js"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
</head>
<body class="dashboard-body">
<?php 
session_start();
if ($_SESSION['usuario']['rol'] !== 'admin') {
    header("Location: ../login/login.php");
    exit();
}
$current_page = basename($_SERVER['PHP_SELF']); ?>


<div class="container">
<h2>Administración del Conjunto</h2>
<div class="dashboard-grid">
    
    
    
    <a href="cuentas.php" class="dashboard-item <?= $current_page == 'cuentas.php' ? 'active' : ''; ?>">
            <i class="fas fa-key"></i>
            <span>Cuentas</span>
    </a>
    <a href="propietarios.php" class="dashboard-item <?= $current_page == 'propietarios.php' ? 'active' : ''; ?>">
        <i class="fas fa-user"></i>
        <span>Propietarios</span>
    </a>
    <a href="inmuebles.php" class="dashboard-item <?= $current_page == 'inmuebles.php' ? 'active' : ''; ?>">
        <i class="fas fa-building"></i>
        <span>Inmuebles</span>
    </a>
    <a href="facturas.php" class="dashboard-item <?= $current_page == 'facturas.php' ? 'active' : ''; ?>">
        <i class="fas fa-file-invoice"></i>
        <span>Facturas</span>
    </a>
    <a href="pagos.php" class="dashboard-item <?= $current_page == 'pagos.php' ? 'active' : ''; ?>">
        <i class="fas fa-credit-card"></i>
        <span>Pagos</span>
    </a>
    <a href="egresos.php" class="dashboard-item <?= $current_page == 'egresos.php' ? 'active' : ''; ?>">
        <i class="fas fa-money-check-alt"></i>
        <span>Egresos</span>
    </a>
    <a href="../login/logout.php" class="dashboard-item">
        <i class="fas fa-sign-out-alt"></i>
        <span>Cerrar Sesión</span>
    </a>

</div>
</div>



</body>
</html>

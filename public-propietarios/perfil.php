<?php

include '../config/db.php';
include '../includes/header-admin.php'; 

// Validar sesión
if ($_SESSION['usuario']['rol'] !== 'propietario') {
    header("Location: ../login/login.php");
    exit();
}


$usuariosFile = '../config/users.json';
$usuarios = json_decode(file_get_contents($usuariosFile), true);


function obtenerPropietario($pdo, $idPropietario) {
    $stmt = $pdo->prepare("SELECT p.*, i.numero_apartamento, i.piso, i.estado 
                           FROM Propietarios p 
                           JOIN Inmuebles i ON p.apartamento = i.id_inmueble 
                           WHERE p.id_propietario = ?");
    $stmt->execute([$idPropietario]);
    return $stmt->fetch(PDO::FETCH_ASSOC);
}


$propietario = obtenerPropietario($pdo, $_SESSION['usuario']['id']);
$nombre = htmlspecialchars($propietario['nombre']);
$apartamento = htmlspecialchars($propietario['numero_apartamento']);
$piso = htmlspecialchars($propietario['piso']);
$estado = htmlspecialchars($propietario['estado']);
$contacto = htmlspecialchars($propietario['numero_contacto']);
$email = htmlspecialchars($propietario['correo_electronico']);


$mensaje = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['cambiar_clave'])) {
    $nuevaClave = $_POST['nueva_clave'];
    $confirmarClave = $_POST['confirmar_clave'];
    $usuarioActual = $_SESSION['usuario']['usuario'];

    if ($nuevaClave === $confirmarClave) {

        foreach ($usuarios as &$usuario) {
            if ($usuario['usuario'] === $usuarioActual) {
                $usuario['clave'] = password_hash($nuevaClave, PASSWORD_DEFAULT);
                break;
            }
        }
        file_put_contents($usuariosFile, json_encode($usuarios, JSON_PRETTY_PRINT));
        header("Location: perfil.php?actualizado=1");
        exit();
    } else {
        header("Location: perfil.php?error=1");
        exit();
    }
}
?>

<main>
    <div class="block">
        <h2>Mi Información</h2>
        <table>
            <tr><th>Nombre:</th><td><?= $nombre ?></td></tr>
            <tr><th>Apartamento:</th><td><?= $apartamento ?> (Piso <?= $piso ?>)</td></tr>
            <tr><th>Estado:</th><td><?= $estado ?></td></tr>
            <tr><th>Contacto:</th><td><?= $contacto ?></td></tr>
            <tr><th>Email:</th><td><?= $email ?></td></tr>
        </table>
    </div>

    <div class="block2">
        <h2>Cambiar Contraseña</h2>
        <form method="POST" action="perfil.php">
            <label for="nueva_clave">Nueva Contraseña:</label>
            <input type="text" id="nueva_clave" name="nueva_clave" required>

            <label for="confirmar_clave">Confirmar Contraseña:</label>
            <input type="text" id="confirmar_clave" name="confirmar_clave" required>

            <button type="submit" name="cambiar_clave">Actualizar Contraseña</button>
        </form>
    </div>
</main>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>

<?php if (isset($_GET['actualizado'])): ?>
    Swal.fire({
        title: 'Éxito',
        text: 'Contraseña actualizada correctamente.',
        icon: 'success',
        confirmButtonText: 'Cerrar'
    });
<?php elseif (isset($_GET['error'])): ?>
    Swal.fire({
        title: 'Error',
        text: 'Las contraseñas no coinciden.',
        icon: 'error',
        confirmButtonText: 'Cerrar'
    });
<?php endif; ?>
</script>

<?php include '../includes/footer.php'; ?>

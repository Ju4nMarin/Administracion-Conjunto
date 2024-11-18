<?php
include '../config/db.php';
include '../includes/header-admin.php';

if ($_SESSION['usuario']['rol'] !== 'admin') {
    header("Location: ../login/login.php");
    exit();
}


$usuariosFile = '../config/users.json';
$usuarios = json_decode(file_get_contents($usuariosFile), true);

$propietarios = $pdo->query("SELECT id_propietario, nombre, correo_electronico FROM Propietarios")->fetchAll(PDO::FETCH_ASSOC);

$busqueda = isset($_GET['search']) ? $_GET['search'] : '';
$resultados = $busqueda ? array_filter($usuarios, function ($usuario) use ($busqueda) {
    return stripos($usuario['usuario'], $busqueda) !== false;
}) : $usuarios;

$errorMensaje = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['crear_usuario'])) {
        $rol = $_POST['rol'];
        $correo = $rol === 'admin' ? $_POST['correo'] : $_POST['correo_propietario'];

        foreach ($usuarios as $usuario) {
            if ($usuario['usuario'] === $correo) {
                $errorMensaje = "Ya existe una cuenta con este correo.";
                break;
            }
        }

        if (empty($errorMensaje)) {
        
            $nuevoUsuario = [
                'id' => $rol === 'admin' ? uniqid('a') : $_POST['id_propietario'],
                'usuario' => $correo,
                'clave' => password_hash($_POST['clave'], PASSWORD_DEFAULT),
                'rol' => $rol
            ];
            $usuarios[] = $nuevoUsuario;
            file_put_contents($usuariosFile, json_encode($usuarios, JSON_PRETTY_PRINT));
            header("Location: cuentas.php?agregado=1"); 
            exit();
        }
    } elseif (isset($_POST['actualizar_usuario'])) {

        foreach ($usuarios as &$usuario) {
            if ($usuario['usuario'] === $_POST['correo']) {
                $usuario['clave'] = password_hash($_POST['clave'], PASSWORD_DEFAULT);
                break;
            }
        }
        file_put_contents($usuariosFile, json_encode($usuarios, JSON_PRETTY_PRINT));
        header("Location: cuentas.php?actualizado=1"); 
        exit();
    }
}
?>

<main>
    <div class="block">
        <h2>Gestión de Cuentas</h2>
        <form method="GET" action="cuentas.php" class="search-form">
            <input type="text" name="search" placeholder="Buscar por correo" value="<?= htmlspecialchars($busqueda); ?>" class="search-input">
            <button type="submit" class="search-btn"><i class="fa fa-search"></i></button>
            <a href="cuentas.php" class="clear-btn"><i class="fa fa-times"></i></a>
        </form>

        <div class="table-container">
            <table>
                <thead>
                    <tr>
                        <th>Correo</th>
                        <th>Rol</th>
                        <th>Ajustes</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($resultados as $usuario) : ?>
                        <tr>
                            <td><?= htmlspecialchars($usuario['usuario']); ?></td>
                            <td><?= htmlspecialchars($usuario['rol']); ?></td>
                            <td>
                                <button class="sw" onclick="openModal(<?= htmlspecialchars(json_encode($usuario)); ?>)">
                                    <i class="fa fa-cog"></i>
                                </button>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

    <div class="block2">
        <h2>Crear Nuevo Usuario</h2>
        <form method="POST" action="cuentas.php">
            <label for="rol">Rol:</label>
            <select id="rol" name="rol" onchange="toggleInputs(this)" required>
                <option value="admin">Admin</option>
                <option value="propietario">Propietario</option>
            </select>

            <div id="admin-fields" style="display: block;">
                <label for="correo">Correo (Admin):</label>
                <input type="email" id="correo" name="correo">
            </div>

            <div id="propietario-fields" style="display: none;">
                <label for="id_propietario">Propietario:</label>
                <div class="select-container">
                    <select id="id_propietario" name="id_propietario" onchange="updateCorreo(this)">
                        <option value="">Seleccione un propietario</option>
                        <?php foreach ($propietarios as $propietario) : ?>
                            <option value="<?= $propietario['id_propietario']; ?>" data-correo="<?= htmlspecialchars($propietario['correo_electronico']); ?>">
                                <?= htmlspecialchars($propietario['nombre']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <input type="hidden" id="correo_propietario" name="correo_propietario">
            </div>

            <label for="clave">Clave:</label>
            <input type="text" id="clave" name="clave" required>

            <button type="submit" name="crear_usuario">Crear Usuario</button>
        </form>
    </div>

    <div id="modal" class="modal">
        <div class="modal-content">
            <div class="titulo">
                <span>Cambiar Clave</span>
                <span class="close" onclick="closeModal()">&times;</span>
            </div>
            <form method="POST" action="cuentas.php">
                <input type="hidden" name="correo" id="modal-correo">
                <label for="modal-clave">Nueva Clave:</label>
                <input type="text" name="clave" id="modal-clave" required>
                <button type="submit" name="actualizar_usuario" class="sw">Actualizar</button>
            </form>
        </div>
    </div>
</main>

<script>
function toggleInputs(select) {
    const adminFields = document.getElementById('admin-fields');
    const propietarioFields = document.getElementById('propietario-fields');

    if (select.value === 'admin') {
        adminFields.style.display = 'block';
        propietarioFields.style.display = 'none';
    } else {
        adminFields.style.display = 'none';
        propietarioFields.style.display = 'block';
    }
}

function updateCorreo(select) {
    const correo = select.options[select.selectedIndex].getAttribute('data-correo');
    document.getElementById('correo_propietario').value = correo;
}

function openModal(data) {
    document.getElementById('modal-correo').value = data.usuario;
    document.getElementById('modal-clave').value = '';
    document.getElementById('modal').style.display = 'block';
}

function closeModal() {
    document.getElementById('modal').style.display = 'none';
}


<?php if (isset($_GET['agregado'])): ?>
    Swal.fire({
        title: 'Éxito',
        text: 'Usuario creado correctamente.',
        icon: 'success',
        confirmButtonText: 'Cerrar'
    });
<?php elseif (isset($_GET['actualizado'])): ?>
    Swal.fire({
        title: 'Éxito',
        text: 'Contraseña actualizada correctamente.',
        icon: 'success',
        confirmButtonText: 'Cerrar'
    });
<?php elseif (!empty($errorMensaje)): ?>
    Swal.fire({
        title: 'Error',
        text: '<?= htmlspecialchars($errorMensaje, ENT_QUOTES, 'UTF-8') ?>',
        icon: 'error',
        confirmButtonText: 'Cerrar'
    });
<?php endif; ?>
</script>

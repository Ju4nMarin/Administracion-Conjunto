<?php
include '../config/db.php';
include '../includes/header-admin.php';

if ($_SESSION['usuario']['rol'] !== 'admin') {
    header("Location: ../login/login.php");
    exit();
}

$busqueda = isset($_GET['search']) ? $_GET['search'] : '';
$searchQuery = "";
if ($busqueda) {
    $searchQuery = "WHERE numero_apartamento LIKE ? OR piso LIKE ?";
}

$stmt = $pdo->prepare("SELECT * FROM Inmuebles $searchQuery");
if ($busqueda) {
    $stmt->execute(['%' . $busqueda . '%', '%' . $busqueda . '%']);
} else {
    $stmt->execute();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['registrar'])) {
        $numero_apartamento = $_POST['numero_apartamento'];
        $piso = $_POST['piso'];
        $estado = $_POST['estado'];

        try {
            $checkNumero = $pdo->prepare("SELECT * FROM Inmuebles WHERE numero_apartamento = ?");
            $checkNumero->execute([$numero_apartamento]);

            if ($checkNumero->rowCount() > 0) {
                throw new Exception("El número de apartamento ya está registrado.");
            }

            $stmt = $pdo->prepare("INSERT INTO Inmuebles (numero_apartamento, piso, estado) VALUES (?, ?, ?)");
            $stmt->execute([$numero_apartamento, $piso, $estado]);

            echo "<script>
                Swal.fire({
                    title: 'Registrado!',
                    text: 'Inmueble registrado correctamente.',
                    icon: 'success',
                    confirmButtonText: 'OK'
                }).then(() => {
                    window.location = 'inmuebles.php';
                });
            </script>";
        } catch (Exception $e) {
            echo "<script>
                Swal.fire({
                    title: 'Error!',
                    text: '" . $e->getMessage() . "',
                    icon: 'error',
                    confirmButtonText: 'Cerrar'
                });
            </script>";
        }
    }

    if (isset($_POST['eliminar'])) {
        $id = $_POST['id'];
    
        try {

            $checkPropietarios = $pdo->prepare("SELECT COUNT(*) FROM Propietarios WHERE apartamento = ?");
            $checkPropietarios->execute([$id]);
            $propietariosCount = $checkPropietarios->fetchColumn();
    
            if ($propietariosCount > 0) {
                throw new Exception("No se puede eliminar el inmueble porque está ocupado por un propietario.");
            }
    

            $stmt = $pdo->prepare("DELETE FROM Inmuebles WHERE id_inmueble = ?");
            $stmt->execute([$id]);
    
            echo "<script>
                Swal.fire({
                    title: 'Eliminado!',
                    text: 'Inmueble eliminado correctamente.',
                    icon: 'success',
                    confirmButtonText: 'OK'
                }).then(() => {
                    window.location = 'inmuebles.php';
                });
            </script>";
        } catch (Exception $e) {
            echo "<script>
                Swal.fire({
                    title: 'Error!',
                    text: '" . $e->getMessage() . "',
                    icon: 'error',
                    confirmButtonText: 'Cerrar'
                });
            </script>";
        }
    }
    
    if (isset($_POST['actualizar'])) {
        $id = $_POST['id'];
        $numero_apartamento = $_POST['numero_apartamento'];
        $piso = $_POST['piso'];
        $estado = $_POST['estado'];
    
        try {

            $checkNumero = $pdo->prepare("SELECT id_inmueble FROM Inmuebles WHERE numero_apartamento = ? AND id_inmueble != ?");
            $checkNumero->execute([$numero_apartamento, $id]);
    
            if ($checkNumero->rowCount() > 0) {
                throw new Exception("El número de apartamento ya está registrado con otro inmueble.");
            }
    

            if ($estado === 'disponible') {
                $checkPropietarios = $pdo->prepare("SELECT COUNT(*) FROM Propietarios WHERE apartamento = ?");
                $checkPropietarios->execute([$id]);
                $propietariosCount = $checkPropietarios->fetchColumn();
    
                if ($propietariosCount > 0) {
                    throw new Exception("No se puede marcar el inmueble como disponible porque está ocupado por un propietario.");
                }
            }

            $stmt = $pdo->prepare("UPDATE Inmuebles SET numero_apartamento = ?, piso = ?, estado = ? WHERE id_inmueble = ?");
            $stmt->execute([$numero_apartamento, $piso, $estado, $id]);
    
            echo "<script>
                Swal.fire({
                    title: 'Actualizado!',
                    text: 'Inmueble actualizado correctamente.',
                    icon: 'success',
                    confirmButtonText: 'OK'
                }).then(() => {
                    window.location = 'inmuebles.php';
                });
            </script>";
        } catch (Exception $e) {
            echo "<script>
                Swal.fire({
                    title: 'Error!',
                    text: '" . $e->getMessage() . "',
                    icon: 'error',
                    confirmButtonText: 'Cerrar'
                });
            </script>";
        }
    }
    
}
?>

<main>
    <div class="block">
        <h2>Lista de Inmuebles</h2>
        <form method="GET" action="inmuebles.php" class="search-form">
            <input type="text" name="search" placeholder="Buscar por Número o Piso" value="<?php echo htmlspecialchars($busqueda); ?>" class="search-input"/>
            <button type="submit" class="search-btn"><i class="fa fa-search"></i></button>
            <a href="inmuebles.php" class="clear-btn"><i class="fa fa-times"></i></a>
        </form>
        
        <div class="table-container">
            <table>
                <thead>
                    <tr>
                        <th>Número Apartamento</th>
                        <th>Piso</th>
                        <th>Estado</th>
                        <th>Ajustes</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) : ?>
                        <tr>
                            <td><?php echo htmlspecialchars($row['numero_apartamento']); ?></td>
                            <td><?php echo htmlspecialchars($row['piso']); ?></td>
                            <td><?php echo htmlspecialchars($row['estado']); ?></td>
                            <td>
                                <button class="sw" onclick="openModal(<?php echo htmlspecialchars(json_encode($row)); ?>)">
                                    <i class="fa fa-cog"></i>
                                </button>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </div>

    <div class="block2">
        <h2>Registrar Nuevo Inmueble</h2>
        <form action="inmuebles.php" method="POST">
            <label for="piso">Piso:</label>
            <select id="piso" name="piso" required onchange="updateApartments()">
                <option value="1">Piso 1</option>
                <option value="2">Piso 2</option>
                <option value="3">Piso 3</option>
                <option value="4">Piso 4</option>
                <option value="5">Piso 5</option>
            </select>

            <label for="numero_apartamento">Número de Apartamento:</label>
            <select id="numero_apartamento" name="numero_apartamento" required>

            </select>

            <label for="estado">Estado:</label>
            <select id="estado" name="estado" required>
                <option value="disponible">Disponible</option>
                <option value="ocupado">Ocupado</option>
                <option value="mantenimiento">Mantenimiento</option>
            </select>

            <button type="submit" name="registrar">Registrar Inmueble</button>
        </form>
    </div>


    <div id="modal" class="modal">
        <div class="modal-content">
            <div class="titulo">
                <span>Ajustes</span>
                <span class="close" onclick="closeModal()">&times;</span>
            </div>

            <form method="POST" action="inmuebles.php">
                <input type="hidden" name="id" id="modal-id">
                <label>Piso:</label>
                <select name="piso" id="modal-piso" required onchange="updateModalApartments()">
                    <option value="1">Piso 1</option>
                    <option value="2">Piso 2</option>
                    <option value="3">Piso 3</option>
                    <option value="4">Piso 4</option>
                    <option value="5">Piso 5</option>
                </select>

                <label>Número de Apartamento:</label>
                <select name="numero_apartamento" id="modal-numero_apartamento" required>

                </select>

                <label>Estado:</label>
                <select name="estado" id="modal-estado" required>
                    <option value="disponible">Disponible</option>
                    <option value="ocupado">Ocupado</option>
                    <option value="mantenimiento">Mantenimiento</option>
                </select>

                <button type="submit" name="actualizar" class="sw">Actualizar</button>
                <button type="submit" name="eliminar" class="sw">Eliminar</button>
            </form>
        </div>
    </div>
</main>

<script>

function updateApartments() {
    const piso = document.getElementById('piso').value;
    const apartmentSelect = document.getElementById('numero_apartamento');
    apartmentSelect.innerHTML = '';

    for (let i = 1; i <= 4; i++) {
        let apartmentNumber = parseInt(piso + '0' + i);
        apartmentSelect.innerHTML += `<option value="${apartmentNumber}">${apartmentNumber}</option>`;
    }
}


function updateModalApartments() {
    const piso = document.getElementById('modal-piso').value;
    const apartmentSelect = document.getElementById('modal-numero_apartamento');
    apartmentSelect.innerHTML = '';

    for (let i = 1; i <= 4; i++) {
        let apartmentNumber = parseInt(piso + '0' + i);
        apartmentSelect.innerHTML += `<option value="${apartmentNumber}">${apartmentNumber}</option>`;
    }
}

function openModal(data) {
    document.getElementById('modal-id').value = data.id_inmueble;
    document.getElementById('modal-piso').value = data.piso;
    updateModalApartments();
    document.getElementById('modal-numero_apartamento').value = data.numero_apartamento;
    document.getElementById('modal-estado').value = data.estado;
    document.getElementById('modal').style.display = 'block';
}

function closeModal() {
    document.getElementById('modal').style.display = 'none';
}


updateApartments();
</script>


<?php include '../includes/footer.php'; ?>

<?php

include '../config/db.php';
include '../includes/header-admin.php';

if ($_SESSION['usuario']['rol'] !== 'admin') {
    header("Location: ../login/login.php");
    exit();
}

$busqueda = isset($_GET['search']) ? $_GET['search'] : '';
$fecha_busqueda = isset($_GET['fecha']) ? $_GET['fecha'] : '';
$searchQuery = "";
$params = [];

if ($busqueda || $fecha_busqueda) {
    $searchQuery = "WHERE 1=1";
    if ($busqueda) {
        $searchQuery .= " AND tipo_egreso LIKE ?";
        $params[] = '%' . $busqueda . '%';
    }
    if ($fecha_busqueda) {
        $searchQuery .= " AND fecha_egreso = ?";
        $params[] = $fecha_busqueda;
    }
}

$stmt = $pdo->prepare("SELECT * FROM Egresos $searchQuery");
$stmt->execute($params);

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $tipo_egreso = $_POST['tipo_egreso'];
    $monto = $_POST['monto'];
    $fecha_egreso = $_POST['fecha_egreso'];
    $descripcion = $_POST['descripcion'];

    if ($monto < 0) {
        echo "<script>
                Swal.fire({
                    position: 'center',
                    icon: 'error',
                    title: 'El monto no puede ser negativo',
                    showConfirmButton: true
                });
              </script>";
    } else {
        if (isset($_POST['registrar'])) {
            $sql = "INSERT INTO Egresos (tipo_egreso, monto, fecha_egreso, descripcion) VALUES (?, ?, ?, ?)";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$tipo_egreso, $monto, $fecha_egreso, $descripcion]);

            echo "<script>
                    Swal.fire({
                        title: 'Registrado!',
                        text: 'Egreso registrado con éxito.',
                        icon: 'success',
                        confirmButtonText: 'OK'
                    }).then(() => {
                        window.location = 'egresos.php';
                    });
                  </script>";
        }

        if (isset($_POST['actualizar'])) {
            $id_egreso = $_POST['id_egreso'];
            $sql = "UPDATE Egresos SET tipo_egreso = ?, monto = ?, fecha_egreso = ?, descripcion = ? WHERE id_egreso = ?";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$tipo_egreso, $monto, $fecha_egreso, $descripcion, $id_egreso]);

            echo "<script>
                    Swal.fire({
                        title: 'Actualizado!',
                        text: 'Egreso actualizado correctamente.',
                        icon: 'success',
                        confirmButtonText: 'OK'
                    }).then(() => {
                        window.location = 'egresos.php';
                    });
                  </script>";
        }

        if (isset($_POST['eliminar'])) {
            $id_egreso = $_POST['id_egreso'];
            echo "<script>
                Swal.fire({
                    title: '¿Estás seguro?',
                    text: 'No podrás deshacer esta acción',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonText: 'Sí, eliminar',
                    cancelButtonText: 'Cancelar',
                    reverseButtons: true
                }).then((result) => {
                    if (result.isConfirmed) {
                        fetch('egresos.php', {
                            method: 'POST',
                            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                            body: 'id_egreso={$id_egreso}&confirmar_eliminar=1'
                        })
                        .then(response => response.text())
                        .then(() => {
                            Swal.fire({
                                title: 'Eliminado!',
                                text: 'Egreso eliminado correctamente.',
                                icon: 'success',
                                confirmButtonText: 'OK'
                            }).then(() => {
                                window.location = 'egresos.php';
                            });
                        })
                        .catch(error => {
                            Swal.fire({
                                title: 'Error!',
                                text: 'Hubo un problema al eliminar.',
                                icon: 'error',
                                confirmButtonText: 'Cerrar'
                            });
                        });
                    }
                });
            </script>";
        }

        if (isset($_POST['confirmar_eliminar'])) {
            try {
                $stmt = $pdo->prepare("DELETE FROM Egresos WHERE id_egreso = ?");
                $stmt->execute([$_POST['id_egreso']]);
            } catch (Exception $e) {
                echo "<script>
                    Swal.fire({
                        title: 'Error!',
                        text: 'No se pudo eliminar: " . $e->getMessage() . "',
                        icon: 'error',
                        confirmButtonText: 'Cerrar'
                    });
                </script>";
            }
        }
    }
}
?>

<main>
    <div class="block">
        <h2>Lista de Egresos</h2>
        <form method="GET" action="egresos.php" class="search-form">
            <input type="text" name="search" placeholder="Buscar por Tipo de Egreso" value="<?php echo htmlspecialchars($busqueda); ?>" class="search-input"/>
            <input type="text" name="fecha" placeholder="Buscar por Fecha" value="<?php echo htmlspecialchars($fecha_busqueda); ?>" onfocus="(this.type='date')" onblur="(this.type='text')" class="search-input"/>
            <button type="submit" class="search-btn"><i class="fa fa-search"></i></button>
            <a href="egresos.php" class="clear-btn"><i class="fa fa-times"></i></a>
        </form>
        
        <div class="table-container">
            <table>
                <thead>
                    <tr>
                        <th>ID Egreso</th>
                        <th>Tipo de Egreso</th>
                        <th>Monto</th>
                        <th>Fecha</th>
                        <th>Descripción</th>
                        <th>Ajustes</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) : ?>
                        <tr>
                            <td><?php echo htmlspecialchars($row['id_egreso']); ?></td>
                            <td><?php echo htmlspecialchars($row['tipo_egreso']); ?></td>
                            <td>$<?php echo number_format($row['monto'], 2); ?></td>
                            <td><?php echo htmlspecialchars($row['fecha_egreso']); ?></td>
                            <td><?php echo htmlspecialchars($row['descripcion']); ?></td>
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
        <h2>Registrar Nuevo Egreso</h2>
        <form action="egresos.php" method="POST">
            <label for="tipo_egreso">Tipo de Egreso:</label>
            <div class="select-container">
                <select id="tipo_egreso" name="tipo_egreso" required>
                    <option value="">Seleccione un tipo</option>
                    <option value="Mantenimiento">Mantenimiento</option>
                    <option value="Servicios">Servicios</option>
                    <option value="Salarios">Salarios</option>
                    <option value="Otros">Otros</option>
                </select>
            </div>

            <label for="monto">Monto:</label>
            <input type="number" id="monto" name="monto" step="1000" required>

            <label for="fecha_egreso">Fecha de Egreso:</label>
            <input type="date" id="fecha_egreso" name="fecha_egreso" required>

            <label for="descripcion">Descripción:</label>
            <textarea id="descripcion" name="descripcion" required></textarea>

            <button type="submit" name="registrar">Registrar Egreso</button>
        </form>
    </div>

    <div id="modal" class="modal">
        <div class="modal-content">
            <div class="titulo">
                <span>Ajustes</span>
                <span class="close" onclick="closeModal()">&times;</span>
            </div>

            <form method="POST" action="egresos.php">
                <input type="hidden" name="id_egreso" id="modal-id_egreso">
                <label>Tipo de Egreso:</label>
                <select name="tipo_egreso" id="modal-tipo_egreso" required>
                    <option value="Mantenimiento">Mantenimiento</option>
                    <option value="Servicios">Servicios</option>
                    <option value="Salarios">Salarios</option>
                    <option value="Otros">Otros</option>
                </select>

                <label>Monto:</label>
                <input type="number" name="monto" id="modal-monto" step="0.01" required>

                <label>Fecha de Egreso:</label>
                <input type="date" name="fecha_egreso" id="modal-fecha_egreso" required>

                <label>Descripción:</label>
                <textarea name="descripcion" id="modal-descripcion" required></textarea>

                <button type="submit" name="actualizar" class="sw">Actualizar</button>
                <button type="submit" name="eliminar" class="sw">Eliminar</button>
            </form>
        </div>
    </div>
</main>

<script>
function openModal(data) {
    document.getElementById('modal-id_egreso').value = data.id_egreso;
    document.getElementById('modal-tipo_egreso').value = data.tipo_egreso;
    document.getElementById('modal-monto').value = data.monto;
    document.getElementById('modal-fecha_egreso').value = data.fecha_egreso;
    document.getElementById('modal-descripcion').value = data.descripcion;
    document.getElementById('modal').style.display = 'block';
}

function closeModal() {
    document.getElementById('modal').style.display = 'none';
}
</script>



<?php include '../includes/footer.php'; ?>

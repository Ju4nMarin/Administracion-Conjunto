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
    $searchQuery = "WHERE p.NIT LIKE ? OR p.nombre LIKE ? OR i.numero_apartamento LIKE ?";
}

$stmt = $pdo->prepare("SELECT p.*, i.numero_apartamento, i.piso FROM Propietarios p JOIN Inmuebles i ON p.apartamento = i.id_inmueble $searchQuery");
if ($busqueda) {
    $stmt->execute(['%' . $busqueda . '%', '%' . $busqueda . '%', '%' . $busqueda . '%']);
} else {
    $stmt->execute();
}

$inmuebles = $pdo->query("SELECT id_inmueble, numero_apartamento FROM Inmuebles WHERE estado = 'disponible'")->fetchAll(PDO::FETCH_ASSOC);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['registrar'])) {
        $nombre = $_POST['nombre'];
        $NIT = $_POST['NIT'];
        $numero_contacto = $_POST['numero_contacto'];
        $correo_electronico = $_POST['correo_electronico'];
        $apartamento = $_POST['apartamento'];

        try {
            $checkCorreo = $pdo->prepare("SELECT COUNT(*) FROM Propietarios WHERE correo_electronico = ?");
            $checkCorreo->execute([$correo_electronico]);

            if ($checkCorreo->fetchColumn() > 0) {
                throw new Exception("El correo electrónico ya está registrado con otro propietario.");
            }
           
            if (!preg_match('/^[0-9]{9,10}$/', $NIT)) {
                throw new Exception("El NIT debe tener entre 9 y 10 dígitos numéricos.");
            }

            
            if (!preg_match('/^[0-9]{10}$/', $numero_contacto)) {
                throw new Exception("El número de contacto debe tener 10 dígitos numéricos.");
            }

         
            if (!filter_var($correo_electronico, FILTER_VALIDATE_EMAIL)) {
                throw new Exception("El formato del correo electrónico no es válido.");
            }

   
            $checkNIT = $pdo->prepare("SELECT * FROM Propietarios WHERE NIT = ?");
            $checkNIT->execute([$NIT]);

            if ($checkNIT->rowCount() > 0) {
                throw new Exception("El NIT ya está registrado con otro propietario.");
            }


            $checkInmueble = $pdo->prepare("SELECT estado FROM Inmuebles WHERE id_inmueble = ?");
            $checkInmueble->execute([$apartamento]);
            $inmuebleEstado = $checkInmueble->fetch(PDO::FETCH_ASSOC);

            if ($inmuebleEstado['estado'] !== 'disponible') {
                throw new Exception("Este apartamento no está disponible para asignación.");
            }


            $pdo->beginTransaction();


            $stmt = $pdo->prepare("INSERT INTO Propietarios (nombre, NIT, numero_contacto, correo_electronico, apartamento) VALUES (?, ?, ?, ?, ?)");
            $stmt->execute([$nombre, $NIT, $numero_contacto, $correo_electronico, $apartamento]);


            $updateInmueble = $pdo->prepare("UPDATE Inmuebles SET estado = 'ocupado' WHERE id_inmueble = ?");
            $updateInmueble->execute([$apartamento]);


            $pdo->commit();

            echo "<script>
                Swal.fire({
                    title: 'Registrado!',
                    text: 'Propietario registrado correctamente.',
                    icon: 'success',
                    confirmButtonText: 'OK'
                }).then(() => {
                    window.location = 'propietarios.php';
                });
            </script>";
        } catch (Exception $e) {

            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }
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
        $nombre = $_POST['nombre'];
        $NIT = $_POST['NIT'];
        $numero_contacto = $_POST['numero_contacto'];
        $correo_electronico = $_POST['correo_electronico'];
        $apartamento = $_POST['apartamento'];

        try {
            $checkCorreo = $pdo->prepare("SELECT id_propietario FROM Propietarios WHERE correo_electronico = ? AND id_propietario != ?");
            $checkCorreo->execute([$correo_electronico, $id]);

            if ($checkCorreo->rowCount() > 0) {
                throw new Exception("El correo electrónico ya está registrado con otro propietario.");
            }

            if (!preg_match('/^[0-9]{9,10}$/', $NIT)) {
                throw new Exception("El NIT debe tener entre 9 y 10 dígitos numéricos.");
            }

            if (!preg_match('/^[0-9]{10}$/', $numero_contacto)) {
                throw new Exception("El número de contacto debe tener 10 dígitos numéricos.");
            }

            if (!filter_var($correo_electronico, FILTER_VALIDATE_EMAIL)) {
                throw new Exception("El formato del correo electrónico no es válido.");
            }

            $checkNIT = $pdo->prepare("SELECT id_propietario FROM Propietarios WHERE NIT = ? AND id_propietario != ?");
            $checkNIT->execute([$NIT, $id]);

            if ($checkNIT->rowCount() > 0) {
                throw new Exception("El NIT ya está registrado con otro propietario.");
            }

 
            $stmt = $pdo->prepare("SELECT apartamento FROM Propietarios WHERE id_propietario = ?");
            $stmt->execute([$id]);
            $oldApartment = $stmt->fetchColumn();


            if ($apartamento != $oldApartment) {
                $checkInmueble = $pdo->prepare("SELECT estado FROM Inmuebles WHERE id_inmueble = ?");
                $checkInmueble->execute([$apartamento]);
                $inmuebleEstado = $checkInmueble->fetch(PDO::FETCH_ASSOC);

                if ($inmuebleEstado['estado'] !== 'disponible') {
                    throw new Exception("El nuevo apartamento no está disponible para asignación.");
                }
            }

      
            $pdo->beginTransaction();

        
            $stmt = $pdo->prepare("UPDATE Propietarios SET nombre = ?, NIT = ?, numero_contacto = ?, correo_electronico = ?, apartamento = ? WHERE id_propietario = ?");
            $stmt->execute([$nombre, $NIT, $numero_contacto, $correo_electronico, $apartamento, $id]);

            if ($apartamento != $oldApartment) {
                $pdo->prepare("UPDATE Inmuebles SET estado = 'disponible' WHERE id_inmueble = ?")->execute([$oldApartment]);
                $pdo->prepare("UPDATE Inmuebles SET estado = 'ocupado' WHERE id_inmueble = ?")->execute([$apartamento]);
            }

    
            $pdo->commit();

            echo "<script>
                Swal.fire({
                    title: 'Actualizado!',
                    text: 'Propietario actualizado correctamente.',
                    icon: 'success',
                    confirmButtonText: 'OK'
                }).then(() => {
                    window.location = 'propietarios.php';
                });
            </script>";
        } catch (Exception $e) {

            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }
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
        $apartamento = $_POST['apartamento'];

        try {

            $checkFacturas = $pdo->prepare("
                SELECT COUNT(*) FROM Facturas 
                WHERE id_propietario = ? AND estado_pago = 'pendiente'
            ");
            $checkFacturas->execute([$id]);
            $facturasCount = $checkFacturas->fetchColumn();

            if ($facturasCount > 0) {
                throw new Exception("No se puede eliminar el propietario porque tiene facturas pendientes de pago.");
            }

            $checkPagos = $pdo->prepare("
                SELECT COUNT(*) FROM Facturas f
                JOIN Pagos p ON f.id_factura = p.id_factura
                WHERE f.id_propietario = ?
            ");
            $checkPagos->execute([$id]);
            $pagosCount = $checkPagos->fetchColumn();

            if ($pagosCount > 0) {
                throw new Exception("No se puede eliminar el propietario porque tiene pagos registrados en el sistema.");
            }

            echo "<script>
                Swal.fire({
                    title: '¿Estás seguro?',
                    text: 'Esta acción eliminará al propietario y todas sus facturas asociadas.',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonText: 'Sí, eliminar',
                    cancelButtonText: 'Cancelar',
                    reverseButtons: true
                }).then((result) => {
                    if (result.isConfirmed) {
                        fetch('propietarios.php', {
                            method: 'POST',
                            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                            body: 'id=" . $id . "&apartamento=" . $apartamento . "&confirmar_eliminar=1'
                        })
                        .then(response => response.text())
                        .then(() => {
                            Swal.fire({
                                title: 'Eliminado!',
                                text: 'Propietario eliminado correctamente.',
                                icon: 'success',
                                confirmButtonText: 'OK'
                            }).then(() => {
                                window.location = 'propietarios.php';
                            });
                        });
                    }
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

    if (isset($_POST['confirmar_eliminar'])) {
        try {
            $pdo->beginTransaction();


            $deleteFacturas = $pdo->prepare("DELETE FROM Facturas WHERE id_propietario = ?");
            $deleteFacturas->execute([$_POST['id']]);


            $deletePropietario = $pdo->prepare("DELETE FROM Propietarios WHERE id_propietario = ?");
            $deletePropietario->execute([$_POST['id']]);


            $updateInmueble = $pdo->prepare("UPDATE Inmuebles SET estado = 'disponible' WHERE id_inmueble = ?");
            $updateInmueble->execute([$_POST['apartamento']]);

            $pdo->commit();
        } catch (Exception $e) {
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }
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


?>




<main>
    <div class="block">
        <h2>Lista de Propietarios</h2>
        <form method="GET" action="propietarios.php" class="search-form">
            <input type="text" name="search" placeholder="Buscar por NIT, Nombre o Apartamento" value="<?php echo htmlspecialchars($busqueda); ?>" class="search-input"/>
            <button type="submit" class="search-btn"><i class="fa fa-search"></i></button>
            <a href="propietarios.php" class="clear-btn"><i class="fa fa-times"></i></a>
        </form>
        
        <div class="table-container">
            <table>
                <thead>
                    <tr>
                        <th>Nombre</th>
                        <th>NIT</th>
                        <th>Contacto</th>
                        <th>Email</th>
                        <th>Apartamento</th>
                        <th>Ajustes</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) : ?>
                        <tr>
                            <td><?php echo htmlspecialchars($row['nombre']); ?></td>
                            <td><?php echo htmlspecialchars($row['NIT']); ?></td>
                            <td><?php echo htmlspecialchars($row['numero_contacto']); ?></td>
                            <td><?php echo htmlspecialchars($row['correo_electronico']); ?></td>
                            <td><?php echo htmlspecialchars($row['numero_apartamento']); ?></td>
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
        <h2>Registrar Nuevo Propietario</h2>
        <form action="propietarios.php" method="POST">
            <label for="nombre">Nombre:</label>
            <input type="text" id="nombre" name="nombre" required>

            <label for="NIT">NIT:</label>
            <input type="text" id="NIT" name="NIT" required>

            <label for="numero_contacto">Número de Contacto:</label>
            <input type="text" id="numero_contacto" name="numero_contacto" required>

            <label for="correo_electronico">Correo Electrónico:</label>
            <input type="email" id="correo_electronico" name="correo_electronico" required>

            <label for="apartamento">Apartamento:</label>
            <div class="select-container">
                <select id="apartamento" name="apartamento" required>
                    <?php foreach ($inmuebles as $inmueble) : ?>
                        <option value="<?php echo $inmueble['id_inmueble']; ?>">
                            <?php echo htmlspecialchars($inmueble['numero_apartamento']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <button type="submit" name="registrar">Registrar Propietario</button>
        </form>
    </div>
    

    <div id="modal" class="modal">
        <div class="modal-content">
        <div class="titulo">
            <span>Ajustes</span>
            <span class="close" onclick="closeModal()">&times;</span>
        </div>
            
            <form method="POST" action="propietarios.php">
                <input type="hidden" name="id" id="modal-id">
                <label>Nombre:</label>
                <input type="text" name="nombre" id="modal-nombre" required>
                <label>NIT:</label>
                <input type="text" name="NIT" id="modal-NIT" required>
                <label>Contacto:</label>
                <input type="text" name="numero_contacto" id="modal-contacto" required>
                <label>Email:</label>
                <input type="email" name="correo_electronico" id="modal-email" required>
                
                <label>Apartamento:</label>
                <select name="apartamento" id="modal-apartamento" required>

                </select>

                <button type="submit" name="actualizar" class="sw">Actualizar</button>
                <button type="submit" name="eliminar" class="sw">Eliminar</button>
            </form>
        </div>
    </div>
</main>

<script>
function openModal(data) {
    document.getElementById('modal-id').value = data.id_propietario;
    document.getElementById('modal-nombre').value = data.nombre;
    document.getElementById('modal-NIT').value = data.NIT;
    document.getElementById('modal-contacto').value = data.numero_contacto;
    document.getElementById('modal-email').value = data.correo_electronico;

    const select = document.getElementById('modal-apartamento');
    select.innerHTML = `<option value="${data.apartamento}" selected>${data.numero_apartamento}</option>`;
    <?php foreach ($inmuebles as $inmueble) : ?>
        if (<?php echo $inmueble['id_inmueble']; ?> != data.apartamento) {
            select.innerHTML += `<option value="<?php echo $inmueble['id_inmueble']; ?>"><?php echo htmlspecialchars($inmueble['numero_apartamento']); ?></option>`;
        }
    <?php endforeach; ?>

    document.getElementById('modal').style.display = 'block';
}

function closeModal() {
    document.getElementById('modal').style.display = 'none';
}
</script>

<style>
.modal { display: none; position: fixed; }
</style>

<?php include '../includes/footer.php'; ?>

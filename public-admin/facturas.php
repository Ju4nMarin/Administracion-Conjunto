<?php
include '../config/db.php';
include '../includes/header-admin.php';

if ($_SESSION['usuario']['rol'] !== 'admin') {
    header("Location: ../login/login.php");
    exit();
}


$facturaBusqueda = isset($_GET['factura_busqueda']) ? $_GET['factura_busqueda'] : '';
$facturaFecha = isset($_GET['factura_fecha']) ? $_GET['factura_fecha'] : '';
$facturaEstado = isset($_GET['factura_estado']) ? $_GET['factura_estado'] : '';
$searchQuery = "WHERE 1=1";
$searchParams = [];

if ($facturaBusqueda) {
    $searchQuery .= " AND f.id_factura = ?";
    $searchParams[] = $facturaBusqueda;
}
if ($facturaFecha) {
    $searchQuery .= " AND f.fecha_emision = ?";
    $searchParams[] = $facturaFecha;
}
if ($facturaEstado) {
    $searchQuery .= " AND f.estado_pago = ?";
    $searchParams[] = $facturaEstado;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Modificar factura
    if (isset($_POST['modificar'])) {
        $id_factura = $_POST['id_factura'];
        $id_propietario = $_POST['id_propietario'];
        $fecha_emision = $_POST['fecha_emision'];
        $monto_total = $_POST['monto_total'];
        $estado_pago = $_POST['estado_pago'];

        try {
            $stmt_apto = $pdo->prepare("SELECT i.numero_apartamento 
                                        FROM Propietarios p
                                        JOIN Inmuebles i ON p.apartamento = i.id_inmueble
                                        WHERE p.id_propietario = ?");
            $stmt_apto->execute([$id_propietario]);
            $numero_apartamento = $stmt_apto->fetchColumn();

            $stmt = $pdo->prepare("
                UPDATE Facturas 
                SET id_propietario = ?, fecha_emision = ?, monto_total = ?, estado_pago = ?, numero_apartamento = ?
                WHERE id_factura = ?
            ");
            $stmt->execute([$id_propietario, $fecha_emision, $monto_total, $estado_pago, $numero_apartamento, $id_factura]);

            echo "<script>
                Swal.fire({
                    title: 'Modificado!',
                    text: 'Factura actualizada correctamente.',
                    icon: 'success',
                    confirmButtonText: 'OK'
                }).then(() => {
                    window.location = 'facturas.php';
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

    if (isset($_POST['agregar'])) {
        $id_propietario = $_POST['id_propietario'];
        $fecha_emision = $_POST['fecha_emision'];    
        $monto_total = $_POST['monto_total'];
        $estado_pago = $_POST['estado_pago'];

        try {

            $stmt_apto = $pdo->prepare("SELECT i.numero_apartamento 
                                        FROM Propietarios p
                                        JOIN Inmuebles i ON p.apartamento = i.id_inmueble
                                        WHERE p.id_propietario = ?");
            $stmt_apto->execute([$id_propietario]);
            $numero_apartamento = $stmt_apto->fetchColumn();

            $stmt = $pdo->prepare("
                INSERT INTO Facturas (id_propietario, fecha_emision, monto_total, estado_pago, numero_apartamento) 
                VALUES (?, ?, ?, ?, ?)");
            $stmt->execute([$id_propietario, $fecha_emision, $monto_total, $estado_pago, $numero_apartamento]);

            echo "<script>
                Swal.fire({
                    title: 'Agregado!',
                    text: 'Factura agregada correctamente.',
                    icon: 'success',
                    confirmButtonText: 'OK'
                }).then(() => {
                    window.location = 'facturas.php';
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

$stmtFacturas = $pdo->prepare("
    SELECT f.*, p.nombre 
    FROM Facturas f 
    JOIN Propietarios p ON f.id_propietario = p.id_propietario
    $searchQuery
    ORDER BY f.fecha_emision DESC
");
$stmtFacturas->execute($searchParams);

$propietarios = $pdo->query("SELECT id_propietario, nombre FROM Propietarios")->fetchAll(PDO::FETCH_ASSOC);
?>

<main>
    <div class="block">
        <h2>Lista de Facturas</h2>
        

        <form method="GET" action="facturas.php" class="search-form">
            <input type="text" name="factura_busqueda" placeholder="Buscar por Número de Factura" value="<?php echo htmlspecialchars($facturaBusqueda); ?>" class="search-input" />
            <input type="text" name="factura_fecha"  placeholder="Fecha Fin" value="<?php echo htmlspecialchars($facturaFecha); ?>" onfocus="(this.type='date')" onblur="(this.type='text')"/>
            <select name="factura_estado" class="search-input">
                <option value="">Estado del Pago</option>
                <option value="pendiente" <?php echo $facturaEstado === 'pendiente' ? 'selected' : ''; ?>>Pendiente</option>
                <option value="pagado" <?php echo $facturaEstado === 'pagado' ? 'selected' : ''; ?>>Pagado</option>
            </select>
            <button type="submit" class="search-btn"><i class="fa fa-search"></i></button>
            <a href="facturas.php" class="clear-btn"><i class="fa fa-times"></i></a>
        </form>


        <div class="table-container"> 
            <table>
                <thead>
                    <tr>
                        <th>ID Factura</th>
                        <th>Propietario</th>
                        <th>Fecha Emisión</th>
                        <th>Monto Total</th>
                        <th>Estado Pago</th>
                        <th>Número de Apartamento</th>
                        <th>Ajustes</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($row = $stmtFacturas->fetch(PDO::FETCH_ASSOC)) : ?>
                        <tr>
                            <td><?php echo htmlspecialchars($row['id_factura']); ?></td>
                            <td><?php echo htmlspecialchars($row['nombre']); ?></td>
                            <td><?php echo htmlspecialchars($row['fecha_emision']); ?></td>
                            <td>$<?php echo number_format($row['monto_total'],2); ?></td>
                            <td><?php echo htmlspecialchars($row['estado_pago']); ?></td>
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
        <h2>Registrar Nueva Factura</h2>
        <form action="facturas.php" method="POST">
            <label for="id_propietario">Propietario:</label>
            <div class="select-container">
                <select id="id_propietario" name="id_propietario" required>
                    <?php foreach ($propietarios as $propietario) : ?>
                        <option value="<?php echo $propietario['id_propietario']; ?>">
                            <?php echo htmlspecialchars($propietario['nombre']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <label for="fecha_emision">Fecha de Emisión:</label>
            <input type="date" id="fecha_emision" name="fecha_emision" required>

            <label for="monto_total">Monto Total:</label>
            <input type="number" id="monto_total" name="monto_total" step="1000" required>

            <label for="estado_pago">Estado del Pago:</label>
            <div class="select-container">
                <select id="estado_pago" name="estado_pago" required>
                    <option value="pendiente">Pendiente</option>
                    <option value="pagado">Pagado</option>
                </select>
            </div>

            <button type="submit" name="agregar">Registrar Factura</button>
        </form>
    </div>
</main>

<!-- Modal para modificar factura -->
<div id="modal" class="modal">
    <div class="modal-content">
        <div class="titulo">
            <span>Modificar Factura</span>
            <span class="close" onclick="closeModal()">&times;</span>
        </div>

        <form method="POST" action="facturas.php">
            <input type="hidden" name="id_factura" id="modal-id_factura">
            
            <label for="modal-id_propietario">Propietario:</label>
            <select id="modal-id_propietario" name="id_propietario" required>
                <?php foreach ($propietarios as $propietario) : ?>
                    <option value="<?php echo $propietario['id_propietario']; ?>">
                        <?php echo htmlspecialchars($propietario['nombre']); ?>
                    </option>
                <?php endforeach; ?>
            </select>

            <label for="modal-fecha_emision">Fecha de Emisión:</label>
            <input type="date" id="modal-fecha_emision" name="fecha_emision" required>

            <label for="modal-monto_total">Monto Total:</label>
            <input type="number" id="modal-monto_total" name="monto_total" step="0.01" required>

            <label for="modal-estado_pago">Estado del Pago:</label>
            <select id="modal-estado_pago" name="estado_pago" required>
                <option value="pendiente">Pendiente</option>
                <option value="pagado">Pagado</option>
            </select>

            <button type="submit" name="modificar">Guardar Cambios</button>
        </form>
    </div>
</div>

<script>
    function openModal(data) {
    document.getElementById('modal-id_factura').value = data.id_factura;
    document.getElementById('modal-id_propietario').value = data.id_propietario;
    document.getElementById('modal-fecha_emision').value = data.fecha_emision;
    document.getElementById('modal-monto_total').value = data.monto_total;
    document.getElementById('modal-estado_pago').value = data.estado_pago;
    document.getElementById('modal').style.display = 'block';
}

function closeModal() {
    document.getElementById('modal').style.display = 'none';
}



</script>

<?php include '../includes/footer.php'; ?>

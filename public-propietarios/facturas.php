<?php
// public-propietarios/facturas.php
include '../config/db.php';
include '../includes/header-admin.php';

// Validar sesión
if ($_SESSION['usuario']['rol'] !== 'propietario') {
    header("Location: ../login/login.php");
    exit();
}

// Parámetros de búsqueda para facturas
$facturaBusqueda = isset($_GET['factura_busqueda']) ? $_GET['factura_busqueda'] : '';
$facturaFecha = isset($_GET['factura_fecha']) ? $_GET['factura_fecha'] : '';
$facturaEstado = isset($_GET['factura_estado']) ? $_GET['factura_estado'] : '';
$facturaQuery = "WHERE f.id_propietario = ?";
$facturaParams = [$_SESSION['usuario']['id']];

if ($facturaBusqueda) {
    $facturaQuery .= " AND (f.id_factura = ?)";
    $facturaParams[] = $facturaBusqueda;
}
if ($facturaFecha) {
    $facturaQuery .= " AND f.fecha_emision = ?";
    $facturaParams[] = $facturaFecha;
}
if ($facturaEstado) {
    $facturaQuery .= " AND f.estado_pago = ?";
    $facturaParams[] = $facturaEstado;
}

// Consultar facturas
$stmtFacturas = $pdo->prepare("
    SELECT f.id_factura, f.fecha_emision, f.monto_total, f.estado_pago, 
           f.numero_apartamento, FLOOR(f.numero_apartamento / 100) AS piso
    FROM Facturas f
    $facturaQuery
    ORDER BY f.fecha_emision DESC
");
$stmtFacturas->execute($facturaParams);
?>


<main>
    <!-- Facturas -->
    <div class="block">
        <h2>Historial de Facturas</h2>
        <form method="GET" action="facturas.php" class="search-form">
            <input type="text" name="factura_busqueda" placeholder="Buscar por Número de Factura" value="<?php echo htmlspecialchars($facturaBusqueda); ?>" class="search-input" />

            <input type="text" name="factura_fecha" placeholder="Buscar por Fecha" value="<?php echo htmlspecialchars($facturaFecha); ?>" class="search-input" onfocus="(this.type='date')" onblur="(this.type='text')" />
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
                        <th>Fecha de Emisión</th>
                        <th>Monto</th>
                        <th>Estado</th>
                        <th>Número de Apartamento</th>
                        <th>Piso</th>
                        <th>Acción</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($row = $stmtFacturas->fetch(PDO::FETCH_ASSOC)) : ?>
                        <tr>
                            <td><?php echo htmlspecialchars($row['fecha_emision']); ?></td>
                            <td>$<?php echo number_format($row['monto_total'], 2); ?></td>
                            <td><?php echo htmlspecialchars($row['estado_pago']); ?></td>
                            <td><?php echo htmlspecialchars($row['numero_apartamento']); ?></td>
                            <td><?php echo htmlspecialchars($row['piso']); ?></td>
                            <td>
                                <?php if ($row['estado_pago'] === 'pendiente') : ?>
                                    <button onclick="pagarFactura(<?php echo $row['id_factura']; ?>)" class="sw">Pagar</button>
                                <?php else : ?>
                                    <span class="badge">Pagado</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </div>

</main>

<script>
function pagarFactura(idFactura) {
    Swal.fire({
        title: '¿Está seguro?',
        text: "Confirme si desea realizar el pago.",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: 'Sí, pagar',
        cancelButtonText: 'Cancelar'
    }).then((result) => {
        if (result.isConfirmed) {
            fetch('realizar_pago.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: `id_factura=${idFactura}`
            }).then(response => response.text()).then(data => {
                Swal.fire('Éxito', data, 'success').then(() => location.reload());
            }).catch(error => {
                Swal.fire('Error', 'No se pudo realizar el pago.', 'error');
            });
        }
    });
}
</script>

<?php include '../includes/footer.php'; ?>

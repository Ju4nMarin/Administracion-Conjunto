<?php
include '../config/db.php';
include '../includes/header-admin.php';


if ($_SESSION['usuario']['rol'] !== 'propietario') {
    header("Location: ../login/login.php");
    exit();
}

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


$stmtFacturas = $pdo->prepare("
    SELECT f.id_factura, f.fecha_emision, f.monto_total, f.estado_pago, 
           f.numero_apartamento, FLOOR(f.numero_apartamento / 100) AS piso
    FROM Facturas f
    $facturaQuery
    ORDER BY f.fecha_emision DESC
");
$stmtFacturas->execute($facturaParams);


$pagoFechaInicio = isset($_GET['pago_fecha_inicio']) ? $_GET['pago_fecha_inicio'] : '';
$pagoFechaFin = isset($_GET['pago_fecha_fin']) ? $_GET['pago_fecha_fin'] : '';
$pagoApto = isset($_GET['pago_apartamento']) ? $_GET['pago_apartamento'] : '';
$pagoQuery = "WHERE f.id_propietario = ?";
$pagoParams = [$_SESSION['usuario']['id']];

if ($pagoFechaInicio && $pagoFechaFin) {
    $pagoQuery .= " AND p.fecha_pago BETWEEN ? AND ?";
    $pagoParams[] = $pagoFechaInicio;
    $pagoParams[] = $pagoFechaFin;
}
if ($pagoApto) {
    $pagoQuery .= " AND f.numero_apartamento = ?";
    $pagoParams[] = $pagoApto;
}


$stmtPagos = $pdo->prepare("
    SELECT p.id_pago, p.fecha_pago, p.monto_pagado, 
           f.numero_apartamento, FLOOR(f.numero_apartamento / 100) AS piso
    FROM Pagos p
    INNER JOIN Facturas f ON p.id_factura = f.id_factura
    $pagoQuery
    ORDER BY p.fecha_pago DESC
");
$stmtPagos->execute($pagoParams);
?>

<main>
  
    <div class="block">
        <h2>Historial de Facturas</h2>
        
        <div class="table-container">
            <table>
                <thead>
                    <tr>
                        <th>Fecha de Emisión</th>
                        <th>Monto</th>
                        <th>Estado</th>
                        <th>Número de Apartamento</th>
                        <th>Piso</th>
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
    
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </div>

    <div class="block">
        <h2>Historial de Pagos</h2>
        
        <div class="table-container">
            <table>
                <thead>
                    <tr>
                        <th>Fecha de Pago</th>
                        <th>Monto Pagado</th>
                        <th>Número de Apartamento</th>
                        <th>Piso</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($row = $stmtPagos->fetch(PDO::FETCH_ASSOC)) : ?>
                        <tr>
                            <td><?php echo htmlspecialchars($row['fecha_pago']); ?></td>
                            <td>$<?php echo number_format($row['monto_pagado'], 2); ?></td>
                            <td><?php echo htmlspecialchars($row['numero_apartamento']); ?></td>
                            <td><?php echo htmlspecialchars($row['piso']); ?></td>
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

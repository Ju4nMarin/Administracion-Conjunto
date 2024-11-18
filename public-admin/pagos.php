<?php
include '../config/db.php';
include '../includes/header-admin.php';

if ($_SESSION['usuario']['rol'] !== 'admin') {
    header("Location: ../login/login.php");
    exit();
}


$pagoFechaInicio = isset($_GET['pago_fecha_inicio']) ? $_GET['pago_fecha_inicio'] : '';
$pagoFechaFin = isset($_GET['pago_fecha_fin']) ? $_GET['pago_fecha_fin'] : '';
$pagoApto = isset($_GET['pago_apartamento']) ? $_GET['pago_apartamento'] : '';
$searchQuery = "WHERE 1=1";
$searchParams = [];

if ($pagoFechaInicio && $pagoFechaFin) {
    $searchQuery .= " AND p.fecha_pago BETWEEN ? AND ?";
    $searchParams[] = $pagoFechaInicio;
    $searchParams[] = $pagoFechaFin;
}
if ($pagoApto) {
    $searchQuery .= " AND f.numero_apartamento = ?";
    $searchParams[] = $pagoApto;
}


$stmtPagos = $pdo->prepare("
    SELECT p.*, f.numero_apartamento, FLOOR(f.numero_apartamento / 100) AS piso 
    FROM Pagos p
    JOIN Facturas f ON p.id_factura = f.id_factura
    $searchQuery
    ORDER BY p.fecha_pago DESC
");
$stmtPagos->execute($searchParams);


$facturas = $pdo->query("SELECT id_factura, monto_total FROM Facturas WHERE estado_pago = 'pendiente'")->fetchAll(PDO::FETCH_ASSOC);
?>

<main>

    <div class="block">
        <h2>Lista de Pagos</h2>
        

        <form method="GET" action="pagos.php" class="search-form">

            <input type="text" name="pago_fecha_inicio" value="<?php echo htmlspecialchars($pagoFechaInicio); ?>"   placeholder="Fecha Inicio" onfocus="(this.type='date')" onblur="(this.type='text')"/>
            <input type="text" name="pago_fecha_fin" value="<?php echo htmlspecialchars($pagoFechaFin); ?>"   placeholder="Fecha Fin" onfocus="(this.type='date')" onblur="(this.type='text')"/>
            <input type="text" name="pago_apartamento" placeholder="Número de Apartamento" value="<?php echo htmlspecialchars($pagoApto); ?>" class="search-input" />
            <button type="submit" class="search-btn"><i class="fa fa-search"></i></button>
            <a href="pagos.php" class="clear-btn"><i class="fa fa-times"></i></a>
        </form>

        <div class="table-container"> 
            <table>
                <thead>
                    <tr>
                        <th>ID Pago</th>
                        <th>Fecha de Pago</th>
                        <th>Monto Pagado</th>
                        <th>Número de Apartamento</th>
                        <th>Piso</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($row = $stmtPagos->fetch(PDO::FETCH_ASSOC)) : ?>
                        <tr>
                            <td><?php echo htmlspecialchars($row['id_pago']); ?></td>
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

<?php include '../includes/footer.php'; ?>

<?php

include '../config/db.php';
include '../includes/header-admin.php';


if ($_SESSION['usuario']['rol'] !== 'propietario') {
    header("Location: ../login/login.php");
    exit();
}



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
        <h2>Historial de Pagos</h2>
        <form method="GET" action="pagos.php" class="search-form">
            
            <input type="text" name="pago_fecha_inicio"  class="form-control" placeholder="Fecha Inicio" onfocus="(this.type='date')" onblur="(this.type='text')"/>
            <input type="text" name="pago_fecha_fin"  class="form-control" placeholder="Fecha Fin" onfocus="(this.type='date')" onblur="(this.type='text')"/>
            
            <input type="text" name="pago_apartamento" placeholder="Número de Apartamento" value="<?php echo htmlspecialchars($pagoApto); ?>" class="search-input" />
            <button type="submit" class="search-btn"><i class="fa fa-search"></i></button>
            <a href="pagos.php" class="clear-btn"><i class="fa fa-times"></i></a>
        </form>
        <div class="table-container">
            <table>
                <thead>
                    <tr>
                        <th>Fecha de Pago</th>
                        <th>Monto Pagado</th>
                        <th>Número de Apartamento</th>
                        <th>Piso</th>
                        <th>Acción</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($row = $stmtPagos->fetch(PDO::FETCH_ASSOC)) : ?>
                        <tr>
                            <td><?php echo htmlspecialchars($row['fecha_pago']); ?></td>
                            <td>$<?php echo number_format($row['monto_pagado'], 2); ?></td>
                            <td><?php echo htmlspecialchars($row['numero_apartamento']); ?></td>
                            <td><?php echo htmlspecialchars($row['piso']); ?></td>
                            <td>
                                <a href="generar_certificado.php?id_pago=<?php echo $row['id_pago']; ?>" class="sw">Certificado</a>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </div>
</main>



<?php include '../includes/footer.php'; ?>

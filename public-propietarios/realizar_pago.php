<?php

session_start();
include '../config/db.php';

if ($_SESSION['usuario']['rol'] !== 'propietario') {
    header("Location: ../login/login.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['id_factura'])) {
    $idFactura = $_POST['id_factura'];
    $propietarioId = $_SESSION['usuario']['id'];
    try {
        $pdo->beginTransaction();


        $stmt = $pdo->prepare("
            SELECT * FROM Facturas 
            WHERE id_factura = ? AND id_propietario = ? AND estado_pago = 'pendiente'
        ");
        $stmt->execute([$idFactura, $propietarioId]);
        $factura = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$factura) {
            throw new Exception("Factura no encontrada o ya pagada.");
        }


        $stmt = $pdo->prepare("
            INSERT INTO Pagos (id_factura, fecha_pago, monto_pagado) 
            VALUES (?, NOW(), ?)
        ");
        $stmt->execute([$idFactura, $factura['monto_total']]);


        $stmt = $pdo->prepare("
            UPDATE Facturas 
            SET estado_pago = 'pagado' 
            WHERE id_factura = ?
        ");
        $stmt->execute([$idFactura]);

        $pdo->commit();
        echo "Pago realizado con éxito.";
    } catch (Exception $e) {
        $pdo->rollBack();
        http_response_code(500);
        echo "Error al procesar el pago: " . $e->getMessage();
    }
} else {
    http_response_code(400);
    echo "Solicitud inválida.";
}
?>

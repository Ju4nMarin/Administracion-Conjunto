<?php

session_start();
include '../config/db.php';
require '../libs/fpdf186/fpdf.php';


if ($_SESSION['usuario']['rol'] !== 'propietario') {
    header("Location: ../login/login.php");
    exit();
}


if (!isset($_GET['id_pago'])) {
    http_response_code(400);
    die("ID de pago no proporcionado.");
}

$idPago = $_GET['id_pago'];
$propietarioId = $_SESSION['usuario']['id'];

$stmt = $pdo->prepare("
    SELECT p.fecha_pago, p.monto_pagado, 
           f.numero_apartamento, FLOOR(f.numero_apartamento / 100) AS piso, 
           pr.nombre, pr.NIT
    FROM Pagos p
    INNER JOIN Facturas f ON p.id_factura = f.id_factura
    INNER JOIN Propietarios pr ON f.id_propietario = pr.id_propietario
    WHERE p.id_pago = ? AND f.id_propietario = ?
");
$stmt->execute([$idPago, $propietarioId]);
$pago = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$pago) {
    http_response_code(404);
    die("Pago no encontrado o no autorizado.");
}


class PDF extends FPDF {
    function Header() {
        $this->SetFont('Arial', 'B', 14);
        $this->SetTextColor(50, 50, 150);
        $this->Cell(0, 10, utf8_decode('Certificado de Pago'), 0, 1, 'C');
        $this->SetFont('Arial', 'I', 10);
        $this->SetTextColor(100, 100, 100);
        $this->Cell(0, 10, utf8_decode('Conjunto ERRE-53, Montería, Córdoba'), 0, 1, 'C');
        $this->Ln(10);
    }

    function Footer() {
        $this->SetY(-20);
        $this->SetFont('Arial', 'I', 8);
        $this->SetTextColor(100, 100, 100);
        $this->Cell(0, 10, utf8_decode('Documento generado automáticamente. Página ') . $this->PageNo(), 0, 0, 'C');
    }

    function AddCertificateDetails($propietario, $pago) {
        $this->SetFont('Arial', '', 12);
        $this->SetTextColor(0, 0, 0);
        $this->MultiCell(0, 10, utf8_decode("

Por la presente, se certifica que el propietario {$propietario['nombre']}, con NIT {$propietario['NIT']}, 
ha realizado el pago correspondiente al inmueble Apartamento {$pago['numero_apartamento']}, del Piso {$pago['piso']}.

Detalles del pago:
"), 0, 'J');
        $this->Ln(5);
    }

    function AddPaymentTable($pago) {
        $this->SetFont('Arial', 'B', 11);
        $this->SetTextColor(50, 50, 150);
        $this->Cell(0, 10, utf8_decode('Detalles del Pago'), 0, 1, 'C');
        $this->Ln(5);

        $this->SetFont('Arial', 'B', 10);
        $this->SetTextColor(0, 0, 0);
        $this->SetFillColor(230, 230, 250);
        $this->Cell(70, 10, utf8_decode('Fecha de Pago'), 1, 0, 'C', true);
        $this->Cell(60, 10, utf8_decode('Monto Pagado'), 1, 0, 'C', true);
        $this->Cell(60, 10, utf8_decode('Inmueble'), 1, 1, 'C', true);

        $this->SetFont('Arial', '', 10);
        $this->SetFillColor(250, 250, 250); 
        $this->Cell(70, 10, $pago['fecha_pago'], 1, 0, 'C', true);
        $this->Cell(60, 10, "$" . number_format($pago['monto_pagado'], 2), 1, 0, 'C', true);
        $this->Cell(60, 10, utf8_decode("Apartamento {$pago['numero_apartamento']}, Piso {$pago['piso']}"), 1, 1, 'C', true);
    }
}

$pdf = new PDF();
$pdf->AddPage();


$pdf->AddCertificateDetails($pago, $pago);


$pdf->AddPaymentTable($pago);


$pdf->Output('D', 'certificado_pago.pdf');

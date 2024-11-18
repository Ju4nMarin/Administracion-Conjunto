<?php
// config/db.php

$host = 'localhost';
$dbname = 'torre_erre_53';
$user = 'root';  // Asegúrate de cambiarlo si tienes una contraseña configurada
$password = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $user, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Error en la conexión: " . $e->getMessage());
}


?>
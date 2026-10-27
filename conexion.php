<?php
// conexion.php
$host     = "localhost";
$dbname   = "sephora_db";
$username = "root";       // Usuario por defecto en XAMPP
$password = "";           // Contraseña por defecto (vacía en XAMPP)

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("Error al conectar con la Base de Datos: " . $e->getMessage());
}
?>
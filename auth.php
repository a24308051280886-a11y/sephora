<?php
// auth.php - Procesa Registro e Inicio de Sesión y redirige a la página principal
session_start();
require_once 'conexion.php';

$action = $_GET['action'] ?? '';

// --- PROCESAR REGISTRO DE USUARIO ---
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $action === 'registro') {
    $nombre    = trim($_POST['nombre'] ?? '');
    $email     = trim($_POST['email'] ?? '');
    $pass      = trim($_POST['password'] ?? '');
    $telefono  = trim($_POST['telefono'] ?? '');
    $direccion = trim($_POST['direccion'] ?? '');

    if (empty($nombre) || empty($email) || empty($pass)) {
        header("Location: index.html?msg=" . urlencode("Por favor completa los campos obligatorios.") . "&type=error#registro");
        exit;
    }

    // Comprobar si el correo ya existe
    $stmtCheck = $pdo->prepare("SELECT id FROM usuarios WHERE email = ?");
    $stmtCheck->execute([$email]);
    if ($stmtCheck->fetch()) {
        header("Location: index.html?msg=" . urlencode("El correo electrónico ya está registrado.") . "&type=error#registro");
        exit;
    }

    // Encriptar contraseña
    $passwordHash = password_hash($pass, PASSWORD_BCRYPT);

    // Insertar en MySQL
    $sql = "INSERT INTO usuarios (nombre, email, password, telefono, direccion) VALUES (?, ?, ?, ?, ?)";
    $stmt = $pdo->prepare($sql);

    if ($stmt->execute([$nombre, $email, $passwordHash, $telefono, $direccion])) {
        $mensaje = "¡Registro exitoso! Ya puedes iniciar sesión con tu correo.";
        header("Location: index.html?msg=" . urlencode($mensaje) . "&type=exito#login");
        exit;
    } else {
        header("Location: index.html?msg=" . urlencode("Error al registrar en la base de datos.") . "&type=error#registro");
        exit;
    }
}

// --- PROCESAR INICIO DE SESIÓN ---
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $action === 'login') {
    $email = trim($_POST['email'] ?? '');
    $pass  = trim($_POST['password'] ?? '');

    $sql = "SELECT id, nombre, email, password FROM usuarios WHERE email = ?";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$email]);
    $usuario = $stmt->fetch();

    if ($usuario && password_verify($pass, $usuario['password'])) {
        $_SESSION['usuario_id']     = $usuario['id'];
        $_SESSION['usuario_nombre'] = $usuario['nombre'];

        $mensaje = "¡Bienvenida de nuevo, " . $usuario['nombre'] . "!";
        header("Location: index.html?msg=" . urlencode($mensaje) . "&type=exito#catalogo");
        exit;
    } else {
        header("Location: index.html?msg=" . urlencode("Correo o contraseña incorrectos.") . "&type=error#login");
        exit;
    }
}
?>
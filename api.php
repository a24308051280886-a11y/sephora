<?php
// api.php
session_start();
require_once 'conexion.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action      = $_POST['action'] ?? '';
    $producto_id = $_POST['producto_id'] ?? 0;

    if (!isset($_SESSION['usuario_id'])) {
        die("<h1>Atención</h1><p>Debes iniciar sesión para realizar compras.</p><a href='index.html#login'>Iniciar Sesión</a>");
    }

    $usuario_id = $_SESSION['usuario_id'];

    if ($action === 'agregar_carrito' && $producto_id > 0) {
        $stmtProd = $pdo->prepare("SELECT precio FROM productos WHERE id = ?");
        $stmtProd->execute([$producto_id]);
        $producto = $stmtProd->fetch();

        if ($producto) {
            $subtotal = $producto['precio'];
            $iva      = $subtotal * 0.16;
            $total    = $subtotal + $iva;

            try {
                $pdo->beginTransaction();

                // 1. Insertar pedido
                $stmtPed = $pdo->prepare("INSERT INTO pedidos (usuario_id, subtotal, iva, total) VALUES (?, ?, ?, ?)");
                $stmtPed->execute([$usuario_id, $subtotal, $iva, $total]);
                $pedido_id = $pdo->lastInsertId();

                // 2. Insertar detalle
                $stmtDet = $pdo->prepare("INSERT INTO detalle_pedidos (pedido_id, producto_id, cantidad, precio_unitario) VALUES (?, ?, 1, ?)");
                $stmtDet->execute([$pedido_id, $producto_id, $subtotal]);

                $pdo->commit();

                echo "<h1>¡Compra Registrada en la BD!</h1>";
                echo "<p>Pedido ID: <b>#" . $pedido_id . "</b></p>";
                echo "<p>Total Cobrado: <b>$" . number_format($total, 2) . " MXN</b> (incluye IVA)</p>";
                echo "<a href='index.html#catalogo'>Volver a la tienda</a>";

            } catch (Exception $e) {
                $pdo->rollBack();
                die("Error al procesar el pedido: " . $e->getMessage());
            }
        }
    }
}
?>
<?php
session_start();
include 'conexion.php';

// Verificar si el formulario se ha enviado
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = intval($_POST['id']);
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);

    // Actualizar la contraseña del usuario
    $sql = "UPDATE usuarios SET contrasena = ? WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("si", $password, $id);

    if ($stmt->execute()) {
        $_SESSION['mensaje'] = "Contraseña actualizada con éxito.";
    } else {
        $_SESSION['error'] = "Error al actualizar la contraseña: " . $conn->error;
    }

    $stmt->close();
    $conn->close();
    header("Location: ../vista/listar_usuarios.php");
    exit();
}
?>

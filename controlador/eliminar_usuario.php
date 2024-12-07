<?php
session_start();
include 'conexion.php';

// Verificar si se recibe un ID válido
if (isset($_GET['id'])) {
    $id = intval($_GET['id']);

    // Eliminar el usuario de la base de datos
    $sql = "DELETE FROM usuarios WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $id);

    if ($stmt->execute()) {
        $_SESSION['mensaje'] = "Usuario eliminado con éxito.";
    } else {
        $_SESSION['error'] = "Error al eliminar el usuario: " . $conn->error;
    }

    $stmt->close();
    $conn->close();
    header("Location: ../vista/listar_usuarios.php");
    exit();
} else {
    $_SESSION['error'] = "ID de usuario no válido.";
    header("Location: ../vista/listar_usuarios.php");
    exit();
}
?>

<?php
session_start();
include 'conexion.php';

// Verificar si el formulario se ha enviado
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $usuario = $_POST['usuario'];
    $email = $_POST['email'];
    $rol_id = $_POST['rol_id'];
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);

    // Insertar el nuevo usuario en la base de datos
    $sql = "INSERT INTO usuarios (usuario, email, contrasena, rol_id) VALUES (?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sssi", $usuario, $email, $password, $rol_id);

    if ($stmt->execute()) {
        $_SESSION['mensaje'] = "Usuario creado con éxito.";
        header("Location: ../Vista/listar_usuarios.php");
    } else {
        $_SESSION['error'] = "Error al crear el usuario: " . $conn->error;
        header("Location: ../Vista/listar_usuarios.php");
    }

    $stmt->close();
    $conn->close();
    exit();
}
?>

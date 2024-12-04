<?php
include 'conexion.php'; // Archivo con la conexión a la base de datos

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Recibir los datos del formulario
    $usuario = $_POST['usuario'];
    $email = $_POST['email'];
    $contrasena = $_POST['contrasena'];
    $rol_id = $_POST['rol_id'];

    // Encriptar la contraseña
    $contrasena_hash = password_hash($contrasena, PASSWORD_BCRYPT);

    // Insertar el nuevo usuario en la base de datos
    $sql = "INSERT INTO usuarios (usuario, contrasena, email, rol_id) VALUES (?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sssi", $usuario, $contrasena_hash, $email, $rol_id);

    if ($stmt->execute()) {
        header("Location: ../public/index.html"); // Redirige al inicio de sesión si es exitoso
        exit();
    } else {
        echo "Error en el registro: " . $conn->error;
    }

    $stmt->close();
    $conn->close();
}
?>

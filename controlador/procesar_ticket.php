<?php
session_start();

// Verificar si el usuario ha iniciado sesión
if (!isset($_SESSION['usuario_id'])) {
    header("Location: ../public/index.html");
    exit();
}

include 'conexion.php';

$usuario_id = $_SESSION['usuario_id'];
$titulo = $_POST['titulo'];
$descripcion = $_POST['descripcion'];
$categoria = $_POST['categoria'];
$estado = 'abierto'; // Estado inicial del ticket
$fecha_creacion = date('Y-m-d H:i:s'); // Fecha y hora actuales

// Insertar el nuevo ticket en la base de datos
$sql = "INSERT INTO tickets (titulo, descripcion, categoria, estado, usuario_id, fecha_creacion) VALUES (?, ?, ?, ?, ?, ?)";
$stmt = $conn->prepare($sql);
$stmt->bind_param("ssssis", $titulo, $descripcion, $categoria, $estado, $usuario_id, $fecha_creacion);

if ($stmt->execute()) {
    // Redirigir al usuario de vuelta a home.php después de crear el ticket
    header("Location: ../Vista/home.php");
    exit();
} else {
    echo "Error al crear el ticket: " . $conn->error;
}

// Cerrar la conexión
$stmt->close();
$conn->close();
?>

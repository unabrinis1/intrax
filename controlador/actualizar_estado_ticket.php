<?php
session_start();

// Verificar si el usuario ha iniciado sesion
if (!isset($_SESSION['usuario_id'])) {
    header("Location: index.html");
    exit();
}


include 'conexion.php';

$ticket_id = $_POST['ticket_id'];
$estado = $_POST['estado'];

// Actualizar el estado del ticket
$sql = "UPDATE tickets SET estado = ? WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("si", $estado, $ticket_id);

if ($stmt->execute()) {
    header("Location: ../Vista/ver_ticket_agente.php?id=$ticket_id");
    exit();
} else {
    echo "Error al actualizar el estado del ticket: " . $conn->error;
}

$stmt->close();
$conn->close();
?>

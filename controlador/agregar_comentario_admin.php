<?php
session_start();

// Verificar si el usuario ha iniciado sesión
if (!isset($_SESSION['usuario_id']) || $_SESSION['rol_id'] != 3) { // Solo para administradores
    header("Location: index.html");
    exit();
}

include 'conexion.php';

$usuario_id = $_SESSION['usuario_id'];
$ticket_id = $_POST['ticket_id'];
$comentario = $_POST['comentario'];

// Verificar que el ticket existe
$sql_ticket = "SELECT id FROM tickets WHERE id = ?";
$stmt_ticket = $conn->prepare($sql_ticket);
$stmt_ticket->bind_param("i", $ticket_id);
$stmt_ticket->execute();
$result_ticket = $stmt_ticket->get_result();

if ($result_ticket->num_rows === 0) {
    echo "Ticket no encontrado.";
    exit();
}

// Insertar el nuevo comentario en la tabla de historial
$sql = "INSERT INTO historial_tickets (ticket_id, usuario_id, comentario) VALUES (?, ?, ?)";
$stmt = $conn->prepare($sql);
$stmt->bind_param("iis", $ticket_id, $usuario_id, $comentario);

if ($stmt->execute()) {
    header("Location: ../Vista/ver_ticket_admin.php?id=$ticket_id"); // Redirigir a la vista del ticket de admin
    exit();
} else {
    echo "Error al añadir el comentario: " . $conn->error;
}

$stmt->close();
$conn->close();
?>

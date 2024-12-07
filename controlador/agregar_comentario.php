<?php
session_start();

// Verificar si el usuario ha iniciado sesión
if (!isset($_SESSION['usuario_id'])) {
    header("Location: index.html");
    exit();
}

include 'conexion.php';

$usuario_id = $_SESSION['usuario_id'];
$ticket_id = $_POST['ticket_id'];
$comentario = $_POST['comentario'];

// Insertar el nuevo comentario en la tabla de historial
$sql = "INSERT INTO historial_tickets (ticket_id, usuario_id, comentario) VALUES (?, ?, ?)";
$stmt = $conn->prepare($sql);
$stmt->bind_param("iis", $ticket_id, $usuario_id, $comentario);

if ($stmt->execute()) {
    // Redirigir al usuario de vuelta a la vista adecuada
    if ($_SESSION['rol_id'] == '2') { // Agente
        header("Location: ../vista/ver_ticket_agente.php?id=$ticket_id");
    } else { // Usuario normal
        header("Location: ../vista/ver_ticket.php?id=$ticket_id");
    }
    exit();
} else {
    echo "Error al añadir el comentario: " . $conn->error;
}

$stmt->close();
$conn->close();
?>

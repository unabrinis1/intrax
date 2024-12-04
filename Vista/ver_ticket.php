<?php
session_start();


include '../controlador/conexion.php';

$usuario_id = $_SESSION['usuario_id'];
$ticket_id = $_GET['id'] ?? null;

if (!$ticket_id) {
    echo "ID de ticket no especificado.";
    exit();
}

// Consultar los detalles del ticket
$sql_ticket = "SELECT id, titulo, descripcion, categoria, estado, fecha_creacion FROM tickets WHERE id = ? AND usuario_id = ?";
$stmt = $conn->prepare($sql_ticket);
$stmt->bind_param("ii", $ticket_id, $usuario_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    echo "Ticket no encontrado o no tienes permisos para verlo.";
    exit();
}

$ticket = $result->fetch_assoc();
$stmt->close();

// Consultar el historial de comentarios
$sql_historial = "SELECT historial_tickets.comentario, historial_tickets.fecha, usuarios.usuario AS usuario_nombre 
                  FROM historial_tickets 
                  JOIN usuarios ON historial_tickets.usuario_id = usuarios.id 
                  WHERE historial_tickets.ticket_id = ? 
                  ORDER BY historial_tickets.fecha ASC";
$stmt_historial = $conn->prepare($sql_historial);
$stmt_historial->bind_param("i", $ticket_id);
$stmt_historial->execute();
$result_historial = $stmt_historial->get_result();

$historial = [];
while ($row = $result_historial->fetch_assoc()) {
    $historial[] = $row;
}

$stmt_historial->close();
$conn->close();
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Detalles del Ticket</title>
    <link href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../public/estilos.css">

</head>
<body>
    <div class="container mt-5">
        <h2>Detalles del Ticket #<?= htmlspecialchars($ticket['id']); ?></h2>
        <div class="card mb-4">
            <div class="card-body">
                <h5 class="card-title">Título: <?= htmlspecialchars($ticket['titulo']); ?></h5>
                <p><strong>Descripción:</strong> <?= nl2br(htmlspecialchars($ticket['descripcion'])); ?></p>
                <p><strong>Categoría:</strong> <?= htmlspecialchars($ticket['categoria']); ?></p>
                <p><strong>Estado:</strong> <?= htmlspecialchars($ticket['estado']); ?></p>
                <p><strong>Fecha de Creación:</strong> <?= htmlspecialchars($ticket['fecha_creacion']); ?></p>
                <a href="home.php" class="btn btn-primary">Volver al Panel Principal</a>
            </div>
        </div>

        <!-- Historial de Comentarios -->
        <h3>Historial de Comentarios</h3>
        <div class="list-group mb-4">
            <?php foreach ($historial as $comentario): ?>
            <div class="list-group-item">
                <p><strong><?= htmlspecialchars($comentario['usuario_nombre']); ?>:</strong> <?= nl2br(htmlspecialchars($comentario['comentario'])); ?></p>
                <small class="text-muted"><?= htmlspecialchars($comentario['fecha']); ?></small>
            </div>
            <?php endforeach; ?>
        </div>

        <!-- Formulario para Añadir un Nuevo Comentario -->
        <h4>Añadir Comentario</h4>
        <form action="../controlador/agregar_comentario.php" method="POST">
            <div class="form-group">
                <textarea name="comentario" class="form-control" rows="3" placeholder="Escribe un comentario..." required></textarea>
            </div>
            <input type="hidden" name="ticket_id" value="<?= $ticket['id']; ?>">
            <button type="submit" class="btn btn-success mt-2">Añadir Comentario</button>
        </form>
    </div>

    <!-- Scripts de Bootstrap -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

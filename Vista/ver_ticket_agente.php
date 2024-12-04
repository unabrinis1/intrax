<?php
session_start();

// Verificar si el usuario ha iniciado sesión
if (!isset($_SESSION['usuario_id'])) {
    header("Location: index.html");
    exit();
}

include '../controlador/conexion.php';

$ticket_id = $_GET['id'];

// Consultar detalles del ticket
$sql_ticket = "SELECT id, titulo, descripcion, categoria, estado, fecha_creacion, agente_id FROM tickets WHERE id = ?";
$stmt_ticket = $conn->prepare($sql_ticket);
$stmt_ticket->bind_param("i", $ticket_id);
$stmt_ticket->execute();
$result_ticket = $stmt_ticket->get_result();
$ticket = $result_ticket->fetch_assoc();

// Consultar comentarios en historial_tickets
$sql_comments = "SELECT h.id, h.comentario, h.fecha, u.usuario AS autor FROM historial_tickets h INNER JOIN usuarios u ON h.usuario_id = u.id WHERE h.ticket_id = ? ORDER BY h.fecha ASC";
$stmt_comments = $conn->prepare($sql_comments);
$stmt_comments->bind_param("i", $ticket_id);
$stmt_comments->execute();
$result_comments = $stmt_comments->get_result();

// Obtener todos los comentarios
$comments = [];
while ($comment = $result_comments->fetch_assoc()) {
    $comments[] = $comment;
}

// Consultar todos los agentes para el desplegable de asignación
$sql_agentes = "SELECT id, usuario FROM usuarios WHERE rol_id = 2";
$result_agentes = $conn->query($sql_agentes);

// Cerrar las consultas de datos
$stmt_ticket->close();
$stmt_comments->close();
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ver Ticket - Panel de Agente</title>
    <link href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../public/estilos.css">
</head>
<body>
    <div class="container mt-5">
        <h2>Detalles del Ticket</h2>
        
        <!-- Detalles del Ticket -->
        <div class="card mb-4">
            <div class="card-body">
                <h5 class="card-title"><?= htmlspecialchars($ticket['titulo']); ?></h5>
                <p class="card-text"><strong>Descripción:</strong> <?= htmlspecialchars($ticket['descripcion']); ?></p>
                <p class="card-text"><strong>Categoría:</strong> <?= htmlspecialchars($ticket['categoria']); ?></p>
                <p class="card-text"><strong>Estado:</strong> <?= htmlspecialchars($ticket['estado']); ?></p>
                <p class="card-text"><strong>Fecha de Creación:</strong> <?= htmlspecialchars($ticket['fecha_creacion']); ?></p>

                <!-- Formulario para cambiar el estado y asignar el ticket -->
                <form method="POST" action="ver_ticket_agente.php?id=<?= $ticket_id; ?>">
                    <input type="hidden" name="ticket_id" value="<?= $ticket['id']; ?>">
                    
                    <!-- Cambiar Estado -->
                    <label for="estado">Cambiar Estado:</label>
                    <select name="estado" id="estado" class="form-control mb-3">
                        <option value="abierto" <?= $ticket['estado'] === 'abierto' ? 'selected' : ''; ?>>Abierto</option>
                        <option value="pendiente" <?= $ticket['estado'] === 'pendiente' ? 'selected' : ''; ?>>Pendiente</option>
                        <option value="cerrado" <?= $ticket['estado'] === 'cerrado' ? 'selected' : ''; ?>>Cerrado</option>
                    </select>
                    
                    <!-- Asignar Agente -->
                    <label for="agente">Asignar Agente:</label>
                    <select name="agente_id" id="agente" class="form-control mb-3">
                        <option value="">Seleccionar agente</option>
                        <?php while ($agente = $result_agentes->fetch_assoc()): ?>
                            <option value="<?= $agente['id']; ?>" <?= $agente['id'] == $ticket['agente_id'] ? 'selected' : ''; ?>>
                                <?= htmlspecialchars($agente['usuario']); ?>
                            </option>
                        <?php endwhile; ?>
                    </select>
                    
                    <button type="submit" class="btn btn-primary">Actualizar Estado y Asignación</button>
                </form>
            </div>
        </div>

        <!-- Sección de Comentarios -->
        <h4>Comentarios</h4>
        <div class="list-group mb-4">
            <?php foreach ($comments as $comment): ?>
                <div class="list-group-item">
                    <p><strong><?= htmlspecialchars($comment['autor']); ?>:</strong> <?= htmlspecialchars($comment['comentario']); ?></p>
                    <small class="text-muted"><?= htmlspecialchars($comment['fecha']); ?></small>
                </div>
            <?php endforeach; ?>
        </div>

        <!-- Formulario para agregar un nuevo comentario -->
        <form method="POST" action="../controlador/agregar_comentario.php">
            <input type="hidden" name="ticket_id" value="<?= $ticket['id']; ?>">
            <div class="form-group">
                <label for="comentario">Agregar Comentario</label>
                <textarea class="form-control" id="comentario" name="comentario" rows="3" required></textarea>
            </div>
            <button type="submit" class="btn btn-success mt-3">Enviar Comentario</button>
        </form>
        
        <a href="agente_home.php" class="btn btn-secondary mt-3">Volver al Panel</a>
    </div>
</body>
</html>

<?php
// Guardar el cambio de estado y asignación en la base de datos si se envió el formulario
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $nuevo_estado = $_POST['estado'];
    $nuevo_agente_id = $_POST['agente_id'];
    
    // Actualizar el estado y agente del ticket en la base de datos
    $sql_update = "UPDATE tickets SET estado = ?, agente_id = ? WHERE id = ?";
    $stmt_update = $conn->prepare($sql_update);
    $stmt_update->bind_param("sii", $nuevo_estado, $nuevo_agente_id, $ticket_id);

    if ($stmt_update->execute()) {
        echo "<script>alert('Ticket actualizado correctamente'); window.location.href = 'ver_ticket_agente.php?id=$ticket_id';</script>";
    } else {
        echo "Error al actualizar el ticket: " . $conn->error;
    }

    $stmt_update->close();
    $conn->close();
}
?>

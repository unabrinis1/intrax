<?php
session_start();

// Verificar si el usuario ha iniciado sesión
if (!isset($_SESSION['usuario_id'])) {
    header("Location: ../public/index.html");
    exit();
}

include '../controlador/conexion.php';

// Valores predeterminados para los filtros
$categoria_filtro = $_POST['categoria'] ?? '';
$estado_filtro = $_POST['estado'] ?? '';
$busqueda = $_POST['busqueda'] ?? ''; // Nuevo campo para búsqueda

// Consultas para contar los tickets según su estado (sin filtrar por usuario)
$sql_abiertos = "SELECT COUNT(*) as total FROM tickets WHERE estado = 'abierto'";
$result_abiertos = $conn->query($sql_abiertos)->fetch_assoc()['total'];

$sql_pendientes = "SELECT COUNT(*) as total FROM tickets WHERE estado = 'pendiente'";
$result_pendientes = $conn->query($sql_pendientes)->fetch_assoc()['total'];

$sql_cerrados = "SELECT COUNT(*) as total FROM tickets WHERE estado = 'cerrado'";
$result_cerrados = $conn->query($sql_cerrados)->fetch_assoc()['total'];

// Construcción dinámica de la consulta SQL para listar los tickets junto con el agente asignado
$sql_tickets = "
    SELECT 
        tickets.id, 
        tickets.titulo, 
        tickets.categoria, 
        tickets.estado, 
        usuarios_creador.usuario AS usuario_creador, 
        usuarios_agente.usuario AS agente_asignado
    FROM tickets
    LEFT JOIN usuarios AS usuarios_creador ON tickets.usuario_id = usuarios_creador.id
    LEFT JOIN usuarios AS usuarios_agente ON tickets.agente_id = usuarios_agente.id
    WHERE 1=1";
$params = [];
$types = "";

// Filtro por categoría
if ($categoria_filtro) {
    $sql_tickets .= " AND tickets.categoria = ?";
    $params[] = $categoria_filtro;
    $types .= "s";
}

// Filtro por estado
if ($estado_filtro) {
    $sql_tickets .= " AND tickets.estado = ?";
    $params[] = $estado_filtro;
    $types .= "s";
}

// Filtro por término de búsqueda
if (!empty($busqueda)) {
    $sql_tickets .= " AND (
        tickets.id LIKE ? OR 
        tickets.titulo LIKE ? OR 
        usuarios_creador.usuario LIKE ?
    )";
    $params[] = '%' . $busqueda . '%';
    $params[] = '%' . $busqueda . '%';
    $params[] = '%' . $busqueda . '%';
    $types .= "sss";
}

// Preparar la consulta con los filtros aplicados
$stmt_tickets = $conn->prepare($sql_tickets);
if ($types) {
    $stmt_tickets->bind_param($types, ...$params); // Expande el array para pasarlo como parámetros
}
$stmt_tickets->execute();
$result_tickets = $stmt_tickets->get_result();

$tickets = [];
while ($ticket = $result_tickets->fetch_assoc()) {
    $tickets[] = $ticket;
}

// Cerrar conexiones
$stmt_tickets->close();
$conn->close();
?>


<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>INTRAX - Panel de Agente</title>
    <link rel="stylesheet" href="../public/estilos.css">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" rel="stylesheet">
    <link href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container mt-5">
        <!-- Encabezado y Botones de Acción -->
        <div class="d-flex justify-content-between align-items-center mb-4">
    <h2>Panel de Administrador</h2>
    <div class="panel-botones">
    <button onclick="window.location.href='crear_ticket_admin.php'" class="btn btn-primary">
        <i class="fas fa-plus-circle"></i> Crear Nuevo Ticket
    </button>
    <button onclick="window.location.href='listar_usuarios.php'" class="btn btn-info">
        <i class="fas fa-users"></i> Usuarios
    </button>
    <button onclick="window.location.href='../controlador/logout.php'" class="btn btn-secondary">
        <i class="fas fa-sign-out-alt"></i> Cerrar Sesión
    </button>
</div>

</div>

        <!-- Formulario de Filtro -->
        <form method="POST" action="admin_home.php" class="mb-4">
            <div class="form-row">
                <div class="col">
                    <label for="categoria">Categoría</label>
                    <select name="categoria" id="categoria" class="form-control">
                        <option value="">Todas</option>
                        <option value="Hardware" <?= $categoria_filtro === 'Hardware' ? 'selected' : '' ?>>Hardware</option>
                        <option value="Software" <?= $categoria_filtro === 'Software' ? 'selected' : '' ?>>Software</option>
                        <option value="Problema de Contraseña" <?= $categoria_filtro === 'Problema de Contraseña' ? 'selected' : '' ?>>Problema de Contraseña</option>
                        <option value="Sin Internet" <?= $categoria_filtro === 'Sin Internet' ? 'selected' : '' ?>>Sin Internet</option>
                    </select>
                </div>
                <div class="col">
                    <label for="estado">Estado</label>
                    <select name="estado" id="estado" class="form-control">
                        <option value="">Todos</option>
                        <option value="abierto" <?= $estado_filtro === 'abierto' ? 'selected' : '' ?>>Abierto</option>
                        <option value="pendiente" <?= $estado_filtro === 'pendiente' ? 'selected' : '' ?>>Pendiente</option>
                        <option value="cerrado" <?= $estado_filtro === 'cerrado' ? 'selected' : '' ?>>Cerrado</option>
                    </select>
                </div>
                <div class="col d-flex align-items-end">
                    <button type="submit" class="btn btn-primary mt-2">Filtrar</button>
                </div>
            </div>
        </form>

        <!-- Resumen de Tickets -->
        <div class="row mb-4">
            <div class="col-md-4">
                <div class="card text-white bg-primary mb-3">
                    <div class="card-body">
                        <h5 class="card-title">Tickets Abiertos</h5>
                        <p class="card-text" id="ticketsAbiertos"><?= $result_abiertos; ?></p>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card text-white bg-warning mb-3">
                    <div class="card-body">
                        <h5 class="card-title">Tickets Pendientes</h5>
                        <p class="card-text" id="ticketsPendientes"><?= $result_pendientes; ?></p>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card text-white bg-success mb-3">
                    <div class="card-body">
                        <h5 class="card-title">Tickets Cerrados</h5>
                        <p class="card-text" id="ticketsCerrados"><?= $result_cerrados; ?></p>
                    </div>
                </div>
            </div>
        </div>

    <!-- boton para buscar Tickets -->

        <form method="POST" action="admin_home.php" class="d-flex mb-4">
    <input type="text" name="busqueda" id="busqueda" class="form-control" placeholder="Buscar tickets por título, usuario o número..."
           value="<?= htmlspecialchars($busqueda ?? ''); ?>">
    <button type="submit" class="btn btn-primary ml-2">
        <i class="fas fa-search"></i>
    </button>
</form>







        <!-- Tabla de Tickets -->
        <div class="table-responsive">
            <table class="table table-bordered">
            <thead class="thead-dark">
    <tr>
        <th>Nro Ticket</th>
        <th>Usuario</th> 
        <th>Título</th>
        <th>Categoría</th>
        <th>Estado</th>
        <th>Agente Asignado</th>
        <th>Acción</th>
    </tr>
</thead>
<tbody id="listaTickets">
    <?php foreach ($tickets as $ticket): ?>
    <tr>
        <td><?= $ticket['id']; ?></td>
        <td><?= htmlspecialchars($ticket['usuario_creador']); ?></td> 
        <td><?= htmlspecialchars($ticket['titulo']); ?></td>
        <td><?= htmlspecialchars($ticket['categoria']); ?></td>
        <td><?= htmlspecialchars($ticket['estado']); ?></td>
        <td><?= htmlspecialchars($ticket['agente_asignado'] ?? 'Sin asignar'); ?></td>
        <td><a href="ver_ticket_admin.php?id=<?= $ticket['id']; ?>" class="btn btn-info btn-sm">Ver</a></td>
    </tr>
    <?php endforeach; ?>
</tbody>

            </table>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

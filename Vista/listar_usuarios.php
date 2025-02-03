<?php
session_start();
include '../controlador/conexion.php';

// Obtener el filtro de rol si existe
$rol_filtro = $_POST['rol'] ?? '';
$search = $_GET['search'] ?? '';

// Construcción de la consulta SQL para filtrar usuarios
$sql_usuarios = "SELECT id, usuario, email, rol_id FROM usuarios WHERE 1=1";
$params = [];
$types = '';

if ($rol_filtro) {
    $sql_usuarios .= " AND rol_id = ?";
    $params[] = $rol_filtro;
    $types .= 'i';
}

if ($search) {
    $sql_usuarios .= " AND (usuario LIKE ? OR email LIKE ?)";
    $search_param = "%$search%";
    $params[] = $search_param;
    $params[] = $search_param;
    $types .= 'ss';
}

// Preparar la consulta con los filtros aplicados
$stmt_usuarios = $conn->prepare($sql_usuarios);
if ($types) {
    $stmt_usuarios->bind_param($types, ...$params);
}
$stmt_usuarios->execute();
$result_usuarios = $stmt_usuarios->get_result();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestión de Usuarios</title>
    <link href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../public/estilos.css">
</head>
<body>
    <div class="container mt-5">
        <h2>Gestión de Usuarios</h2>

        <!-- Barra de búsqueda -->
        <form method="GET" action="listar_usuarios.php" class="mb-4">
            <div class="input-group">
                <input type="text" name="search" class="form-control" placeholder="Buscar usuarios por nombre o email" value="<?= htmlspecialchars($search); ?>">
                <button class="btn btn-primary" type="submit">
                    <span class="bi bi-search"></span>
                </button>
            </div>
        </form>

       

        <!-- Formulario de Filtro -->
        <form method="POST" action="listar_usuarios.php" class="mb-4">
            <div class="form-group">
                <label for="rol">Filtrar por Rol</label>
                <select name="rol" id="rol" class="form-control">
                    <option value="">Todos</option>
                    <option value="1" <?= $rol_filtro === '1' ? 'selected' : ''; ?>>Usuario</option>
                    <option value="2" <?= $rol_filtro === '2' ? 'selected' : ''; ?>>Agente</option>
                    <option value="3" <?= $rol_filtro === '3' ? 'selected' : ''; ?>>Admin</option>
                </select>
            </div>
            <button type="submit" class="btn btn-primary">Filtrar</button>
        </form>

        <!-- Botón para Crear Usuario -->
        <button class="btn btn-success mb-4" data-bs-toggle="modal" data-bs-target="#crearUsuarioModal">Crear Nuevo Usuario</button>

        <div class="d-flex justify-content-between align-items-center mb-4">
    <h2>Listado de Usuarios</h2>
    <a href="../controlador/exportar_usuarios.php" class="btn btn-success">Exportar a CSV</a>
</div>


        <!-- Tabla de Usuarios -->
        <div class="table-responsive">
            <table class="table table-bordered">
                <thead class="thead-dark">
                    <tr>
                        <th>ID</th>
                        <th>Usuario</th>
                        <th>Email</th>
                        <th>Rol</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($row = $result_usuarios->fetch_assoc()): ?>
                        <tr>
                            <td><?= $row['id']; ?></td>
                            <td><?= htmlspecialchars($row['usuario']); ?></td>
                            <td><?= htmlspecialchars($row['email']); ?></td>
                            <td>
                                <?= $row['rol_id'] == 1 ? 'Usuario' : ($row['rol_id'] == 2 ? 'Agente' : 'Admin'); ?>
                            </td>
                            <td>
                                <button class="btn btn-danger btn-sm" onclick="eliminarUsuario(<?= $row['id']; ?>)">Eliminar</button>
                                <button class="btn btn-warning btn-sm" onclick="abrirCambiarContraseñaModal(<?= $row['id']; ?>)">Cambiar Contraseña</button>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>

        <a href="admin_home.php" class="btn btn-secondary mt-3">Volver al Panel de Administrador</a>
    </div>

    <!-- Modal Crear Usuario -->
    <div class="modal fade" id="crearUsuarioModal" tabindex="-1" aria-labelledby="crearUsuarioLabel" aria-hidden="true">
        <div class="modal-dialog">
            <form method="POST" action="../controlador/crear_usuario.php">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="crearUsuarioLabel">Crear Nuevo Usuario</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="form-group">
                            <label for="nuevoUsuario">Usuario</label>
                            <input type="text" name="usuario" id="nuevoUsuario" class="form-control" required>
                        </div>
                        <div class="form-group">
                            <label for="nuevoEmail">Email</label>
                            <input type="email" name="email" id="nuevoEmail" class="form-control" required>
                        </div>
                        <div class="form-group">
                            <label for="nuevoRol">Rol</label>
                            <select name="rol_id" id="nuevoRol" class="form-control" required>
                                <option value="1">Usuario</option>
                                <option value="2">Agente</option>
                                <option value="3">Admin</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="nuevoPassword">Contraseña</label>
                            <input type="password" name="password" id="nuevoPassword" class="form-control" required>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-success">Crear Usuario</button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Modal Cambiar Contraseña -->
    <div class="modal fade" id="cambiarContraseñaModal" tabindex="-1" aria-labelledby="cambiarContraseñaLabel" aria-hidden="true">
        <div class="modal-dialog">
            <form method="POST" action="../controlador/cambiar_contraseña.php">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="cambiarContraseñaLabel">Cambiar Contraseña</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <input type="hidden" name="id" id="usuarioIdCambiarContraseña">
                        <div class="form-group">
                            <label for="nuevaContraseña">Nueva Contraseña</label>
                            <input type="password" name="password" id="nuevaContraseña" class="form-control" required>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-warning">Cambiar Contraseña</button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <script>
        function eliminarUsuario(id) {
            if (confirm('¿Estás seguro de que deseas eliminar este usuario?')) {
                window.location.href = `../controlador/eliminar_usuario.php?id=${id}`;
            }
        }

        function abrirCambiarContraseñaModal(id) {
            document.getElementById('usuarioIdCambiarContraseña').value = id;
            new bootstrap.Modal(document.getElementById('cambiarContraseñaModal')).show();
        }
    </script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
<?php 
$stmt_usuarios->close();
$conn->close();
?>


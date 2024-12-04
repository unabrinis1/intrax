<?php
session_start();

// Verificar si el usuario ha iniciado sesión
if (!isset($_SESSION['usuario_id'])) {
    header("Location: ../public/index.html");
    exit();
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>INTRAX - Crear Ticket</title>
    <link rel="stylesheet" href="../public/estilos.css">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" rel="stylesheet">
    <link href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container mt-5">
        <h2>Crear Nuevo Ticket</h2>
        <form id="ticketForm" action="../controlador/procesar_ticket.php" method="POST">
            <div class="form-group">
                <label for="titulo">Título</label>
                <input type="text" class="form-control" id="titulo" name="titulo" required>
            </div>
            <div class="form-group">
                <label for="descripcion">Descripción</label>
                <textarea class="form-control" id="descripcion" name="descripcion" rows="4" required></textarea>
            </div>
            <div class="form-group">
                <label for="categoria">Categoría</label>
                <select class="form-control" id="categoria" name="categoria" required>
                    <option value="Hardware">Hardware</option>
                    <option value="Software">Software</option>
                    <option value="Problema de Contraseña">Problema de Contraseña</option>
                    <option value="Sin Internet">Sin Internet</option>
                </select>
            </div>
            <!-- Botón para abrir el modal -->
            <button type="button" class="btn btn-primary mt-3" onclick="if (validarFormulario()) $('#confirmModal').modal('show');">
    <i class="fas fa-ticket-alt"></i> Crear Ticket
</button>
<a href="home.php" class="btn btn-secondary mt-3">
    <i class="fas fa-ban"></i> Cancelar
</a>
        </form>
    </div>

    <!-- Modal de Confirmación -->
    <div class="modal fade" id="confirmModal" tabindex="-1" aria-labelledby="confirmModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="confirmModalLabel">Confirmación de Creación de Ticket</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    ¿Estás seguro de que deseas crear este ticket?
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="button" class="btn btn-primary" onclick="document.getElementById('ticketForm').submit();">Confirmar</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Scripts de Bootstrap  y JQUERY-->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>

<script>

function validarFormulario() {
    const titulo = document.getElementById('titulo').value.trim();
    const descripcion = document.getElementById('descripcion').value.trim();
    

    if (!titulo || !descripcion) {
        alert("Por favor, completa el título y la descripción ");
        return false;
    }
    return true;
}


</script>


</body>
</html>



<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Crear Ticket - Panel de Agente</title>
    <link href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../public/estilos.css">

</head>
<body>
    <div class="container mt-5">
        <h2>Crear Nuevo Ticket</h2>
        <form id="ticketForm" action="../controlador/procesar_ticket_agente.php" method="post" onsubmit="return validarUsuario()">
            <!-- Campo para buscar usuario -->
            <div class="form-group">
                <label for="buscarUsuario">Buscar Usuario</label>
                <input type="text" id="buscarUsuario" name="usuario" class="form-control" placeholder="Buscar usuario..." autocomplete="off" required>
                <div id="resultadosBusqueda" class="list-group mt-2"></div>
                <input type="hidden" id="usuarioValido" value="false">
            </div>

            <!-- Otros campos del formulario para el ticket -->
            <div class="form-group">
                <label for="titulo">Título del Ticket</label>
                <input type="text" id="titulo" name="titulo" class="form-control" required>
            </div>
            
            <div class="form-group">
                <label for="descripcion">Descripción</label>
                <textarea id="descripcion" name="descripcion" class="form-control" required></textarea>
            </div>
            
            <div class="form-group">
                <label for="categoria">Categoría</label>
                <select id="categoria" name="categoria" class="form-control" required>
                    <option value="Hardware">Hardware</option>
                    <option value="Software">Software</option>
                    <option value="Problema de Contraseña">Problema de Contraseña</option>
                    <option value="Sin Internet">Sin Internet</option>
                </select>
            </div>


            <!-- Campo para asignar el ticket a un agente -->
            <div class="form-group">
                <label for="agente_id">Asignar a un Agente</label>
                <select name="agente_id" id="agente_id" class="form-control" required>
                    <option value="">Seleccione un agente</option>
                    <?php
                    include '../controlador/conexion.php';

                    // Consulta para obtener solo los usuarios con rol de agente (rol_id = 2)
                    $sql_agentes = "SELECT id, usuario FROM usuarios WHERE rol_id = 2";
                    $result_agentes = $conn->query($sql_agentes);
                    while ($agente = $result_agentes->fetch_assoc()):
                    ?>
                        <option value="<?= $agente['id']; ?>"><?= htmlspecialchars($agente['usuario']); ?></option>
                    <?php endwhile; ?>
                </select>
            </div>

            <!-- Botón para abrir el modal de confirmación -->
            <button type="button" class="btn btn-primary mt-3" onclick="if (validarFormulario()) $('#confirmModal').modal('show');">
    <i class="fas fa-ticket-alt"></i> Crear Ticket
</button>
<a href="agente_home.php" class="btn btn-secondary mt-3">
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
                    <button type="button" class="btn btn-primary" onclick="validarModal();">Confirmar</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Scripts de Bootstrap y JavaScript para manejar la búsqueda y validación -->
    <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        $(document).ready(function() {
            $('#buscarUsuario').on('input', function() {
                const query = $(this).val();
                
                if (query.length > 1) { // Buscar solo si hay al menos 2 caracteres
                    $.ajax({
                        url: '../controlador/buscar_usuario.php',
                        type: 'GET',
                        data: { query: query },
                        success: function(data) {
                            $('#resultadosBusqueda').html(data);
                            $('#usuarioValido').val("false"); // Marcar como inválido hasta que se seleccione
                        }
                    });
                } else {
                    $('#resultadosBusqueda').empty();
                    $('#usuarioValido').val("false"); // Marcar como inválido si se limpia la búsqueda
                }
            });

            // Manejar el clic en los resultados de búsqueda
            $('#resultadosBusqueda').on('click', '.list-group-item', function() {
                const usuarioSeleccionado = $(this).text();
                $('#buscarUsuario').val(usuarioSeleccionado);
                $('#usuarioValido').val("true"); // Marcar como válido
                $('#resultadosBusqueda').empty();
            });
        });

        // Validar que el usuario sea válido y el campo no esté vacío antes de enviar el formulario
        function validarUsuario() {
            const usuarioValido = $('#usuarioValido').val() === "true";
            const buscarUsuario = $('#buscarUsuario').val().trim() !== "";

            // Verificar si el usuario es válido y el campo de búsqueda no está vacío
            if (!usuarioValido || !buscarUsuario) {
                alert("Por favor, seleccione un usuario válido de la lista y asegúrese de que el campo de búsqueda no esté vacío.");
                return false;
            }
            return true;
        }

        // Validación adicional antes de enviar el formulario desde el modal
        function validarModal() {
            if (validarUsuario()) {
                document.getElementById('ticketForm').submit();
            }
        }


        function validarFormulario() {
    const titulo = document.getElementById('titulo').value.trim();
    const descripcion = document.getElementById('descripcion').value.trim();
    const usuarioValido = document.getElementById('usuarioValido').value === "true";

    if (!titulo || !descripcion || !usuarioValido) {
        alert("Por favor, completa el título, la descripción y selecciona un usuario válido.");
        return false;
    }
    return true;
}




    </script>
</body>
</html>

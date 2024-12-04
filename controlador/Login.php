<?php
session_start();
include 'conexion.php';

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    // Sanitización de las entradas
    $usuario = trim($_POST['usuario']);
    $contrasena = trim($_POST['contrasena']);

    if (empty($usuario) || empty($contrasena)) {
        echo "<script>alert('Por favor, completa todos los campos.'); window.location.href = '../public/index.html';</script>";
        exit();
    }

    // Consulta preparada para evitar inyección SQL
    $sql = "SELECT id, rol_id, contrasena FROM usuarios WHERE usuario = ?";
    $stmt = $conn->prepare($sql);

    if (!$stmt) {
        echo "<script>alert('Error al preparar la consulta.'); window.location.href = '../public/index.html';</script>";
        exit();
    }

    $stmt->bind_param("s", $usuario);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 1) {
        $row = $result->fetch_assoc();

        // Verificar la contraseña
        if (password_verify($contrasena, $row['contrasena'])) {
            // Establecer variables de sesión
            $_SESSION['usuario_id'] = $row['id'];
            $_SESSION['rol_id'] = $row['rol_id'];

            // Redirigir según el rol
            switch ($row['rol_id']) {
                case 1:
                    header("Location: ../vista/home.php");
                    break;
                case 2:
                    header("Location: ../vista/agente_home.php");
                    break;
                case 3:
                    header("Location: ../vista/admin_home.php");
                    break;
                default:
                    echo "<script>alert('Rol no reconocido.'); window.location.href = '../public/index.html';</script>";
                    exit();
            }
            exit();
        } else {
            // Contraseña incorrecta
            echo "<script>alert('Contraseña incorrecta.'); window.location.href = '../public/index.html';</script>";
        }
    } else {
        // Usuario no encontrado
        echo "<script>alert('Usuario no encontrado.'); window.location.href = '../public/index.html';</script>";
    }

    $stmt->close();
}

$conn->close();
?>

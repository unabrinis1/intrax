<?php
session_start();
include 'conexion.php';

// Verificar si el usuario tiene permisos de administrador
if ($_SESSION['rol_id'] != 3) {
    header("Location: index.html");
    exit();
}

// Consultar todos los usuarios
$sql = "SELECT usuario, email, rol_id FROM usuarios";
$result = $conn->query($sql);

// Nombre del archivo exportado
$nombre_archivo = "usuarios_" . date("Y-m-d_H-i-s") . ".csv";

// Establecer encabezados para la descarga
header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename=' . $nombre_archivo);

// Crear un archivo CSV en memoria
$output = fopen('php://output', 'w');

// Encabezados del CSV
fputcsv($output, ['Usuario', 'Email', 'Rol']);

// Agregar los datos al archivo CSV
while ($row = $result->fetch_assoc()) {
    $rol = ($row['rol_id'] == 1) ? 'Usuario' : (($row['rol_id'] == 2) ? 'Agente' : 'Administrador');
    fputcsv($output, [$row['usuario'], $row['email'], $rol]);
}

// Cerrar la conexión
fclose($output);
$conn->close();
exit();

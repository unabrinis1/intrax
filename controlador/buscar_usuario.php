<?php
include 'conexion.php';

$query = $_GET['query'] ?? '';

if ($query) {
    $sql = "SELECT usuario FROM usuarios WHERE usuario LIKE ?";
    $stmt = $conn->prepare($sql);
    $param = "%$query%";
    $stmt->bind_param("s", $param);
    $stmt->execute();
    $result = $stmt->get_result();

    // Generar la lista de resultados
    while ($row = $result->fetch_assoc()) {
        echo '<a href="#" class="list-group-item list-group-item-action">' . htmlspecialchars($row['usuario']) . '</a>';
    }

    $stmt->close();
}

$conn->close();
?>

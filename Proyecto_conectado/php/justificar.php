<?php
// Incluir la conexión
require_once 'conexion.php';  // Asegúrate de que la ruta sea correcta

// Verificar si el formulario fue enviado
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Recoger los datos del formulario
    $evento = $_POST['evento'];
    $fecha_falta = $_POST['fecha_falta'];
    $motivo = $_POST['motivo'];
    $adjunto = null;

    // Verificar si se ha subido un archivo
    if (isset($_FILES['adjunto']) && $_FILES['adjunto']['error'] == UPLOAD_ERR_OK) {
        $nombreArchivo = $_FILES['adjunto']['name'];
        $rutaTemporal = $_FILES['adjunto']['tmp_name'];
        $carpetaDestino = 'uploads/';  // Carpeta donde se guardará el archivo

        // Verificar si la carpeta de destino existe, si no, crearla
        if (!is_dir($carpetaDestino)) {
            mkdir($carpetaDestino, 0777, true);
        }

        // Mover el archivo a la carpeta de destino
        $rutaFinal = $carpetaDestino . basename($nombreArchivo);
        move_uploaded_file($rutaTemporal, $rutaFinal);

        // Guardar la ruta del archivo en la base de datos
        $adjunto = $rutaFinal;
    }

    // Insertar la justificación en la base de datos
    try {
        $sql = "INSERT INTO justificaciones (id_usuario, id_evento, fecha_falta, motivo, archivo_adjunto_ruta, estado)
                VALUES (?, ?, ?, ?, ?, 'PENDIENTE')";
        
        // Preparar la consulta
        $stmt = $pdo->prepare($sql);
        
        // Ejecutar la consulta con los valores recibidos
        $stmt->execute([$_SESSION['id_usuario'], $evento, $fecha_falta, $motivo, $adjunto]);
        
        // Redirigir o mostrar un mensaje de éxito
        echo "Justificación enviada correctamente.";
    } catch (PDOException $e) {
        echo "Error al insertar la justificación: " . $e->getMessage();
    }
}
?>

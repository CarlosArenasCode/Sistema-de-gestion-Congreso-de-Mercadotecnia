<?php
session_start();
require '../php/conexion.php';

// Verificar que sea administrador
if (!isset($_SESSION['admin_id']) || $_SESSION['tipo'] !== 'admin') {
    http_response_code(401);
    echo json_encode([
        'success' => false,
        'message' => 'Acceso no autorizado. Se requiere sesión de administrador.'
    ]);
    exit;
}

header('Content-Type: application/json');

// Obtener parámetros desde GET, POST o JSON body
$id_usuario = null;
$id_evento = null;

// Intentar desde GET
if (isset($_GET['id_usuario']) && isset($_GET['id_evento'])) {
    $id_usuario = $_GET['id_usuario'];
    $id_evento = $_GET['id_evento'];
}
// Intentar desde POST
elseif (isset($_POST['id_usuario']) && isset($_POST['id_evento'])) {
    $id_usuario = $_POST['id_usuario'];
    $id_evento = $_POST['id_evento'];
}
// Intentar desde JSON body
else {
    $input = json_decode(file_get_contents('php://input'), true);
    $id_usuario = $input['id_usuario'] ?? null;
    $id_evento = $input['id_evento'] ?? null;
}

if (!$id_usuario || !$id_evento) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => 'Se requiere id_usuario e id_evento.'
    ]);
    exit;
}

try {
    // Verificar que existe asistencia registrada
    $sql = "SELECT 
                a.id_asistencia,
                a.fecha_asistencia,
                u.nombre_completo,
                u.matricula,
                u.semestre,
                e.nombre_evento,
                e.descripcion as evento_descripcion,
                e.fecha_inicio,
                e.hora_inicio,
                e.horas_para_constancia as duracion
            FROM asistencias a
            JOIN usuarios u ON a.id_usuario = u.id_usuario
            JOIN eventos e ON a.id_evento = e.id_evento
            WHERE a.id_usuario = :id_usuario AND a.id_evento = :id_evento";
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        ':id_usuario' => $id_usuario,
        ':id_evento' => $id_evento
    ]);
    
    $data = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$data) {
        http_response_code(404);
        echo json_encode([
            'success' => false,
            'message' => 'No se encontró asistencia registrada para generar constancia.'
        ]);
        exit;
    }
    
    // Convertir CLOBs
    foreach (['nombre_completo', 'matricula', 'nombre_evento', 'evento_descripcion'] as $field) {
        if (isset($data[$field]) && is_resource($data[$field])) {
            $data[$field] = stream_get_contents($data[$field]);
        }
    }
    
    // Generar nombre de archivo único
    $nombre_archivo = 'constancia_' . $id_usuario . '_' . $id_evento . '_' . time() . '.pdf';
    $ruta_completa = __DIR__ . '/../constancias_pdf/' . $nombre_archivo;
    
    // Asegurar que el directorio existe
    if (!file_exists(__DIR__ . '/../constancias_pdf/')) {
        mkdir(__DIR__ . '/../constancias_pdf/', 0777, true);
    }
    
    // Generar PDF simple (en producción usar una librería como TCPDF o FPDF)
    // Por ahora, generar un archivo de texto simulando PDF
    $contenido = "CONSTANCIA DE ASISTENCIA\n\n";
    $contenido .= "Se hace constar que:\n\n";
    $contenido .= "Nombre: " . $data['nombre_completo'] . "\n";
    $contenido .= "Matrícula: " . $data['matricula'] . "\n";
    $contenido .= "Semestre: " . ($data['semestre'] ?? 'N/A') . "\n\n";
    $contenido .= "Asistió al evento:\n\n";
    $contenido .= "Evento: " . $data['nombre_evento'] . "\n";
    $contenido .= "Fecha: " . $data['fecha_inicio'] . "\n";
    $contenido .= "Duración: " . $data['duracion'] . " horas\n\n";
    $contenido .= "Fecha de asistencia: " . $data['fecha_asistencia'] . "\n\n";
    $contenido .= "___________________________\n";
    $contenido .= "Firma del Coordinador\n";
    
    file_put_contents($ruta_completa, $contenido);
    
    // Actualizar registro de asistencia con ruta de constancia
    $sql_update = "UPDATE asistencias 
                   SET constancia_generada = 1, 
                       ruta_constancia = :ruta 
                   WHERE id_asistencia = :id_asistencia";
    
    $stmt_update = $pdo->prepare($sql_update);
    $stmt_update->execute([
        ':ruta' => '/constancias_pdf/' . $nombre_archivo,
        ':id_asistencia' => $data['id_asistencia']
    ]);
    
    echo json_encode([
        'success' => true,
        'message' => 'Constancia generada exitosamente.',
        'archivo' => $nombre_archivo,
        'url' => '/constancias_pdf/' . $nombre_archivo,
        'usuario' => $data['nombre_completo'],
        'evento' => $data['nombre_evento']
    ], JSON_PRETTY_PRINT);

} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Error al generar constancia: ' . $e->getMessage()
    ]);
}
?>

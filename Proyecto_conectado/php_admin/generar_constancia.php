<?php
session_start();
require '../php/conexion.php';
require '../php/fpdf/fpdf.php';
require '../php/phpqrcode/qrlib.php';

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
                u.id_usuario,
                u.nombre_completo,
                u.matricula,
                u.email,
                u.semestre,
                u.codigo_qr,
                e.id_evento,
                e.nombre_evento,
                e.ponente,
                e.descripcion as evento_descripcion,
                TO_CHAR(e.fecha_inicio, 'YYYY-MM-DD') as fecha_inicio,
                TO_CHAR(e.hora_inicio, 'HH24:MI') as hora_inicio,
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
    foreach (['nombre_completo', 'matricula', 'email', 'nombre_evento', 'evento_descripcion', 'ponente', 'codigo_qr'] as $field) {
        if (isset($data[$field]) && is_resource($data[$field])) {
            $data[$field] = stream_get_contents($data[$field]);
        }
    }
    
    // Generar datos para el QR Code
    $qr_data = json_encode([
        'tipo' => 'CONSTANCIA',
        'id_usuario' => $data['id_usuario'],
        'matricula' => $data['matricula'],
        'nombre' => $data['nombre_completo'],
        'email' => $data['email'],
        'evento_id' => $data['id_evento'],
        'evento' => $data['nombre_evento'],
        'fecha_evento' => $data['fecha_inicio'],
        'codigo_qr_usuario' => $data['codigo_qr'],
        'fecha_emision' => date('Y-m-d H:i:s'),
        'verificacion' => hash('sha256', $data['id_usuario'] . $data['id_evento'] . date('Ymd'))
    ], JSON_UNESCAPED_UNICODE);

    // Generar imagen QR temporal
    $temp_qr_dir = __DIR__ . '/../temp_qr/';
    if (!is_dir($temp_qr_dir)) {
        mkdir($temp_qr_dir, 0777, true);
    }
    $qr_filename = 'qr_constancia_' . $id_usuario . '_' . $id_evento . '_' . time() . '.png';
    $qr_filepath = $temp_qr_dir . $qr_filename;
    
    // Generar QR usando phpqrcode
    QRcode::png($qr_data, $qr_filepath, QR_ECLEVEL_L, 5, 2);
    
    // Generar nombre de archivo único
    $nombre_archivo = 'constancia_' . $id_usuario . '_' . $id_evento . '_' . time() . '.pdf';
    $ruta_completa = __DIR__ . '/../constancias_pdf/' . $nombre_archivo;
    
    // Asegurar que el directorio existe
    if (!file_exists(__DIR__ . '/../constancias_pdf/')) {
        mkdir(__DIR__ . '/../constancias_pdf/', 0777, true);
    }
    
    // Crear el PDF con FPDF
    $pdf = new FPDF('L', 'mm', 'A4'); // Landscape
    $pdf->AddPage();
    
    // Título
    $pdf->SetFont('Arial', 'B', 16);
    $pdf->Cell(0, 20, 'CONSTANCIA DE ASISTENCIA', 0, 1, 'C');
    $pdf->Ln(10);
    
    // Texto introductorio
    $pdf->SetFont('Arial', '', 12);
    $pdf->MultiCell(0, 10, utf8_decode("La Universidad Autónoma de Aguascalientes otorga la presente constancia a:"), 0, 'C');
    $pdf->Ln(10);
    
    // Nombre del participante
    $pdf->SetFont('Arial', 'B', 20);
    $pdf->Cell(0, 15, utf8_decode($data['nombre_completo']), 0, 1, 'C');
    $pdf->Ln(5);
    
    // Matrícula
    $pdf->SetFont('Arial', '', 10);
    $pdf->Cell(0, 5, utf8_decode("Matrícula: " . $data['matricula']), 0, 1, 'C');
    $pdf->Ln(10);
    
    // Descripción del evento
    $pdf->SetFont('Arial', '', 12);
    $texto_evento = "Por su valiosa participación en el evento '" . $data['nombre_evento'] . "'";
    if (!empty($data['ponente'])) {
        $texto_evento .= " impartido por " . $data['ponente'];
    }
    $texto_evento .= ".";
    $pdf->MultiCell(0, 10, utf8_decode($texto_evento), 0, 'C');
    $pdf->Ln(5);
    
    // Fecha y duración
    setlocale(LC_TIME, 'es_ES.UTF-8', 'Spanish_Spain', 'Spanish');
    $fecha_formateada = date("d \d\\e F \d\\e Y", strtotime($data['fecha_inicio']));
    $pdf->Cell(0, 10, utf8_decode("Realizado el " . $fecha_formateada), 0, 1, 'C');
    if (!empty($data['duracion'])) {
        $pdf->Cell(0, 5, utf8_decode("Duración: " . $data['duracion'] . " horas"), 0, 1, 'C');
    }
    $pdf->Ln(10);
    
    // Agregar código QR
    if (file_exists($qr_filepath)) {
        // Posicionar QR en la esquina inferior derecha
        $pdf->Image($qr_filepath, 230, 165, 50, 50, 'PNG');
        
        // Texto explicativo del QR
        $pdf->SetXY(225, 217);
        $pdf->SetFont('Arial', 'B', 8);
        $pdf->MultiCell(60, 3, utf8_decode("Código QR de Verificación"), 0, 'C');
        
        // Mostrar código QR del usuario como texto
        $pdf->SetXY(225, 222);
        $pdf->SetFont('Arial', '', 6);
        $codigo_corto = substr($data['codigo_qr'], 0, 30) . '...';
        $pdf->MultiCell(60, 3, utf8_decode($codigo_corto), 0, 'C');
    }
    
    // Agregar nombre completo y matrícula en la parte inferior izquierda
    $pdf->SetXY(10, 217);
    $pdf->SetFont('Arial', '', 8);
    $pdf->Cell(100, 4, utf8_decode("Otorgado a: " . $data['nombre_completo']), 0, 0, 'L');
    $pdf->SetXY(10, 222);
    $pdf->Cell(100, 4, utf8_decode("Matrícula: " . $data['matricula']), 0, 0, 'L');
    
    // Firma
    $pdf->SetY(180);
    $pdf->SetFont('Arial', '', 12);
    $pdf->Cell(0, 10, '_________________________', 0, 1, 'C');
    $pdf->Cell(0, 5, 'Rector de la Universidad', 0, 1, 'C');
    
    // Guardar PDF
    $pdf->Output('F', $ruta_completa);
    
    // Limpiar archivo QR temporal
    if (file_exists($qr_filepath)) {
        unlink($qr_filepath);
    }
    
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

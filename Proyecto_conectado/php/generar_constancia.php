<?php
// php/generar_constancia.php - Versión Oracle
require_once 'conexion.php';
require_once 'oracle_helpers.php';
require_once 'fpdf/fpdf.php';
require_once 'phpqrcode/qrlib.php';

// if (!isset($_SESSION['id_admin'])) {
//     http_response_code(403);
//     exit('Acceso denegado');
// }

function generarConstancia($id_usuario, $id_evento) {
    global $pdo;

    // 1. Obtener datos del usuario y del evento
    // Oracle: TO_CHAR para convertir DATE a string
    $stmt = $pdo->prepare("
        SELECT 
            u.id_usuario,
            u.matricula,
            u.nombre_completo,
            u.email,
            u.codigo_qr,
            e.id_evento,
            e.nombre_evento, 
            e.ponente, 
            TO_CHAR(e.fecha_inicio, 'YYYY-MM-DD') as fecha_inicio 
        FROM usuarios u, eventos e 
        WHERE u.id_usuario = :id_usuario 
        AND e.id_evento = :id_evento
    ");
    $stmt->execute([':id_usuario' => $id_usuario, ':id_evento' => $id_evento]);
    $datos = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$datos) {
        throw new Exception("No se encontraron datos para generar la constancia.");
    }

    // Convertir CLOBs a strings si es necesario
    foreach (['nombre_completo', 'matricula', 'email', 'nombre_evento', 'ponente', 'codigo_qr'] as $field) {
        if (isset($datos[$field]) && is_resource($datos[$field])) {
            $datos[$field] = stream_get_contents($datos[$field]);
        }
    }

    // 2. Generar datos para el QR Code
    $qr_data = json_encode([
        'tipo' => 'CONSTANCIA',
        'id_usuario' => $datos['id_usuario'],
        'matricula' => $datos['matricula'],
        'nombre' => $datos['nombre_completo'],
        'email' => $datos['email'],
        'evento_id' => $datos['id_evento'],
        'evento' => $datos['nombre_evento'],
        'fecha_evento' => $datos['fecha_inicio'],
        'codigo_qr_usuario' => $datos['codigo_qr'],
        'fecha_emision' => date('Y-m-d H:i:s'),
        'verificacion' => hash('sha256', $datos['id_usuario'] . $datos['id_evento'] . date('Ymd'))
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

    // 3. Crear el PDF
    $pdf = new FPDF('L', 'mm', 'A4'); // L = Landscape (Horizontal)
    $pdf->AddPage();
    $pdf->SetFont('Arial', 'B', 16);
    
    // --- DISEÑO DE LA CONSTANCIA (PERSONALIZABLE) ---
    $pdf->Cell(0, 20, 'CONSTANCIA DE ASISTENCIA', 0, 1, 'C');
    $pdf->Ln(10);

    $pdf->SetFont('Arial', '', 12);
    $pdf->MultiCell(0, 10, utf8_decode("La Universidad Autónoma de Aguascalientes otorga la presente constancia a:"), 0, 'C');
    $pdf->Ln(10);

    $pdf->SetFont('Arial', 'B', 20);
    $pdf->Cell(0, 15, utf8_decode($datos['nombre_completo']), 0, 1, 'C');
    $pdf->Ln(5);
    
    // Matrícula
    $pdf->SetFont('Arial', '', 10);
    $pdf->Cell(0, 5, utf8_decode("Matrícula: " . $datos['matricula']), 0, 1, 'C');
    $pdf->Ln(10);

    $pdf->SetFont('Arial', '', 12);
    $pdf->MultiCell(0, 10, utf8_decode("Por su valiosa participación en el evento '" . $datos['nombre_evento'] . "' impartido por " . $datos['ponente'] . "."), 0, 'C');
    $pdf->Ln(5);

    setlocale(LC_TIME, 'es_ES.UTF-8', 'Spanish_Spain', 'Spanish');
    $fecha_formateada = strftime("%d de %B de %Y", strtotime($datos['fecha_inicio']));
    $pdf->Cell(0, 10, utf8_decode("Realizado el " . $fecha_formateada . "."), 0, 1, 'C');
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
        $codigo_corto = substr($datos['codigo_qr'], 0, 30) . '...';
        $pdf->MultiCell(60, 3, utf8_decode($codigo_corto), 0, 'C');
    }
    
    // Agregar nombre completo en la parte inferior izquierda
    $pdf->SetXY(10, 217);
    $pdf->SetFont('Arial', '', 8);
    $pdf->Cell(100, 4, utf8_decode("Otorgado a: " . $datos['nombre_completo']), 0, 0, 'L');
    $pdf->SetXY(10, 222);
    $pdf->Cell(100, 4, utf8_decode("Matrícula: " . $datos['matricula']), 0, 0, 'L');
    
    // Firma
    $pdf->SetY(180);
    $pdf->SetFont('Arial', '', 12);
    $pdf->Cell(0, 10, '_________________________', 0, 1, 'C');
    $pdf->Cell(0, 5, 'Rector de la Universidad', 0, 1, 'C');
    
    // 3. Guardar el archivo en el servidor
    $directorio = '../constancias_generadas/';
    if (!is_dir($directorio)) {
        mkdir($directorio, 0777, true);
    }
    $numero_serie = uniqid('CONST-', true);
    $nombre_archivo = 'Constancia-' . preg_replace('/[^a-zA-Z0-9_-]/', '-', $datos['nombre_evento']) . '-' . $id_usuario . '.pdf';
    $ruta_completa = $directorio . $nombre_archivo;
    
    $pdf->Output('F', $ruta_completa);
    
    // Limpiar archivo QR temporal
    if (file_exists($qr_filepath)) {
        unlink($qr_filepath);
    }

    // 4. Guardar en la base de datos
    $stmt_check = $pdo->prepare("SELECT id_constancia FROM constancias WHERE id_usuario = :id_usuario AND id_evento = :id_evento");
    $stmt_check->execute([':id_usuario' => $id_usuario, ':id_evento' => $id_evento]);

    $ruta_db = 'constancias_generadas/' . $nombre_archivo;

    if ($stmt_check->fetch()) {
        // Oracle: SYSDATE en lugar de NOW()
        $sql = "UPDATE constancias 
                SET ruta_archivo_pdf = :ruta, 
                    fecha_emision = SYSDATE, 
                    numero_serie = :serie 
                WHERE id_usuario = :id_usuario 
                AND id_evento = :id_evento";
    } else {
        // Oracle: fecha_emision se establece por defecto con SYSDATE en el trigger/default
        $sql = "INSERT INTO constancias (id_usuario, id_evento, numero_serie, ruta_archivo_pdf) 
                VALUES (:id_usuario, :id_evento, :serie, :ruta)";
    }
    
    $stmt_db = $pdo->prepare($sql);
    $stmt_db->execute([
        ':id_usuario' => $id_usuario,
        ':id_evento' => $id_evento,
        ':serie' => $numero_serie,
        ':ruta' => $ruta_db
    ]);

    return [
        'success' => true, 
        'message' => "Constancia para {$datos['nombre_completo']} generada/actualizada.", 
        'path' => $ruta_completa
    ];
}
?>

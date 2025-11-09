<?php
// php/generar_constancia.php
require_once 'conexion.php';
require_once 'fpdf/fpdf.php'; // Esta es la línea que corregimos

// if (!isset($_SESSION['id_admin'])) {
//     http_response_code(403);
//     exit('Acceso denegado');
// }

function generarConstancia($id_usuario, $id_evento) {
    global $pdo;

    // 1. Obtener datos del usuario y del evento
    $stmt = $pdo->prepare("SELECT u.nombre_completo, e.nombre_evento, e.ponente, e.fecha_inicio FROM usuarios u, eventos e WHERE u.id_usuario = :id_usuario AND e.id_evento = :id_evento");
    $stmt->execute([':id_usuario' => $id_usuario, ':id_evento' => $id_evento]);
    $datos = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$datos) {
        throw new Exception("No se encontraron datos para generar la constancia.");
    }

    // 2. Crear el PDF
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
    $pdf->Ln(10);

    $pdf->SetFont('Arial', '', 12);
    $pdf->MultiCell(0, 10, utf8_decode("Por su valiosa participación en el evento '" . $datos['nombre_evento'] . "' impartido por " . $datos['ponente'] . "."), 0, 'C');
    $pdf->Ln(5);

    setlocale(LC_TIME, 'es_ES.UTF-8', 'Spanish_Spain', 'Spanish');
    $fecha_formateada = strftime("%d de %B de %Y", strtotime($datos['fecha_inicio']));
    $pdf->Cell(0, 10, utf8_decode("Realizado el " . $fecha_formateada . "."), 0, 1, 'C');
    $pdf->Ln(20);
    
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

    // 4. Guardar en la base de datos
    $stmt_check = $pdo->prepare("SELECT id_constancia FROM constancias WHERE id_usuario = :id_usuario AND id_evento = :id_evento");
    $stmt_check->execute([':id_usuario' => $id_usuario, ':id_evento' => $id_evento]);

    $ruta_db = 'constancias_generadas/' . $nombre_archivo;

    if ($stmt_check->fetch()) {
        $sql = "UPDATE constancias SET ruta_archivo_pdf = :ruta, fecha_emision = NOW(), numero_serie = :serie WHERE id_usuario = :id_usuario AND id_evento = :id_evento";
    } else {
        $sql = "INSERT INTO constancias (id_usuario, id_evento, numero_serie, ruta_archivo_pdf) VALUES (:id_usuario, :id_evento, :serie, :ruta)";
    }
    
    $stmt_db = $pdo->prepare($sql);
    $stmt_db->execute([
        ':id_usuario' => $id_usuario,
        ':id_evento' => $id_evento,
        ':serie' => $numero_serie,
        ':ruta' => $ruta_db
    ]);

    return ['success' => true, 'message' => "Constancia para {$datos['nombre_completo']} generada/actualizada.", 'path' => $ruta_completa];
}
?>
<?php
/**
 * generar_constancias_automaticas.php
 * 
 * Script automático que genera constancias para eventos finalizados
 * Se ejecuta periódicamente (cron job o Task Scheduler)
 * 
 * Funcionalidad:
 * 1. Busca eventos que ya terminaron (hora_fin < SYSDATE)
 * 2. Verifica que generen constancia (genera_constancia = 1)
 * 3. Busca usuarios con asistencia completa (hora_entrada Y hora_salida)
 * 4. Genera constancias PDF automáticamente para usuarios elegibles
 * 5. Evita duplicados verificando constancias ya generadas
 */

require_once 'conexion.php';
require_once 'oracle_helpers.php';
require_once 'fpdf/fpdf.php';
require_once 'phpqrcode/qrlib.php';

// Configuración
$DEBUG_MODE = true;
$LIMITE_EVENTOS = 50; // Máximo de eventos a procesar por ejecución
$TIEMPO_ESPERA = 30; // Minutos después de hora_fin para generar constancias

// Log de ejecución
$log_file = __DIR__ . '/../logs/constancias_auto_' . date('Y-m-d') . '.log';
if (!is_dir(__DIR__ . '/../logs/')) {
    mkdir(__DIR__ . '/../logs/', 0777, true);
}

function logMessage($message) {
    global $log_file, $DEBUG_MODE;
    $timestamp = date('Y-m-d H:i:s');
    $log_entry = "[{$timestamp}] {$message}\n";
    file_put_contents($log_file, $log_entry, FILE_APPEND);
    
    if ($DEBUG_MODE) {
        echo $log_entry;
    }
}

try {
    logMessage("=== INICIO DE GENERACIÓN AUTOMÁTICA DE CONSTANCIAS ===");
    
    // 1. Buscar eventos finalizados que generen constancia
    $sql_eventos = "
        SELECT 
            e.id_evento,
            e.nombre_evento,
            e.ponente,
            e.tipo_evento,
            e.horas_para_constancia,
            TO_CHAR(e.fecha_inicio, 'YYYY-MM-DD') as fecha_inicio,
            TO_CHAR(e.hora_fin, 'YYYY-MM-DD HH24:MI:SS') as hora_fin_str
        FROM eventos e
        WHERE e.genera_constancia = 1
          AND e.hora_fin < SYSDATE - INTERVAL '{$TIEMPO_ESPERA}' MINUTE
          AND e.hora_fin > SYSDATE - INTERVAL '7' DAY
        ORDER BY e.hora_fin DESC
        FETCH FIRST {$LIMITE_EVENTOS} ROWS ONLY
    ";
    
    $stmt_eventos = $pdo->query($sql_eventos);
    $eventos_finalizados = $stmt_eventos->fetchAll(PDO::FETCH_ASSOC);
    
    logMessage("Eventos finalizados encontrados: " . count($eventos_finalizados));
    
    if (count($eventos_finalizados) === 0) {
        logMessage("No hay eventos finalizados pendientes de generar constancias.");
        logMessage("=== FIN DE EJECUCIÓN ===\n");
        exit;
    }
    
    $total_generadas = 0;
    $total_errores = 0;
    $total_ya_existentes = 0;
    
    // 2. Procesar cada evento
    foreach ($eventos_finalizados as $evento) {
        $id_evento = $evento['id_evento'];
        $nombre_evento = $evento['nombre_evento'];
        $tipo_evento = $evento['tipo_evento'];
        $horas_requeridas = $evento['horas_para_constancia'];
        
        logMessage("\n--- Procesando Evento ID {$id_evento}: {$nombre_evento} ---");
        logMessage("   Tipo: {$tipo_evento} | Hora fin: {$evento['hora_fin_str']}");
        
        // 3. Buscar usuarios inscritos con asistencia
        // Nota: La tabla asistencias actual NO tiene hora_entrada/hora_salida/duracion
        // Solo verificamos si existe registro de asistencia
        $sql_usuarios = "
            SELECT DISTINCT
                i.id_usuario,
                u.nombre_completo,
                u.matricula,
                u.email,
                CASE WHEN MAX(a.id_asistencia) IS NOT NULL THEN 1 ELSE 0 END as tiene_asistencia,
                CASE WHEN c.id_constancia IS NOT NULL THEN 1 ELSE 0 END as constancia_existe
            FROM inscripciones i
            JOIN usuarios u ON i.id_usuario = u.id_usuario
            LEFT JOIN asistencias a ON a.id_usuario = i.id_usuario AND a.id_evento = i.id_evento
            LEFT JOIN constancias c ON c.id_usuario = i.id_usuario AND c.id_evento = i.id_evento
            WHERE i.id_evento = :id_evento
              AND i.estado = 'Inscrito'
            GROUP BY i.id_usuario, u.nombre_completo, u.matricula, u.email, c.id_constancia
        ";
        
        $stmt_usuarios = $pdo->prepare($sql_usuarios);
        $stmt_usuarios->execute([':id_evento' => $id_evento]);
        $usuarios_inscritos = $stmt_usuarios->fetchAll(PDO::FETCH_ASSOC);
        
        logMessage("   Usuarios inscritos totales: " . count($usuarios_inscritos));
        
        // Filtrar usuarios elegibles
        $usuarios_elegibles = [];
        foreach ($usuarios_inscritos as $usuario) {
            // Con la estructura actual, si tiene asistencia registrada, es elegible
            if ($usuario['tiene_asistencia'] == 1) {
                $usuarios_elegibles[] = $usuario;
            }
        }
        
        logMessage("   Usuarios elegibles para constancia: " . count($usuarios_elegibles));
        
        // 4. Generar constancia para cada usuario elegible
        foreach ($usuarios_elegibles as $usuario) {
            $id_usuario = $usuario['id_usuario'];
            $nombre = $usuario['nombre_completo'];
            $matricula = $usuario['matricula'];
            
            // Verificar si ya existe constancia
            if ($usuario['constancia_existe'] == 1) {
                logMessage("   ✓ {$nombre} ({$matricula}) - Constancia ya existente (SKIP)");
                $total_ya_existentes++;
                continue;
            }
            
            try {
                // Generar constancia usando la función existente
                $resultado = generarConstanciaPDF($id_usuario, $id_evento, $pdo);
                
                if ($resultado['success']) {
                    logMessage("   ✓ {$nombre} ({$matricula}) - Constancia generada: {$resultado['archivo']}");
                    $total_generadas++;
                } else {
                    logMessage("   ✗ {$nombre} ({$matricula}) - ERROR: {$resultado['message']}");
                    $total_errores++;
                }
                
            } catch (Exception $e) {
                logMessage("   ✗ {$nombre} ({$matricula}) - EXCEPCIÓN: " . $e->getMessage());
                $total_errores++;
            }
        }
    }
    
    // 5. Resumen final
    logMessage("\n=== RESUMEN DE EJECUCIÓN ===");
    logMessage("Eventos procesados: " . count($eventos_finalizados));
    logMessage("Constancias generadas: {$total_generadas}");
    logMessage("Constancias ya existentes: {$total_ya_existentes}");
    logMessage("Errores: {$total_errores}");
    logMessage("=== FIN DE EJECUCIÓN ===\n");
    
} catch (PDOException $e) {
    logMessage("ERROR CRÍTICO DE BASE DE DATOS: " . $e->getMessage());
    logMessage("Stack trace: " . $e->getTraceAsString());
} catch (Exception $e) {
    logMessage("ERROR CRÍTICO: " . $e->getMessage());
    logMessage("Stack trace: " . $e->getTraceAsString());
}

/**
 * Función para generar constancia en formato PDF
 * Versión simplificada de generar_constancia.php
 */
function generarConstanciaPDF($id_usuario, $id_evento, $pdo) {
    // 1. Obtener datos del usuario y evento
    $sql = "
        SELECT 
            u.id_usuario,
            u.matricula,
            u.nombre_completo,
            u.email,
            u.codigo_qr,
            e.id_evento,
            e.nombre_evento, 
            e.ponente, 
            e.descripcion,
            TO_CHAR(e.fecha_inicio, 'YYYY-MM-DD') as fecha_inicio,
            e.horas_para_constancia as duracion
        FROM usuarios u
        CROSS JOIN eventos e
        WHERE u.id_usuario = :id_usuario 
          AND e.id_evento = :id_evento
    ";
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute([':id_usuario' => $id_usuario, ':id_evento' => $id_evento]);
    $datos = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$datos) {
        return [
            'success' => false,
            'message' => "No se encontraron datos para generar constancia"
        ];
    }
    
    // Convertir CLOBs
    foreach (['nombre_completo', 'matricula', 'email', 'nombre_evento', 'ponente', 'codigo_qr', 'descripcion'] as $field) {
        if (isset($datos[$field]) && is_resource($datos[$field])) {
            $datos[$field] = stream_get_contents($datos[$field]);
        }
    }
    
    // 2. Generar datos para QR
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
    
    // 3. Generar imagen QR temporal
    $temp_qr_dir = __DIR__ . '/../temp_qr/';
    if (!is_dir($temp_qr_dir)) {
        mkdir($temp_qr_dir, 0777, true);
    }
    $qr_filename = 'qr_auto_' . $id_usuario . '_' . $id_evento . '_' . time() . '.png';
    $qr_filepath = $temp_qr_dir . $qr_filename;
    
    QRcode::png($qr_data, $qr_filepath, QR_ECLEVEL_L, 5, 2);
    
    // 4. Crear PDF
    $pdf = new FPDF('L', 'mm', 'A4');
    $pdf->AddPage();
    $pdf->SetFont('Arial', 'B', 16);
    
    // Título
    $pdf->Cell(0, 20, 'CONSTANCIA DE ASISTENCIA', 0, 1, 'C');
    $pdf->Ln(10);
    
    // Texto introductorio
    $pdf->SetFont('Arial', '', 12);
    $pdf->MultiCell(0, 10, utf8_decode("La Universidad Autónoma de Aguascalientes otorga la presente constancia a:"), 0, 'C');
    $pdf->Ln(10);
    
    // Nombre del participante
    $pdf->SetFont('Arial', 'B', 20);
    $pdf->Cell(0, 15, utf8_decode($datos['nombre_completo']), 0, 1, 'C');
    $pdf->Ln(5);
    
    // Matrícula
    $pdf->SetFont('Arial', '', 10);
    $pdf->Cell(0, 5, utf8_decode("Matrícula: " . $datos['matricula']), 0, 1, 'C');
    $pdf->Ln(10);
    
    // Descripción del evento
    $pdf->SetFont('Arial', '', 12);
    $texto_evento = "Por su valiosa participación en el evento '" . $datos['nombre_evento'] . "'";
    if (!empty($datos['ponente'])) {
        $texto_evento .= " impartido por " . $datos['ponente'];
    }
    $texto_evento .= ".";
    $pdf->MultiCell(0, 10, utf8_decode($texto_evento), 0, 'C');
    $pdf->Ln(5);
    
    // Fecha y duración
    $fecha_formateada = date("d \d\\e F \d\\e Y", strtotime($datos['fecha_inicio']));
    $pdf->Cell(0, 10, utf8_decode("Realizado el " . $fecha_formateada), 0, 1, 'C');
    if (!empty($datos['duracion'])) {
        $pdf->Cell(0, 5, utf8_decode("Duración: " . $datos['duracion'] . " horas"), 0, 1, 'C');
    }
    $pdf->Ln(10);
    
    // QR Code
    if (file_exists($qr_filepath)) {
        $pdf->Image($qr_filepath, 230, 165, 50, 50, 'PNG');
    }
    
    // Información del footer
    $pdf->SetFont('Arial', '', 8);
    $pdf->SetXY(10, 217);
    $pdf->Cell(100, 4, utf8_decode("Otorgado a: " . $datos['nombre_completo']), 0, 0, 'L');
    $pdf->SetXY(10, 222);
    $pdf->Cell(100, 4, utf8_decode("Matrícula: " . $datos['matricula']), 0, 0, 'L');
    
    // Título QR
    $pdf->SetFont('Arial', 'B', 8);
    $pdf->SetXY(225, 217);
    $pdf->Cell(60, 4, utf8_decode("Código QR de Verificación"), 0, 0, 'L');
    
    // Código QR truncado
    $pdf->SetFont('Courier', '', 6);
    $pdf->SetXY(225, 222);
    $codigo_truncado = substr($datos['codigo_qr'], 0, 30) . '...';
    $pdf->Cell(60, 4, $codigo_truncado, 0, 0, 'L');
    
    // Firma
    $pdf->SetY(180);
    $pdf->SetFont('Arial', '', 12);
    $pdf->Cell(0, 10, '_________________________', 0, 1, 'C');
    $pdf->Cell(0, 5, 'Rector de la Universidad', 0, 1, 'C');
    
    // 5. Guardar PDF
    $nombre_archivo = 'constancia_' . $id_usuario . '_' . $id_evento . '_' . time() . '.pdf';
    $directorio = __DIR__ . '/../constancias_pdf/';
    if (!is_dir($directorio)) {
        mkdir($directorio, 0777, true);
    }
    $ruta_completa = $directorio . $nombre_archivo;
    
    $pdf->Output('F', $ruta_completa);
    
    // Limpiar QR temporal
    if (file_exists($qr_filepath)) {
        unlink($qr_filepath);
    }
    
    // 6. Registrar en base de datos
    $numero_serie = uniqid('AUTO-CONST-', true);
    $ruta_db = 'constancias_pdf/' . $nombre_archivo;
    
    $sql_check = "SELECT id_constancia FROM constancias WHERE id_usuario = :id_usuario AND id_evento = :id_evento";
    $stmt_check = $pdo->prepare($sql_check);
    $stmt_check->execute([':id_usuario' => $id_usuario, ':id_evento' => $id_evento]);
    
    if ($stmt_check->fetch()) {
        // Actualizar
        $sql = "UPDATE constancias 
                SET ruta_archivo_pdf = :ruta, 
                    fecha_emision = SYSDATE, 
                    numero_serie = :serie 
                WHERE id_usuario = :id_usuario 
                AND id_evento = :id_evento";
    } else {
        // Insertar
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
    
    // 7. Actualizar tabla asistencias
    $sql_update_asist = "UPDATE asistencias 
                         SET constancia_generada = 1, 
                             ruta_constancia = :ruta 
                         WHERE id_usuario = :id_usuario 
                         AND id_evento = :id_evento";
    
    $stmt_update = $pdo->prepare($sql_update_asist);
    $stmt_update->execute([
        ':ruta' => '/' . $ruta_db,
        ':id_usuario' => $id_usuario,
        ':id_evento' => $id_evento
    ]);
    
    return [
        'success' => true,
        'message' => 'Constancia generada automáticamente',
        'archivo' => $nombre_archivo,
        'usuario' => $datos['nombre_completo']
    ];
}
?>

<?php
// php/constancias_usuario.php
// Configuración de sesión segura
ini_set('session.cookie_samesite', 'Lax');
ini_set('session.cookie_httponly', 1);
ini_set('session.use_strict_mode', 1);

session_start();
require_once 'conexion.php';

// Limpiar buffer de salida para evitar errores en el JSON
if (ob_get_level()) ob_end_clean();

header('Content-Type: application/json');

if (!isset($_SESSION['usuario_id'])) {
    http_response_code(401);
    echo json_encode(['error' => 'Sesión expirada. Recarga la página.']);
    exit;
}

$id_usuario = $_SESSION['usuario_id'];

try {
    // Consulta optimizada para Oracle
    // Calculamos la duración dinámicamente (hora_salida - hora_entrada)
    // para no depender de que la columna 'duracion' exista o esté actualizada.
    $sql = "
        WITH potenciales AS (
            -- TALLERES: Usuario inscrito
            SELECT e.id_evento, e.nombre_evento, e.tipo_evento, e.horas_para_constancia
            FROM eventos e
            JOIN inscripciones i ON e.id_evento = i.id_evento
            WHERE i.id_usuario = :id_1 AND e.tipo_evento = 'taller'
            
            UNION
            
            -- CONFERENCIAS: Usuario asistió al menos una vez
            SELECT e.id_evento, e.nombre_evento, e.tipo_evento, e.horas_para_constancia
            FROM eventos e
            JOIN asistencia a ON e.id_evento = a.id_evento
            WHERE a.id_usuario = :id_2 AND e.tipo_evento = 'conferencia'
        ),
        att AS (
            -- Cálculo de tiempos
            SELECT
                id_evento,
                SUM(CASE WHEN hora_salida IS NOT NULL THEN 1 ELSE 0 END) as asistencia_completa_count,
                SUM(
                    CASE WHEN hora_salida IS NOT NULL THEN
                        EXTRACT(DAY FROM (hora_salida - hora_entrada)) * 86400 +
                        EXTRACT(HOUR FROM (hora_salida - hora_entrada)) * 3600 +
                        EXTRACT(MINUTE FROM (hora_salida - hora_entrada)) * 60 +
                        EXTRACT(SECOND FROM (hora_salida - hora_entrada))
                    ELSE 0 END
                ) as duracion_total_seg
            FROM asistencia
            WHERE id_usuario = :id_3
            GROUP BY id_evento
        )
        SELECT DISTINCT
            p.id_evento,
            p.nombre_evento,
            p.tipo_evento,
            p.horas_para_constancia,
            COALESCE(att.asistencia_completa_count, 0) AS asistencia_completa,
            COALESCE(att.duracion_total_seg, 0) AS duracion_total_seg,
            c.ruta_archivo_pdf
        FROM potenciales p
        LEFT JOIN att ON p.id_evento = att.id_evento
        LEFT JOIN constancias c ON p.id_evento = c.id_evento AND c.id_usuario = :id_4
    ";

    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        ':id_1' => $id_usuario,
        ':id_2' => $id_usuario,
        ':id_3' => $id_usuario,
        ':id_4' => $id_usuario
    ]);
    
    $eventos = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $resultado = [];
    foreach ($eventos as $evento) {
        $item = [
            'nombre_evento' => $evento['NOMBRE_EVENTO'], // Oracle devuelve mayúsculas por defecto
            'estado' => 'Asistencia Incompleta',
            'url_descarga' => null
        ];

        // Lógica de elegibilidad
        $elegible = false;
        
        // Normalizamos las claves del array (Oracle usa mayúsculas)
        $tipo = strtolower($evento['TIPO_EVENTO']);
        $asistencias = intval($evento['ASISTENCIA_COMPLETA']);
        $segundos = floatval($evento['DURACION_TOTAL_SEG']);
        $horas_req = floatval($evento['HORAS_PARA_CONSTANCIA']);
        $pdf = $evento['RUTA_ARCHIVO_PDF'];

        if ($tipo == 'conferencia') {
            if ($asistencias > 0) $elegible = true;
        } elseif ($tipo == 'taller') {
            if ($segundos >= ($horas_req * 3600)) $elegible = true;
        }

        if ($elegible) {
            if ($pdf) {
                $item['estado'] = 'Disponible';
                $item['url_descarga'] = '../' . $pdf;
            } else {
                $item['estado'] = 'Pendiente de Emisión';
            }
        }
        
        $resultado[] = $item;
    }

    echo json_encode($resultado);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Error SQL: ' . $e->getMessage()]);
}
?>
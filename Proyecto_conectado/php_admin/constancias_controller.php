<?php
// php_admin/constancias_controller.oracle.php

// Desactivar warnings para evitar que rompan el JSON
error_reporting(E_ERROR | E_PARSE);
ini_set('display_errors', '0');

require_once '../php/conexion.php';
require_once '../php/oracle_helpers.php';
require_once '../php/generar_constancia.php';

header('Content-Type: application/json');

// if (!isset($_SESSION['id_admin'])) {
//     http_response_code(403);
//     echo json_encode(['error' => 'Acceso denegado.']);
//     exit;
// }

$action = $_REQUEST['action'] ?? '';

switch ($action) {
    case 'get_elegibles':
        getElegibles();
        break;
    case 'generar_una_constancia':
        generarUnaConstancia();
        break;
    case 'get_eventos_filtro':
        getEventosFiltro();
        break;
    default:
        http_response_code(400);
        echo json_encode(['error' => 'Acción no válida.']);
}

function getEventosFiltro() {
    global $pdo;
    try {
        // Solo mostrar eventos que generan constancias
        $stmt = $pdo->query("SELECT id_evento, nombre_evento, tipo_evento, 
                             TO_CHAR(fecha_inicio, 'YYYY-MM-DD') as fecha_inicio 
                             FROM eventos 
                             WHERE genera_constancia = 1 
                             ORDER BY fecha_inicio DESC, nombre_evento");
        $eventos = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Convertir CLOBs si es necesario
        foreach ($eventos as &$evento) {
            if (isset($evento['nombre_evento']) && is_resource($evento['nombre_evento'])) {
                $evento['nombre_evento'] = stream_get_contents($evento['nombre_evento']);
            }
        }
        
        echo json_encode(['success' => true, 'eventos' => $eventos]);
    } catch (Exception $e) {
        http_response_code(500);
        error_log("Error en getEventosFiltro: " . $e->getMessage());
        echo json_encode(['success' => false, 'error' => $e->getMessage()]);
    }
}

// --- FUNCIÓN getElegibles() MIGRADA A ORACLE ---
function getElegibles() {
    global $pdo;
    $id_evento_filtro = $_GET['id_evento'] ?? null;

    if (!$id_evento_filtro) {
        echo json_encode(['success' => true, 'usuarios' => []]);
        return;
    }

    try {
        $stmt_evento = $pdo->prepare("SELECT tipo_evento, horas_para_constancia FROM eventos WHERE id_evento = :id_evento");
        $stmt_evento->execute([':id_evento' => $id_evento_filtro]);
        $evento_info = $stmt_evento->fetch(PDO::FETCH_ASSOC);

        if (!$evento_info) {
            throw new Exception("Evento no encontrado.");
        }

        // Siempre buscar desde inscripciones
        $sql_users = "SELECT u.id_usuario, u.nombre_completo 
                     FROM usuarios u 
                     JOIN inscripciones i ON u.id_usuario = i.id_usuario 
                     WHERE i.id_evento = ? AND i.estado = 'Inscrito'";
        $stmt_users = $pdo->prepare($sql_users);
        $stmt_users->execute([$id_evento_filtro]);
        $usuarios_base = $stmt_users->fetchAll(PDO::FETCH_ASSOC);

        if (empty($usuarios_base)) {
            echo json_encode(['success' => true, 'usuarios' => []]);
            return;
        }
        
        $user_ids = array_column($usuarios_base, 'id_usuario');
        $placeholders = implode(',', array_fill(0, count($user_ids), '?'));
        
        // Nota: La tabla asistencias actual NO tiene hora_entrada/hora_salida/duracion
        // Solo verificamos si existe registro de asistencia
        $sql_details = "
            SELECT
                u.id_usuario,
                CASE WHEN MAX(a.id_asistencia) IS NOT NULL THEN 1 ELSE 0 END as asistencia_completa_count,
                CASE WHEN MAX(c.id_constancia) IS NOT NULL THEN 1 ELSE 0 END as emitida,
                MAX(c.ruta_archivo_pdf) as ruta_archivo_pdf
            FROM usuarios u
            LEFT JOIN asistencias a ON u.id_usuario = a.id_usuario AND a.id_evento = ?
            LEFT JOIN constancias c ON u.id_usuario = c.id_usuario AND c.id_evento = ?
            WHERE u.id_usuario IN ($placeholders)
            GROUP BY u.id_usuario
        ";
        
        $params = array_merge([$id_evento_filtro, $id_evento_filtro], $user_ids);
        $stmt_details = $pdo->prepare($sql_details);
        $stmt_details->execute($params);
        $details_map = [];
        while ($row = $stmt_details->fetch(PDO::FETCH_ASSOC)) {
            $details_map[$row['id_usuario']] = $row;
        }

        $resultado_final = [];
        foreach ($usuarios_base as $usuario) {
            $details = $details_map[$usuario['id_usuario']] ?? [
                'asistencia_completa_count' => 0, 
                'emitida' => 0, 
                'ruta_archivo_pdf' => null
            ];

            // Con la estructura actual de asistencias, simplemente si asistió (tiene registro), es elegible
            $usuario['elegible'] = ($details['asistencia_completa_count'] > 0);

            // Oracle devuelve 1/0 en lugar de booleano, convertir a bool
            $usuario['emitida'] = (bool)$details['emitida'];
            $usuario['ruta_archivo_pdf'] = $details['ruta_archivo_pdf'];
            $resultado_final[] = $usuario;
        }

        echo json_encode(['success' => true, 'usuarios' => $resultado_final]);

    } catch (Exception $e) {
        http_response_code(500);
        error_log("Error en getElegibles (Oracle): " . $e->getMessage());
        echo json_encode(['success' => false, 'error' => $e->getMessage()]);
    }
}

function generarUnaConstancia() {
    $id_usuario = $_POST['id_usuario'] ?? 0;
    $id_evento = $_POST['id_evento'] ?? 0;
    if (!$id_usuario || !$id_evento) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Faltan ID de usuario o evento.']);
        return;
    }
    try {
        // La función generarConstancia() debe ser compatible con Oracle
        // Asegúrate de que generar_constancia.php use conexion.oracle.php
        $resultado = generarConstancia($id_usuario, $id_evento);
        echo json_encode($resultado);
    } catch (Exception $e) {
        http_response_code(500);
        error_log("Error en generarUnaConstancia (Oracle): " . $e->getMessage());
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    }
}
?>

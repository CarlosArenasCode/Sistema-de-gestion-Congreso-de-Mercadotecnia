<?php
// php_admin/constancias_controller.php
require_once '../php/conexion.php';
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
        getEventosFiltro(); // CORREGIDO
        break;
    default:
        http_response_code(400);
        echo json_encode(['error' => 'Acción no válida.']);
}

function getEventosFiltro() {
    global $pdo;
    $stmt = $pdo->query("SELECT id_evento, nombre_evento FROM eventos ORDER BY nombre_evento");
    echo json_encode(['success' => true, 'eventos' => $stmt->fetchAll(PDO::FETCH_ASSOC)]);
}

// --- FUNCIÓN getElegibles() COMPLETAMENTE REESCRITA PARA MÁXIMA ESTABILIDAD ---
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

        if ($evento_info['tipo_evento'] == 'taller') {
            $sql_users = "SELECT u.id_usuario, u.nombre_completo FROM usuarios u JOIN inscripciones i ON u.id_usuario = i.id_usuario WHERE i.id_evento = ? AND i.estado = 'Inscrito'";
        } else {
            $sql_users = "SELECT DISTINCT u.id_usuario, u.nombre_completo FROM usuarios u JOIN asistencia a ON u.id_usuario = a.id_usuario WHERE a.id_evento = ?";
        }
        $stmt_users = $pdo->prepare($sql_users);
        $stmt_users->execute([$id_evento_filtro]);
        $usuarios_base = $stmt_users->fetchAll(PDO::FETCH_ASSOC);

        if (empty($usuarios_base)) {
            echo json_encode(['success' => true, 'usuarios' => []]);
            return;
        }
        
        $user_ids = array_column($usuarios_base, 'id_usuario');
        $placeholders = implode(',', array_fill(0, count($user_ids), '?'));
        
        $sql_details = "
            SELECT
                u.id_usuario,
                COALESCE(SUM(CASE WHEN a.hora_salida IS NOT NULL THEN 1 ELSE 0 END), 0) as asistencia_completa_count,
                COALESCE(SUM(TIME_TO_SEC(a.duracion)), 0) as duracion_total_seg,
                MAX(c.id_constancia) IS NOT NULL as emitida,
                MAX(c.ruta_archivo_pdf) as ruta_archivo_pdf
            FROM usuarios u
            LEFT JOIN asistencia a ON u.id_usuario = a.id_usuario AND a.id_evento = ?
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
                'asistencia_completa_count' => 0, 'duracion_total_seg' => 0, 'emitida' => false, 'ruta_archivo_pdf' => null
            ];

            $usuario['elegible'] = false;
            if ($evento_info['tipo_evento'] == 'conferencia' && $details['asistencia_completa_count'] > 0) {
                $usuario['elegible'] = true;
            } elseif ($evento_info['tipo_evento'] == 'taller' && $details['duracion_total_seg'] >= ($evento_info['horas_para_constancia'] * 3600)) {
                $usuario['elegible'] = true;
            }

            $usuario['emitida'] = (bool)$details['emitida'];
            $usuario['ruta_archivo_pdf'] = $details['ruta_archivo_pdf'];
            $resultado_final[] = $usuario;
        }

        echo json_encode(['success' => true, 'usuarios' => $resultado_final]);

    } catch (Exception $e) {
        http_response_code(500);
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
        $resultado = generarConstancia($id_usuario, $id_evento);
        echo json_encode($resultado);
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    }
}
?>
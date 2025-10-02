<?php
// php_admin/asistencia_controller.php
require_once '../php/conexion.php'; // Asumiendo que conexion.php está en php/ al nivel de php_admin/

header('Content-Type: application/json');

// --- Verificación de Sesión de Administrador ---
// session_start(); // Asegúrate de que la sesión esté iniciada si vas a usarla
// if (!isset($_SESSION['id_admin'])) {
//     http_response_code(403);
//     echo json_encode(['error' => 'Acceso denegado. Se requiere ser administrador.']);
//     exit;
// }

$action = $_REQUEST['action'] ?? '';

switch ($action) {
    case 'get_eventos_activos':
        getEventosActivos();
        break;
    case 'validar_qr':
        validarQr();
        break;
    case 'registrar_asistencia':
        registrarAsistencia();
        break;
    default:
        http_response_code(400);
        echo json_encode(['error' => 'Acción no válida.']);
        break;
}

function getEventosActivos() {
    global $pdo;
    $sql = "SELECT id_evento, nombre_evento, fecha_inicio
            FROM eventos
            WHERE fecha_inicio <= CURDATE() AND fecha_fin >= CURDATE() 
            ORDER BY fecha_inicio ASC, nombre_evento ASC";
    try {
        $stmt = $pdo->query($sql);
        $eventos = $stmt->fetchAll(PDO::FETCH_ASSOC);
        echo json_encode(['success' => true, 'eventos' => $eventos]);
    } catch (PDOException $e) {
        error_log("Error al obtener eventos activos: " . $e->getMessage());
        http_response_code(500);
        echo json_encode(['error' => 'Error al cargar lista de eventos.']);
    }
}

function validarQr() {
    global $pdo;
    $qr_data = $_POST['qr_data'] ?? '';
    $id_evento_seleccionado = isset($_POST['id_evento']) ? (int)$_POST['id_evento'] : 0;

    if (empty($qr_data) || empty($id_evento_seleccionado)) {
        http_response_code(400);
        echo json_encode(['error' => 'Datos QR o ID de evento faltantes.']);
        exit;
    }

    $response = [
        'success' => false,
        'id_usuario' => null, 'nombre_usuario' => null,
        'id_evento' => $id_evento_seleccionado, 'nombre_evento' => null,
        'inscrito' => false, 'mensaje_inscripcion' => 'No verificado',
        'ultimo_estado_asistencia' => null, 
        'fecha_ultima_accion' => null, 
        'hora_ultima_accion' => null,
        'mensaje_estado_general' => 'Esperando validación completa...',
        'puede_registrar_entrada' => false,
        'puede_registrar_salida' => false
    ];

    try {
        $stmtEvento = $pdo->prepare("SELECT nombre_evento FROM eventos WHERE id_evento = :id_evento");
        $stmtEvento->bindParam(':id_evento', $id_evento_seleccionado, PDO::PARAM_INT);
        $stmtEvento->execute();
        $evento = $stmtEvento->fetch(PDO::FETCH_ASSOC);
        if ($evento) {
            $response['nombre_evento'] = $evento['nombre_evento'];
        } else {
            echo json_encode(['error' => 'Evento seleccionado no válido.', 'success' => false]);
            exit;
        }

        $stmtUsuario = $pdo->prepare("SELECT id_usuario, nombre_completo FROM usuarios WHERE qr_code_data = :qr_data");
        $stmtUsuario->bindParam(':qr_data', $qr_data, PDO::PARAM_STR);
        $stmtUsuario->execute();
        $usuario = $stmtUsuario->fetch(PDO::FETCH_ASSOC);

        if (!$usuario) {
            $response['mensaje_estado_general'] = 'Código QR no reconocido o no asociado a un usuario.';
            echo json_encode($response);
            exit;
        }
        $response['id_usuario'] = $usuario['id_usuario'];
        $response['nombre_usuario'] = $usuario['nombre_completo'];

        $stmtInscripcion = $pdo->prepare("SELECT estado FROM inscripciones WHERE id_usuario = :id_usuario AND id_evento = :id_evento");
        $stmtInscripcion->bindParam(':id_usuario', $usuario['id_usuario'], PDO::PARAM_INT);
        $stmtInscripcion->bindParam(':id_evento', $id_evento_seleccionado, PDO::PARAM_INT);
        $stmtInscripcion->execute();
        $inscripcion = $stmtInscripcion->fetch(PDO::FETCH_ASSOC);

        if ($inscripcion && $inscripcion['estado'] === 'Inscrito') {
            $response['inscrito'] = true;
            $response['mensaje_inscripcion'] = 'Usuario INSCRITO en el evento.';
            $response['success'] = true; 

            $stmtAsistencia = $pdo->prepare(
                "SELECT id_asistencia, fecha, hora_entrada, hora_salida FROM asistencia 
                 WHERE id_usuario = :id_usuario AND id_evento = :id_evento
                 ORDER BY id_asistencia DESC LIMIT 1"
            );
            $stmtAsistencia->bindParam(':id_usuario', $usuario['id_usuario'], PDO::PARAM_INT);
            $stmtAsistencia->bindParam(':id_evento', $id_evento_seleccionado, PDO::PARAM_INT);
            $stmtAsistencia->execute();
            $asistencia_reciente = $stmtAsistencia->fetch(PDO::FETCH_ASSOC);

            if ($asistencia_reciente) {
                $response['fecha_ultima_accion'] = $asistencia_reciente['fecha'];
                if ($asistencia_reciente['hora_entrada'] && !$asistencia_reciente['hora_salida']) {
                    $response['ultimo_estado_asistencia'] = 'entrada_registrada';
                    $response['hora_ultima_accion'] = $asistencia_reciente['hora_entrada'];
                    $fecha_entrada_str = $asistencia_reciente['fecha'];
                    $hora_entrada_str = $asistencia_reciente['hora_entrada'];
                    $mensaje_detalle_entrada = ($fecha_entrada_str === date('Y-m-d')) ? "HOY" : "el {$fecha_entrada_str}";
                    
                    $response['mensaje_estado_general'] = "Inscrito. Entrada registrada {$mensaje_detalle_entrada} a las {$hora_entrada_str}. Puede registrar salida.";
                    $response['puede_registrar_salida'] = true;
                    $response['puede_registrar_entrada'] = false; 
                } elseif ($asistencia_reciente['hora_entrada'] && $asistencia_reciente['hora_salida']) {
                    $response['ultimo_estado_asistencia'] = 'salida_registrada';
                    $response['hora_ultima_accion'] = $asistencia_reciente['hora_salida'];
                    $response['mensaje_estado_general'] = 'Inscrito. Último ciclo Entrada/Salida completado. Puede registrar NUEVA entrada.';
                    $response['puede_registrar_entrada'] = true;
                    $response['puede_registrar_salida'] = false;
                } else {
                    $response['mensaje_estado_general'] = 'Inscrito. Estado de asistencia anterior inconsistente. Contactar administrador.';
                    $response['puede_registrar_entrada'] = false; 
                    $response['puede_registrar_salida'] = false;
                }
            } else {
                $response['ultimo_estado_asistencia'] = null;
                $response['mensaje_estado_general'] = 'Inscrito. Listo para registrar entrada.';
                $response['puede_registrar_entrada'] = true;
                $response['puede_registrar_salida'] = false;
            }
        } else { 
            $response['inscrito'] = false;
            $response['mensaje_inscripcion'] = $inscripcion ? 'Estado de inscripción: ' . $inscripcion['estado'] : 'Usuario NO INSCRITO en este evento.';
            $response['mensaje_estado_general'] = 'No se puede registrar asistencia: El usuario no está inscrito o la inscripción no es válida.';
        }

    } catch (PDOException $e) {
        error_log("Error en validarQr: " . $e->getMessage());
        $response['error'] = 'Error de base de datos durante la validación.';
        $response['success'] = false;
        http_response_code(500);
    }
    echo json_encode($response);
}


function registrarAsistencia() {
    global $pdo;
    $id_usuario = isset($_POST['id_usuario']) ? (int)$_POST['id_usuario'] : 0;
    $id_evento = isset($_POST['id_evento']) ? (int)$_POST['id_evento'] : 0;
    $tipo_registro = $_POST['tipo_registro'] ?? ''; 

    if (empty($id_usuario) || empty($id_evento) || !in_array($tipo_registro, ['entrada', 'salida'])) {
        http_response_code(400);
        echo json_encode(['success' => false, 'error' => 'Datos de usuario, evento o tipo de registro incorrectos.']);
        exit;
    }

    $stmtInscripcion = $pdo->prepare("SELECT estado FROM inscripciones WHERE id_usuario = :id_usuario AND id_evento = :id_evento");
    $stmtInscripcion->execute([':id_usuario' => $id_usuario, ':id_evento' => $id_evento]);
    $inscripcion = $stmtInscripcion->fetch(PDO::FETCH_ASSOC);

    if (!$inscripcion || $inscripcion['estado'] !== 'Inscrito') {
        http_response_code(403);
        echo json_encode(['success' => false, 'message' => 'Acción denegada: El usuario no está inscrito válidamente en este evento.']);
        exit;
    }

    $metodo_registro = 'QR_SCAN';
    $fecha_operacion = date('Y-m-d'); 
    $hora_operacion = date('H:i:s'); 
    $mensaje = '';
    $stmt = null; 

    try {
        $pdo->beginTransaction();

        if ($tipo_registro === 'entrada') {
            $stmtCheckOpenEntry = $pdo->prepare(
                "SELECT id_asistencia, fecha, hora_entrada FROM asistencia 
                 WHERE id_usuario = :id_usuario AND id_evento = :id_evento AND hora_salida IS NULL 
                 ORDER BY id_asistencia DESC LIMIT 1"
            );
            $stmtCheckOpenEntry->execute([':id_usuario' => $id_usuario, ':id_evento' => $id_evento]);
            $open_entry = $stmtCheckOpenEntry->fetch(PDO::FETCH_ASSOC);
            
            if ($open_entry) {
                $pdo->rollBack();
                $fecha_entrada_abierta = $open_entry['fecha'];
                $hora_entrada_abierta = $open_entry['hora_entrada'];
                $mensaje_error = "Ya existe un registro de ENTRADA ABIERTA del {$fecha_entrada_abierta} a las {$hora_entrada_abierta}. Debe registrar una SALIDA primero.";
                echo json_encode(['success' => false, 'message' => $mensaje_error]);
                exit;
            }

            $sql = "INSERT INTO asistencia (id_usuario, id_evento, fecha, hora_entrada, metodo_registro, estado_asistencia)
                    VALUES (:id_usuario, :id_evento, :fecha, :hora_entrada, :metodo_registro, 'Incompleta')";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([
                ':id_usuario' => $id_usuario,
                ':id_evento' => $id_evento,
                ':fecha' => $fecha_operacion, 
                ':hora_entrada' => $hora_operacion, 
                ':metodo_registro' => $metodo_registro
            ]);
            $mensaje = "Entrada registrada exitosamente a las {$hora_operacion} del {$fecha_operacion}.";

        } elseif ($tipo_registro === 'salida') {
            $sql_find_entry = "SELECT id_asistencia, fecha, hora_entrada FROM asistencia
                               WHERE id_usuario = :id_usuario AND id_evento = :id_evento 
                               AND hora_salida IS NULL
                               ORDER BY id_asistencia DESC LIMIT 1";
            $stmtFind = $pdo->prepare($sql_find_entry);
            $stmtFind->execute([':id_usuario' => $id_usuario, ':id_evento' => $id_evento]);
            $entrada_abierta = $stmtFind->fetch(PDO::FETCH_ASSOC);

            if ($entrada_abierta) {
                $fecha_entrada_obj = new DateTime($entrada_abierta['fecha'] . ' ' . $entrada_abierta['hora_entrada']);
                $fecha_salida_obj = new DateTime($fecha_operacion . ' ' . $hora_operacion); 
                
                if ($fecha_salida_obj < $fecha_entrada_obj) {
                    $pdo->rollBack();
                    echo json_encode(['success' => false, 'message' => 'Error: La hora de salida no puede ser anterior a la hora de entrada. Entrada: '.$entrada_abierta['fecha'] . ' ' . $entrada_abierta['hora_entrada'] . ', Intento Salida: ' . $fecha_operacion . ' ' . $hora_operacion]);
                    exit;
                }

                $intervalo = $fecha_entrada_obj->diff($fecha_salida_obj);

                // Calcular duración para formato MySQL TIME (HHH:MM:SS)
                $total_hours_for_mysql = ($intervalo->d * 24) + $intervalo->h;
                $minutes_for_mysql = $intervalo->i;
                $seconds_for_mysql = $intervalo->s;
                $duracion_mysql_format = sprintf('%d:%02d:%02d', $total_hours_for_mysql, $minutes_for_mysql, $seconds_for_mysql);

                // Formato de duración legible para el mensaje
                $mensaje_duracion_legible = '';
                if ($intervalo->d > 0) {
                    $mensaje_duracion_legible .= $intervalo->d . " día" . ($intervalo->d > 1 ? "s" : "") . " ";
                }
                // $intervalo->h, ->i, ->s son los componentes restantes después de los días completos
                $mensaje_duracion_legible .= sprintf('%02dh %02dm %02ds', $intervalo->h, $intervalo->i, $intervalo->s);


                $sql_update = "UPDATE asistencia SET hora_salida = :hora_salida, estado_asistencia = 'Completa', duracion = :duracion
                               WHERE id_asistencia = :id_asistencia";
                $stmt = $pdo->prepare($sql_update);
                $stmt->execute([
                    ':hora_salida' => $hora_operacion, 
                    ':duracion' => $duracion_mysql_format, // <--- USAR FORMATO CORRECTO PARA BD
                    ':id_asistencia' => $entrada_abierta['id_asistencia']
                ]);
                $mensaje = "Salida registrada exitosamente a las {$hora_operacion} del {$fecha_operacion}. Duración: " . trim($mensaje_duracion_legible) . ". (Entrada original: {$entrada_abierta['fecha']} {$entrada_abierta['hora_entrada']})";
            } else {
                $pdo->rollBack();
                echo json_encode(['success' => false, 'message' => 'No se encontró una entrada abierta para este usuario y evento. No se puede registrar salida.']);
                exit;
            }
        }

        if ($stmt && $stmt->rowCount() > 0) {
            $pdo->commit();
            echo json_encode(['success' => true, 'message' => $mensaje, 'hora_registrada' => $hora_operacion, 'tipo_registro_exitoso' => $tipo_registro]);
        } else {
            $pdo->rollBack();
            $accion = ($tipo_registro === 'entrada') ? 'registrar la entrada' : 'actualizar la salida';
            if (!$stmt) { 
                 echo json_encode(['success' => false, 'message' => 'Tipo de registro no reconocido internamente.']);
            } else {
                 echo json_encode(['success' => false, 'message' => "No se pudo {$accion} o no hubo cambios. Verifique el estado actual o si los datos ya eran correctos."]);
            }
        }

    } catch (PDOException $e) {
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }
        http_response_code(500);
        error_log("Error en registrarAsistencia: " . $e->getMessage());
        echo json_encode(['success' => false, 'error' => 'Error de base de datos al registrar asistencia: ' . $e->getMessage()]);
    }
}
?>
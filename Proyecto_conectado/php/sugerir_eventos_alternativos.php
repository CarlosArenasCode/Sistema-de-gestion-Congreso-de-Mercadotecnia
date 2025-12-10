<?php
/**
 * php/sugerir_eventos_alternativos.php
 * Sugiere eventos alternativos cuando un evento está lleno
 * Busca eventos similares por: tipo, fecha cercana, mismo ponente, con cupo disponible
 */

header('Content-Type: application/json');
require_once 'conexion.php';

try {
    // Obtener ID del evento lleno
    $id_evento = $_GET['id_evento'] ?? null;
    
    if (!$id_evento) {
        throw new Exception('ID de evento no proporcionado');
    }
    
    // Obtener información del evento original
    $stmt = $pdo->prepare("
        SELECT 
            tipo_evento,
            fecha_inicio,
            ponente,
            nombre_evento
        FROM eventos 
        WHERE id_evento = :id_evento
    ");
    $stmt->execute([':id_evento' => $id_evento]);
    $evento_original = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$evento_original) {
        throw new Exception('Evento no encontrado');
    }
    
    // Buscar eventos alternativos con las siguientes prioridades:
    // 1. Mismo tipo de evento
    // 2. Con cupo disponible
    // 3. Fecha futura
    // 4. Ordenados por: mismo ponente primero, luego por fecha más cercana
    
    $sql = "SELECT 
                e.id_evento,
                e.nombre_evento,
                e.descripcion,
                e.fecha_inicio,
                e.hora_inicio,
                e.fecha_fin,
                e.hora_fin,
                e.lugar,
                e.ponente,
                e.cupo_maximo,
                e.cupo_actual,
                e.genera_constancia,
                e.tipo_evento,
                e.horas_para_constancia,
                TO_CHAR(e.fecha_inicio, 'YYYY-MM-DD') as fecha_inicio_format,
                TO_CHAR(e.hora_inicio, 'HH24:MI') as hora_inicio_format,
                -- Calcular prioridad (mismo ponente = mayor prioridad)
                CASE 
                    WHEN UPPER(e.ponente) = UPPER(:ponente) THEN 1
                    ELSE 2
                END as prioridad,
                -- Calcular diferencia de días
                ABS(e.fecha_inicio - TO_DATE(:fecha_original, 'YYYY-MM-DD')) as dias_diferencia
            FROM eventos e
            WHERE e.id_evento != :id_evento  -- No incluir el evento lleno
                AND e.tipo_evento = :tipo_evento  -- Mismo tipo
                AND e.cupo_actual < e.cupo_maximo  -- Con cupo disponible
                AND e.fecha_inicio >= TRUNC(SYSDATE)  -- Solo eventos futuros
            ORDER BY prioridad ASC, dias_diferencia ASC, e.fecha_inicio ASC
            FETCH FIRST 5 ROWS ONLY";  -- Limitar a 5 sugerencias
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        ':id_evento' => $id_evento,
        ':tipo_evento' => $evento_original['tipo_evento'],
        ':ponente' => $evento_original['ponente'] ?? '',
        ':fecha_original' => $evento_original['fecha_inicio']
    ]);
    
    $eventos_alternativos = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Procesar resultados
    foreach ($eventos_alternativos as &$evento) {
        // Convertir CLOBs a strings
        if (isset($evento['descripcion']) && is_resource($evento['descripcion'])) {
            $evento['descripcion'] = stream_get_contents($evento['descripcion']);
        }
        
        // Calcular cupos disponibles
        $evento['cupos_disponibles'] = $evento['cupo_maximo'] - $evento['cupo_actual'];
        $evento['esta_lleno'] = $evento['cupo_actual'] >= $evento['cupo_maximo'];
        
        // Determinar si es del mismo ponente
        $evento['mismo_ponente'] = (
            !empty($evento_original['ponente']) && 
            !empty($evento['ponente']) &&
            strtoupper(trim($evento_original['ponente'])) === strtoupper(trim($evento['ponente']))
        );
        
        // Remover campos internos
        unset($evento['prioridad']);
        unset($evento['dias_diferencia']);
    }
    unset($evento);
    
    echo json_encode([
        'success' => true,
        'evento_original' => [
            'id_evento' => $id_evento,
            'nombre_evento' => $evento_original['nombre_evento'],
            'tipo_evento' => $evento_original['tipo_evento']
        ],
        'eventos_alternativos' => $eventos_alternativos,
        'total' => count($eventos_alternativos),
        'mensaje' => count($eventos_alternativos) > 0 
            ? 'Se encontraron ' . count($eventos_alternativos) . ' evento(s) alternativo(s) disponible(s)'
            : 'No hay eventos alternativos disponibles en este momento'
    ]);
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => 'Error al buscar eventos alternativos',
        'message' => $e->getMessage()
    ]);
}

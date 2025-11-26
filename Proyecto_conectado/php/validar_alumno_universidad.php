<?php
/**
 * php/validar_alumno_universidad.php
 * Endpoint para validar que un alumno existe en la base de datos
 * oficial de la universidad (simulada para pruebas locales)
 * 
 * USO:
 * POST: { "matricula": "A12345678" }
 * GET: ?matricula=A12345678
 */

require_once 'conexion.php';

header('Content-Type: application/json');

// Verificar que se recibió la matrícula
$matricula = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);
    $matricula = $data['matricula'] ?? '';
} elseif ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $matricula = $_GET['matricula'] ?? '';
} else {
    http_response_code(405);
    echo json_encode([
        'success' => false,
        'message' => 'Método no permitido. Use POST o GET.'
    ]);
    exit;
}

// Validar que se proporcionó una matrícula
if (empty($matricula)) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'valid' => false,
        'message' => 'Matrícula es requerida.'
    ]);
    exit;
}

// Limpiar y normalizar la matrícula
$matricula = strtoupper(trim($matricula));

try {
    // Consultar en la tabla de usuarios del sistema
    $sql = "SELECT 
                matricula,
                nombre_completo,
                email,
                semestre,
                rol,
                verificado,
                TO_CHAR(fecha_registro, 'YYYY-MM-DD') as fecha_registro
            FROM usuarios 
            WHERE UPPER(matricula) = :matricula";
    
    $stmt = $pdo->prepare($sql);
    $stmt->bindParam(':matricula', $matricula, PDO::PARAM_STR);
    $stmt->execute();
    
    $alumno = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$alumno) {
        // La matrícula NO existe en la base de datos
        http_response_code(404);
        echo json_encode([
            'success' => false,
            'valid' => false,
            'message' => 'La matrícula no se encuentra registrada en el sistema.',
            'error_code' => 'MATRICULA_NO_ENCONTRADA'
        ]);
        exit;
    }
    
    // Verificar que el usuario esté verificado
    if ($alumno['verificado'] != 1) {
        http_response_code(403);
        echo json_encode([
            'success' => false,
            'valid' => false,
            'message' => "El usuario no ha verificado su cuenta. Por favor verifica tu email antes de inscribirte.",
            'error_code' => 'USUARIO_NO_VERIFICADO',
            'data' => [
                'matricula' => $alumno['matricula'],
                'nombre_completo' => $alumno['nombre_completo'],
                'verificado' => (int)$alumno['verificado']
            ]
        ]);
        exit;
    }
    
    // Usuario válido y verificado
    http_response_code(200);
    echo json_encode([
        'success' => true,
        'valid' => true,
        'message' => 'Alumno validado correctamente.',
        'data' => [
            'matricula' => $alumno['matricula'],
            'nombre_completo' => $alumno['nombre_completo'],
            'email' => $alumno['email'],
            'semestre' => $alumno['semestre'] ? (int)$alumno['semestre'] : null,
            'rol' => $alumno['rol'],
            'verificado' => (int)$alumno['verificado'],
            'fecha_registro' => $alumno['fecha_registro']
        ]
    ]);
    
} catch (PDOException $e) {
    error_log("Error validando alumno universidad: " . $e->getMessage());
    
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'valid' => false,
        'message' => 'Error al validar la matrícula. Por favor intente nuevamente.',
        'error_code' => 'ERROR_BASE_DATOS'
    ]);
}
?>

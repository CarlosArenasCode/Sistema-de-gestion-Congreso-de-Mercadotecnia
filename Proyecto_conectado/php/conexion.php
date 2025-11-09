<?php
/**
 * Archivo de Conexión a Oracle Database
 * Sistema de Gestión del Congreso de Mercadotecnia
 * 
 * Configuración para Oracle Database 23ai Free usando PDO_OCI
 * Migrado desde MySQL a Oracle
 */

// Configuración de errores para desarrollo
// En producción, cambiar a false
ini_set('display_errors', 1);
error_reporting(E_ALL);

// Configurar charset UTF-8 para PHP
ini_set('default_charset', 'UTF-8');
mb_internal_encoding('UTF-8');
mb_http_output('UTF-8');

// Asegurar que todas las salidas usen UTF-8
header('Content-Type: text/html; charset=UTF-8');

// =====================================================
// CONFIGURACIÓN DE CONEXIÓN ORACLE
// =====================================================

// Obtener configuración desde variables de entorno o usar valores por defecto
$oracle_host = getenv('ORACLE_HOST') ?: 'oracle_db';
$oracle_port = getenv('ORACLE_PORT') ?: '1521';
$oracle_service = getenv('ORACLE_SERVICE') ?: 'FREEPDB1';
$oracle_user = getenv('ORACLE_USER') ?: 'congreso_user';
$oracle_pass = getenv('ORACLE_PASSWORD') ?: 'congreso_pass';
$charset = 'AL32UTF8';

// Construir DSN para Oracle
// Formato: oci:dbname=//host:port/service_name;charset=charset
$dsn = "oci:dbname=//{$oracle_host}:{$oracle_port}/{$oracle_service};charset={$charset}";

// =====================================================
// OPCIONES DE PDO
// =====================================================

$options = [
    // Modo de error: lanzar excepciones
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    
    // Modo de fetch por defecto: array asociativo
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    
    // Convertir nombres de columnas a minúsculas (Oracle usa mayúsculas por defecto)
    PDO::ATTR_CASE               => PDO::CASE_LOWER,
    
    // No emular prepared statements (mejor rendimiento con Oracle)
    PDO::ATTR_EMULATE_PREPARES   => false,
    
    // Habilitar autocommit
    PDO::ATTR_AUTOCOMMIT         => true,
    
    // Timeout de conexión (segundos)
    PDO::ATTR_TIMEOUT            => 30,
];

// =====================================================
// ESTABLECER CONEXIÓN
// =====================================================

try {
    // Crear conexión PDO
    $pdo = new PDO($dsn, $oracle_user, $oracle_pass, $options);
    
    // Configurar formato de fecha en Oracle para coincidir con MySQL
    // Esto facilita la migración de código existente
    $pdo->exec("ALTER SESSION SET NLS_DATE_FORMAT = 'YYYY-MM-DD'");
    $pdo->exec("ALTER SESSION SET NLS_TIMESTAMP_FORMAT = 'YYYY-MM-DD HH24:MI:SS'");
    $pdo->exec("ALTER SESSION SET NLS_TIMESTAMP_TZ_FORMAT = 'YYYY-MM-DD HH24:MI:SS TZH:TZM'");
    
    // Configurar zona horaria (ajustar según tu ubicación)
    $pdo->exec("ALTER SESSION SET TIME_ZONE = 'America/Mexico_City'");
    
} catch (PDOException $e) {
    // En caso de error, registrar y mostrar mensaje apropiado
    error_log('Error de conexión a Oracle: ' . $e->getMessage());
    
    // En producción, no mostrar detalles técnicos
    if (getenv('APP_ENV') === 'production') {
        die('Error de conexión a la base de datos. Por favor, contacte al administrador.');
    } else {
        // En desarrollo, mostrar detalles para debugging
        die('Error de conexión a Oracle Database:<br>' . 
            'Mensaje: ' . $e->getMessage() . '<br>' .
            'Código: ' . $e->getCode() . '<br>' .
            'DSN: ' . $dsn . '<br>' .
            'Usuario: ' . $oracle_user);
    }
}

// =====================================================
// FUNCIONES AUXILIARES PARA COMPATIBILIDAD
// =====================================================

/**
 * Función para obtener el último ID insertado
 * Oracle maneja esto diferente a MySQL
 * 
 * @param PDO $pdo Conexión PDO
 * @param string $sequence Nombre de la secuencia (ej: 'usuarios_seq')
 * @return int Último ID insertado
 */
function getLastInsertId($pdo, $table) {
    // Oracle usa IDENTITY columns, podemos obtener el último valor así:
    $sequences = [
        'usuarios' => 'ISEQ$$_76803',  // Se genera automáticamente
        'eventos' => 'ISEQ$$_76806',
        'administradores' => 'ISEQ$$_76809',
        // Agregar más según sea necesario
    ];
    
    // Alternativa: obtener directamente del último registro insertado
    try {
        $stmt = $pdo->query("SELECT MAX(id_" . $table . ") as last_id FROM " . $table);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result['last_id'];
    } catch (PDOException $e) {
        error_log("Error obteniendo último ID: " . $e->getMessage());
        return null;
    }
}

/**
 * Función para convertir booleanos de Oracle (0/1) a PHP (true/false)
 * 
 * @param int $value Valor numérico (0 o 1)
 * @return bool Valor booleano
 */
function oracleBoolToPHP($value) {
    return (bool)$value;
}

/**
 * Función para convertir booleanos de PHP a Oracle
 * 
 * @param bool $value Valor booleano
 * @return int Valor numérico (0 o 1)
 */
function phpBoolToOracle($value) {
    return $value ? 1 : 0;
}

/**
 * Función para formatear fechas de Oracle a formato deseado
 * 
 * @param string $oracleDate Fecha en formato Oracle
 * @param string $format Formato deseado (default: 'd/m/Y H:i')
 * @return string Fecha formateada
 */
function formatOracleDate($oracleDate, $format = 'd/m/Y H:i') {
    if (empty($oracleDate)) return '';
    
    try {
        $date = new DateTime($oracleDate);
        return $date->format($format);
    } catch (Exception $e) {
        error_log("Error formateando fecha: " . $e->getMessage());
        return $oracleDate;
    }
}

/**
 * Función para escapar LIKE patterns en Oracle
 * Oracle usa \ como escape character por defecto
 * 
 * @param string $string String a escapar
 * @return string String escapado
 */
function escapeLikeOracle($string) {
    return str_replace(['\\', '%', '_'], ['\\\\', '\\%', '\\_'], $string);
}

// =====================================================
// INICIAR SESIÓN
// =====================================================

// Iniciar sesión si no está iniciada
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// =====================================================
// VERIFICACIÓN DE CONEXIÓN (solo para debugging)
// =====================================================

// Descomentar para verificar que la conexión funciona
/*
try {
    $stmt = $pdo->query("SELECT 'Conexión exitosa a Oracle Database' AS mensaje FROM DUAL");
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    // echo $result['mensaje'];
} catch (PDOException $e) {
    error_log('Error de verificación: ' . $e->getMessage());
}
*/

?>
